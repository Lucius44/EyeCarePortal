<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Enums\UserRole;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// --- Schedule the Reminder Command ---
// Runs daily at 8:00 AM (Make sure 'php artisan schedule:work' is running)
Schedule::command('app:send-reminders')->dailyAt('08:00');

// --- Daily Cleanup: Hard Delete 7-Day Unverified Email Accounts ---
// Runs daily at midnight to permanently remove stale, unverified registrations.
Schedule::call(function () {
    User::where('role', UserRole::Patient)
        ->whereNull('email_verified_at')
        ->where('created_at', '<', now()->subDays(7))
        ->forceDelete(); // <--- Changed from delete() to forceDelete()
})->dailyAt('00:00')->name('cleanup-unverified-users');