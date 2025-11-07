<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CheckFinancialAlertsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule financial alerts check (runs every hour)
Schedule::job(new CheckFinancialAlertsJob())->hourly();

// Schedule weekly digest (runs daily at 9 AM to check user preferences)
// Users can choose their preferred day in settings
Schedule::command('digest:send-weekly')->dailyAt('09:00');
