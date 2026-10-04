<?php

namespace App\Support;

class Locales
{
    public const DEFAULT = 'en';

    public const SUPPORTED = [
        'en' => ['label' => 'English', 'native' => 'EN', 'dir' => 'ltr', 'og' => 'en_US'],
        'ps' => ['label' => 'Pashto', 'native' => 'پښتو', 'dir' => 'rtl', 'og' => 'ps_AF'],
        'fa' => ['label' => 'Persian', 'native' => 'فارسی', 'dir' => 'rtl', 'og' => 'fa_AF'],
    ];

    public static function codes(): array
    {
        return array_keys(self::SUPPORTED);
    }

    public static function isRtl(string $locale): bool
    {
        return (self::SUPPORTED[$locale]['dir'] ?? 'ltr') === 'rtl';
    }
}
