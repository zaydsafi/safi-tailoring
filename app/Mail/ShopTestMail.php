<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Delivery check the admin can send from the settings page. Its failure
 * surfaces the exact SMTP error so the shop can fix host, port or
 * credentials before real order mail depends on it.
 */
class ShopTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: t('mail.test_email_subject', 'Email delivery test from :shop', [
                'shop' => Setting::get('shop_name'),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.test',
            with: ['shopName' => Setting::get('shop_name')],
        );
    }
}
