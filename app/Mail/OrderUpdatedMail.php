<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $headline,
        public string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: t('mail.order_updated_subject', 'Order :number update', ['number' => $this->order->order_number]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-updated',
            with: [
                'order' => $this->order,
                'headline' => $this->headline,
                'message' => $this->message,
                'shopName' => \App\Models\Setting::get('shop_name', 'Safi Tailoring'),
            ],
        );
    }
}
