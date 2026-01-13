<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'section_id';
    public $timestamps = false;

    protected $fillable = [
        'section_name',
        'section_code',
        'division_id',
        'description',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'section_id', 'section_id');
    }
}
