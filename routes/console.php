<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily templates scan at 2 AM
Schedule::command('templates:scan')->dailyAt('02:00');

// Chrome cleanup at 5 AM (3 hours after scan completes)
Schedule::command('chrome:cleanup --force')->dailyAt('05:00');

// Weekly garbage collector on Sunday at 3 AM
Schedule::command('templates:gc')->weekly()->sundays()->at('03:00');

// Clear cache weekly on Sunday at 4 AM
Schedule::command('cache:clear')->weekly()->sundays()->at('04:00');
