<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly database backup. Keeps the newest 30 files so
// storage/app/backups does not grow without bound.
Schedule::command('shop:backup --prune=30')->dailyAt('02:00');
