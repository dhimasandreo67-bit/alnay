<?php

namespace App\Console\Commands;

use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * SEKALI JALAN saat model sewa dipindah ke "ketersediaan per tanggal".
 *
 * Dulu transaksi sewa kasir memotong stok_sewa saat dibuat dan mengembalikannya saat selesai.
 * Sekarang stok sewa tidak pernah dipotong. Perintah ini mengembalikan stok yang masih
 * terpotong oleh sewa kasir yang BELUM selesai, supaya tidak terhitung dua kali.
 *
 *   php artisan stok:migrasi-sewa-kasir --dry-run   (lihat dulu)
 *   php artisan stok:migrasi-sewa-kasir             (jalankan, hanya sekali)
 */
class MigrasiStokSewaKasir extends Command
{
    protected $signature = 'stok:migrasi-sewa-kasir {--dry-run : Hanya tampilkan, tidak mengubah data}';

    protected $description = 'Kembalikan stok yang masih terpotong oleh sewa kasir yang belum selesai (sekali jalan)';

    public function handle(): int
    {
        $penanda = storage_path('app/migrasi_stok_sewa_kasir.done');
        $kering = (bool) $this->option('dry-run');

        if (! $kering && is_file($penanda)) {
            $this->error('Sudah pernah dijalankan pada ' . trim((string) file_get_contents($penanda)) . '. Menjalankan lagi akan menambah stok dua kali.');

            return self::FAILURE;
        }

        $daftar = Transaksi::with('detail.produk')
            ->where('channel', 'kasir')
            ->where('jenis', 'sewa')
            ->whereHas('sewa', fn ($q) => $q->whereIn('status', ['aktif', 'pending']))
            ->get();

        if ($daftar->isEmpty()) {
            $this->info('Tidak ada sewa kasir yang masih berjalan. Tidak ada yang perlu dikembalikan.');
            if (! $kering) {
                file_put_contents($penanda, now()->toDateTimeString());
            }

            return self::SUCCESS;
        }

        $this->table(
            ['Transaksi', 'Produk', 'Ukuran', 'Warna', 'Jumlah'],
            $daftar->flatMap(fn ($t) => $t->detail->map(fn ($d) => [
                $t->kode_transaksi, $d->produk->nama ?? '-', $d->ukuran ?: '-', $d->warna ?: '-', $d->jumlah,
            ]))->all()
        );

        if ($kering) {
            $this->warn('Dry-run: tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($daftar) {
            foreach ($daftar as $t) {
                foreach ($t->detail as $d) {
                    $produk = Produk::whereKey($d->produk_id)->lockForUpdate()->first();
                    if ($produk) {
                        $produk->tambahStok('sewa', $d->ukuran, $d->warna, (int) $d->jumlah);
                    }
                }
            }
        });

        file_put_contents($penanda, now()->toDateTimeString());
        $this->info('Selesai. Stok dikembalikan untuk ' . $daftar->count() . ' transaksi sewa kasir.');

        return self::SUCCESS;
    }
}