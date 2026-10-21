<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\AdminLoginController; // Tambahkan ini
use Illuminate\Support\Facades\Route;

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
    Route::middleware('auth')->group(function () {

        Route::post('/logout', [AdminLoginController::class, 'logout'])
        ->name('logout')
        ->middleware('auth');
        
        // 1. Redirect halaman utama /admin langsung ke dashboard
        Route::get('/', fn () => redirect()->route('admin.dashboard'));

        // 2. Dashboard Utama
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // 3. Master data
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
        Route::get('/kategori/tambah', fn () => redirect()->route('admin.kategori.index'));
        Route::get('/kategori/{id}/edit', fn ($id) => redirect()->route('admin.kategori.index'));
        
        Route::get('/produk', fn () => view('placeholder'))->name('produk.index');
        Route::get('/produk/tambah', fn () => view('placeholder'))->name('produk.create');
        Route::get('/produk/{id}/edit', fn ($id) => view('placeholder'))->name('produk.edit');
        Route::get('/pengguna', fn () => view('placeholder'))->name('pengguna.index');

        // 4. Transaksi
        Route::get('/transaksi', fn () => view('placeholder'))->name('transaksi.index');
        Route::get('/transaksi/baru', fn () => view('placeholder'))->name('transaksi.create');

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
    });
});