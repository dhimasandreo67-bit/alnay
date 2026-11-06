<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;

/**
 * Semua urusan QRIS Midtrans untuk Sewa (DP) dan Beli (lunas) ada di sini.
 *
 * - QR dibuat SEKALI per transaksi lalu dipakai ulang sampai kedaluwarsa
 *   (tidak ada lagi QR ganda tiap halaman dimuat).
 * - Status dibaca dari Midtrans, bukan dari klik pengguna.
 * - Data QR disimpan di cache, jadi tidak perlu migrasi database.
 */
class MidtransQris
{
    public const MENIT = 15;

    public function __construct()
    {
        $serverKey = (string) config('services.midtrans.server_key');

        Config::$serverKey = $serverKey;
        // Kunci sandbox selalu berawalan "SB-"; mode mengikuti kunci agar tidak salah set.
        Config::$isProduction = $serverKey !== '' && ! str_starts_with($serverKey, 'SB-');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /* ---------------------------------------------------------------- */

    public function sudahLunas(Transaksi $t): bool
    {
        return in_array($t->status_pembayaran, ['dp_dibayar', 'lunas'], true);
    }

    public function tagihan(Transaksi $t)
    {
        return $t->pembayaran()
            ->whereIn('jenis_bayar', ['dp', 'lunas'])
            ->orderByDesc('id')
            ->first();
    }

        public function nominal(Transaksi $t): int
    {
        $tagihan = $this->tagihan($t);
        $jumlah  = (int) round((float) ($tagihan->jumlah ?? 0));

        if ($jumlah < 1) {
            throw new \RuntimeException(
                'Nominal tagihan Rp0 untuk transaksi ' . $t->kode_transaksi
                . '. Periksa DP produk dan data pembayaran, lalu buat pesanan baru.'
            );
        }

        return $jumlah;
    }

    private function key(Transaksi $t): string
    {
        return 'qris:' . $t->id;
    }

    private function pastikanSiap(): void
    {
        if (! class_exists(CoreApi::class)) {
            throw new \RuntimeException('Paket midtrans/midtrans-php belum terpasang. Jalankan: composer require midtrans/midtrans-php');
        }
        if ((string) config('services.midtrans.server_key') === '') {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum diisi di .env (lalu jalankan php artisan config:clear).');
        }
    }

    /* ---------------------------------------------------------------- */

    /**
     * Ambil QR yang masih berlaku, atau buat yang baru.
     *
     * @return array{paid?:bool,qr_url?:string,expires_in?:int,order_id?:string}
     */
    public function qr(Transaksi $t, bool $baru = false): array
    {
        $this->pastikanSiap();

        // Jangan pernah membuat QR baru kalau ternyata sudah dibayar.
        if ($this->sync($t) === 'paid') {
            return ['paid' => true];
        }

        $key = $this->key($t);
        $lama = Cache::get($key);

        if ($lama && ! $baru && $lama['expires_at'] > time()) {
            return $this->bentuk($lama);
        }

        return Cache::lock($key . ':lock', 20)->block(8, function () use ($t, $key, $baru) {
            $lama = Cache::get($key);

            if ($lama && ! $baru && $lama['expires_at'] > time()) {
                return $this->bentuk($lama);
            }

            if ($lama) {
                try {
                    Transaction::cancel($lama['order_id']);
                } catch (\Throwable $e) {
                    // Sudah kedaluwarsa / tidak ada: abaikan.
                }
            }

            $nominal = $this->nominal($t);
            $orderId = $t->kode_transaksi . '-' . time();

            $res = CoreApi::charge([
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $nominal,
                ],
                'qris' => ['acquirer' => 'gopay'],
                'custom_expiry' => [
                    'expiry_duration' => self::MENIT,
                    'unit' => 'minute',
                ],
            ]);

            $res = json_decode(json_encode($res));
            $qrUrl = null;
            foreach (($res->actions ?? []) as $aksi) {
                if (($aksi->name ?? '') === 'generate-qr-code') {
                    $qrUrl = $aksi->url ?? null;
                }
            }

            if (! $qrUrl) {
                Log::error('QRIS: Midtrans tidak mengembalikan QR', ['order_id' => $orderId, 'respons' => $res]);
                throw new \RuntimeException(
                    'Midtrans tidak mengembalikan QR (' . ($res->status_code ?? '?') . ': ' . ($res->status_message ?? 'tanpa pesan')
                        . '). Pastikan QRIS/GoPay sudah aktif di akun Midtrans sandbox Anda.'
                );
            }

            $data = [
                'order_id' => $orderId,
                'qr_url' => $qrUrl,
                'expires_at' => time() + self::MENIT * 60,
            ];
            Cache::put($key, $data, now()->addDay());

            // Transaksi yang sempat kedaluwarsa dihidupkan lagi.
            if ($t->status_pembayaran === 'gagal') {
                $t->update(['status_pembayaran' => $t->jenis === 'sewa' ? 'dp' : 'pending']);
                $this->tagihan($t)?->update(['status_midtrans' => 'pending']);
            }

            return $this->bentuk($data);
        });
    }

