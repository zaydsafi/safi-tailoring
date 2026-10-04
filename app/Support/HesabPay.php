<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HesabPay
{
    public static function enabled(): bool
    {
        return Setting::get('hesabpay_enabled') === '1' && filled(Setting::get('hesabpay_api_key'));
    }

    public static function baseUrl(): string
    {
        return Setting::get('hesabpay_environment') === 'sandbox'
            ? 'https://api-sandbox.hesab.com'
            : 'https://api.hesab.com';
    }

    /**
     * Create a HesabPay checkout session for the order.
     * Returns the hosted payment URL, or null when the session could not be created.
     */
    public static function createSession(Order $order): ?string
    {
        $apiKey = Setting::get('hesabpay_api_key');

        if (blank($apiKey)) {
            return null;
        }

        $order->loadMissing('items');

        $items = $order->items->map(fn ($item) => [
            'id' => (string) ($item->product_id ?? $item->id),
            'product_id' => (string) ($item->product_id ?? $item->id),
            'name' => $item->product_name . ($item->size ? ' (' . $item->size . ')' : ''),
            'price' => (float) $item->line_total,
        ])->values()->all();

        if ((float) $order->delivery_fee > 0) {
            $items[] = [
                'id' => 'delivery',
                'product_id' => 'delivery',
                'name' => 'Delivery fee',
                'price' => (float) $order->delivery_fee,
            ];
        }

        // The nonce lets the return endpoint tell a genuine HesabPay redirect
        // apart from a forged one. It travels in the path so HesabPay can append
        // "?data={json}" without breaking the URL.
        $nonce = Str::random(40);
        $order->forceFill(['payment_nonce' => $nonce])->save();

        $returnUrl = route('hesabpay.return', ['orderNumber' => $order->order_number, 'nonce' => $nonce]);
        $cancelUrl = route('hesabpay.cancel', $order->order_number);

        $payload = [
            'order_id' => $order->order_number,
            'items' => $items,
            'email' => $order->customer_email ?: (Setting::get('shop_email') ?: 'orders@safitailoring.com'),
            'callback_url' => route('webhooks.hesabpay'),
            'success_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'redirect_success_url' => $returnUrl,
            'redirect_failure_url' => $cancelUrl,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'API-KEY ' . $apiKey,
                'Accept' => 'application/json',
            ])->timeout(25)->post(static::baseUrl() . '/api/v1/payment/create-session', $payload);
        } catch (\Throwable $e) {
            Log::error('HesabPay: create-session request failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (!$response->successful()) {
            Log::error('HesabPay: create-session rejected', [
                'order' => $order->order_number,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $url = $response->json('url')
            ?? $response->json('payment_url')
            ?? $response->json('redirect_url')
            ?? ($response->json('data.url'));

        if (blank($url)) {
            Log::error('HesabPay: create-session response has no payment URL', [
                'order' => $order->order_number,
                'body' => $response->body(),
            ]);

            return null;
        }

        return (string) $url;
    }
}
