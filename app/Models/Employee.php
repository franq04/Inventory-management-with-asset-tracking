<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_of_birth',
        'marital_status',
        'gender',
        'contact_no',
        'email',
        'account_id',
        'profile_img',
        'signature',
        'position_id',
        'section_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }

    public function getDivisionAttribute(): ?Division
    {
        $this->loadMissing('section.division');

        return $this->section?->division;
    }

    public function getFullNameAttribute(): string
    {
        $parts = [
            trim((string) $this->first_name),
            trim((string) $this->middle_name),
            trim((string) $this->last_name),
        ];

        $name = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($parts))));

        if ($this->suffix) {
            $name = trim($name.' '.$this->suffix);
        }

        return $name;
    }
}
