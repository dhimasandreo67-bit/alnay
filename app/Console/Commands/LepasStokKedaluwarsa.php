<?php

namespace App\Console\Commands;

use App\Models\Transaksi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LepasStokKedaluwarsa extends Command
{
    protected $signature = 'pesanan:lepas-stok {--menit=60 : Batas pesanan online (QRIS dll) yang belum dibayar} {--tunai-jam=24 : Batas pesanan tunai (ambil di toko) yang belum dibayar}';

    protected $description = 'Kembalikan stok pesanan beli online yang tidak dibayar sampai batas waktu, lalu tandai gagal.';

    public function handle(): int
    {
        $batasOnline = now()->subMinutes((int) $this->option('menit'));
        $batasTunai  = now()->subHours((int) $this->option('tunai-jam'));
        $jumlah = 0;

        Transaksi::query()
            ->with('pembayaran')
            ->where('jenis', 'beli')
            ->where(function ($q) {
                $q->whereNull('channel')->orWhere('channel', 'online');
            })
            ->where('status_pembayaran', 'pending')
            ->where('stok_ditahan', true)
            ->where('created_at', '<', $batasOnline)
            ->chunkById(100, function ($daftar) use ($batasTunai, &$jumlah) {
                foreach ($daftar as $t) {
                    // Tunai: pelanggan bayar saat ambil di toko, beri waktu lebih lama.
                    $tunai = $t->pembayaran->contains(fn ($p) => $p->metode === 'tunai');
                    if ($tunai && $t->created_at->gt($batasTunai)) {
                        continue;
                    }

                    // QR yang masih berlaku berarti pelanggan mungkin sedang membayar.
                    $qr = Cache::get('qris:' . $t->id);
                    if ($qr && (($qr['expires_at'] ?? 0) > time())) {
                        continue;
                    }

                    $dilepas = DB::transaction(function () use ($t) {
                        // Cek ulang dengan kunci baris: kalau webhook baru saja melunasi, jangan dilepas.
                        $kunci = Transaksi::whereKey($t->id)->lockForUpdate()->first();
                        if (! $kunci || $kunci->status_pembayaran !== 'pending') {
                            return false;
                        }

                        $kunci->lepasStok();
                        $kunci->update(['status_pembayaran' => 'gagal']);
                        $kunci->pembayaran()
                            ->where('jenis_bayar', 'lunas')
                            ->update(['status_midtrans' => 'failed']);

                        return true;
                    });

                    if ($dilepas) {
                        $jumlah++;
                    }
                }
            });

        $this->info("Pesanan kedaluwarsa, stok dikembalikan: {$jumlah}");

        return self::SUCCESS;
    }
}