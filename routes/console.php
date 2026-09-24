<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Default command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// FastPay collection results (paid / failed) → file_details.status.
// Laravel 12 does not read app/Console/Kernel.php, so it must be scheduled here.
Schedule::command('sync:fastpay-status')
    ->everyTenMinutes()
    ->withoutOverlapping();

// ✅ YOUR MEMBERSHIP MAIL SCHEDULER
Schedule::command('membership:send-scheduled-mails')
    ->dailyAt('08:00')
    ->timezone('Asia/Kolkata'); // optional but recommended