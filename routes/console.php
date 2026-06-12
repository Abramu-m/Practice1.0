<?php

use App\Jobs\PollNhifClaimStatusJob;
use App\Jobs\SyncNhifTariffsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SyncNhifTariffsJob(config('nhif.facility_code')))->dailyAt('02:00');
Schedule::job(new PollNhifClaimStatusJob())->dailyAt('06:00');

// Phase 6.2 — bidirectional sync (practice.local <-> janet-healthcare.com).
Schedule::command('sync:run')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->when(fn () => config('sync.enabled'));
