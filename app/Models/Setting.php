<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_encrypted',
        'sort_order',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type, $setting->is_encrypted);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            if ($setting->is_encrypted) {
                $value = encrypt($value);
            }

            $setting->update(['value' => $value]);
        } else {
            static::create([
                'key' => $key,
                'value' => $value,
                'label' => ucwords(str_replace('_', ' ', $key)),
            ]);
        }

        Cache::forget("setting.{$key}");
    }

    /**
     * Get all settings as key-value array
     */
    public static function allSettings(): array
    {
        return Cache::rememberForever('settings.all', function () {
            $settings = [];

            foreach (static::query()->get() as $setting) {
                $settings[$setting->key] = static::castValue(
                    $setting->value,
                    $setting->type,
                    $setting->is_encrypted
                );
            }

            return $settings;
        });
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key')
            ->toArray();
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('settings.all');

        foreach (static::query()->pluck('key') as $key) {
            Cache::forget("setting.{$key}");
        }
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue($value, string $type, bool $isEncrypted)
    {
        if ($isEncrypted && $value) {
            $value = decrypt($value);
        }

        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? (float) $value : $value,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}
