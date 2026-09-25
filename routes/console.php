<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * E-Library Automated Midnight Backup:
 * Runs every day at 12:00 AM (00:00 midnight) to Server Drive D.
 */
Schedule::command('backup:run --type=auto')
    ->dailyAt('00:00')
    ->timezone('Asia/Phnom_Penh')
    ->name('auto-midnight-backup')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));

