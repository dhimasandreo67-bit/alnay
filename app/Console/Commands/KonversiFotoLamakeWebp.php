<?php

namespace App\Console\Commands;

use App\Models\Kategori;
use App\Models\Produk;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;

/**
 * Jalankan sekali lewat terminal untuk mengonversi foto lama (yang masih .jpg/.png)
 * milik Produk & Kategori menjadi WebP, lalu update path-nya di database.
 *
 *     php artisan foto:konversi-webp
 *     php artisan foto:konversi-webp --dry-run   (lihat dulu apa yang akan diubah, tanpa eksekusi)
 */
class KonversiFotoLamaKeWebp extends Command
{
    protected $signature = 'foto:konversi-webp {--dry-run : Tampilkan saja apa yang akan dikonversi, tanpa mengubah apa pun}';

    protected $description = 'Konversi foto lama Produk & Kategori yang masih .jpg/.png menjadi .webp';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode --dry-run: tidak ada file atau data yang diubah.');
        }

        $this->konversiKategori($dryRun);
        $this->konversiProduk($dryRun);

        $this->info('Selesai.');

        return self::SUCCESS;
    }

    protected function konversiKategori(bool $dryRun): void
    {
        $kategoriList = Kategori::whereNotNull('foto')->where('foto', '!=', '')->get();

        $this->info("Kategori: memeriksa {$kategoriList->count()} baris yang punya foto...");
        $berhasil = 0;
        $dilewati = 0;

        foreach ($kategoriList as $kategori) {
            $pathLama = $kategori->foto;

            if ($dryRun) {
                $this->line(" - [kategori #{$kategori->id}] {$pathLama} -> akan dikonversi (atau dilewati kalau sudah .webp)");
                continue;
            }

            $pathBaru = ImageOptimizer::konversiFileTersimpan($pathLama, 'kategori');

            if ($pathBaru === null) {
                $dilewati++;
                continue;
            }

            $kategori->update(['foto' => $pathBaru]);
            ImageOptimizer::hapus($pathLama);
            $berhasil++;

            $this->line(" - [kategori #{$kategori->id}] {$pathLama} -> {$pathBaru}");
        }

        if (!$dryRun) {
            $this->info("Kategori selesai: {$berhasil} dikonversi, {$dilewati} dilewati (sudah webp/file tidak ditemukan).");
        }
    }

    protected function konversiProduk(bool $dryRun): void
    {
        $produkList = Produk::whereNotNull('foto_produk')->get();

        $this->info("Produk: memeriksa {$produkList->count()} baris yang punya data foto...");
        $berhasil = 0;
        $dilewati = 0;

        foreach ($produkList as $produk) {
            $fotoLama = (array) $produk->foto_produk;
            $fotoBaru = $fotoLama;
            $adaPerubahan = false;

            foreach ($fotoLama as $i => $path) {
                if (!$path) {
                    continue;
                }

                if ($dryRun) {
                    $this->line(" - [produk #{$produk->id} slot {$i}] {$path} -> akan dikonversi (atau dilewati kalau sudah webp)");
                    continue;
                }

                $pathBaru = ImageOptimizer::konversiFileTersimpan($path, 'produk');

                if ($pathBaru === null) {
                    $dilewati++;
                    continue;
                }

                $fotoBaru[$i] = $pathBaru;
                $adaPerubahan = true;
                $berhasil++;
                $this->line(" - [produk #{$produk->id} slot {$i}] {$path} -> {$pathBaru}");

                // Hapus file lama SETELAH baris foto_baru siap disimpan, bukan di sini,
                // supaya kalau prosesnya terhenti di tengah, file lama tidak hilang sebelum DB sempat ter-update.
            }

            if ($dryRun || !$adaPerubahan) {
                continue;
            }

            $fileLamaUntukDihapus = array_diff_assoc($fotoLama, $fotoBaru);

            $produk->update(['foto_produk' => $fotoBaru]);

            foreach ($fileLamaUntukDihapus as $pathLama) {
                ImageOptimizer::hapus($pathLama);
            }
        }

        if (!$dryRun) {
            $this->info("Produk selesai: {$berhasil} foto dikonversi, {$dilewati} dilewati (sudah webp/file tidak ditemukan).");
        }
    }
}