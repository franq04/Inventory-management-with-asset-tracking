<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\PurchaseRequest;
use App\Models\Status;

/**
 * Policy for Purchase Request authorization.
 * 
 * This policy enforces Role-Based Access Control (RBAC) for the procurement workflow:
 * - Employee: Can create and view own PRs
 * - Division Head: Can recommend PRs (transition from For Recommendation → Recommended)
 * - BAC: Can give final approval (transition from Recommended → For BAC Approval → Approved)
 * - Inspector: Can view PRs for inspection context
 * - Custodian: Can view all PRs and manage inventory
 */
class PurchaseRequestPolicy
{
    protected function resolveDivisionId(Account $user): ?int
    {
        $user->loadMissing('employee.section');

        return $user->employee?->section?->division_id
            ? (int) $user->employee->section->division_id
            : null;
    }

    protected function canDivisionHeadAccessPurchaseRequest(Account $user, PurchaseRequest $purchaseRequest): bool
    {
        $divisionId = $this->resolveDivisionId($user);

        if (! $divisionId) {
            return false;
        }

        return (int) $purchaseRequest->division_id === $divisionId;
    }

    /**
     * Determine if the user can view any purchase requests.
     */
    public function viewAny(Account $user): bool
    {
        return in_array($user->role, ['employee', 'division_head', 'bac', 'custodian', 'iac'], true);
    }

    /**
     * Determine if the user can view the purchase request.
     */
    public function view(Account $user, PurchaseRequest $purchaseRequest): bool
    {
        // Employees can only view their own requests
        if ($user->role === 'employee') {
            return $purchaseRequest->account_id === $user->account_id;
        }

        // Division heads can only view requests in their own division
        if ($user->role === 'division_head') {
            return $this->canDivisionHeadAccessPurchaseRequest($user, $purchaseRequest);
        }

        // BAC, custodians, and inspectors can view all
        return in_array($user->role, ['bac', 'custodian', 'iac'], true);
    }

    /**
     * Determine if the user can create purchase requests.
     */
    public function create(Account $user): bool
    {
        return $user->role === 'employee';
    }

    /**
     * Determine if the user can recommend the purchase request.
     * Only Division Heads can recommend PRs that are "For Recommendation".
     */
    public function recommend(Account $user, PurchaseRequest $purchaseRequest): bool
    {
        if ($user->role !== 'division_head') {
            return false;
        }

        if (! $this->canDivisionHeadAccessPurchaseRequest($user, $purchaseRequest)) {
            return false;
        }

        // Can only recommend PRs that are awaiting recommendation
        return (int) $purchaseRequest->status_id === Status::PR_FOR_RECOMMENDATION;
    }

    /**
     * Determine if the user can approve the purchase request (BAC final approval).
     * ONLY BAC role can give final approval.
     */
    public function approve(Account $user, PurchaseRequest $purchaseRequest): bool
    {
        // STRICT: Only BAC can approve
        if ($user->role !== 'bac') {
            return false;
        }

        // Can only approve PRs that are recommended or for BAC approval
        return in_array((int) $purchaseRequest->status_id, [
            Status::PR_RECOMMENDED,
            Status::PR_FOR_APPROVAL,
        ], true);
    }

    /**
     * Determine if the user can cancel the purchase request.
     */
    public function cancel(Account $user, PurchaseRequest $purchaseRequest): bool
    {
        // Division heads can cancel during recommendation phase
        if ($user->role === 'division_head') {
            if (! $this->canDivisionHeadAccessPurchaseRequest($user, $purchaseRequest)) {
                return false;
            }

            return (int) $purchaseRequest->status_id === Status::PR_FOR_RECOMMENDATION;
        }

        // BAC can cancel during approval phase
        if ($user->role === 'bac') {
            return in_array((int) $purchaseRequest->status_id, [
                Status::PR_RECOMMENDED,
                Status::PR_FOR_APPROVAL,
            ], true);
        }

        return false;
    }

    /**
     * Determine if the user can update the purchase request status.
     */
    public function updateStatus(Account $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->recommend($user, $purchaseRequest) 
            || $this->approve($user, $purchaseRequest)
            || $this->cancel($user, $purchaseRequest);
    }
}
