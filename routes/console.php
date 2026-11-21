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

// Bersihkan notifikasi lama, tiap hari jam 01:00 pagi
Schedule::command('notifikasi:bersihkan')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Lepaskan stok pesanan online yang belum dibayar setelah 60 menit (tunai: 24 jam)
Schedule::command('pesanan:lepas-stok')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Batalkan otomatis refund yang disetujui tapi barang tidak dikirim sebelum batas waktu
Schedule::command('refund:kedaluwarsa')
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->runInBackground();

// Hitung ulang denda keterlambatan sewa setiap pergantian hari
Schedule::command('denda:sinkron-telat')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground();

// Kirim pengingat ke admin: sewa jatuh tempo besok/hari ini dan yang terlambat
Schedule::command('notifikasi:pengingat-sewa')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
