<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Cache key for all settings
     */
    protected const ALL_SETTINGS_CACHE_KEY = 'app_settings_all';

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("app_setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (! $setting) {
            return $default;
        }

        // Cast value based on type
        return match ($setting->type) {
            'number' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        Cache::forget("app_setting_{$key}");
        Cache::forget(self::ALL_SETTINGS_CACHE_KEY);
    }

    /**
     * Get all settings grouped with caching
     */
    public static function getAllGrouped(): array
    {
        return Cache::remember(self::ALL_SETTINGS_CACHE_KEY, 3600, function () {
            return static::all()->groupBy('group')->toArray();
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("app_setting_{$setting->key}");
        }
        Cache::forget(self::ALL_SETTINGS_CACHE_KEY);
    }
}
