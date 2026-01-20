<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequestItem extends Model
{
    protected $table = 'purchase_request_items';
    protected $primaryKey = 'pri_id';
    public $incrementing = false;
    protected $keyType = 'int';

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
        'suggested_by',
        'suggested_at',
        'original_description',
        'employee_decision',
        'employee_decided_at',
        'employee_wait_until',
        'employee_wait_note',
        'removed_at',
        'removal_reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_unit_cost' => 'decimal:2',
        'estimated_total_cost' => 'decimal:2',
        'suggested_at' => 'datetime',
        'employee_decided_at' => 'datetime',
        'employee_wait_until' => 'date',
        'removed_at' => 'datetime',
    ];

    /**
     * Check if this item has a pending alternative suggestion awaiting employee response.
     */
    public function hasPendingAlternative(): bool
    {
        return $this->fulfillment_status === 'alternative' 
            && $this->alternate_description 
            && !$this->employee_decision;
    }

    /**
     * Check if this item is waiting for the original (employee chose to wait).
     */
    public function isWaitingForOriginal(): bool
    {
        return $this->employee_decision === 'wait' 
            && $this->employee_wait_until;
    }

    /**
     * Check if the wait period has expired.
     */
    public function isWaitExpired(): bool
    {
        if (!$this->isWaitingForOriginal()) {
            return false;
        }
        
        return now()->startOfDay()->gt($this->employee_wait_until);
    }

    /**
     * Check if item is active (not removed).
     */
    public function isActive(): bool
    {
        return is_null($this->removed_at);
    }

    /**
     * Get the account that suggested the alternative.
     */
    public function suggester(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'suggested_by', 'account_id');
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_no', 'pr_no');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'pri_id', 'pri_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (!empty($item->pri_id)) {
                return;
            }

            $item->pri_id = ((int) static::max('pri_id')) + 1;
        });
    }
}
