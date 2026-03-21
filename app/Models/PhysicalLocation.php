<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalLocation extends Model
{
    protected $table = 'physical_locations';
    protected $primaryKey = 'location_id';

    protected $fillable = [
        'location_name',
        'location_code',
        'location_type',
        'parent_location_id',
        'division_id',
        'section_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_location_id', 'location_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_location_id', 'location_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }
}
