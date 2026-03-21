<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PqsRecord extends Model
{
    use Auditable;

    protected $table = 'pqs';
    protected $primaryKey = 'property_no';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Audit configuration
    protected $auditModule = 'asset';
    protected $auditIdentifierField = 'property_no';
    protected $auditModelName = 'Property Record';

    protected $fillable = [
        'property_no',
        'article',
        'description',
        'serial_number',
        'date_acquired',
        'unit_value',
        'unit',
        'on_hand_per_count',
        'total_value',
        'remarks',
        'accountable_officer_id',
        'cat_id',
        // Asset tracking fields
        'current_location_id',
        'current_custodian_employee_id',
        'assigned_division_id',
        'assigned_section_id',
        'asset_status',
        'last_movement_at',
        'last_inventory_date',
        'last_inventoried_by',
    ];

    protected $casts = [
        'date_acquired' => 'date',
        'unit_value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'on_hand_per_count' => 'integer',
        'last_movement_at' => 'datetime',
        'last_inventory_date' => 'date',
    ];

    // Asset status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_TRANSFERRED = 'transferred';
    const STATUS_DISPOSED = 'disposed';
    const STATUS_LOST = 'lost';
    const STATUS_FOR_REPAIR = 'for_repair';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }

    public function accountableOfficer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'accountable_officer_id', 'employee_id');
    }

    public function inspectionItems(): HasMany
    {
        return $this->hasMany(InspectionReportItem::class, 'property_no', 'property_no');
    }

    public function icsRecord(): HasOne
    {
        return $this->hasOne(IcsRecord::class, 'property_no', 'property_no');
    }

    public function parRecord(): HasOne
    {
        return $this->hasOne(ParRecord::class, 'property_no', 'property_no');
    }

    // ============================================
    // Asset Tracking Relationships
    // ============================================

    /**
     * Get the current physical location
     */
    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(PhysicalLocation::class, 'current_location_id', 'location_id');
    }

    /**
     * Get the current custodian employee
     */
    public function currentCustodian(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_custodian_employee_id', 'employee_id');
    }

    /**
     * Get the assigned division
     */
    public function assignedDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'assigned_division_id', 'division_id');
    }

    /**
     * Get the assigned section
     */
    public function assignedSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'assigned_section_id', 'section_id');
    }

    /**
     * Get the employee who last inventoried this asset
     */
    public function lastInventoriedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'last_inventoried_by', 'employee_id');
    }

    /**
     * Get recorded movement history for this asset
     */
    public function movements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'property_no', 'property_no');
    }

    // ============================================
    // Helper Methods
    // ============================================

    /**
     * Check if asset can be transferred
     */
    public function canBeTransferred(): bool
    {
        // Cannot transfer disposed or lost assets
        if (in_array($this->asset_status, [self::STATUS_DISPOSED, self::STATUS_LOST])) {
            return false;
        }

        return true;
    }

    /**
     * Check if asset can be turned over.
     */
    public function canBeTurnedOver(): bool
    {
        return $this->canBeTransferred();
    }

    /**
     * Get the property number attribute (for consistency)
     */
    public function getPropertyNumberAttribute(): string
    {
        return $this->property_no;
    }

    /**
     * Get the primary key for polymorphic relations
     */
    public function getPqsIdAttribute()
    {
        return $this->property_no;
    }

    /**
     * Get division through accountable officer
     */
    public function getDivisionAttribute()
    {
        if ($this->assignedDivision) {
            return $this->assignedDivision;
        }

        return $this->accountableOfficer?->division;
    }

    /**
     * Get asset status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->asset_status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_TRANSFERRED => 'info',
            self::STATUS_DISPOSED => 'secondary',
            self::STATUS_LOST => 'danger',
            self::STATUS_FOR_REPAIR => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Scope for assets by division
     */
    public function scopeInDivision($query, int $divisionId)
    {
        return $query->where('assigned_division_id', $divisionId);
    }

    /**
     * Scope for active assets
     */
    public function scopeActive($query)
    {
        return $query->where('asset_status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for assets needing inventory
     */
    public function scopeNeedsInventory($query, int $daysSinceLastInventory = 365)
    {
        return $query->where(function($q) use ($daysSinceLastInventory) {
            $q->whereNull('last_inventory_date')
              ->orWhere('last_inventory_date', '<', now()->subDays($daysSinceLastInventory));
        });
    }
}
