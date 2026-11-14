<?php

namespace App\Console\Commands;

use App\Models\Refund;
use Illuminate\Console\Command;

class KedaluwarsakanRefund extends Command
{
    protected $signature = 'refund:kedaluwarsa';

    protected $description = 'Batalkan otomatis refund yang sudah disetujui tetapi barangnya tidak dikirim sebelum batas waktu.';

    public function handle(): int
    {
        $jumlah = 0;

        Refund::where('status', 'disetujui')
            ->whereNotNull('batas_kirim')
            ->where('batas_kirim', '<', now())
            ->each(function (Refund $refund) use (&$jumlah) {
                $ok = $refund->pindahKe('dibatalkan', [
                    'alasan_penolakan' => 'Dibatalkan otomatis: barang tidak dikirim sebelum batas waktu '
                        . $refund->batas_kirim->format('d M Y') . '. Silakan ajukan ulang bila masih memenuhi syarat.',
                ], $refund->diproses_oleh ?? $refund->diajukan_oleh);

                if ($ok) {
                    $jumlah++;
                }
            });

        $this->info("Refund kedaluwarsa dibatalkan: {$jumlah}");

        return self::SUCCESS;
    }
}