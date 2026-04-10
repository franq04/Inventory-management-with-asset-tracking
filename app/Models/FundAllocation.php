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

    /**
     * Compute utilized amount from linked purchase orders.
     *
     * Utilization is based on PO item totals so PR-only records do not deduct funds.
     */
    public function utilizedAmountFromPurchaseOrders(bool $excludeCancelled = true): float
    {
        $query = DB::table('purchase_orders as po')
            ->join('purchase_requests as pr', 'pr.pr_no', '=', 'po.pr_no')
            ->leftJoin('purchase_order_items as poi', 'poi.po_no', '=', 'po.po_no')
            ->where('pr.fund_allocation_id', $this->id);

        if ($excludeCancelled) {
            $query->where('po.status_id', '!=', Status::PO_CANCELLED);
        }

        $utilized = $query
            ->selectRaw('COALESCE(SUM(COALESCE(poi.total_cost, (COALESCE(poi.quantity, 0) * COALESCE(poi.unit_cost, 0)))), 0) as utilized_amount')
            ->value('utilized_amount');

        return (float) ($utilized ?? 0);
    }

    /**
     * Compute live remaining amount from total budget minus PO utilization.
     */
    public function computedRemainingAmount(bool $excludeCancelled = true): float
    {
        $remaining = (float) $this->total_amount - $this->utilizedAmountFromPurchaseOrders($excludeCancelled);
        return max($remaining, 0);
    }

    /**
     * Sync persisted remaining_amount with computed remaining.
     * Returns the normalized remaining amount.
     */
    public function syncRemainingAmount(bool $excludeCancelled = true): float
    {
        $computed = $this->computedRemainingAmount($excludeCancelled);

        if (abs(((float) $this->remaining_amount) - $computed) > 0.009) {
            $this->remaining_amount = $computed;
            $this->saveQuietly();
            $this->refresh();
        }

        return (float) $this->remaining_amount;
    }
}
