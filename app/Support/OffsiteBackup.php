<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

/**
 * Off-site backup storage: copies freshly created database backups to
 * the owner's cPanel hosting over FTP/FTPS so the shop survives a total
 * loss of the local server. Configured entirely from the admin Settings
 * page; never throws — a failed upload is recorded for the flash/report
 * so a broken FTP setup cannot prevent the local backup from being made.
 */
class OffsiteBackup
{
    public static function enabled(): bool
    {
        return Setting::get('offsite_backup_enabled') === '1' && filled(Setting::get('offsite_ftp_host'));
    }

    /**
     * Where the copy lands inside the hosting account, e.g.
     * "/backups/shop-backup-2026-10-29_020000.sql".
     */
    public static function remotePath(string $name): string
    {
        $directory = trim((string) Setting::get('offsite_ftp_path'));

        if ($directory === '' || $directory === '.') {
            return '/'.$name;
        }

        return '/'.trim($directory, '/').'/'.$name;
    }

    /**
     * Push one backup file to cPanel. Returns true when the copy landed.
     */
    public static function push(string $name): bool
    {
        if (! self::enabled() || ! Backup::exists($name)) {
            return false;
        }

        try {
            app(BackupTransporter::class)->upload(Backup::path($name), self::remotePath($name));
        } catch (Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }
}
