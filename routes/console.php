<?php

use App\Jobs\ProcessInstallmentRemindersJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// === FITUR BARU: Reminder Jatuh Tempo Angsuran ===
Schedule::job(new ProcessInstallmentRemindersJob)->dailyAt('08:00')
    ->name('process-installment-reminders')
    ->withoutOverlapping();
