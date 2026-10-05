<?php

use App\Models\Setting;
use App\Models\Translation;
use App\Support\Locales;

if (!function_exists('money')) {
    /**
     * Format an amount with the shop's currency symbol.
     */
    function money(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $formatted = number_format((float) $amount, (float) $amount === floor((float) $amount) ? 0 : 2);

        return $withSymbol ? $formatted . ' ' . Setting::get('currency_symbol', 'AFN') : $formatted;
    }
}

if (!function_exists('payment_method_label')) {
    function payment_method_label(?string $method): string
    {
        return match ($method) {
            'cash_on_delivery' => t('payment.cash_on_delivery', 'Cash on Delivery'),
            'bank_transfer' => t('payment.bank_transfer', 'Bank Transfer'),
            'hesabpay' => t('payment.hesabpay', 'HesabPay'),
            default => filled($method) ? ucfirst(str_replace('_', ' ', $method)) : '—',
        };
    }
}

if (!function_exists('status_badge_class')) {
    function status_badge_class(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'in_tailoring' => 'bg-indigo-100 text-indigo-800',
            'ready' => 'bg-purple-100 text-purple-800',
            'delivered', 'paid' => 'bg-green-100 text-green-800',
            'cancelled', 'refunded' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}

if (!function_exists('t')) {
    /**
     * Translate via the DB-backed translations table, falling back to the
     * English default supplied by the caller and finally to the key itself.
     * Placeholders written as :name in the translated string are replaced
     * from the $replace array.
     */
    function t(string $key, ?string $default = null, array $replace = []): string
    {
        $value = (string) (Translation::value($key) ?? $default ?? $key);

        foreach ($replace as $name => $replacement) {
            $value = str_replace(':' . $name, (string) $replacement, $value);
        }

        return $value;
    }
}

if (!function_exists('is_rtl')) {
    function is_rtl(?string $locale = null): bool
    {
        return Locales::isRtl($locale ?? app()->getLocale());
    }
}

if (!function_exists('switch_locale_url')) {
    /**
     * Current URL with its locale segment swapped, keeping the query string.
     */
    function switch_locale_url(string $locale): string
    {
        $request = request();
        $segments = $request->segments();

        if (isset($segments[0]) && array_key_exists($segments[0], Locales::SUPPORTED)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        return url(implode('/', $segments)) . ($request->getQueryString() ? '?' . $request->getQueryString() : '');
    }
}
