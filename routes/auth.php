<?php

use App\Http\Controllers\Auth\DaftarController;
use App\Http\Controllers\Auth\MasukController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Masuk
    Route::get('/masuk', [MasukController::class, 'create'])->name('masuk');
    Route::post('/masuk', [OtpController::class, 'sendLogin'])->name('masuk.attempt');

    // OTP Login
    Route::get('/masuk/otp', fn () => view('auth.masuk-otp', ['email' => request('email')]))->name('masuk.otp.form');
    Route::post('/masuk/otp', [OtpController::class, 'verifyLogin'])->name('masuk.otp.verify');

    // Daftar
    Route::get('/daftar', [DaftarController::class, 'create'])->name('daftar');
    Route::post('/daftar', [OtpController::class, 'sendDaftar'])->name('daftar.store');

    // OTP Daftar
    Route::get('/daftar/otp', fn () => view('auth.daftar-otp'))->name('daftar.otp.form');
    Route::post('/daftar/otp', [OtpController::class, 'verifyDaftar'])->name('daftar.otp.verify');
});

// Google OAuth
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleController::class, 'callback'])->name('auth.google.callback');

Route::middleware('auth')->post('/keluar', [MasukController::class, 'destroy'])->name('keluar');

