<?php

namespace App\Support;

use App\Mail\CustomOrderStatusMail;
use App\Mail\OrderUpdatedMail;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use App\Models\Setting;

/**
 * Customer notification fanout for order events: WhatsApp and email,
 * each switchable from the admin settings page. Never throws — a failed
 * channel is logged and skipped so the shop keeps running.
 */
class Notify
{
    public static function emailEnabled(): bool
    {
        return Setting::get('notify_email_enabled') === '1';
    }

    public static function whatsappEnabled(): bool
    {
        return Setting::get('notify_whatsapp_enabled') === '1';
    }

    public static function customOrderStatusChange(CustomOrderRequest $request, string $statusKey, ?string $assignedTo = null): void
    {
        $label = CustomOrderRequest::STATUSES[$statusKey] ?? $statusKey;

        if (! self::emailEnabled()) {
            return;
        }

        $address = filled($request->customer_email) ? (string) $request->customer_email : null;

        if ($address === null) {
            return;
        }

        $message = 'Safi Tailoring: your custom order request is now '.$label.'.';

        if ($assignedTo !== null) {
            $message .= ' It has been assigned to our tailoring team.';
        }

        $message .= ' Track your request with us over WhatsApp or phone.';

        try {
            EmailLog::send($address, new CustomOrderStatusMail($request, $label, $message));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function orderStatus(Order $order, string $statusKey, ?string $note = null): void
    {
        $label = Order::STATUSES[$statusKey] ?? $statusKey;

        $message = 'Safi Tailoring: your order '.$order->order_number.' is now '.$label.'.';

        if (filled($note)) {
            $message .= ' Note: '.$note;
        }

        $message .= ' Track: '.route('orders.track');

        self::email($order, t('mail.order_status_headline', 'Order :number is now :status', [
            'number' => $order->order_number,
            'status' => $label,
        ]), $message);

        if (self::whatsappEnabled()) {
            WhatsApp::send($order->customer_phone, $message);
        }
    }

    public static function paymentReceived(Order $order, ?string $transactionId = null): void
    {
        $message = 'Safi Tailoring: payment received for order '.$order->order_number
            .'. We will start working on it right away. Track your order: '.route('orders.track');

        self::email($order, t('mail.order_paid_headline', 'Payment received for order :number', [
            'number' => $order->order_number,
        ]), $message);

        if (self::whatsappEnabled()) {
            WhatsApp::send($order->customer_phone, $message);
        }
    }

    public static function orderPlaced(Order $order, string $summary): void
    {
        $message = 'Safi Tailoring: order '.$order->order_number.' placed. '.$summary
            .' Track: '.route('orders.track');

        self::email($order, t('mail.order_placed_headline', 'Order :number received', [
            'number' => $order->order_number,
        ]), $message);

        if (self::whatsappEnabled()) {
            WhatsApp::send($order->customer_phone, $message);
        }
    }

    /**
     * Send one order email. Skipped when disabled or no customer address;
     * failures are reported, never raised.
     */
    private static function email(Order $order, string $headline, string $message): void
    {
        if (! self::emailEnabled()) {
            return;
        }

        $address = filled($order->customer_email) ? (string) $order->customer_email : null;

        if ($address === null) {
            return;
        }

        try {
            EmailLog::send($address, new OrderUpdatedMail($order, $headline, $message));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
