<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    protected $fillable = ['group', 'key', 'en', 'ps', 'fa'];

    private static ?array $cache = null;

    public static function value(string $key, ?string $locale = null, ?string $default = null): ?string
    {
        $locale ??= app()->getLocale();

        $row = static::allCached()[$key] ?? null;

        if (! is_array($row)) {
            return $default;
        }

        return $row[$locale] ?: ($row['en'] ?: $default);
    }

    public static function allCached(): array
    {
        // The translation table is read on every render through t(); caching
        // the plain key => locale/values array turns that per-request query
        // into one cached read. Plain arrays (not Eloquent models) because
        // model objects break on dequeue from a serialized cache store.
        return static::$cache ??= Cache::rememberForever('translations:all', function () {
            return static::query()
                ->get()
                ->keyBy('key')
                ->map(fn (self $row) => [
                    'en' => $row->en,
                    'ps' => $row->ps,
                    'fa' => $row->fa,
                ])
                ->all();
        });
    }

    public static function flushCache(): void
    {
        static::$cache = null;

        Cache::forget('translations:all');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
