<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agendamento diário de envio de lembretes de vencimento (FR-031, FR-032)
Schedule::command('expenses:send-reminders --days=2')
    ->dailyAt('08:00')
    ->name('expenses:send-daily-reminders')
    ->withoutOverlapping();
