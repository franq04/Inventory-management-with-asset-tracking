<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\Status;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Procurement Workflow Service
 * 
 * Centralizes the procurement workflow logic and status transitions.
 * This service ensures consistent handling of the workflow across all controllers.
 * 
 * Workflow Diagram:
 * 
 *   ┌─────────────────────────────────────────────────────────────────────┐
 *   │                    PROCUREMENT WORKFLOW                             │
 *   └─────────────────────────────────────────────────────────────────────┘
 *   
 *   [EMPLOYEE]                                                             
 *       │                                                                  
 *       ▼                                                                  
 *   ┌─────────────────┐                                                   
 *   │ For Recommendation │ (102)                                          
 *   │   [Division Head]  │                                                 
 *   └─────────────────┘                                                   
 *           │                                                              
 *       ┌───┴───┐                                                          
 *       ▼       ▼                                                          
 *   [RECOMMEND] [CANCEL]                                                   
 *       │         │                                                        
 *       ▼         ▼                                                        
 *   ┌──────────┐  ┌──────────┐                                            
 *   │Recommended│  │Cancelled │ (106)                                     
 *   │   (103)   │  └──────────┘                                            
 *   └──────────┘                                                           
 *       │                                                                  
 *       ▼                                                                  
 *   ┌─────────────────┐                                                   
 *   │ For BAC Approval │ (104)                                            
 *   │     [BAC Only]   │                                                   
 *   └─────────────────┘                                                   
 *           │                                                              
 *       ┌───┴───┐                                                          
 *       ▼       ▼                                                          
 *   [APPROVE]  [CANCEL]                                                    
 *       │         │                                                        
 *       ▼         ▼                                                        
 *   ┌──────────┐  ┌──────────┐                                            
 *   │ Approved │  │Cancelled │                                            
 *   │  (105)   │  │  (106)   │                                            
 *   └──────────┘  └──────────┘                                            
 *       │                                                                  
 *       ▼                                                                  
 *   [CUSTODIAN: Generate PO]                                               
 *       │                                                                  
 *       ▼                                                                  
 *   [SUPPLIER: Delivery]                                                   
 *       │                                                                  
 *       ▼                                                                  
 *   [INSPECTOR: Accept/Reject]                                             
 *       │                                                                  
 *       ▼                                                                  
 *   [CUSTODIAN: Create PQS Record]                                        
 */
class ProcurementWorkflowService
{
    /**
     * Valid status transitions matrix.
     * Key: Current status ID
     * Value: Array of allowed target status IDs with required role
     */
    public const TRANSITIONS = [
        // For Recommendation -> Recommended (Division Head) or Cancelled
        Status::PR_FOR_RECOMMENDATION => [
            Status::PR_RECOMMENDED => ['division_head'],
            Status::PR_CANCELLED => ['division_head'],
        ],
        
        // Recommended -> For BAC Approval (BAC) or Cancelled
        Status::PR_RECOMMENDED => [
            Status::PR_FOR_APPROVAL => ['bac'],
            Status::PR_CANCELLED => ['bac'],
        ],
        
        // For BAC Approval -> Approved (BAC Only) or Cancelled
        Status::PR_FOR_APPROVAL => [
            Status::PR_APPROVED => ['bac'],  // STRICT: Only BAC
            Status::PR_CANCELLED => ['bac'],
        ],
        
        // Approved -> PO Created (System/Custodian)
        Status::PR_APPROVED => [
            Status::PR_PO_CREATED => ['custodian', 'system'],
        ],
    ];

    /**
     * Check if a status transition is allowed.
     */
    public function canTransition(PurchaseRequest $pr, int $targetStatus, string $role): bool
    {
        $currentStatus = (int) $pr->status_id;
        
        if (!isset(self::TRANSITIONS[$currentStatus])) {
            return false;
        }
        
        $allowedTransitions = self::TRANSITIONS[$currentStatus];
        
        if (!isset($allowedTransitions[$targetStatus])) {
            return false;
        }
        
        $allowedRoles = $allowedTransitions[$targetStatus];
        
        return in_array($role, $allowedRoles, true);
    }

