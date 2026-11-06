<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Nonaktifkan akun yang tidak aktif ≥ 360 hari, tiap hari jam 02:00
Schedule::command('users:deactivate-inactive --days=360')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()          // kalau pakai multi-server
    ->runInBackground();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
