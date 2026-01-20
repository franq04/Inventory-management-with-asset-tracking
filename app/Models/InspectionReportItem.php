<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionReportItem extends Model
{
    protected $table = 'inspection_report_items';
    protected $primaryKey = 'ia_item_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (! empty($item->ia_item_id)) {
                return;
            }

            $item->ia_item_id = ((int) static::query()->max('ia_item_id')) + 1;
        });
    }

    protected $fillable = [
        'ia_no',
        'po_item_id',
        'quantity_delivered',
        'quantity_accepted',
        'quantity_rejected',
        'inspection_status_id',
        'inspection_remarks',
        'property_no',
        'warranty_expiration',
    ];

    protected $casts = [
        'quantity_delivered' => 'integer',
        'quantity_accepted' => 'integer',
        'quantity_rejected' => 'integer',
        'warranty_expiration' => 'date',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(InspectionReport::class, 'ia_no', 'ia_no');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id', 'poi_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'inspection_status_id', 'status_id');
    }

    public function propertyRecord(): BelongsTo
    {
        return $this->belongsTo(PqsRecord::class, 'property_no', 'property_no');
    }
}