    /**
     * Perform a status transition with full audit trail.
     */
    public function transition(
        PurchaseRequest $pr,
        int $targetStatus,
        Account $actor,
        ?string $remarks = null
    ): PurchaseRequest {
        $currentStatus = (int) $pr->status_id;
        $role = $actor->role;
        
        if (!$this->canTransition($pr, $targetStatus, $role)) {
            throw new InvalidArgumentException(
                "Invalid transition from status {$currentStatus} to {$targetStatus} for role {$role}"
            );
        }
        
        return DB::transaction(function () use ($pr, $targetStatus, $actor, $remarks, $currentStatus) {
            $now = now();
            
            // Update PR status
            $updateData = ['status_id' => $targetStatus];
            
            // Set actor-specific fields based on transition
            if ($targetStatus === Status::PR_RECOMMENDED) {
                $updateData['recommended_by'] = $actor->account_id;
                $updateData['recommended_at'] = $now;
                $updateData['recommendation_remarks'] = $remarks;
            } elseif ($targetStatus === Status::PR_APPROVED) {
                $updateData['approved_by'] = $actor->account_id;
                $updateData['approved_at'] = $now;
                $updateData['approval_remarks'] = $remarks;
            } elseif ($targetStatus === Status::PR_CANCELLED) {
                $updateData['approval_remarks'] = $remarks;
            }
            
            $pr->update($updateData);
            
            // Record status history
            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $pr->pr_no,
                'old_status_id' => $currentStatus,
                'new_status_id' => $targetStatus,
                'changed_by' => $actor->account_id,
                'remarks' => $remarks ?? $this->getDefaultRemarks($targetStatus),
                'changed_at' => $now,
            ]);
            
            // Audit log
            AuditLog::create([
                'account_id' => $actor->account_id,
                'table_name' => 'purchase_requests',
                'action' => 'STATUS_UPDATE',
                'description' => "Updated purchase request {$pr->pr_no} status from {$currentStatus} to {$targetStatus}",
                'log_time' => $now,
            ]);
            
            // Send notifications
            $this->sendTransitionNotifications($pr, $targetStatus, $actor);
            
            return $pr->fresh();
        });
    }

    /**
     * Get default remarks for a status transition.
     */
    protected function getDefaultRemarks(int $targetStatus): string
    {
        return match ($targetStatus) {
            Status::PR_RECOMMENDED => 'Division Head recommendation provided.',
            Status::PR_FOR_APPROVAL => 'Moved to BAC review queue.',
            Status::PR_APPROVED => 'BAC final approval granted.',
            Status::PR_CANCELLED => 'Request cancelled.',
            Status::PR_PO_CREATED => 'Purchase order generated.',
            default => 'Status updated.',
        };
    }

    /**
     * Send notifications based on status transition.
     */
    protected function sendTransitionNotifications(PurchaseRequest $pr, int $targetStatus, Account $actor): void
    {
        // Always notify the requester
        if ($pr->account_id !== $actor->account_id) {
            Notification::create([
                'recipient_id' => $pr->account_id,
                'sender_id' => $actor->account_id,
                'table_name' => 'purchase_requests',
                'record_id' => $pr->pr_no,
                'message' => $this->getRequesterNotificationMessage($pr, $targetStatus),
                'type' => $this->getNotificationType($targetStatus),
            ]);
        }
        
        // Notify next role in workflow
        $this->notifyNextActor($pr, $targetStatus, $actor);
    }

    /**
     * Get notification message for requester.
     */
    protected function getRequesterNotificationMessage(PurchaseRequest $pr, int $targetStatus): string
    {
        return match ($targetStatus) {
            Status::PR_RECOMMENDED => "Your purchase request {$pr->pr_no} has been recommended by Division Head and forwarded to BAC.",
            Status::PR_FOR_APPROVAL => "Your purchase request {$pr->pr_no} is now under BAC review.",
            Status::PR_APPROVED => "Your purchase request {$pr->pr_no} has received BAC final approval.",
            Status::PR_CANCELLED => "Your purchase request {$pr->pr_no} has been cancelled.",
            default => "Your purchase request {$pr->pr_no} status has been updated.",
        };
    }

    /**
     * Get notification type based on status.
     */
    protected function getNotificationType(int $targetStatus): string
    {
        return match ($targetStatus) {
            Status::PR_APPROVED => 'success',
            Status::PR_CANCELLED => 'warning',
            default => 'info',
        };
    }

    /**
     * Notify the next actor in the workflow.
     */
    protected function notifyNextActor(PurchaseRequest $pr, int $targetStatus, Account $actor): void
    {
        $nextRole = match ($targetStatus) {
            Status::PR_RECOMMENDED => 'bac',
            Status::PR_APPROVED => 'custodian',
            default => null,
        };
        
        if (!$nextRole) {
            return;
        }
        
        $message = match ($targetStatus) {
            Status::PR_RECOMMENDED => "Purchase request {$pr->pr_no} has been recommended and requires BAC approval.",
            Status::PR_APPROVED => "Purchase request {$pr->pr_no} approved by BAC. Ready for purchase order generation.",
            default => "Purchase request {$pr->pr_no} requires your attention.",
        };
        
        $recipients = Account::where('role', $nextRole)->pluck('account_id');
        
        foreach ($recipients as $recipientId) {
            Notification::create([
                'recipient_id' => $recipientId,
                'sender_id' => $actor->account_id,
                'table_name' => 'purchase_requests',
                'record_id' => $pr->pr_no,
                'message' => $message,
                'type' => 'task',
            ]);
        }
    }

    /**
     * Get the workflow state for a purchase request.
     */
    public function getWorkflowState(PurchaseRequest $pr): array
    {
        $currentStatus = (int) $pr->status_id;
        $availableTransitions = self::TRANSITIONS[$currentStatus] ?? [];
        
        return [
            'current_status' => $currentStatus,
            'current_status_name' => $pr->status?->status_name,
            'available_transitions' => array_keys($availableTransitions),
            'next_actor' => $this->getNextActor($currentStatus),
            'is_terminal' => empty($availableTransitions),
            'can_be_approved' => isset($availableTransitions[Status::PR_APPROVED]),
            'can_be_cancelled' => isset($availableTransitions[Status::PR_CANCELLED]),
        ];
    }

    /**
     * Get the next actor role for a status.
     */
    protected function getNextActor(int $status): ?string
    {
        return match ($status) {
            Status::PR_FOR_RECOMMENDATION => 'Division Head',
            Status::PR_RECOMMENDED, Status::PR_FOR_APPROVAL => 'BAC',
            Status::PR_APPROVED => 'Custodian',
            default => null,
        };
    }
}
