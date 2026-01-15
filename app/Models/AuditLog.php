<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
	protected $table = 'audit_logs';
	protected $primaryKey = 'log_id';
	public $timestamps = false;
	public $incrementing = false;
	protected $keyType = 'int';

	protected $fillable = [
		'account_id',
		'table_name',
		'action',
		'description',
		'log_time',
	];

	protected $casts = [
		'log_time' => 'datetime',
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'account_id', 'account_id');
	}

	protected static function booted(): void
	{
		static::creating(function (self $auditLog): void {
			if (!empty($auditLog->log_id)) {
				return;
			}

			$auditLog->log_id = ((int) static::max('log_id')) + 1;
		});
	}
}

