<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionReport extends Model
{
    protected $table = 'inspection_reports';
    protected $primaryKey = 'ia_no';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ia_no',
        'po_no',
        'fund_cluster',
        'inspection_date',
        'accepted_date',
        'invoice_no',
        'invoice_date',
        'inspected_by',
        'accepted_by',
        'remarks',
        'overall_status_id',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'accepted_date' => 'date',
        'invoice_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InspectionReportItem::class, 'ia_no', 'ia_no');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_no', 'po_no');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'overall_status_id', 'status_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inspected_by', 'account_id');
    }

    public function accepter(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'accepted_by', 'account_id');
    }
}
