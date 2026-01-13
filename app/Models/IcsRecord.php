<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcsRecord extends Model
{
    protected $table = 'ics';
    protected $primaryKey = 'ics_no';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'property_no',
        'description',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'estimated_useful_life',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function pqsRecord(): BelongsTo
    {
        return $this->belongsTo(PqsRecord::class, 'property_no', 'property_no');
    }
}
