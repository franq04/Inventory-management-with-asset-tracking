<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    protected $table = 'purchase_requests';
    protected $primaryKey = 'pr_no';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'pr_no',
        'account_id',
        'status_id',
        'division_id',
        'section_id',
        'sai_no',
        'alobs_no',
        'purpose',
        'recommending_officer_id',
        'total_estimated_cost',
        'fund_allocation_id',
        'funds_available',
        'printed',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class, 'pr_no', 'pr_no');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'approved_by', 'account_id');
    }

    public function recommender(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'recommended_by', 'account_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'reviewed_by', 'account_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id', 'status_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'record_id', 'pr_no')->where('table_name', 'purchase_requests');
    }

    /**
     * Relation to the fund allocation.
     */
    public function fundAllocation(): BelongsTo
    {
        return $this->belongsTo(FundAllocation::class, 'fund_allocation_id');
    }

    /**
     * Relation to the generated purchase order (if any).
     * Some parts of the code call `purchaseOrder()` on a PR — provide a safe relation.
     */
    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'pr_no', 'pr_no');
    }
}
