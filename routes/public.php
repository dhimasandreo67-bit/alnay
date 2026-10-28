<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\KoleksiController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SewaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FittingController;
use App\Http\Controllers\BeliController;
use App\Http\Controllers\TestimoniController;

/*
|--------------------------------------------------------------------------
| Route Publik (tanpa login)
|--------------------------------------------------------------------------
*/

// Beranda
Route::get('/', [HomeController::class, 'index'])->name('beranda');

// Koleksi
Route::get('/koleksi', [KoleksiController::class, 'index'])->name('koleksi.index');
Route::get('/koleksi/{kode}', fn ($kode) => view('placeholder'))->name('koleksi.kategori');

// Detail produk
Route::get('/produk/{sku}', [ProdukController::class, 'show'])->name('produk.detail');

// Alur Sewa
Route::post('/sewa/simpan', [SewaController::class, 'simpanSession'])->name('sewa.simpan');
Route::get('/sewa/checkout', [SewaController::class, 'checkout'])->name('sewa.checkout');
Route::post('/sewa/checkout', [SewaController::class, 'store'])->name('sewa.store');
Route::get('/sewa/konfirmasi/{id}', [SewaController::class, 'konfirmasi'])->name('sewa.konfirmasi');

// Halaman statis
Route::get('/cara-sewa', fn () => view('customer.cara-sewa'))->name('cara-sewa');
Route::get('/tentang', fn () => view('customer.tentang'))->name('tentang');
Route::get('/testimoni', [TestimoniController::class, 'index'])->name('testimoni');
Route::get('/kontak', fn () => view('placeholder'))->name('kontak');

// Reservasi fitting (opsional, bukan syarat sewa)
Route::get('/reservasi-fitting', [FittingController::class, 'create'])->name('fitting.create');
Route::post('/reservasi-fitting', [FittingController::class, 'store'])->name('fitting.store');
Route::get('/reservasi-fitting/{id}', [FittingController::class, 'show'])->name('fitting.show');
Route::post('/reservasi-fitting/{id}/cancel', [FittingController::class, 'cancel'])->name('fitting.cancel');

// Alur Beli
Route::post('/beli/simpan', [BeliController::class, 'simpanSession'])->name('beli.simpan');
Route::get('/beli/checkout', [BeliController::class, 'checkout'])->name('beli.checkout');
Route::post('/beli/checkout', [BeliController::class, 'store'])->name('beli.store');
Route::get('/beli/konfirmasi/{id}', [BeliController::class, 'konfirmasi'])->name('beli.konfirmasi');

// Event — TAUTAN KELUAR ke sistem terpisah (event.alnay.id)
Route::get('/event', function () {
    return redirect()->away(config('alnay.event.url'));
})->name('event');