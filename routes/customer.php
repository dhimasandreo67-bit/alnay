<?php

use App\Http\Controllers\Customer\ProfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Area Customer (butuh login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('akun')->name('akun.')->group(function () {
    // Dashboard Akun
    Route::get('/', fn () => view('placeholder'))->name('dashboard');

    // Profil
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // Pesanan
    Route::get('/pesanan', fn () => view('placeholder'))->name('pesanan.index');
    Route::get('/pesanan/{id}', fn ($id) => view('placeholder'))->name('pesanan.detail');

    // Lainnya
    Route::get('/refund', fn () => view('placeholder'))->name('refund.index');
    Route::post('/refund', fn () => view('placeholder'))->name('refund.store');
    Route::get('/fitting', fn () => view('placeholder'))->name('fitting.index');
});