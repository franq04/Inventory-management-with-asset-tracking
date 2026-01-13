<?php

namespace App\Support;

use App\Models\PurchaseOrder;
use App\Models\Status;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\DB;

class PurchaseRequestStatusSynchronizer
{
    public static function sync(PurchaseOrder $purchaseOrder, ?int $actorId = null, ?string $reason = null): void
    {
        $purchaseOrder->loadMissing('purchaseRequest');

        $purchaseRequest = $purchaseOrder->purchaseRequest;
        if (! $purchaseRequest) {
            return;
        }

        $targetStatus = self::mapOrderStatusToRequestStatus((int) $purchaseOrder->status_id);
        if (! $targetStatus) {
            return;
        }

        if ((int) $purchaseRequest->status_id === $targetStatus) {
            return;
        }

        $actor = $actorId ?? $purchaseOrder->ordered_by;
        $previousStatus = (int) $purchaseRequest->status_id;
        $remarks = $reason ?: self::defaultRemarksForStatus($targetStatus, $purchaseOrder->po_no);

        DB::transaction(function () use ($purchaseRequest, $targetStatus, $previousStatus, $actor, $remarks) {
            $purchaseRequest->update(['status_id' => $targetStatus]);

            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => $previousStatus,
                'new_status_id' => $targetStatus,
                'changed_by' => $actor,
                'remarks' => $remarks,
                'changed_at' => now(),
            ]);
        });
    }

    protected static function mapOrderStatusToRequestStatus(int $orderStatus): ?int
    {
        return match ($orderStatus) {
            Status::PO_CREATED => Status::PR_PO_CREATED,
            Status::PO_SENT_TO_SUPPLIER => Status::PR_PO_SENT_TO_SUPPLIER,
            Status::PO_PARTIALLY_DELIVERED => Status::PR_PARTIALLY_DELIVERED,
            Status::PO_DELIVERED_PENDING_INSPECTION => Status::PR_DELIVERED_PENDING_INSPECTION,
            Status::PO_CLOSED => Status::PR_INSPECTION_COMPLETED,
            Status::PO_CANCELLED => Status::PR_CANCELLED,
            default => null,
        };
    }

    protected static function defaultRemarksForStatus(int $targetStatus, string $poNumber): string
    {
        $messages = [
            Status::PR_PO_CREATED => 'Purchase order '.$poNumber.' drafted for this request.',
            Status::PR_PO_SENT_TO_SUPPLIER => 'Purchase order '.$poNumber.' accepted and sent to the supplier.',
            Status::PR_PARTIALLY_DELIVERED => 'Deliveries recorded against purchase order '.$poNumber.'.',
            Status::PR_DELIVERED_PENDING_INSPECTION => 'All items received for purchase order '.$poNumber.' and awaiting inspection.',
            Status::PR_INSPECTION_COMPLETED => 'Inspection completed for purchase order '.$poNumber.'.',
            Status::PR_CANCELLED => 'Purchase order '.$poNumber.' was cancelled.',
        ];

        return $messages[$targetStatus] ?? 'Lifecycle status updated based on purchase order '.$poNumber.'.';
    }
}
