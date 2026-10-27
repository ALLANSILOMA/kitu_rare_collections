<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Central writer for the audit trail. It must NEVER break the business action
 * being audited, so every failure is swallowed and sent to the normal log.
 */
class AuditLogger
{
    /** @var string[] */
    protected static array $reasons = [];

    /** Tag every entry written inside $callback with a human reason (e.g. "Order KRC-1042"). */
    public static function withReason(string $reason, \Closure $callback): mixed
    {
        static::$reasons[] = $reason;

        try {
            return $callback();
        } finally {
            array_pop(static::$reasons);
        }
    }

    public static function currentReason(): ?string
    {
        $reasons = static::$reasons;

        return $reasons ? end($reasons) : null;
    }

    /** Write a custom entry, e.g. AuditLogger::log('payment_verified', $order, [...]). */
    public static function log(string $event, ?Model $subject = null, array $attributes = []): ?AuditLog
    {
        try {
            $console = app()->runningInConsole();
            $request = $console ? null : request();

            return AuditLog::create(array_merge(
                static::actorAttributes(auth()->user()),
                [
                    'event'         => $event,
                    'subject_type'  => $subject?->getMorphClass(),
                    'subject_id'    => $subject?->getKey(),
                    'subject_label' => $subject ? static::labelFor($subject) : null,
                    'reason'        => static::currentReason(),
                    'source'        => $console ? 'console' : 'web',
                    'ip_address'    => $request?->ip(),
                    'user_agent'    => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
                ],
                $attributes
            ));
        } catch (\Throwable $e) {
            Log::warning('Audit log write failed: ' . $e->getMessage());

            return null;
        }
    }

    public static function actorAttributes(?Authenticatable $user): array
    {
        return [
            'actor_id'   => $user?->getAuthIdentifier(),
            'actor_name' => $user ? data_get($user, 'name') : null,
            'actor_role' => $user ? data_get($user, 'role') : null,
        ];
    }

    /** Called by the Auditable trait for created / updated / deleted. */
    public static function recordModelEvent(Model $model, string $event): void
    {
        try {
            $ignore    = config('audit.ignore', []);
            $sensitive = array_diff(
                array_merge(config('audit.redact', []), $model->getHidden()),
                $ignore
            );
            $skip = array_merge($ignore, $sensitive, method_exists($model, 'auditExclude') ? $model->auditExclude() : []);

            $old = null;
            $new = null;
            $changedKeys = [];

            if ($event === 'updated') {
                $changes = Arr::except($model->getChanges(), $ignore);
                $visible = Arr::except($changes, $skip);

                $old = [];
                $new = [];
                foreach (array_keys($visible) as $key) {
                    $old[$key] = static::normalise($model->getOriginal($key));
                    $new[$key] = static::normalise($model->getAttribute($key));
                }

                // Sensitive fields (e.g. password): record THAT they changed, never the values.
                foreach (array_intersect(array_keys($changes), $sensitive) as $key) {
                    $old[$key] = '[redacted]';
                    $new[$key] = '[changed]';
                }

                if (! $new) {
                    return; // nothing worth recording (e.g. only updated_at / remember_token moved)
                }
                $changedKeys = array_keys($new);
            } else {
                $snapshot = Arr::except($model->attributesToArray(), array_merge($skip, ['id', 'created_at']));
                $snapshot = array_map(fn ($v) => static::normalise($v), $snapshot);

                if ($event === 'created') {
                    $new = $snapshot;
                } else {
                    $old = $snapshot;
                }
            }

            $label = static::labelFor($model);
            $type  = class_basename($model);
            $description = match ($event) {
                'created' => "Created {$type} {$label}",
                'deleted' => "Deleted {$type} {$label}",
                default   => "Updated {$type} {$label} (" . implode(', ', $changedKeys) . ')',
            };

            static::log($event, $model, [
                'description' => mb_substr($description, 0, 1000),
                'old_values'  => $old,
                'new_values'  => $new,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Audit model event failed: ' . $e->getMessage());
        }
    }

    public static function labelFor(Model $model): string
    {
        $label = method_exists($model, 'auditLabel')
            ? $model->auditLabel()
            : class_basename($model) . ' #' . $model->getKey();

        return mb_substr($label, 0, 255);
    }

    protected static function normalise(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }
        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string) $value : json_encode($value);
        }

        return $value;
    }
}
