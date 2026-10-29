<?php

namespace App\Support;

use App\Models\Setting;
use RuntimeException;

/**
 * Ships backups to cPanel shared hosting over the FTP/FTPS extension
 * (bundled with PHP, no composer package needed). Reads its connection
 * from the admin Settings page, so the shop owner never touches .env.
 *
 * The remote path and file name are passed only as arguments to chdir,
 * mkdir and put, never executed or evaluated as SQL, so a mis-typed
 * setting cannot damage anything beyond the FTP directory itself.
 */
class FtpBackupTransporter implements BackupTransporter
{
    public function upload(string $localPath, string $remotePath): void
    {
        $host = trim((string) Setting::get('offsite_ftp_host'));
        $port = (int) Setting::get('offsite_ftp_port') ?: 21;
        $username = (string) Setting::get('offsite_ftp_username');
        $password = (string) Setting::get('offsite_ftp_password');
        $secure = Setting::get('offsite_ftp_secure') === 'ssl';

        if ($host === '' || $username === '') {
            throw new RuntimeException('Off-site FTP is not configured (host and username are required).');
        }

        $connection = $secure ? @ftp_ssl_connect($host, $port, 20) : @ftp_connect($host, -1, 20);

        if ($connection === false) {
            throw new RuntimeException("Could not connect to {$host}:{$port}".($secure ? ' (FTPS)' : ''));
        }

        try {
            if (! @ftp_login($connection, $username, $password)) {
                throw new RuntimeException("FTP login failed for {$username} — check the username and password in settings.");
            }

            // Passive mode keeps shared-hosting firewalls happy.
            @ftp_pasv($connection, true);

            [$directory, $fileName] = self::splitPath($remotePath);

            if ($directory !== '') {
                self::ensureDirectory($connection, $directory);
            }

            // Overwrites an identical file name, so re-uploading a backup
            // updates its copy instead of erroring or duplicating.
            if (! @ftp_put($connection, ($directory !== '' ? $directory.'/' : '').$fileName, $localPath, FTP_BINARY)) {
                throw new RuntimeException("FTP upload failed for {$fileName} — check the directory path and disk quota in cPanel.");
            }
        } finally {
            @ftp_close($connection);
        }
    }

    /**
     * @return array{0: string, 1: string} [directory, file name]
     */
    private static function splitPath(string $path): array
    {
        $directory = rtrim(str_replace('\\', '/', $path), '/');
        $position = strrpos($directory, '/');

        if ($position === false) {
            return ['', $directory];
        }

        return [substr($directory, 0, $position), substr($directory, $position + 1)];
    }

    /**
     * Create any missing directories in the path relative to the FTP root
     * (the directory cPanel drops the user in). An existing directory
     * makes mkdir fail harmlessly; the chdir at the end verifies it.
     */
    private static function ensureDirectory($connection, string $directory): void
    {
        $current = [];

        foreach (explode('/', $directory) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($current);

                continue;
            }

            $current[] = $segment;
            self::mkdirIfMissing($connection, implode('/', $current));
        }

        $target = implode('/', $current);

        if ($target !== '' && ! @ftp_chdir($connection, $target)) {
            throw new RuntimeException("Could not reach remote directory {$target}.");
        }
    }

    private static function mkdirIfMissing($connection, string $path): void
    {
        if (@ftp_mkdir($connection, $path) !== false) {
            return;
        }

        // Failure usually means the directory already exists; if chdir
        // also fails, the directory really is unreachable.
        if (! @ftp_chdir($connection, $path)) {
            throw new RuntimeException("Could not create remote directory {$path}.");
        }
    }
}
