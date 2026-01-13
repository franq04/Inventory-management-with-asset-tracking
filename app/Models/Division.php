<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $table = 'divisions';
    protected $primaryKey = 'division_id';
    public $timestamps = false;

    protected $fillable = [
        'division_name',
        'division_code',
        'description',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'division_id', 'division_id');
    }
}
