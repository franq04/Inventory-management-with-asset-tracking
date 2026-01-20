<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParRecord extends Model
{
    protected $table = 'par';
    protected $primaryKey = 'par_no';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (self $record): void {
            if (! empty($record->par_no)) {
                return;
            }

            $record->par_no = ((int) static::query()->max('par_no')) + 1;
        });
    }

    protected $fillable = [
        'property_no',
        'article_desc',
        'quantity',
        'unit',
        'date_acquired',
        'unit_value',
        'amount',
    ];

    protected $casts = [
        'date_acquired' => 'date',
        'quantity' => 'integer',
        'unit_value' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function pqsRecord(): BelongsTo
    {
        return $this->belongsTo(PqsRecord::class, 'property_no', 'property_no');
    }
}
