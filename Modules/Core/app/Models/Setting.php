<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /**
     * @var string
     */
    protected $table = 'settings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * Cache key prefix.
     */
    protected const CACHE_PREFIX = 'app_setting_';

    /**
     * Cache TTL in seconds (1 day).
     */
    protected const CACHE_TTL = 86400;

    /**
     * Retrieve a setting by its key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX.$key;

        try {
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
                try {
                    $setting = static::where('key', $key)->first();
                    if (! $setting) {
                        return $default;
                    }

                    return match ($setting->type) {
                        'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                        'integer', 'int' => (int) $setting->value,
                        'float' => (float) $setting->value,
                        'json', 'array' => json_decode((string) $setting->value, true),
                        default => $setting->value,
                    };
                } catch (\Throwable) {
                    return $default;
                }
            });
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Store or update a setting.
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): static
    {
        if (is_array($value) && $type === 'string') {
            $type = 'json';
        }

        $rawValue = match ($type) {
            'boolean', 'bool' => $value ? '1' : '0',
            'json', 'array' => is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE),
            default => (string) $value,
        };

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $rawValue,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget(self::CACHE_PREFIX.$key);

        if ($key === 'site_title' && ! empty($value)) {
            config(['app.name' => (string) $value]);
        }

        return $setting;
    }

    /**
     * Retrieve the dynamic site title from settings or fallback to app name / default.
     */
    public static function getSiteTitle(): string
    {
        $title = static::get('site_title');

        return ! empty($title) ? (string) $title : 'SNGPOS';
    }

    /**
     * Remove a setting.
     */
    public static function remove(string $key): bool
    {
        Cache::forget(self::CACHE_PREFIX.$key);

        return (bool) static::where('key', $key)->delete();
    }

    /**
     * Check if the public landing page is enabled.
     */
    public static function isLandingPageEnabled(): bool
    {
        $value = static::get('landing_page_enabled', null);
        if ($value === null) {
            return (bool) config('app.landing_page_enabled', true);
        }

        return (bool) $value;
    }

    /**
     * Enable or disable the public landing page.
     */
    public static function setLandingPageEnabled(bool $enabled): void
    {
        static::set('landing_page_enabled', $enabled, 'boolean', 'system');
    }

    /**
     * Check if self-service shop registration is enabled.
     */
    public static function isRegistrationEnabled(): bool
    {
        $value = static::get('registration_enabled', null);
        if ($value === null) {
            return (bool) config('app.registration_enabled', true);
        }

        return (bool) $value;
    }

    /**
     * Enable or disable self-service shop registration.
     */
    public static function setRegistrationEnabled(bool $enabled): void
    {
        static::set('registration_enabled', $enabled, 'boolean', 'system');
    }
}
