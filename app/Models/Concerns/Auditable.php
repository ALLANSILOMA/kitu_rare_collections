<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Add `use Auditable;` to a model to record created / updated / deleted.
 * Override auditLabel() for a readable name and auditExclude() to skip noisy columns.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => AuditLogger::recordModelEvent($model, 'created'));
        static::updated(fn (Model $model) => AuditLogger::recordModelEvent($model, 'updated'));
        static::deleted(fn (Model $model) => AuditLogger::recordModelEvent($model, 'deleted'));
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }

    public function auditLabel(): string
    {
        return class_basename($this) . ' #' . $this->getKey();
    }

    /** Attributes that should never appear in the trail for this model. */
    public function auditExclude(): array
    {
        return [];
    }
}
