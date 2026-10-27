<?php

namespace App\Console\Commands;

use App\Support\Backup;
use App\Support\MailBackup;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:backup
                            {--prune= : After creating the backup, delete all but the newest N backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump the shop database to a SQL file in storage/app/backups';

    public function handle(): int
    {
        $name = Backup::create();
        $file = Backup::path($name);
        $sizeKb = round(filesize($file) / 1024, 1);

        $this->info("Backup created: storage/app/backups/{$name} ({$sizeKb} KB)");

        // Same behavior as the admin button: a copy goes straight to the
        // owner's email as an attachment.
        if (MailBackup::sendCopyOf($name)) {
            $this->info('A copy was emailed to '.(MailBackup::recipient() ?? 'the shop email').'.');
        } else {
            $this->warn('No email copy sent (set a valid shop email in settings).');
        }

        $keep = (int) ($this->option('prune') ?? 0);

        if ($keep > 0) {
            $removed = Backup::prune($keep);
            $this->info($removed === 0
                ? 'No older backups to prune.'
                : "Pruned {$removed} older backup(s); kept the newest {$keep}.");
        }

        return self::SUCCESS;
    }
}
