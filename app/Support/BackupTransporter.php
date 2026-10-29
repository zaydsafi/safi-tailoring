<?php

namespace App\Support;

interface BackupTransporter
{
    /**
     * Copy one local file to off-site storage at the given absolute
     * remote path (root directory + file name included). Throws on any
     * failure so callers can report the exact transport error.
     */
    public function upload(string $localPath, string $remotePath): void;
}
