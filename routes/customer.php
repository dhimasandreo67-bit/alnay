<?php

use App\Http\Controllers\Customer\AkunController;
use App\Http\Controllers\Customer\ProfilController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('akun')->name('akun.')->group(function () {
    // Dashboard
    Route::get('/', [AkunController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // Pesanan
    Route::get('/pesanan', [AkunController::class, 'pesanan'])->name('pesanan.index');
    Route::get('/pesanan/{id}', [AkunController::class, 'pesananDetail'])->name('pesanan.detail');

    // Fitting
    Route::get('/fitting', [AkunController::class, 'fitting'])->name('fitting.index');

    // Refund
    Route::get('/refund', [AkunController::class, 'refund'])->name('refund.index');
    Route::post('/refund', fn () => back()->with('info', 'Form refund akan diimplementasikan.'))->name('refund.store');
});