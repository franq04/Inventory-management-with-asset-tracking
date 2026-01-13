<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';
    protected $primaryKey = 'poi_id';
    public $timestamps = false;

    protected $fillable = [
        'po_no',
        'pri_id',
        'item_description',
        'quantity',
        'unit',
        'unit_cost',
        'remarks',
        'fulfillment_status',
        'alternate_description',
        'employee_decision',
        'employee_decided_at',
        'employee_wait_until',
        'employee_wait_note',
        'received_at',
        'received_by',
        'receiving_note',
        'inspection_status_id',
        'inspection_remarks',
        'warranty_start',
        'warranty_end',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
        'employee_decided_at' => 'datetime',
        'employee_wait_until' => 'date',
        'received_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_no', 'po_no');
    }

    public function purchaseRequestItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestItem::class, 'pri_id', 'pri_id');
    }

    public function inspectionItems(): HasMany
    {
        return $this->hasMany(InspectionReportItem::class, 'po_item_id', 'poi_id')
            ->orderByDesc('ia_item_id');
    }

    public function latestInspectionItem(): HasOne
    {
        return $this->hasOne(InspectionReportItem::class, 'po_item_id', 'poi_id')
            ->latestOfMany('ia_item_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'received_by', 'account_id');
    }
}
