<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\InspectionReport;
use App\Models\Status;

/**
 * Policy for Inspection Report authorization.
 * 
 * RBAC for inspection workflow:
 * - Inspector (IAC): Can inspect delivered items and mark as accepted/rejected
 * - Custodian: Can view inspection reports and create PQS records
 * - BAC: Can view inspection reports
 */
class InspectionReportPolicy
{
    /**
     * Determine if the user can view any inspection reports.
     */
    public function viewAny(Account $user): bool
    {
        return in_array($user->role, ['iac', 'custodian', 'bac', 'admin'], true);
    }

    /**
     * Determine if the user can view the inspection report.
     */
    public function view(Account $user, InspectionReport $inspectionReport): bool
    {
        return in_array($user->role, ['iac', 'custodian', 'bac', 'admin'], true);
    }

    /**
     * Determine if the user can create inspection reports.
     * Only inspectors (IAC) can create inspection reports.
     */
    public function create(Account $user): bool
    {
        return in_array($user->role, ['iac', 'custodian', 'admin'], true);
    }

    /**
     * Determine if the user can perform inspection (accept/reject items).
     * Only IAC role can inspect items.
     */
    public function inspect(Account $user, InspectionReport $inspectionReport): bool
    {
        if (!in_array($user->role, ['iac', 'custodian', 'admin'], true)) {
            return false;
        }

        // Can only inspect reports that are pending
        return (int) $inspectionReport->overall_status_id === Status::ITEM_PENDING_INSPECTION;
    }

    /**
     * Determine if the user can accept items during inspection.
     */
    public function acceptItems(Account $user): bool
    {
        return in_array($user->role, ['iac', 'custodian', 'admin'], true);
    }

    /**
     * Determine if the user can reject items during inspection.
     */
    public function rejectItems(Account $user): bool
    {
        return in_array($user->role, ['iac', 'custodian', 'admin'], true);
    }
}
