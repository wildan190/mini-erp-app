<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'is_secret',
        'label',
    ];

    protected $casts = [
        'is_secret' => 'boolean',
    ];

    // ── Static helpers ────────────────────────────────────────────────────────

    /**
     * Get a setting value by key.
     * Secret values are decrypted automatically.
     * Falls back to $default if not set.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = Cache::remember("setting:{$key}", 3600, fn () => static::where('key', $key)->first());

        if (!$row) return $default;

        if ($row->is_secret && $row->value) {
            try {
                return Crypt::decryptString($row->value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $row->value;
    }

    /**
     * Set (upsert) a setting. Encrypts value if is_secret = true.
     */
    public static function set(string $key, mixed $value, string $group = 'general', bool $isSecret = false, ?string $label = null): void
    {
        $stored = $isSecret && $value ? Crypt::encryptString($value) : $value;

        static::updateOrCreate(
            ['key' => $key],
            [
                'group'     => $group,
                'value'     => $stored,
                'is_secret' => $isSecret,
                'label'     => $label,
            ]
        );

        Cache::forget("setting:{$key}");
    }

    /**
     * Get all settings in a group, with secrets masked.
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(function ($row) {
                $value = $row->is_secret
                    ? ($row->value ? '••••••••' : null)
                    : $row->value;

                return [$row->key => [
                    'key'       => $row->key,
                    'label'     => $row->label,
                    'value'     => $value,
                    'is_secret' => $row->is_secret,
                    'is_set'    => (bool) $row->value,
                ]];
            })
            ->toArray();
    }
}
