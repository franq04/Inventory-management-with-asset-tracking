<?php

namespace App\Services;

use App\Models\FundAllocation;
use App\Models\PurchaseRequest;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FundAllocationService
{
    /**
     * Reserve funds for a new purchase request.
     *
     * @param FundAllocation $allocation
     * @param float $amount
     * @param string $prNo
     * @return bool
     */
    public function reserve(FundAllocation $allocation, float $amount, string $prNo): bool
    {
        if ($amount <= 0) {
            return true;
        }

        $success = $allocation->reserve($amount);

        if ($success) {
            $this->logAllocation($allocation, $amount, 'RESERVE', $prNo);
        }

        return $success;
    }

    /**
     * Release funds from a cancelled or rejected purchase request.
     *
     * @param FundAllocation $allocation
     * @param float $amount
     * @param string $prNo
     * @return void
     */
    public function release(FundAllocation $allocation, float $amount, string $prNo): void
    {
        if ($amount <= 0) {
            return;
        }

        $allocation->release($amount);
        $this->logAllocation($allocation, $amount, 'RELEASE', $prNo);
    }

    /**
     * Apply delta when purchase request is updated.
     * If new total is higher, reserve additional funds.
     * If new total is lower, release excess funds.
     *
     * @param PurchaseRequest $purchaseRequest
     * @param float $newTotal
     * @return bool
     */
    public function applyDelta(PurchaseRequest $purchaseRequest, float $newTotal): bool
    {
        $oldTotal = $purchaseRequest->total_estimated_cost;
        $delta = $newTotal - $oldTotal;

        if (abs($delta) < 0.01) {
            return true; // No significant change
        }

        $allocation = $purchaseRequest->fundAllocation;
        if (!$allocation) {
            return false;
        }

        if ($delta > 0) {
            // Need to reserve additional funds
            $success = $allocation->reserve($delta);
            if ($success) {
                $this->logAllocation($allocation, $delta, 'ADJUST_RESERVE', $purchaseRequest->pr_no, "Increased from ₱" . number_format($oldTotal, 2) . " to ₱" . number_format($newTotal, 2));
            }
            return $success;
        } else {
            // Release excess funds
            $allocation->release(abs($delta));
            $this->logAllocation($allocation, abs($delta), 'ADJUST_RELEASE', $purchaseRequest->pr_no, "Decreased from ₱" . number_format($oldTotal, 2) . " to ₱" . number_format($newTotal, 2));
            return true;
        }
    }

    /**
     * Log fund allocation action to audit log.
     *
     * @param FundAllocation $allocation
     * @param float $amount
     * @param string $action
     * @param string $prNo
     * @param string|null $note
     * @return void
     */
    protected function logAllocation(FundAllocation $allocation, float $amount, string $action, string $prNo, ?string $note = null): void
    {
        $descriptions = [
            'RESERVE' => "Reserved ₱%s from fund cluster '%s' for PR %s",
            'RELEASE' => "Released ₱%s back to fund cluster '%s' from PR %s",
            'ADJUST_RESERVE' => "Reserved additional ₱%s from fund cluster '%s' for PR %s",
            'ADJUST_RELEASE' => "Released ₱%s back to fund cluster '%s' from PR %s",
        ];

        $template = $descriptions[$action] ?? "Fund allocation action: %s";
        $description = sprintf($template, number_format($amount, 2), $allocation->fund_cluster, $prNo);

        if ($note) {
            $description .= ". " . $note;
        }

        AuditLog::create([
            'account_id' => Auth::id(),
            'table_name' => 'fund_allocations',
            'action' => $action,
            'description' => $description,
            'log_time' => now(),
        ]);
    }

    /**
     * Check if allocation has sufficient funds.
     *
     * @param FundAllocation $allocation
     * @param float $amount
     * @return bool
     */
    public function hasSufficientFunds(FundAllocation $allocation, float $amount): bool
    {
        return $allocation->remaining_amount >= $amount;
    }

    /**
     * Get shortfall amount if insufficient.
     *
     * @param FundAllocation $allocation
     * @param float $amount
     * @return float
     */
    public function getShortfall(FundAllocation $allocation, float $amount): float
    {
        return max(0, $amount - $allocation->remaining_amount);
    }
}
