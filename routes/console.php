<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every day at midnight to mark overdue installments and apply penalties
Schedule::command('kuweza:apply-overdue-penalties')->dailyAt('00:00');
