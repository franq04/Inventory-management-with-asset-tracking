<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $table = 'positions';
    protected $primaryKey = 'position_id';
    public $timestamps = false;

    protected $fillable = [
        'position_title',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id', 'position_id');
    }
}
