<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Area Customer (butuh login)
|--------------------------------------------------------------------------
| Prefix: /akun  |  Name: akun.*
*/

Route::middleware('auth')->prefix('akun')->name('akun.')->group(function () {
    Route::get('/', fn () => view('placeholder'))->name('dashboard');
    Route::get('/pesanan', fn () => view('placeholder'))->name('pesanan.index');
    Route::get('/pesanan/{id}', fn ($id) => view('placeholder'))->name('pesanan.detail');
    Route::get('/refund', fn () => view('placeholder'))->name('refund.index');
    Route::post('/refund', fn () => view('placeholder'))->name('refund.store');
    Route::get('/fitting', fn () => view('placeholder'))->name('fitting.index');
});