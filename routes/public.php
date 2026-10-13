<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Publik (tanpa login)
|--------------------------------------------------------------------------
*/

// Beranda
Route::get('/', [HomeController::class, 'index'])->name('beranda');

// Koleksi
Route::get('/koleksi', fn () => view('placeholder'))->name('koleksi.index');
Route::get('/koleksi/{kode}', fn ($kode) => view('placeholder'))->name('koleksi.kategori');

// Detail produk
Route::get('/produk/{sku}', fn ($sku) => view('placeholder'))->name('produk.detail');

// Halaman statis
Route::get('/cara-sewa', fn () => view('placeholder'))->name('cara-sewa');
Route::get('/tentang', fn () => view('placeholder'))->name('tentang');
Route::get('/testimoni', fn () => view('placeholder'))->name('testimoni');
Route::get('/kontak', fn () => view('placeholder'))->name('kontak');

// Reservasi fitting (opsional, bukan syarat sewa)
Route::get('/reservasi-fitting', fn () => view('placeholder'))->name('fitting.create');
Route::post('/reservasi-fitting', fn () => view('placeholder'))->name('fitting.store');

// Keranjang — tampilan saja (fitur keranjang belum ada di spesifikasi final)
Route::get('/keranjang', fn () => view('placeholder'))->name('keranjang.index');

// Event — TAUTAN KELUAR ke sistem terpisah (event.alnay.id)
// JANGAN buat route, query, atau model event di project ini.
Route::get('/event', function () {
    return redirect()->away(config('alnay.event.url'));
})->name('event');