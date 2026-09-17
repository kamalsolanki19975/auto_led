<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* ---------------- Scheduler ---------------- */
Schedule::command('devices:check-offline')->everyMinute()->withoutOverlapping();
Schedule::command('campaigns:check-delivery')->hourly();
Schedule::command('playback:aggregate')->hourly();
Schedule::command('settlements:generate')->monthlyOn(1, '02:00');
Schedule::command('sims:check-expiry')->dailyAt('06:00');
Schedule::command('warranties:check-expiry')->dailyAt('06:15');
Schedule::command('notifications:process')->dailyAt('07:00');
Schedule::command('reports:cleanup')->weekly();
