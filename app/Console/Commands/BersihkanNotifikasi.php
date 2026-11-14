<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BersihkanNotifikasi extends Command
{
    protected $signature = 'notifikasi:bersihkan {--dibaca=30 : Hapus yang sudah dibaca lebih lama dari N hari} {--semua=90 : Hapus semua yang lebih lama dari N hari}';

    protected $description = 'Hapus notifikasi admin lama agar tabel notifications tidak membengkak.';

    public function handle(): int
    {
        $a = DatabaseNotification::whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays((int) $this->option('dibaca')))
            ->delete();

        $b = DatabaseNotification::where('created_at', '<', now()->subDays((int) $this->option('semua')))->delete();

        // Catatan pengingat sewa: aman dihapus setelah lewat jauh dari BATAS_HARI_TELAT (30 hari)
        $c = Schema::hasTable('pengingat_admin')
            ? DB::table('pengingat_admin')->where('created_at', '<', now()->subDays(120))->delete()
            : 0;

        $this->info("Terhapus: {$a} sudah dibaca, {$b} kedaluwarsa, {$c} catatan pengingat lama.");

        return self::SUCCESS;
    }
}