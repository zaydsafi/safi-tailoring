<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Applies the SMTP settings saved in the admin Settings page on top of the
 * .env mail config. When no SMTP host is saved the .env/default driver keeps
 * working, so local development keeps using "log" without setup.
 */
class MailerSettings
{
    public static function smtpConfigured(): bool
    {
        return filled(Setting::get('mail_smtp_host'));
    }

    public static function apply(): void
    {
        if (! self::smtpConfigured()) {
            return;
        }

        $host = (string) Setting::get('mail_smtp_host');
        $port = (int) Setting::get('mail_smtp_port') ?: 587;
        $username = (string) Setting::get('mail_smtp_username');
        $password = (string) Setting::get('mail_smtp_password');
        $encryption = Setting::get('mail_smtp_encryption');

        $config = [
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.encryption' => in_array($encryption, ['tls', 'ssl'], true) ? $encryption : null,
        ];

        if (filled($username)) {
            $config['mail.mailers.smtp.username'] = $username;
        }

        if (filled($password)) {
            $config['mail.mailers.smtp.password'] = $password;
        }

        $fromAddress = Setting::get('mail_from_address');
        $fromName = Setting::get('mail_from_name');

        if (filled($fromAddress)) {
            $config['mail.from.address'] = $fromAddress;
        }

        if (filled($fromName)) {
            $config['mail.from.name'] = $fromName;
        }

        config($config);
    }
}
