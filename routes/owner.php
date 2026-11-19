<?php

use App\Http\Middleware\EnsureOwner;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Area Owner (butuh login)
|--------------------------------------------------------------------------
| Akses dibatasi ke role owner lewat EnsureOwner (setelah auth:web).
| Prefix: /owner  |  Name: owner.*
|
| ⚠️ Chip "Studio Bandung Dago" di Figma TIDAK dibuat
|    (aturan bisnis: lokasi fitting hanya satu, tidak ada pilihan cabang).
*/

Route::middleware(['auth:web', EnsureOwner::class])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', fn () => view('placeholder'))->name('dashboard');

    Route::get('/laporan', fn () => view('placeholder'))->name('laporan');
    Route::get('/admin', fn () => view('placeholder'))->name('admin.index');
    Route::get('/pengaturan', fn () => view('placeholder'))->name('pengaturan');
});