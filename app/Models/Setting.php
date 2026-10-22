<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static array $defaults = [
        'shop_name' => 'Safi Tailoring Shop',
        'shop_tagline' => 'Custom tailoring & fine ready-to-wear',
        'shop_phone' => '+93 700 000 000',
        'shop_email' => 'sscc.url@gmail.com',
        'shop_address' => 'Main Bazaar Road, Kabul, Afghanistan',
        'currency_symbol' => 'AFN',
        'currency_code' => 'AFN',
        'delivery_fee' => '100',
        'free_delivery_over' => '5000',
        'hero_title' => 'Crafted to Fit, Stitched with Care',
        'hero_subtitle' => 'Bespoke tailoring and quality ready-made garments for men and women. Order online — we stitch to your measurements.',
        'announcement' => 'Welcome to Safi Tailoring Shop — free delivery on orders over 5,000 AFN',
        'facebook_url' => '',
        'instagram_url' => '',
        'whatsapp_number' => '',
        'hesabpay_enabled' => '1',
        'hesabpay_api_key' => 'NDc1ZjM3YzQtZjExYi00ZTdmLTk1MWItZjMwN2ZlODA2ZTk2X180NzU0MGZiZGMzM2YxYmYwODdmYQ==',
        'hesabpay_environment' => 'production',
        'hesabpay_webhook_secret' => '',
        'whatsapp_cloud_enabled' => '0',
        'whatsapp_phone_number_id' => '',
        'whatsapp_cloud_token' => '',
        'whatsapp_country_code' => '93',
        'whatsapp_auth_template' => '',

        // Customer notifications (order events): one toggle per channel.
        'notify_email_enabled' => '1',
        'notify_whatsapp_enabled' => '1',

        // Outgoing email (SMTP). Empty host keeps the .env driver — filled
        // values override it at runtime (see App\Support\MailerSettings).
        'mail_smtp_host' => '',
        'mail_smtp_port' => '587',
        'mail_smtp_username' => '',
        'mail_smtp_password' => '',
        'mail_smtp_encryption' => 'tls',
        'mail_from_address' => '',
        'mail_from_name' => '',

        // Registration verification: 6-digit OTP sent over the enabled
        // channels; the account stays unverified until one succeeds.
        'otp_required' => '1',
        'otp_via_whatsapp' => '1',
        'otp_via_email' => '1',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $default = $default ?? static::$defaults[$key] ?? null;

        return Cache::rememberForever('setting:'.$key, function () use ($key, $default) {
            return static::query()->where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('setting:'.$key);
        Cache::forget('setting:view_settings');
    }

    public static function flushCache(): void
    {
        foreach (array_keys(static::$defaults) as $key) {
            Cache::forget('setting:'.$key);
        }

        Cache::forget('setting:view_settings');
    }

    /**
     * The settings shared with every layout in one cached query instead of
     * one cache/DB lookup per key per render.
     *
     * @return array<string, ?string>
     */
    public static function viewSettings(): array
    {
        $keys = [
            'shop_name', 'shop_tagline', 'shop_phone', 'shop_email', 'shop_address',
            'currency_symbol', 'announcement', 'facebook_url', 'instagram_url',
            'whatsapp_number', 'hero_title', 'hero_subtitle',
        ];

        return Cache::rememberForever('setting:view_settings', function () use ($keys) {
            $stored = static::query()->whereIn('key', $keys)->pluck('value', 'key');

            return collect($keys)
                ->mapWithKeys(fn (string $key) => [
                    $key => $stored[$key] ?? static::$defaults[$key] ?? null,
                ])
                ->all();
        });
    }
}
