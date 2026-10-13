<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Area Admin (butuh login)
|--------------------------------------------------------------------------
| Middleware role belum ada — tambahkan saat tabel user & migrasi siap.
| Prefix: /admin  |  Name: admin.*
|
| Menu mengikuti Figma dashboard admin. Item di luar ERD ditandai TODO.
*/

    Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('placeholder'))->name('dashboard');

    // Master data
    Route::get('/kategori', fn () => view('placeholder'))->name('kategori.index');
    Route::get('/produk', fn () => view('placeholder'))->name('produk.index');
    Route::get('/produk/tambah', fn () => view('placeholder'))->name('produk.create');
    Route::get('/produk/{id}/edit', fn ($id) => view('placeholder'))->name('produk.edit');
    Route::get('/pengguna', fn () => view('placeholder'))->name('pengguna.index');

    // Transaksi
    Route::get('/transaksi', fn () => view('placeholder'))->name('transaksi.index');
    Route::get('/transaksi/baru', fn () => view('placeholder'))->name('transaksi.create');

    // Sewa & jaminan
    Route::get('/sewa', fn () => view('placeholder'))->name('sewa.index');
    Route::get('/jaminan', fn () => view('placeholder'))->name('jaminan.index');

    // Layanan atelier
    Route::get('/refund', fn () => view('placeholder'))->name('refund.index');
    Route::get('/pengiriman', fn () => view('placeholder'))->name('pengiriman.index');
    Route::get('/fitting', fn () => view('placeholder'))->name('fitting.index');
    Route::get('/rating', fn () => view('placeholder'))->name('rating.index');

    // Sistem
    Route::get('/audit-log', fn () => view('placeholder'))->name('audit.index');
});