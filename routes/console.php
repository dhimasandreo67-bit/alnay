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

// --- TAMBAHKAN BLOK INI ---
// Bersihkan notifikasi lama, tiap hari jam 01:00 pagi
Schedule::command('notifikasi:bersihkan')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();
// --------------------------

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');