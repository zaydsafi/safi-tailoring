<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public int $ttlMinutes,
        public ?string $name = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: t('mail.otp_subject', 'Your verification code: :code', ['code' => $this->code]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.otp',
            with: ['code' => $this->code, 'ttl' => $this->ttlMinutes],
        );
    }
}
