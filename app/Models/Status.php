<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $table = 'statuses';
    protected $primaryKey = 'status_id';

    public const SCOPE_PURCHASE_REQUEST = 'purchase_request';
    public const SCOPE_PURCHASE_ORDER = 'purchase_order';
    public const SCOPE_PURCHASE_ORDER_ITEM = 'purchase_order_item';

    // Purchase request statuses (PR-xx)
    public const PR_DRAFT = 101;
    public const PR_FOR_RECOMMENDATION = 102;
    public const PR_RECOMMENDED = 103;
    public const PR_FOR_APPROVAL = 104;
    public const PR_APPROVED = 105;
    public const PR_CANCELLED = 106;
    public const PR_PO_CREATED = 107;
    public const PR_PO_SENT_TO_SUPPLIER = 108;
    public const PR_PARTIALLY_DELIVERED = 109;
    public const PR_DELIVERED_PENDING_INSPECTION = 110;
    public const PR_INSPECTION_COMPLETED = 111;

    // Purchase order statuses (PO-xx)
    public const PO_CREATED = 201;
    public const PO_SENT_TO_SUPPLIER = 202;
    public const PO_PARTIALLY_DELIVERED = 203;
    public const PO_DELIVERED_PENDING_INSPECTION = 204;
    public const PO_CLOSED = 205;
    public const PO_CANCELLED = 206;

    // Purchase order item / inspection statuses (IT-xx)
    public const ITEM_PENDING_INSPECTION = 301;
    public const ITEM_ACCEPTED = 302;
    public const ITEM_DEFECTIVE = 303;
    public const ITEM_RETURNED = 304;
    public const ITEM_REPLACED = 305;
    public const ITEM_RECORDED = 306;

    protected $fillable = [
        'status_code',
        'status_scope',
        'status_name',
        'remarks',
    ];

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'status_id', 'status_id');
    }

    public static function purchaseRequestLifecycleStatuses(): array
    {
        return [
            self::PR_PO_CREATED,
            self::PR_PO_SENT_TO_SUPPLIER,
            self::PR_PARTIALLY_DELIVERED,
            self::PR_DELIVERED_PENDING_INSPECTION,
            self::PR_INSPECTION_COMPLETED,
        ];
    }
}
