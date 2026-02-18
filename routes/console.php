<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// --- Schedule the Reminder Command ---
// Runs daily at 8:00 AM (Make sure 'php artisan schedule:work' is running)
Schedule::command('app:send-reminders')->dailyAt('08:00');