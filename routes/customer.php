<?php

use App\Http\Controllers\Customer\AkunController;
use App\Http\Controllers\Customer\AlamatController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\NotifikasiController;
use App\Http\Controllers\Customer\ProfilController;
use App\Http\Controllers\Customer\RatingController;
use App\Http\Controllers\Customer\RefundController;
use App\Http\Controllers\FittingController;
use App\Http\Controllers\OngkirController;
use App\Http\Controllers\QrisController;
use App\Http\Controllers\SewaController;
use Illuminate\Support\Facades\Route;

// Grup Khusus Halaman Akun (Prefix: /akun)
Route::middleware('auth:web')->prefix('akun')->name('akun.')->group(function () {
    Route::get('/', [AkunController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');
    Route::post('/alamat', [AlamatController::class, 'store'])->name('alamat.store');
    Route::patch('/alamat/{id}/utama', [AlamatController::class, 'jadikanUtama'])->name('alamat.utama');
    Route::put('/alamat/{id}', [AlamatController::class, 'update'])->name('alamat.update');
    Route::delete('/alamat/{id}', [AlamatController::class, 'destroy'])->name('alamat.destroy');

    Route::get('/pesanan', [AkunController::class, 'pesanan'])->name('pesanan.index');
    Route::get('/pesanan/{id}', [AkunController::class, 'pesananDetail'])->name('pesanan.detail');

    Route::get('/fitting', [AkunController::class, 'fitting'])->name('fitting.index');
    Route::get('/fitting/riwayat', [FittingController::class, 'index'])->name('akun.fitting.index');

    // Notifikasi lonceng pelanggan (header)
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/{id}/buka', [NotifikasiController::class, 'buka'])->whereNumber('id')->name('buka');
        Route::post('/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('baca-semua');
        Route::delete('/semua', [NotifikasiController::class, 'hapusSemua'])->name('hapus-semua');
        Route::delete('/{id}', [NotifikasiController::class, 'hapus'])->whereNumber('id')->name('hapus');
    });

    // Refund pembelian
    Route::get('/refund', [RefundController::class, 'index'])->name('refund.index');
    Route::get('/refund/pesanan/{id}', [RefundController::class, 'create'])->name('refund.create');
    Route::post('/refund/pesanan/{id}', [RefundController::class, 'store'])->name('refund.store');
    Route::get('/refund/{id}', [RefundController::class, 'show'])->whereNumber('id')->name('refund.show');
    Route::patch('/refund/{id}/kirim', [RefundController::class, 'kirim'])->name('refund.kirim');
    Route::patch('/refund/{id}/batal', [RefundController::class, 'batal'])->name('refund.batal');
});

// Grup Khusus Fitur Auth (Bebas URL)
Route::middleware('auth:web')->group(function () {
    // --- KERANJANG ---
    Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang/tambah', [CartController::class, 'add'])->name('keranjang.add');
    Route::patch('/keranjang/update/{id}', [CartController::class, 'updateQuantity'])->name('keranjang.updateQty');
    Route::delete('/keranjang/hapus/{id}', [CartController::class, 'remove'])->name('keranjang.remove');
    Route::post('/keranjang/hapus-pilihan', [CartController::class, 'removeSelected'])->name('keranjang.removeSelected');

    // --- CHECKOUT ---
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/alamat', [CheckoutController::class, 'simpanAlamat'])->name('checkout.alamat.simpan');

    // --- FITTING ---
    Route::get('/fitting', [FittingController::class, 'create'])->name('fitting.create');
    Route::post('/fitting', [FittingController::class, 'store'])->name('fitting.store');
    Route::get('/fitting/{id}', [FittingController::class, 'show'])->name('fitting.show');
    Route::patch('/fitting/{id}/cancel', [FittingController::class, 'cancel'])->name('fitting.cancel');
    Route::delete('/fitting/{id}', [FittingController::class, 'destroy'])->name('fitting.destroy');

    // TAMBAHKAN DUA BARIS INI:
    Route::get('/fitting/{id}/edit', [FittingController::class, 'edit'])->name('fitting.edit');
    Route::put('/fitting/{id}', [FittingController::class, 'update'])->name('fitting.update');

    // --- MIDTRANS CHARGE (Untuk Pembayaran Sewa) ---
    Route::post('/sewa/{id}/charge', [QrisController::class, 'charge'])->name('sewa.charge')->middleware('throttle:10,1');
    // --- MIDTRANS CHARGE (QRIS untuk Beli) ---
    Route::post('/qris/{id}/charge', [QrisController::class, 'charge'])->name('qris.charge')->middleware('throttle:10,1');
    // Route untuk cek status pembayaran QRIS
    Route::get('/qris/{id}/status', [QrisController::class, 'status'])->name('qris.status')->middleware('throttle:60,1');

    // --- KERANJANG SEWA ---
    Route::get('/sewa/keranjang', [SewaController::class, 'keranjangIndex'])->name('sewa.keranjang.index');
    Route::post('/sewa/keranjang/tambah', [SewaController::class, 'tambahKeKeranjang'])->name('sewa.keranjang.tambah');
    Route::delete('/sewa/keranjang/hapus/{id}', [SewaController::class, 'hapusKeranjang'])->name('sewa.keranjang.hapus');
    Route::post('/sewa/keranjang/hapus-pilihan', [SewaController::class, 'hapusPilihanKeranjang'])->name('sewa.keranjang.hapusPilihan');

    // --- RATING ---
    Route::get('/rating/{detail_id}/create', [RatingController::class, 'create'])->name('rating.create');
    Route::post('/rating/{detail_id}', [RatingController::class, 'store'])->name('rating.store');

    // --- KONFIRMASI PENERIMAAN PENGIRIMAN ---
    Route::patch('/akun/pengiriman/{id}/terima', [AkunController::class, 'terimaPengiriman'])->name('akun.pengiriman.terima');

    Route::get('/ongkir/tujuan', [OngkirController::class, 'tujuan'])->name('ongkir.tujuan');
    Route::post('/ongkir/hitung', [OngkirController::class, 'hitung'])->name('ongkir.hitung');
    Route::get('/ongkir/provinsi', [OngkirController::class, 'provinsi'])->name('ongkir.provinsi');
    Route::get('/ongkir/kota/{provinsiId}', [OngkirController::class, 'kota'])->name('ongkir.kota');
    Route::get('/ongkir/kecamatan/{kotaId}', [OngkirController::class, 'kecamatan'])->name('ongkir.kecamatan');

});

// Halaman info untuk akun yang dinonaktifkan (diisi oleh listener BlokirAkunNonaktif saat login)
Route::get('/akun-nonaktif', function () {
    $info = session('akun_nonaktif');

    if (! $info) {
        return redirect('/');
    }

    return view('auth.akun-nonaktif', ['info' => $info]);
})->name('akun.nonaktif');

// Webhook Midtrans
Route::post('/midtrans/webhook', [SewaController::class, 'webhook'])->name('midtrans.webhook');
