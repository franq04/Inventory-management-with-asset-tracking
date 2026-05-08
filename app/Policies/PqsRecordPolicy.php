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
        return in_array($user->role, ['custodian', 'bac', 'iac', 'admin'], true);
    }

    /**
     * Determine if the user can view the PQS record.
     */
    public function view(Account $user, PqsRecord $pqsRecord): bool
    {
        return in_array($user->role, ['custodian', 'bac', 'iac', 'admin'], true);
    }

    /**
     * Determine if the user can create PQS records.
     * Only custodians can create PQS records from accepted items.
     */
    public function create(Account $user): bool
    {
        return in_array($user->role, ['custodian', 'admin'], true);
    }

    /**
     * Determine if the user can create a PQS record for a specific inspection item.
     * Item must be accepted before PQS can be created.
     */
    public function createFromInspection(Account $user, InspectionReportItem $inspectionItem): bool
    {
        if (! in_array($user->role, ['custodian', 'admin'], true)) {
            return false;
        }

        // Item must be accepted to create PQS
        return (int) $inspectionItem->inspection_status_id === Status::ITEM_ACCEPTED;
    }

    /**
     * Determine if the user can transfer an asset record.
     */
    public function transfer(Account $user, PqsRecord $pqsRecord): bool
    {
        if (! in_array($user->role, ['custodian', 'admin'], true)) {
            return false;
        }

        return $pqsRecord->canBeTransferred();
    }

    /**
     * Determine if the user can process a bulk turnover.
     */
    public function turnover(Account $user): bool
    {
        return in_array($user->role, ['custodian', 'admin'], true);
    }
}
