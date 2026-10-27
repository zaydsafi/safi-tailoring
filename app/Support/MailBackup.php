<?php

namespace App\Support;

use App\Mail\BackupMail;
use App\Models\Setting;
use Throwable;

/**
 * Sends a copy of a freshly created database backup to the shop owner's
 * configured email. Never throws — a failed send is recorded in the
 * email log and reported, so a broken SMTP setup can't stop a backup
 * from being created.
 */
class MailBackup
{
    public static function recipient(): ?string
    {
        $address = Setting::get('shop_email');

        if (! filled($address)) {
            return null;
        }

        return filter_var($address, FILTER_VALIDATE_EMAIL) ? $address : null;
    }

    /**
     * Email the backup copy. Returns true when an attempt was made, so
     * callers can word their flash message correctly.
     */
    public static function sendCopyOf(string $name): bool
    {
        $address = self::recipient();

        if ($address === null) {
            return false;
        }

        try {
            EmailLog::send($address, new BackupMail($name, Backup::path($name)));
        } catch (Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }
}
