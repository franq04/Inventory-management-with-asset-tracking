<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'po_no';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'po_no',
        'pr_no',
        'supplier_id',
        'order_date',
        'delivery_date',
        'mode_of_procurement',
        'place_of_delivery',
        'delivery_term',
        'payment_term',
        'fund_cluster',
        'funds_available',
        'ors_burs_no',
        'ors_burs_date',
        'ors_burs_amount',
        'amount_in_words',
        'conforme_name',
        'conforme_date',
        'authorized_by',
        'ordered_by',
        'status_id',
        'remarks',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'ors_burs_date' => 'date',
        'conforme_date' => 'date',
        'funds_available' => 'decimal:2',
        'ors_burs_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_no', 'po_no');
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_no', 'pr_no');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id', 'status_id');
    }

    public function inspectionReports(): HasMany
    {
        return $this->hasMany(InspectionReport::class, 'po_no', 'po_no');
    }
}
