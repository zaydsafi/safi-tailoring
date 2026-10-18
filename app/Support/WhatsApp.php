<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsApp
{
    /**
     * Normalize a phone number to international digits (no "+", no spaces).
     * Leading "00" is stripped; a single leading "0" is replaced with the shop's country code.
     */
    public static function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (blank($digits)) {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $country = preg_replace('/\D+/', '', (string) Setting::get('whatsapp_country_code', '93'));
            $digits = ($country ?: '93') . substr($digits, 1);
        }

        return $digits !== '' ? $digits : null;
    }

    /**
     * Free WhatsApp chat link (works with no API setup — opens WhatsApp with a pre-filled message).
     */
    public static function link(?string $phone, string $message = ''): ?string
    {
        $number = static::normalize($phone);

        if ($number === null) {
            return null;
        }

        $url = 'https://wa.me/' . $number;

        return $message !== '' ? $url . '?text=' . rawurlencode($message) : $url;
    }

    /**
     * Link to the shop's own WhatsApp number (Setting: whatsapp_number).
     */
    public static function shopLink(string $message = ''): ?string
    {
        return static::link(Setting::get('whatsapp_number'), $message);
    }

    public static function cloudEnabled(): bool
    {
        return Setting::get('whatsapp_cloud_enabled') === '1'
            && filled(Setting::get('whatsapp_phone_number_id'))
            && filled(Setting::get('whatsapp_cloud_token'));
    }

    /**
     * Send a WhatsApp message through Meta's Cloud API (free tier).
     * Returns false when the Cloud API is not configured or the send fails — never throws.
     */
    public static function send(?string $phone, string $message): bool
    {
        $number = static::normalize($phone);

        if ($number === null || blank($message) || !static::cloudEnabled()) {
            return false;
        }

        try {
            $response = Http::withToken(Setting::get('whatsapp_cloud_token'))
                ->timeout(15)
                ->post('https://graph.facebook.com/v21.0/' . Setting::get('whatsapp_phone_number_id') . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $number,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('WhatsApp Cloud API: send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp Cloud API: request error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deliver the registration OTP. When the shop configured a WhatsApp
     * authentication template we use Meta's proper authentication message;
     * otherwise fall back to a plain text message (which only delivers
     * inside an already-open 24h conversation window with the customer).
     */
    public static function sendOtp(?string $phone, string $code, int $ttlMinutes): bool
    {
        $number = static::normalize($phone);

        if ($number === null || !static::cloudEnabled()) {
            return false;
        }

        $template = Setting::get('whatsapp_auth_template');

        if (filled($template)) {
            $ok = static::postMessage([
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $number,
                'type' => 'authentication',
                'authentication' => [
                    'otp_type' => 'one_time',
                    'code' => $code,
                ],
            ], 'otp (authentication template)');

            if ($ok) {
                return true;
            }
        }

        return static::send($phone, t('otp.whatsapp_body',
            'Safi Tailoring: your verification code is :code (valid for :minutes minutes). Do not share it.',
            ['code' => $code, 'minutes' => $ttlMinutes]));
    }

    private static function postMessage(array $payload, string $context): bool
    {
        try {
            $response = Http::withToken(Setting::get('whatsapp_cloud_token'))
                ->timeout(15)
                ->post('https://graph.facebook.com/v21.0/' . Setting::get('whatsapp_phone_number_id') . '/messages', $payload);

            if (!$response->successful()) {
                Log::warning('WhatsApp Cloud API: send failed (' . $context . ')', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp Cloud API: request error (' . $context . ')', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Notify the customer about an order event (no-op when Cloud API is not configured).
     */
    public static function notifyCustomer(?string $phone, string $message): bool
    {
        return static::send($phone, $message);
    }

    /**
     * Notify the shop owner about an event (no-op when Cloud API is not configured).
     */
    public static function notifyShop(string $message): bool
    {
        return static::send(Setting::get('whatsapp_number'), $message);
    }
}
