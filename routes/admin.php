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
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\AuditLogController;
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
        // --- Kategori ---
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::get('/kategori/tambah', [KategoriController::class, 'create'])->name('kategori.create');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::get('/kategori/{id}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        // Hapus massal (checklist). Harus SEBELUM '/kategori/{id}' agar 'bulk-destroy' tidak dianggap sebagai {id}.
        Route::delete('/kategori/bulk-destroy', [KategoriController::class, 'bulkDestroy'])->name('kategori.bulk-destroy');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
        // Jaring pengaman: kategori tidak punya halaman detail, jadi GET ke URL ini (mis. salah ketik,
        // bookmark lama, atau refresh) dikembalikan ke daftar kategori alih-alih error 405.
        Route::get('/kategori/{id}', fn () => redirect()->route('admin.kategori.index'))->whereNumber('id');

        
        // --- Produk (Menggunakan rute baru dari GitHub) ---
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/produk/export', [ProdukController::class, 'export'])->name('produk.export');
        Route::get('/produk/tambah', [ProdukController::class, 'create'])->name('produk.create');
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('/produk/{id}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
        // Hapus massal (checklist). Harus SEBELUM '/produk/{id}' agar 'bulk-destroy' tidak dianggap sebagai {id}.
        Route::delete('/produk/bulk-destroy', [ProdukController::class, 'bulkDestroy'])->name('produk.bulk-destroy');
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
        Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
    
        Route::patch('/transaksi/{id}/lunasi', [TransaksiController::class, 'lunasi'])->name('transaksi.lunasi');
        Route::patch('/transaksi/{id}/selesaikan-sewa', [TransaksiController::class, 'selesaikanSewa'])->name('transaksi.selesai-sewa');

        // 5. Sewa & jaminan
        Route::get('/sewa', [App\Http\Controllers\Admin\SewaController::class, 'index'])->name('sewa.index');
        Route::get('/sewa/{id}', [App\Http\Controllers\Admin\SewaController::class, 'show'])->name('sewa.show');
        Route::get('/jaminan', [App\Http\Controllers\Admin\JaminanController::class, 'index'])->name('jaminan.index');

        // 6. Layanan atelier
        Route::get('/refund', [App\Http\Controllers\Admin\RefundController::class, 'index'])->name('refund.index');
        Route::patch('/refund/{id}/status', [App\Http\Controllers\Admin\RefundController::class, 'updateStatus'])->name('refund.update-status');
        Route::get('/pengiriman', [App\Http\Controllers\Admin\PengirimanController::class, 'index'])->name('pengiriman.index');
        Route::patch('/pengiriman/{id}/status', [App\Http\Controllers\Admin\PengirimanController::class, 'updateStatus'])->name('pengiriman.update-status');
                Route::get('/fitting', fn () => view('placeholder'))->name('fitting.index');
                Route::get('/rating', fn () => view('placeholder'))->name('rating.index');

                // 7. Sistem
                Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
                Route::get('/audit-log/feed', [AuditLogController::class, 'feed'])->name('audit.feed');

                Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
                Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');

                // Route untuk aksi ubah status
                Route::patch('/transaksi/{id}/lunasi', [App\Http\Controllers\Admin\TransaksiController::class, 'lunasi'])->name('transaksi.lunasi');
                Route::patch('/transaksi/{id}/selesaikan-sewa', [App\Http\Controllers\Admin\TransaksiController::class, 'selesaikanSewa'])->name('transaksi.selesai-sewa');

                Route::prefix('pengguna')->name('pengguna.')->group(function () {
                    Route::get('/', [PenggunaController::class, 'index'])->name('index');
                    Route::post('/', [PenggunaController::class, 'store'])->name('store');
                    Route::put('/{id}', [PenggunaController::class, 'update'])->name('update');
                    // Kalau URL ini terbuka lewat GET (mis. refresh setelah submit), kembalikan ke daftar pengguna
                    Route::get('/{id}', fn () => redirect()->route('admin.pengguna.index'))->whereNumber('id');
                    Route::post('/massal', [PenggunaController::class, 'massal'])->name('massal');
                    Route::patch('/{id}/status', [PenggunaController::class, 'status'])->name('status');
                    Route::delete('/{id}', [PenggunaController::class, 'destroy'])->name('destroy');
                });

                Route::get('/fitting', [ReservasiFittingController::class, 'index'])->name('fitting.index');
                Route::patch('/fitting/{id}/status', [ReservasiFittingController::class, 'updateStatus'])->name('fitting.update-status');

                Route::get('/rating', [RatingController::class, 'index'])->name('rating.index');

                // Aksi Verifikasi Jaminan Fisik
                Route::post('/sewa/{id}/verifikasi-jaminan', [App\Http\Controllers\Admin\SewaController::class, 'verifikasiJaminan'])->name('sewa.verifikasi-jaminan');

                // Aksi Pengembalian Deposit
                Route::post('/sewa/{id}/kembalikan-deposit', [App\Http\Controllers\Admin\SewaController::class, 'kembalikanDeposit'])->name('sewa.kembalikan-deposit');

                // QC & Denda
                Route::match(['get', 'post'], '/sewa/{id}/simpan-qc', [App\Http\Controllers\Admin\SewaController::class, 'simpanQC'])->name('sewa.simpan-qc');
                Route::post('/sewa/{id}/tambah-denda', [App\Http\Controllers\Admin\SewaController::class, 'tambahDenda'])->name('sewa.tambah-denda');
                Route::patch('/denda/{id}/lunasi', [App\Http\Controllers\Admin\SewaController::class, 'lunasiDenda'])->name('sewa.lunasi-denda');
                Route::patch('/sewa/{id}/ubah-status', [App\Http\Controllers\Admin\SewaController::class, 'ubahStatus'])->name('sewa.ubah-status');

                Route::put('/pengecekan/{id}', [App\Http\Controllers\Admin\SewaController::class, 'updateQC'])->name('sewa.update-qc');
                Route::delete('/pengecekan/{id}', [App\Http\Controllers\Admin\SewaController::class, 'hapusQC'])->name('sewa.hapus-qc');
                Route::patch('/pengiriman/{id}/batalkan-terima', [App\Http\Controllers\Admin\PengirimanController::class, 'batalkanTerima'])->name('pengiriman.batalkan-terima');
            });
        });