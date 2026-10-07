<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('invoices:send-reminders')->dailyAt('08:00')->timezone('Asia/Jakarta');

// Reset data demo tiap malam (hanya jalan kalau DEMO_MODE=true)
\Illuminate\Support\Facades\Schedule::command('demo:reset --force')->dailyAt('03:00')->when(fn () => config('demo.enabled'));
