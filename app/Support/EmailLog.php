<?php

namespace App\Support;

use App\Models\EmailDelivery;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Central "send an email and record the attempt" helper. Every outgoing
 * email in the app uses this so the admin Email Log has one consistent
 * record of attempts, successes and transport errors. A transport failure
 * is reported (never raised) so a broken SMTP setup can't break checkout,
 * OTP or order flows.
 */
class EmailLog
{
    /**
     * Send and log. Returns the delivery record so callers (OTP, test email)
     * can tell success from failure after the fact.
     *
     * @param  non-empty-string  $address
     */
    public static function send(string $address, object $mailable): EmailDelivery
    {
        $record = EmailDelivery::create([
            'subject' => method_exists($mailable, 'envelope') ? $mailable->envelope()->subject : null,
            'recipient' => $address,
            'mailable_class' => $mailable::class,
            'status' => EmailDelivery::STATUS_SENT,
        ]);

        try {
            Mail::to($address)->send($mailable);
        } catch (Throwable $e) {
            $record->update([
                'status' => EmailDelivery::STATUS_FAILED,
                'error' => mb_substr($e->getMessage(), 0, 65000),
            ]);

            report($e);
        }

        return $record;
    }
}
