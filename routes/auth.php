<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth — Login, Register, Logout
|--------------------------------------------------------------------------
| Satu pintu untuk semua role. Redirect setelah login ditentukan nanti.
| Sekarang hanya placeholder — logika auth tidak termasuk lingkup tugas ini.
*/

Route::middleware('guest')->group(function () {
    Route::get('/masuk', fn () => view('placeholder'))->name('masuk');
    Route::post('/masuk', fn () => view('placeholder'))->name('masuk.attempt');

    Route::get('/daftar', fn () => view('placeholder'))->name('daftar');
    Route::post('/daftar', fn () => view('placeholder'))->name('daftar.store');
});

Route::middleware('auth')->post('/keluar', fn () => view('placeholder'))->name('keluar');