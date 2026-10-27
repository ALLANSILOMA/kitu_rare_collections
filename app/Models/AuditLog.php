<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'event',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'reason',
        'old_values',
        'new_values',
        'properties',
        'source',
        'ip_address',
        'user_agent',
    ];
    protected function casts(): array{
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    public function subjectTypeLabel(): string
    {
        return $this->subject_type ? class_basename($this->subject_type) : '—';
    }

    /** One-line "field: old → new; field: old → new" summary for tables. */
    public function changeSummary(): string
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        if ($this->event !== 'updated' || ! $new) {
            return '—';
        }

        $fmt = function ($value): string {
            if (is_null($value)) {
                return '∅';
            }
            $text = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return mb_strlen($text) > 40 ? mb_substr($text, 0, 40) . '…' : $text;
        };

        return collect($new)
            ->map(fn ($value, $key) => $key . ': ' . $fmt($old[$key] ?? null) . ' → ' . $fmt($value))
            ->implode('; ');
    }
}

