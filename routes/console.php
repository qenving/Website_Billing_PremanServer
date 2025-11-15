<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// HBM Scheduled Tasks
Schedule::command('hbm:process-invoices')->daily();
Schedule::command('hbm:check-overdue')->hourly();
Schedule::command('hbm:auto-suspend')->daily();
Schedule::command('hbm:auto-terminate')->weekly();
Schedule::command('hbm:cleanup-logs')->weekly();
