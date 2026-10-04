<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Support\HesabPay;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Customer came back from the HesabPay hosted checkout (?data={json} appended).
     */
    public function return(Request $request, string $orderNumber, ?string $nonce = null)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $data = $this->decodeData($request->query('data'));
        $success = array_key_exists('success', $data) && filter_var($data['success'], FILTER_VALIDATE_BOOLEAN);
        $transactionId = $data['transaction_id'] ?? $data['transactionId'] ?? null;
        $message = trim((string) ($data['message'] ?? ''));

        // Only a redirect carrying the nonce we generated for this order is trusted
        // to mark it paid. Anything else must be settled by the webhook or by staff.
        $verified = filled($order->payment_nonce)
            && filled($nonce)
            && hash_equals($order->payment_nonce, (string) $nonce);

        if ($success && $verified && $order->payment_method === 'hesabpay') {
            $this->markPaid($order, $transactionId ? (string) $transactionId : null, 'redirect');

            if (!$this->canViewOrder($order)) {
                return redirect()->route('orders.track')
                    ->with('success', t('flash.payment_received_prefix', 'Payment received for order') . ' ' . $order->order_number . '! ' . t('flash.enter_phone_to_view', 'Enter your phone number to view it.'));
            }

            session(['order_confirmed' => $order->order_number]);

            return redirect()->route('orders.success', $order)->with('success', t('flash.payment_thank_you', 'Payment received — thank you!'));
        }

        if ($success) {
            Log::warning('HesabPay return: success payload could not be verified', [
                'order' => $order->order_number,
                'has_nonce' => filled($nonce),
            ]);

            if (!$this->canViewOrder($order)) {
                return redirect()->route('orders.track')
                    ->with('success', t('flash.confirming_payment_prefix', 'We are confirming the payment for order') . ' ' . $order->order_number . '. ' . t('flash.check_back_moment', 'Check back in a moment.'));
            }

            session(['order_confirmed' => $order->order_number]);
            $request->session()->flash('hesabpay_confirming', true);

            return redirect()->route('orders.success', $order)
                ->with('success', t('flash.payment_confirming', 'Payment received — we are confirming it with HesabPay. This usually takes a moment.'));
        }

        if (!$this->canViewOrder($order)) {
            return redirect()->route('orders.track')
                ->with('error', t('flash.enter_order_and_phone', 'Please enter your order number and phone number to check the order status.'));
        }

        return view('shop.payment-failed', [
            'order' => $order,
            'message' => $message ?: t('flash.payment_not_completed', 'The payment was not completed.'),
        ]);
    }

    /**
     * Customer cancelled or was sent back to cancel_url / redirect_failure_url.
     */
    public function cancel(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if (!$this->canViewOrder($order)) {
            return redirect()->route('orders.track');
        }

        $data = $this->decodeData($request->query('data'));
        $message = trim((string) ($data['message'] ?? ''));

        return view('shop.payment-failed', [
            'order' => $order,
            'message' => $message ?: t('flash.payment_cancelled', 'You cancelled the payment. Your order is saved — you can pay again whenever you are ready.'),
        ]);
    }

    /**
     * Start a new HesabPay checkout for an unpaid order.
     */
    public function retry(Order $order)
    {
        abort_unless(HesabPay::enabled(), 404);
        abort_unless($this->canViewOrder($order), 403);
        abort_unless($order->payment_method === 'hesabpay' && $order->payment_status !== 'paid', 403);

        $url = HesabPay::createSession($order);

        if (!$url) {
            return back()->with('error', t('flash.hesabpay_retry_failed', 'We could not open the HesabPay checkout right now. Please try again in a moment or contact us on WhatsApp.'));
        }

        session(['order_confirmed' => $order->order_number]);

        return redirect()->away($url);
    }

    /**
     * HesabPay webhook — the authoritative payment confirmation.
     */
    public function webhook(Request $request)
    {
        $payload = $request->json()->all() ?: $request->all();

        if (is_string($payload)) {
            $payload = json_decode($payload, true) ?: [];
        }

        $data = $payload;

        foreach (['data', 'payment', 'transaction'] as $wrapper) {
            if (isset($payload[$wrapper]) && is_array($payload[$wrapper])) {
                $data = array_merge($payload, $payload[$wrapper]);
                break;
            }
        }

        $secret = Setting::get('hesabpay_webhook_secret');

        if (blank($secret)) {
            Log::warning('HesabPay webhook: rejected — no webhook secret configured in admin settings');

            return response()->json(['status' => 'disabled'], 503);
        }

        if (!$this->webhookAuthorized($request, $data, $secret)) {
            Log::warning('HesabPay webhook: rejected — bad secret or signature');

            return response()->json(['status' => 'unauthorized'], 401);
        }

        $orderNumber = $data['order_id'] ?? $data['orderId'] ?? $data['order_number'] ?? null;
        $transactionId = $data['transaction_id'] ?? $data['transactionId'] ?? $data['trx_id'] ?? null;

        $order = null;

        if (filled($orderNumber)) {
            $order = Order::where('order_number', $orderNumber)->first();
        }

        if (!$order && filled($transactionId)) {
            $order = Order::where('payment_transaction_id', $transactionId)->first();
        }

        if (!$order || $order->payment_method !== 'hesabpay') {
            return response()->json(['status' => 'ignored']);
        }

        if ($this->payloadIsSuccess($data)) {
            $this->markPaid($order, $transactionId ? (string) $transactionId : null, 'webhook');
        }

        return response()->json(['status' => 'ok']);
    }

    private function payloadIsSuccess(array $data): bool
    {
        if (array_key_exists('success', $data)) {
            return filter_var($data['success'], FILTER_VALIDATE_BOOLEAN);
        }

        $status = strtolower((string) ($data['status'] ?? $data['payment_status'] ?? ''));

        return in_array($status, ['success', 'successful', 'paid', 'completed', 'complete'], true);
    }

    /**
     * Accept a matching shared secret (header or payload) or an HMAC-SHA256
     * signature of the raw body. When the signature travels inside the JSON
     * payload it must be computed over the payload without the signature field.
     */
    private function webhookAuthorized(Request $request, array $data, string $secret): bool
    {
        $provided = $request->header('X-HesabPay-Secret')
            ?? $request->header('X-Webhook-Secret')
            ?? (is_string($data['secret'] ?? null) ? $data['secret'] : '');

        if (filled($provided) && hash_equals($secret, (string) $provided)) {
            return true;
        }

        $signature = $request->header('X-HesabPay-Signature')
            ?? $request->header('X-Signature')
            ?? (is_string($data['signature'] ?? null) ? $data['signature'] : '');

        $signature = preg_replace('/^sha256=/i', '', trim((string) $signature));

        if ($signature === '') {
            return false;
        }

        $candidates = [(string) $request->getContent()];

        if (array_key_exists('signature', $data)) {
            $payload = $data;
            unset($payload['signature']);
            $candidates[] = (string) json_encode($payload);
        }

        foreach ($candidates as $candidate) {
            $hex = hash_hmac('sha256', $candidate, $secret);
            $base64 = base64_encode(hash_hmac('sha256', $candidate, $secret, true));

            if (hash_equals($hex, strtolower($signature)) || hash_equals($base64, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function markPaid(Order $order, ?string $transactionId, string $source): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_transaction_id' => $transactionId ?: $order->payment_transaction_id,
        ]);

        $order->statusHistories()->create([
            'status' => $order->status,
            'note' => 'Payment received via HesabPay'
                . ($transactionId ? ' (TRX: ' . $transactionId . ')' : '')
                . ' [' . $source . ']',
        ]);

        WhatsApp::notifyCustomer(
            $order->customer_phone,
            'Safi Tailoring: we received your payment for order ' . $order->order_number
            . '. We will start working on it right away. Track your order: ' . route('orders.track')
        );
    }

    private function canViewOrder(Order $order): bool
    {
        return session('order_confirmed') === $order->order_number
            || (auth()->check() && (auth()->id() === $order->user_id || auth()->user()->isAdmin()));
    }

    private function decodeData(?string $raw): array
    {
        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
