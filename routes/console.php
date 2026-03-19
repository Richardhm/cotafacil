<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

app(Schedule::class)->command('email:send-reminder')->hourly();

app(\Illuminate\Console\Scheduling\Schedule::class)
    ->command('email:send-pending-verifications')
    ->dailyAt('00:00');

app(\Illuminate\Console\Scheduling\Schedule::class)
    ->command('pix:alerta-vencimento')
    ->dailyAt('08:00');


