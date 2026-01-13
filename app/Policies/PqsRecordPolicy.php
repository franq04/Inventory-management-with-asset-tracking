<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\PqsRecord;
use App\Models\InspectionReportItem;
use App\Models\Status;

/**
 * Policy for PQS Record authorization.
 * 
 * RBAC for PQS workflow:
 * - Custodian: Can create PQS records from accepted inspection items
 * - BAC: Can view PQS records
 */
class PqsRecordPolicy
{
    /**
     * Determine if the user can view any PQS records.
     */
    public function viewAny(Account $user): bool
    {
        return in_array($user->role, ['custodian', 'bac', 'iac'], true);
    }

    /**
     * Determine if the user can view the PQS record.
     */
    public function view(Account $user, PqsRecord $pqsRecord): bool
    {
        return in_array($user->role, ['custodian', 'bac', 'iac'], true);
    }

    /**
     * Determine if the user can create PQS records.
     * Only custodians can create PQS records from accepted items.
     */
    public function create(Account $user): bool
    {
        return $user->role === 'custodian';
    }

    /**
     * Determine if the user can create a PQS record for a specific inspection item.
     * Item must be accepted before PQS can be created.
     */
    public function createFromInspection(Account $user, InspectionReportItem $inspectionItem): bool
    {
        if ($user->role !== 'custodian') {
            return false;
        }

        // Item must be accepted to create PQS
        return (int) $inspectionItem->inspection_status_id === Status::ITEM_ACCEPTED;
    }
}
