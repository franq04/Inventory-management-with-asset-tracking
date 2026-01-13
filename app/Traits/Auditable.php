<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Str;

trait Auditable
{
    /**
     * Boot the auditable trait to register model events.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created');
        });

        static::updated(function ($model) {
            $model->writeAudit('updated');
        });

        static::deleted(function ($model) {
            $model->writeAudit('deleted');
        });
    }

    protected function writeAudit(string $action): void
    {
        try {
            $module = $this->auditModule ?? Str::snake(class_basename($this));
            $identifierField = $this->auditIdentifierField ?? $this->getKeyName();
            $identifier = $this->{$identifierField} ?? null;

            AuditLog::create([
                'account_id' => auth()->id() ?? session('account_id') ?? null,
                'table_name' => $module,
                'action' => $action,
                'description' => ($this->auditModelName ?? class_basename($this)) . ' ' . ($identifier ?? ''),
                'log_time' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail silently to avoid breaking application flow
        }
    }
}