    private function bentuk(array $d): array
    {
        return [
            'qr_url' => $d['qr_url'],
            'order_id' => $d['order_id'],
            'expires_in' => max(0, $d['expires_at'] - time()),
        ];
    }

    /* ---------------------------------------------------------------- */

    /**
     * Tanya Midtrans, perbarui database, kembalikan: paid | pending | expired.
     */
    public function sync(Transaksi $t): string
    {
        $t->refresh();

        if ($this->sudahLunas($t)) {
            return 'paid';
        }

        $d = Cache::get($this->key($t));
        if (! $d) {
            return 'pending';
        }

        try {
            $r = json_decode(json_encode(Transaction::status($d['order_id'])));
        } catch (\Throwable $e) {
            return 'pending';
        }

        $status = $r->transaction_status ?? 'pending';
        $this->terapkan($t, $d['order_id'], $status, $r->payment_type ?? 'qris');
        $t->refresh();

        if ($this->sudahLunas($t)) {
            return 'paid';
        }

        return in_array($status, ['expire', 'cancel', 'deny', 'failure'], true) ? 'expired' : 'pending';
    }

    /**
     * Terapkan status Midtrans ke database. Aman dipanggil berulang.
     */
    public function terapkan(Transaksi $t, string $orderId, string $status, ?string $type = 'qris'): void
    {
        if ($this->sudahLunas($t)) {
            return;
        }

        $sukses = in_array($status, ['settlement', 'capture'], true);
        $saatIni = Cache::get($this->key($t))['order_id'] ?? null;

        // Kabar gagal/kedaluwarsa dari QR lama tidak boleh mematikan QR baru.
        if (! $sukses && $saatIni && $saatIni !== $orderId) {
            return;
        }

        if ($sukses) {
            $t->update(['status_pembayaran' => $t->jenis === 'sewa' ? 'dp_dibayar' : 'lunas']);
            $this->tagihan($t)?->update([
                'status_midtrans' => 'success',
                'metode' => $type ?: 'qris',
                'tanggal_bayar' => now(),
            ]);

            // ==== KURANGI STOK PRODUK (Hanya untuk transaksi BELI) ====
            // Stok jual dikurangi per ukuran & warna yang dibeli (disimpan di detail_transaksi).
            // Stok SEWA tidak dikurangi: ketersediaannya dihitung per tanggal (lihat Support\KetersediaanSewa).
            if ($t->jenis === 'beli') {
                DB::transaction(function () use ($t) {
                    foreach ($t->detail as $detail) {
                        $produk = Produk::whereKey($detail->produk_id)->lockForUpdate()->first();
                        if ($produk) {
                            $produk->kurangiStok('jual', $detail->ukuran, $detail->warna, (int) $detail->jumlah);
                        }
                    }
                });
            }
            // ==========================================================

            Cache::forget($this->key($t));
        } elseif (in_array($status, ['expire', 'cancel', 'deny', 'failure'], true)) {
            $t->update(['status_pembayaran' => 'gagal']);
            $this->tagihan($t)?->update(['status_midtrans' => 'failed']);
        }
    }

    /* ---------------------------------------------------------------- */

    /**
     * Notifikasi (webhook) dari Midtrans — dipakai Sewa maupun Beli.
     */
    public function tanganiWebhook(Request $request)
    {
        $ok = response('OK', 200)->header('Content-Type', 'text/plain');

        if (! $request->has(['order_id', 'status_code', 'gross_amount', 'signature_key'])) {
            return $ok;
        }

        $serverKey = (string) config('services.midtrans.server_key');
        $hash = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if (! hash_equals($hash, (string) $request->signature_key)) {
            Log::warning('QRIS webhook: signature tidak cocok', ['order_id' => $request->order_id]);

            return $ok;
        }

        $bagian = explode('-', (string) $request->order_id);
        if (count($bagian) < 3) {
            return $ok; // mis. tombol "test" dari dashboard Midtrans
        }

        $t = Transaksi::where('kode_transaksi', implode('-', array_slice($bagian, 0, 3)))->first();
        if (! $t) {
            return $ok;
        }

        $status = (string) $request->transaction_status;

        // Nominal harus sama dengan tagihan sebelum dianggap lunas.
        if (
            in_array($status, ['settlement', 'capture'], true)
            && (int) round((float) $request->gross_amount) !== $this->nominal($t)
        ) {
            Log::warning('QRIS webhook: nominal tidak cocok', ['order_id' => $request->order_id]);

            return $ok;
        }

        $this->terapkan($t, (string) $request->order_id, $status, (string) $request->payment_type);

        return $ok;
    }
}