<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\CartController;

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
Route::get('/cara-sewa', fn () => view('customer.cara-sewa'))->name('cara-sewa');
Route::get('/tentang', fn () => view('customer.tentang'))->name('tentang');
Route::get('/testimoni', fn () => view('placeholder'))->name('testimoni');
Route::get('/kontak', fn () => view('placeholder'))->name('kontak');

// Reservasi fitting (opsional, bukan syarat sewa)
Route::get('/reservasi-fitting', fn () => view('placeholder'))->name('fitting.create');
Route::post('/reservasi-fitting', fn () => view('placeholder'))->name('fitting.store');

// ✅ HAPUS BARIS INI (sudah ada CartController):
// Route::get('/keranjang', fn () => view('placeholder'))->name('keranjang.index');

// Event — TAUTAN KELUAR ke sistem terpisah (event.alnay.id)
Route::get('/event', function () {
    return redirect()->away(config('alnay.event.url'));
})->name('event');

// ✅ Route Keranjang yang benar (pakai CartController):
Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang.index');
Route::patch('/keranjang/{id}/qty', [CartController::class, 'updateQuantity'])->name('keranjang.updateQty');
Route::delete('/keranjang/{id}', [CartController::class, 'remove'])->name('keranjang.remove');
Route::post('/keranjang/hapus-pilihan', [CartController::class, 'removeSelected'])->name('keranjang.removeSelected');