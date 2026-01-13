<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'cat_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'cat_id',
        'cat_name',
        'parent_id',
        'description',
    ];

    protected $casts = [
        'cat_id' => 'string',
        'parent_id' => 'string',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'cat_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'cat_id');
    }

    public function pqsRecords(): HasMany
    {
        return $this->hasMany(PqsRecord::class, 'cat_id', 'cat_id');
    }

    public static function parentsWithChildren(): Collection
    {
        return self::with('children')->whereNull('parent_id')->orderBy('cat_name')->get();
    }
}
