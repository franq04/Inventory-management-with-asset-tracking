<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequestItem extends Model
{
    protected $table = 'purchase_request_items';
    protected $primaryKey = 'pri_id';
    public $incrementing = true;

    protected $fillable = [
        'pr_no',
        'item_description',
        'item_type',
        'quantity',
        'unit',
        'stock_number',
        'estimated_unit_cost',
        'remarks',
        'fulfillment_status',
        'alternate_description',
        'employee_decision',
        'employee_decided_at',
        'employee_wait_until',
        'employee_wait_note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_unit_cost' => 'decimal:2',
        'estimated_total_cost' => 'decimal:2',
        'employee_decided_at' => 'datetime',
        'employee_wait_until' => 'date',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_no', 'pr_no');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'pri_id', 'pri_id');
    }
}
