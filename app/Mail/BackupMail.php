<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A copy of a database backup sent to the shop owner's email. The SQL
 * file rides along as an attachment so there is always a second copy
 * outside the server, made the moment a backup is taken.
 */
class BackupMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  non-empty-string  $name  backup file name, e.g. shop-backup-....sql
     * @param  non-empty-string  $path  absolute path to the backup file
     */
    public function __construct(
        public readonly string $name,
        public readonly string $path,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: t('mail.backup_subject', 'Database backup :name — :shop', [
                'name' => $this->name,
                'shop' => Setting::get('shop_name'),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.backup',
            with: [
                'shopName' => Setting::get('shop_name'),
                'name' => $this->name,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->path)->as($this->name),
        ];
    }
}
