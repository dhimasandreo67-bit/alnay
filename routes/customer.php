<?php

use App\Http\Controllers\Customer\AkunController;
use App\Http\Controllers\Customer\ProfilController;
use App\Http\Controllers\SewaController; // Pastikan ini di-import
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FittingController;

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
    Route::get('/fitting', [FittingController::class, 'create'])->name('fitting.create');
    Route::post('/fitting', [FittingController::class, 'store'])->name('fitting.store');
    
    // Route Riwayat (Harus di atas /fitting/{id})
    Route::get('/fitting/riwayat', [FittingController::class, 'index'])->name('akun.fitting.index');
    
    Route::get('/fitting/{id}', [FittingController::class, 'show'])->name('fitting.show');
    Route::patch('/fitting/{id}/cancel', [FittingController::class, 'cancel'])->name('fitting.cancel');
});

Route::post('/midtrans/webhook', [SewaController::class, 'webhook'])->name('midtrans.webhook');