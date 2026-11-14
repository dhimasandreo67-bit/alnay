<?php

namespace App\Console\Commands;

use App\Support\NotifikasiAdmin;
use Illuminate\Console\Command;

class PengingatSewa extends Command
{
    protected $signature = 'notifikasi:pengingat-sewa {--simulasi : Hanya tampilkan yang akan dikirim, tanpa mengirim}';

    protected $description = 'Kirim pengingat ke admin: sewa jatuh tempo besok/hari ini dan sewa yang terlambat dikembalikan.';

    public function handle(): int
    {
        $simulasi = (bool) $this->option('simulasi');
        $hasil = NotifikasiAdmin::pengingatSewa($simulasi);

        if (empty($hasil)) {
            $this->info('Tidak ada sewa yang jatuh tempo atau terlambat.');

            return self::SUCCESS;
        }

        $this->table(
            ['Sewa', 'Kode', 'Tahap', 'Selisih hari', $simulasi ? 'Akan dikirim' : 'Dikirim'],
            array_map(fn ($b) => [
                $b['sewa_id'],
                $b['kode'] ?? '-',
                $b['tahap'],
                $b['hari'] > 0 ? "telat {$b['hari']} hari" : ($b['hari'] === 0 ? 'hari ini' : 'besok'),
                $b['dikirim'] ? 'ya' : 'sudah pernah',
            ], $hasil)
        );

        $baru = count(array_filter($hasil, fn ($b) => $b['dikirim']));
        $this->info(($simulasi ? 'Simulasi: ' : '') . "{$baru} pengingat " . ($simulasi ? 'akan dikirim.' : 'dikirim.'));

        return self::SUCCESS;
    }
}