<?php

namespace App\Http\Controllers\BAC;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\Status;
use App\Models\StatusHistory;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * BAC (Bids and Awards Committee) Controller
 * 
 * This controller handles the BAC-specific approval workflow.
 * BAC is the ONLY role authorized to give final approval on purchase requests.
 * 
 * Workflow:
 * 1. PR with status "Recommended" (103) arrives from Division Head
 * 2. BAC reviews and moves to "For BAC Approval" (104)
 * 3. BAC gives "Final Approval" (105) or "Cancels" (106)
 */
class PurchaseRequestController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display purchase requests pending BAC action.
     */
    public function index(Request $request)
    {
        // Verify BAC role using Gate
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can access this page.');
        }

        $tabs = [
            'recommended' => [
                'label' => 'Awaiting BAC Review',
                'statuses' => [Status::PR_RECOMMENDED],
            ],
            'for_approval' => [
                'label' => 'For Final Approval',
                'statuses' => [Status::PR_FOR_APPROVAL],
            ],
            'approved' => [
                'label' => 'Approved',
                'statuses' => [Status::PR_APPROVED],
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'statuses' => [Status::PR_CANCELLED],
            ],
        ];

        $activeTab = $request->query('tab', 'recommended');
        if (!array_key_exists($activeTab, $tabs)) {
            $activeTab = 'recommended';
        }

        $trackedStatusIds = collect($tabs)
            ->pluck('statuses')
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $query = PurchaseRequest::with(['status', 'requester.employee', 'division', 'section', 'recommender.employee'])
            ->orderByDesc('created_at');

        $selectedStatuses = $tabs[$activeTab]['statuses'] ?? null;
        if (is_array($selectedStatuses)) {
            $query->whereIn('status_id', $selectedStatuses);
        }

        $purchaseRequests = $query->paginate(10)->withQueryString();

        // Count for each tab
        $tabCounts = [];
        foreach ($tabs as $key => $definition) {
            $tabCounts[$key] = PurchaseRequest::whereIn('status_id', $definition['statuses'])->count();
        }

        $statuses = Status::whereIn('status_id', [
            Status::PR_RECOMMENDED,
            Status::PR_FOR_APPROVAL,
            Status::PR_APPROVED,
            Status::PR_CANCELLED,
        ])->get();

        return view('bac.purchase_requests.index', [
            'purchaseRequests' => $purchaseRequests,
            'statuses' => $statuses,
            'activeTab' => $activeTab,
            'tabCounts' => $tabCounts,
            'tabs' => collect($tabs)->mapWithKeys(fn ($def, $key) => [$key => $def['label']])->toArray(),
        ]);
    }

    /**
     * Display a single purchase request for BAC review.
     */
    public function show(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can access this page.');
        }

        $purchaseRequest->load([
            'items',
            'status',
            'fundAllocation',
            'requester.employee.position',
            'division',
            'section',
            'recommender.employee.position',
            'approver.employee.position',
            'statusHistory' => fn ($q) => $q->with('account.employee.position')->orderByDesc('changed_at'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $this->transformPurchaseRequest($purchaseRequest),
            ]);
        }

        return view('bac.purchase_requests.show', compact('purchaseRequest'));
    }

    /**
     * BAC Final Approval Action
     * 
     * This is the ONLY method that can grant final approval.
     * Protected by Gate and Policy to ensure only BAC can access.
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        // STRICT: Gate check for BAC role
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members are authorized to approve purchase requests.');
        }

        // Policy check for this specific PR
        $this->authorize('approve', $purchaseRequest);

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
            'fund_cluster' => 'nullable|string|max:50',
        ]);

        $currentStatus = (int) $purchaseRequest->status_id;

        // Validate current status allows approval
        if (!in_array($currentStatus, [Status::PR_RECOMMENDED, Status::PR_FOR_APPROVAL], true)) {
            throw ValidationException::withMessages([
                'status' => 'This purchase request cannot be approved in its current state.',
            ]);
        }

        // Prevent self-approval
        if ($purchaseRequest->account_id === Auth::id()) {
            throw ValidationException::withMessages([
                'status' => 'You cannot approve a purchase request you submitted.',
            ]);
        }

        DB::transaction(function () use ($purchaseRequest, $validated, $currentStatus) {
            $accountId = Auth::id();
            $now = now();
            $purchaseRequest->loadMissing('fundAllocation');

            $liveFundsAvailable = $purchaseRequest->fundAllocation?->syncRemainingAmount();
            if ($liveFundsAvailable !== null && (float) $liveFundsAvailable < (float) $purchaseRequest->total_estimated_cost) {
                throw ValidationException::withMessages([
                    'funds_available' => 'Funds available must cover the total estimated cost.',
                ]);
            }

            // Update PR to Approved status
            $purchaseRequest->update([
                'status_id' => Status::PR_APPROVED,
                'approved_by' => $accountId,
                'approved_at' => $now,
                'approval_remarks' => $validated['remarks'] ?? null,
                'fund_cluster' => $validated['fund_cluster'] ?? $purchaseRequest->fund_cluster ?? $purchaseRequest->fundAllocation?->fund_cluster,
                'funds_available' => $liveFundsAvailable ?? $purchaseRequest->funds_available,
            ]);

            // Record status history
            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => $currentStatus,
                'new_status_id' => Status::PR_APPROVED,
                'changed_by' => $accountId,
                'remarks' => $validated['remarks'] ?? 'BAC final approval granted.',
                'changed_at' => $now,
            ]);

            // Create audit log
            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_requests',
                'action' => 'BAC_APPROVAL',
                'description' => "BAC approved purchase request {$purchaseRequest->pr_no}",
                'log_time' => $now,
            ]);

            // Notify the requester
            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => "Your purchase request {$purchaseRequest->pr_no} has received BAC final approval.",
                'type' => 'success',
            ]);

            // Notify custodians to proceed with PO generation
            $custodians = Account::where('role', 'custodian')->pluck('account_id');
            foreach ($custodians as $custodianId) {
                Notification::create([
                    'recipient_id' => $custodianId,
                    'sender_id' => $accountId,
                    'table_name' => 'purchase_requests',
                    'record_id' => $purchaseRequest->pr_no,
                    'message' => "Purchase request {$purchaseRequest->pr_no} approved by BAC. Ready for purchase order generation.",
                    'type' => 'task',
                ]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase request approved successfully.',
                'data' => $this->transformPurchaseRequest($purchaseRequest->fresh()),
            ]);
        }

        return redirect()
            ->route('bac.requests.show', $purchaseRequest)
            ->with('success', 'Purchase request approved successfully.');
    }

    /**
     * Move PR to "For BAC Approval" status for detailed review.
     */
    public function moveToReview(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can access this action.');
        }

        $currentStatus = (int) $purchaseRequest->status_id;

        if ($currentStatus !== Status::PR_RECOMMENDED) {
            throw ValidationException::withMessages([
                'status' => 'Only recommended purchase requests can be moved to BAC review.',
            ]);
        }

        DB::transaction(function () use ($purchaseRequest, $currentStatus) {
            $accountId = Auth::id();
            $now = now();

            $purchaseRequest->update([
                'status_id' => Status::PR_FOR_APPROVAL,
            ]);

            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => $currentStatus,
                'new_status_id' => Status::PR_FOR_APPROVAL,
                'changed_by' => $accountId,
                'remarks' => 'Moved to BAC review queue.',
                'changed_at' => $now,
            ]);

            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_requests',
                'action' => 'STATUS_UPDATE',
                'description' => "Moved purchase request {$purchaseRequest->pr_no} to BAC review",
                'log_time' => $now,
            ]);

            // Notify requester
            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => "Your purchase request {$purchaseRequest->pr_no} is now under BAC review.",
                'type' => 'info',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase request moved to BAC review.',
            ]);
        }

        return back()->with('success', 'Purchase request moved to BAC review.');
    }

    /**
     * Cancel/Reject a purchase request.
     */
    public function cancel(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can access this action.');
        }

        $this->authorize('cancel', $purchaseRequest);

        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $currentStatus = (int) $purchaseRequest->status_id;

        if (!in_array($currentStatus, [Status::PR_RECOMMENDED, Status::PR_FOR_APPROVAL], true)) {
            throw ValidationException::withMessages([
                'status' => 'This purchase request cannot be cancelled in its current state.',
            ]);
        }

        DB::transaction(function () use ($purchaseRequest, $validated, $currentStatus) {
            $accountId = Auth::id();
            $now = now();

            $purchaseRequest->update([
                'status_id' => Status::PR_CANCELLED,
                'approval_remarks' => $validated['remarks'],
            ]);

            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => $currentStatus,
                'new_status_id' => Status::PR_CANCELLED,
                'changed_by' => $accountId,
                'remarks' => $validated['remarks'],
                'changed_at' => $now,
            ]);

            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_requests',
                'action' => 'BAC_REJECTION',
                'description' => "BAC cancelled purchase request {$purchaseRequest->pr_no}. Reason: {$validated['remarks']}",
                'log_time' => $now,
            ]);

            // Notify requester
            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => "Your purchase request {$purchaseRequest->pr_no} was cancelled by BAC.\nReason: {$validated['remarks']}",
                'type' => 'warning',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase request cancelled.',
            ]);
        }

        return redirect()
            ->route('bac.requests.index')
            ->with('success', 'Purchase request cancelled.');
    }

    /**
     * Transform purchase request for JSON response.
     */
    protected function transformPurchaseRequest(PurchaseRequest $pr): array
    {
        $liveFundsAvailable = $pr->fundAllocation?->syncRemainingAmount();

        $approvalHistory = $pr->statusHistory
            ->first(fn ($history) => (int) $history->new_status_id === Status::PR_APPROVED);

        $approverAccount = $pr->approver ?: $approvalHistory?->account;

        $requesterSignature = $this->signatureDataUri($pr->requester?->employee?->signature);
        $recommendedSignature = $this->signatureDataUri($pr->recommender?->employee?->signature);
        $approvedSignature = $this->signatureDataUri($approverAccount?->employee?->signature);

        $approvedByName = $approverAccount?->employee
            ? collect([
                $approverAccount->employee->first_name,
                $approverAccount->employee->middle_name,
                $approverAccount->employee->last_name,
                $approverAccount->employee->suffix,
            ])->filter()->implode(' ')
            : null;

        if (!$approvedByName && $approverAccount?->username) {
            $approvedByName = $approverAccount->username;
        }

        $approvedAtValue = $pr->approved_at ?: $approvalHistory?->changed_at;

        return [
            'pr_no' => $pr->pr_no,
            'status' => $pr->status?->status_name,
            'status_id' => $pr->status_id,
            'purpose' => $pr->purpose,
            'sai_no' => $pr->sai_no,
            'alobs_no' => $pr->alobs_no,
            'fund_cluster' => $pr->fund_cluster ?: $pr->fundAllocation?->fund_cluster,
            'funds_available' => $liveFundsAvailable ?? $pr->funds_available,
            'total_estimated_cost' => $pr->total_estimated_cost,
            'division' => $pr->division?->division_name,
            'section' => $pr->section?->section_name,
            'requester' => $pr->requester?->employee
                ? collect([
                    $pr->requester->employee->first_name,
                    $pr->requester->employee->middle_name,
                    $pr->requester->employee->last_name,
                    $pr->requester->employee->suffix,
                ])->filter()->implode(' ')
                : 'Unknown',
            'requester_signature' => $requesterSignature,
            'requester_designation' => $pr->requester?->employee?->position?->position_title,
            'recommended_by_name' => $pr->recommender?->employee
                ? collect([
                    $pr->recommender->employee->first_name,
                    $pr->recommender->employee->middle_name,
                    $pr->recommender->employee->last_name,
                    $pr->recommender->employee->suffix,
                ])->filter()->implode(' ')
                : null,
            'recommended_signature' => $recommendedSignature,
            'recommended_designation' => $pr->recommender?->employee?->position?->position_title,
            'recommended_at' => $pr->recommended_at instanceof \DateTimeInterface
                ? $pr->recommended_at->toDateTimeString()
                : ($pr->recommended_at ? (string) $pr->recommended_at : null),
            'recommendation_remarks' => $pr->recommendation_remarks,
            'approved_by' => $approvedByName,
            'approved_signature' => $approvedSignature,
            'approved_designation' => $approverAccount?->employee?->position?->position_title,
            'approved_at' => $approvedAtValue instanceof \DateTimeInterface
                ? $approvedAtValue->toDateTimeString()
                : ($approvedAtValue ? (string) $approvedAtValue : null),
            'approval_remarks' => $pr->approval_remarks,
            'created_at' => $pr->created_at instanceof \DateTimeInterface
                ? $pr->created_at->toDateTimeString()
                : ($pr->created_at ? (string) $pr->created_at : null),
            'items' => $pr->items->map(fn ($item) => [
                'pri_id' => $item->pri_id,
                'item_description' => $item->item_description,
                'original_description' => $item->original_description,
                'alternate_description' => $item->alternate_description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'stock_property_no' => $item->stock_property_no,
                'estimated_unit_cost' => $item->estimated_unit_cost,
                'estimated_total_cost' => $item->estimated_total_cost,
                'fulfillment_status' => $item->fulfillment_status,
                'employee_decision' => $item->employee_decision,
                'employee_wait_until' => $item->employee_wait_until?->toDateString(),
                'employee_wait_note' => $item->employee_wait_note,
                'suggested_at' => $item->suggested_at?->toDateTimeString(),
                'removed_at' => $item->removed_at?->toDateTimeString(),
                'removal_reason' => $item->removal_reason,
            ])->toArray(),
        ];
    }

    private function signatureDataUri($signature): ?string
    {
        if ($signature === null || $signature === '') {
            return null;
        }

        if (is_resource($signature)) {
            $signature = stream_get_contents($signature);
        }

        if (! is_string($signature) || $signature === '') {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($signature);
    }

    /**
     * BAC suggests an alternative item for a specific item in the PR.
     * 
     * This notifies the requester who can then:
     * 1. Accept the alternative (item_description gets replaced)
     * 2. Wait for the original until a specific date
     */
    public function suggestAlternative(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can suggest alternatives.');
        }

        $validated = $request->validate([
            'pri_id' => 'required|integer',
            'alternate_description' => 'required|string|max:500',
            'unit_cost' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $item = $purchaseRequest->items()->where('pri_id', $validated['pri_id'])->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in this purchase request.',
            ], 404);
        }

        // Verify PR is in a status that allows suggesting alternatives
        $currentStatus = (int) $purchaseRequest->status_id;
        if (!in_array($currentStatus, [Status::PR_RECOMMENDED, Status::PR_FOR_APPROVAL], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot suggest alternatives for this purchase request in its current state.',
            ], 422);
        }

        // Check if item already has a pending alternative
        if ($item->hasPendingAlternative()) {
            return response()->json([
                'success' => false,
                'message' => 'This item already has a pending alternative awaiting the requester\'s response.',
            ], 422);
        }

        $accountId = Auth::id();
        $now = now();
        $newUnitCost = (float) $validated['unit_cost'];
        $newTotalCost = $newUnitCost * $item->quantity;

        DB::transaction(function () use ($item, $validated, $purchaseRequest, $accountId, $now, $newUnitCost, $newTotalCost) {
            // Store original description if not already saved
            if (!$item->original_description) {
                $item->original_description = $item->item_description;
            }

            $item->update([
                'alternate_description' => $validated['alternate_description'],
                'fulfillment_status' => 'alternative',
                'suggested_by' => $accountId,
                'suggested_at' => $now,
                'employee_decision' => null,
                'employee_decided_at' => null,
                'employee_wait_until' => null,
                'employee_wait_note' => null,
                // Store the new cost with the alternative
                'estimated_unit_cost' => $newUnitCost,
                'estimated_total_cost' => $newTotalCost,
            ]);

            // Update PR total
            $purchaseRequest->total_estimated_cost = $purchaseRequest->items()
                ->whereNull('removed_at')
                ->sum('estimated_total_cost');
            $purchaseRequest->save();

            // Log the action
            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_request_items',
                'action' => 'SUGGEST_ALTERNATIVE',
                'description' => sprintf(
                    'BAC suggested alternative for item in PR %s: "%s" → "%s" (Cost: ₱%s)',
                    $purchaseRequest->pr_no,
                    \Illuminate\Support\Str::limit($item->original_description, 50),
                    \Illuminate\Support\Str::limit($validated['alternate_description'], 50),
                    number_format($newUnitCost, 2)
                ),
                'log_time' => $now,
            ]);

            // Notify the requester
            $message = sprintf(
                'BAC has suggested an alternative for an item in your purchase request %s. Original: "%s". Suggested alternative: "%s" at ₱%s/unit. Please review and respond.',
                $purchaseRequest->pr_no,
                \Illuminate\Support\Str::limit($item->original_description, 60),
                \Illuminate\Support\Str::limit($validated['alternate_description'], 60),
                number_format($newUnitCost, 2)
            );

            if (!empty($validated['remarks'])) {
                $message .= ' Note: ' . $validated['remarks'];
            }

            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => $message,
                'type' => 'action_required',
                'is_read' => false,
                'created_at' => $now,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Alternative suggestion sent to the requester.',
            'data' => [
                'pri_id' => $item->pri_id,
                'original_description' => $item->fresh()->original_description,
                'alternate_description' => $item->fresh()->alternate_description,
                'fulfillment_status' => $item->fresh()->fulfillment_status,
                'estimated_unit_cost' => $item->fresh()->estimated_unit_cost,
                'estimated_total_cost' => $item->fresh()->estimated_total_cost,
            ],
        ]);
    }

    /**
     * Update item costs (for BAC in review/for-approval statuses)
     */
    public function updateItemCosts(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Only BAC can update costs
        $currentStatus = (int) $purchaseRequest->status_id;
        if (!in_array($currentStatus, [Status::PR_RECOMMENDED, Status::PR_FOR_APPROVAL], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Item costs can only be updated while the request is under BAC review.',
            ], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.pri_id' => 'required|integer',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $accountId = Auth::id();
        $now = now();

        DB::transaction(function () use ($validated, $purchaseRequest, $accountId, $now) {
            foreach ($validated['items'] as $itemData) {
                $item = $purchaseRequest->items()
                    ->where('pri_id', $itemData['pri_id'])
                    ->whereNull('removed_at')
                    ->first();

                if ($item) {
                    $newUnitCost = (float) $itemData['unit_cost'];
                    $newTotalCost = $newUnitCost * $item->quantity;

                    $item->update([
                        'estimated_unit_cost' => $newUnitCost,
                        'estimated_total_cost' => $newTotalCost,
                    ]);
                }
            }

            // Update PR total
            $purchaseRequest->total_estimated_cost = $purchaseRequest->items()
                ->whereNull('removed_at')
                ->sum('estimated_total_cost');
            $purchaseRequest->save();

            // Log the action
            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_requests',
                'action' => 'UPDATE_ITEM_COSTS',
                'description' => sprintf(
                    'BAC updated item costs for PR %s. New total: ₱%s',
                    $purchaseRequest->pr_no,
                    number_format($purchaseRequest->total_estimated_cost, 2)
                ),
                'log_time' => $now,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Item costs updated successfully.',
            'data' => [
                'total_estimated_cost' => $purchaseRequest->fresh()->total_estimated_cost,
            ],
        ]);
    }
}
