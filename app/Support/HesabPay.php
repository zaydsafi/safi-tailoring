<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
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

        // Documented create-session fields: email, user_id (our order
        // reference, echoed back in webhooks), items, and the dynamic
        // browser redirect endpoints. The webhook URL itself is registered
        // in the HesabPay dashboard, not sent per-transaction.
        $payload = [
            'email' => $order->customer_email ?: (Setting::get('shop_email') ?: 'orders@safitailoring.com'),
            'user_id' => mb_substr($order->order_number, 0, 50),
            'items' => $items,
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

    /**
     * Ask HesabPay to verify a webhook's signature and timestamp, as its
     * webhooks documentation prescribes. Returns true/false when the API
     * gave a definitive answer, or null when it could not be reached —
     * callers then fall back to their own shared-secret check.
     */
    public static function verifyWebhookSignature(Request $request): ?bool
    {
        $apiKey = Setting::get('hesabpay_api_key');

        if (blank($apiKey)) {
            return null;
        }

        $payload = $request->json()->all();

        $signature = is_string($request->header('X-Signature') ?? null) ? (string) $request->header('X-Signature') : null;
        $signature ??= is_string($payload['signature'] ?? null) ? $payload['signature'] : null;
        $timestamp = (string) ($payload['timestamp'] ?? '');

        // Nothing to verify against — let the caller decide the fallback.
        if (blank($signature) || blank($timestamp)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'API-KEY ' . $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(10)->post(static::baseUrl() . '/api/v1/hesab/webhooks/verify-signature', [
                'signature' => $signature,
                'timestamp' => $timestamp,
            ]);
        } catch (\Throwable $e) {
            Log::warning('HesabPay: verify-signature request failed', ['error' => $e->getMessage()]);

            return null;
        }

        if (!$response->successful()) {
            Log::warning('HesabPay: verify-signature not confirmed', ['status' => $response->status()]);

            return false;
        }

        return filter_var($response->json('success'), FILTER_VALIDATE_BOOLEAN);
    }
}
