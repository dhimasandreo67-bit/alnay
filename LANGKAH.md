# Perbaikan Pembayaran QRIS (Sewa + Beli)

1. Salin isi zip ke project (struktur folder sama). Timpa: SewaController, BeliController,
   sewa-konfirmasi.blade.php. File baru: MidtransQris.php, QrisController.php, qris-panel.blade.php,
   beli-konfirmasi.blade.php.

2. routes/web.php — HAPUS route `sewa.charge`, lalu tambah (di grup yang sama dengan sewa.konfirmasi / beli.konfirmasi):

   use App\Http\Controllers\QrisController;

   Route::post('/pembayaran/{id}/qris', [QrisController::class, 'charge'])->name('qris.charge');
   Route::get('/pembayaran/{id}/status', [QrisController::class, 'status'])->name('qris.status')->middleware('throttle:60,1');

   Route webhook Midtrans yang sudah ada TIDAK diubah.

3. Pastikan paket terpasang:  composer show midtrans/midtrans-php
   (jika kosong: composer require midtrans/midtrans-php)

4. php artisan config:clear && php artisan route:clear && php artisan view:clear

5. Akun Midtrans SANDBOX: aktifkan QRIS/GoPay (Settings > Payment). Webhook: Settings > Configuration >
   Payment Notification URL = URL route webhook yang sudah ada.

6. Jika QR masih gagal, pesan penyebab sekarang tampil di kotak QR dan tercatat di storage/logs/laravel.log.

Catatan: status transaksi Beli kini 'pending' -> 'lunas' (hanya lewat Midtrans). Jika kolom status_pembayaran
berupa ENUM dan 'pending'/'gagal' tidak ada, tambahkan nilainya (Sewa sudah memakai keduanya).
