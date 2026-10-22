<?php

namespace App\Models;

use Database\Factories\EmailDeliveryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Attempt log for outgoing emails: what was sent, to whom, and how the
 * transport responded. Powers the admin "Email Log" page.
 *
 * @property string $recipient
 * @property string $status
 */
class EmailDelivery extends Model
{
    /**
     * @use HasFactory<EmailDeliveryFactory>
     */
    use HasFactory;

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = ['subject', 'recipient', 'mailable_class', 'status', 'error'];

    public function failed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
