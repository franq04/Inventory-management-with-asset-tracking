<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMovement extends Model
{
    protected $table = 'asset_movements';
    protected $primaryKey = 'movement_id';

    protected $fillable = [
        'property_no',
        'from_location_id',
        'to_location_id',
        'from_custodian_employee_id',
        'to_custodian_employee_id',
        'from_division_id',
        'to_division_id',
        'from_section_id',
        'to_section_id',
        'movement_type',
        'reason_code',
        'effective_at',
        'recorded_by',
        'source_table',
        'source_record_id',
        'remarks',
    ];

    protected $casts = [
        'effective_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(PqsRecord::class, 'property_no', 'property_no');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(PhysicalLocation::class, 'from_location_id', 'location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(PhysicalLocation::class, 'to_location_id', 'location_id');
    }

    public function fromCustodian(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'from_custodian_employee_id', 'employee_id');
    }

    public function toCustodian(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'to_custodian_employee_id', 'employee_id');
    }

    public function movedByAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'recorded_by', 'account_id');
    }

    public function fromDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'from_division_id', 'division_id');
    }

    public function toDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'to_division_id', 'division_id');
    }

    public function fromSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'from_section_id', 'section_id');
    }

    public function toSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'to_section_id', 'section_id');
    }
}
