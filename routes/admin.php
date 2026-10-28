<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TransaksiController;
use App\Http\Controllers\Admin\ReservasiFittingController;
use App\Http\Controllers\Admin\RatingController;
/*
|--------------------------------------------------------------------------
| Area Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    
    // --- ROUTE LOGIN (Harus di luar middleware auth) ---
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.store');

    // --- ROUTE YANG BUTUH LOGIN ---
    Route::middleware('auth:admin')->group(function () {
        
        Route::post('/logout', [AdminLoginController::class, 'logout'])
            ->name('logout')
            ->middleware('auth:admin');

        // 1. Redirect halaman utama /admin langsung ke dashboard
        Route::get('/', fn () => redirect()->route('admin.dashboard'));

        // 2. Dashboard Utama
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // 3. Master data
        // --- Kategori (Diperbaiki agar mengarah ke Controller, bukan redirect) ---
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::get('/kategori/tambah', [KategoriController::class, 'create'])->name('kategori.create');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::get('/kategori/{id}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
        // --- Kategori (Menggunakan rute editan Anda) ---
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
        Route::get('/kategori/tambah', fn () => redirect()->route('admin.kategori.index'));
        Route::get('/kategori/{id}/edit', fn ($id) => redirect()->route('admin.kategori.index'));
        
        // --- Produk (Menggunakan rute baru dari GitHub) ---
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/produk/tambah', [ProdukController::class, 'create'])->name('produk.create');
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('/produk/{id}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
        Route::get('/pengguna', fn () => view('placeholder'))->name('pengguna.index');

        // --- Kelola Admin (akun & hak akses panel admin) ---
        Route::get('/kelola-admin', [AdminController::class, 'index'])->name('kelola-admin.index');
        Route::post('/kelola-admin', [AdminController::class, 'store'])->name('kelola-admin.store');
        Route::put('/kelola-admin/{id}', [AdminController::class, 'update'])->name('kelola-admin.update');
        Route::delete('/kelola-admin/{id}', [AdminController::class, 'destroy'])->name('kelola-admin.destroy');

        // 4. Transaksi
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/baru', [TransaksiController::class, 'create'])->name('transaksi.create');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');
    
        Route::patch('/transaksi/{id}/lunasi', [TransaksiController::class, 'lunasi'])->name('transaksi.lunasi');
        Route::patch('/transaksi/{id}/selesaikan-sewa', [TransaksiController::class, 'selesaikanSewa'])->name('transaksi.selesai-sewa');

        // 5. Sewa & jaminan
        Route::get('/sewa', fn () => view('placeholder'))->name('sewa.index');
        Route::get('/jaminan', fn () => view('placeholder'))->name('jaminan.index');

        // 6. Layanan atelier
        Route::get('/refund', fn () => view('placeholder'))->name('refund.index');
        Route::get('/pengiriman', fn () => view('placeholder'))->name('pengiriman.index');
        Route::get('/fitting', fn () => view('placeholder'))->name('fitting.index');
        Route::get('/rating', fn () => view('placeholder'))->name('rating.index');

        // 7. Sistem
        Route::get('/audit-log', fn () => view('placeholder'))->name('audit.index');

        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');

        // Route untuk aksi ubah status
        Route::patch('/transaksi/{id}/lunasi', [App\Http\Controllers\Admin\TransaksiController::class, 'lunasi'])->name('transaksi.lunasi');
        Route::patch('/transaksi/{id}/selesaikan-sewa', [App\Http\Controllers\Admin\TransaksiController::class, 'selesaikanSewa'])->name('transaksi.selesai-sewa');

        Route::get('/fitting', [ReservasiFittingController::class, 'index'])->name('fitting.index');
        Route::patch('/fitting/{id}/status', [ReservasiFittingController::class, 'updateStatus'])->name('fitting.update-status');

        Route::get('/rating', [RatingController::class, 'index'])->name('rating.index');
    });
});