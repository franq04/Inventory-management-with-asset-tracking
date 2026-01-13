<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusHistory extends Model
{
    protected $table = 'status_history';
    protected $primaryKey = 'history_id';
    public $timestamps = false;

    protected $fillable = [
        'table_name',
        'record_id',
        'old_status_id',
        'new_status_id',
        'changed_by',
        'remarks',
        'changed_at',
    ];

    protected $casts = [
        'old_status_id' => 'integer',
        'new_status_id' => 'integer',
        'changed_at' => 'datetime',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'new_status_id', 'status_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'changed_by', 'account_id');
    }
}
