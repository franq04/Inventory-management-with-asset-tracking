<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'recipient_id',
        'sender_id',
        'table_name',
        'record_id',
        'message',
        'type',
        'is_read',
        'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $notification): void {
            if (! empty($notification->notification_id)) {
                return;
            }

            $maxId = (int) static::query()->max('notification_id');
            $notification->notification_id = $maxId + 1;
        });
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'recipient_id', 'account_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sender_id', 'account_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->is_read = true;
            $this->save();
        }
    }
}
