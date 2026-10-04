<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = ['group', 'key', 'en', 'ps', 'fa'];

    private static ?array $cache = null;

    public static function value(string $key, ?string $locale = null, ?string $default = null): ?string
    {
        $locale ??= app()->getLocale();

        $row = static::allCached()[$key] ?? null;

        if (! $row) {
            return $default;
        }

        return $row->{$locale} ?: ($row->en ?: $default);
    }

    public static function allCached(): array
    {
        return static::$cache ??= static::query()->get()->keyBy('key')->all();
    }

    public static function flushCache(): void
    {
        static::$cache = null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
