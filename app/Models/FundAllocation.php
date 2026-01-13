<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FundAllocation extends Model
{
    protected $fillable = [
        'fund_cluster',
        'total_amount',
        'remaining_amount',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    /**
     * Atomically reserve funds from this allocation.
     * Returns true if successful, false if insufficient funds.
     */
    public function reserve(float $amount): bool
    {
        // Atomic check and update to prevent race conditions
        $updated = DB::update(
            'UPDATE fund_allocations SET remaining_amount = remaining_amount - ? WHERE id = ? AND remaining_amount >= ?',
            [$amount, $this->id, $amount]
        );

        if ($updated > 0) {
            $this->refresh();
            return true;
        }

        return false;
    }

    /**
     * Release (restore) funds back to this allocation.
     */
    public function release(float $amount): void
    {
        $this->increment('remaining_amount', $amount);
        $this->refresh();
    }

    /**
     * Get the account that created this allocation.
     */
    public function creator()
    {
        return $this->belongsTo(Account::class, 'created_by', 'account_id');
    }

    /**
     * Get all purchase requests using this allocation.
     */
    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'fund_allocation_id');
    }
}
