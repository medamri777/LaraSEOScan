<?php

use App\Jobs\SyncSearchConsoleData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks — Seo4ma / Seo4ma
|--------------------------------------------------------------------------
*/

// Run rank checks daily at 03:00 UTC (5am Morocco time)
Schedule::command('ranktracker:check-all')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/rank-tracker.log'));

// Process the queue every minute (database driver)
Schedule::command('queue:work --stop-when-empty --max-jobs=50')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();

// Sync Google Search Console data daily at 06:00 UTC (7am Morocco time)
Schedule::job(new SyncSearchConsoleData)
    ->dailyAt('06:00')
    ->withoutOverlapping();

// Purge old tool usage logs daily at midnight (keeps table small)
Schedule::command('usage:reset-daily')
    ->daily()
    ->runInBackground();

// Check for expired PayPal subscriptions/trials and downgrade to free (fallback for missed webhooks)
Schedule::command('subscriptions:check-expired')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
