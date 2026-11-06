<?php

namespace App\Console\Commands;

use App\Models\Sewa;
use App\Support\DendaTelat;
use Illuminate\Console\Command;

/**
 * Simpan di app/Console/Commands/SinkronDendaTelat.php
 * Jadwalkan harian (lihat petunjuk di bawah).
 */
class SinkronDendaTelat extends Command
{
    protected $signature = 'denda:sinkron-telat';
    protected $description = 'Hitung ulang denda keterlambatan otomatis untuk semua sewa yang belum dikembalikan';

    public function handle(): int
    {
        $jumlah = 0;

        Sewa::where('status', '!=', 'selesai')
            ->whereDate('tanggal_kembali_rencana', '<', now()->toDateString())
            ->with('transaksi.detail.produk')
            ->each(function (Sewa $sewa) use (&$jumlah) {
                DendaTelat::sinkron($sewa);
                $jumlah++;
            });

        $this->info("Denda telat disinkronkan untuk {$jumlah} sewa.");

        return self::SUCCESS;
    }
}

/*
 * JADWAL (pilih sesuai versi Laravel):
 *
 * Laravel 11+  -> routes/console.php:
 *     use Illuminate\Support\Facades\Schedule;
 *     Schedule::command('denda:sinkron-telat')->dailyAt('00:05');
 *
 * Laravel 10-  -> app/Console/Kernel.php, method schedule():
 *     $schedule->command('denda:sinkron-telat')->dailyAt('00:05');
 *
 * Di server, cron: * * * * * php /path/ke/alnay/artisan schedule:run
 */