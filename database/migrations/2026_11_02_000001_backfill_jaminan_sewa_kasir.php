<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transaksi sewa kasir yang dibuat sebelum fitur ini belum punya baris
     * jaminan_sewa. Buatkan supaya muncul di halaman Verifikasi Jaminan.
     */
    public function up(): void
    {
        $sewaTanpaJaminan = DB::table('sewa')
            ->join('transaksi', 'transaksi.id', '=', 'sewa.transaksi_id')
            ->leftJoin('jaminan_sewa', 'jaminan_sewa.sewa_id', '=', 'sewa.id')
            ->where('transaksi.channel', 'kasir')
            ->whereNull('jaminan_sewa.id')
            ->pluck('sewa.id');

        $punyaTimestamps = Schema::hasColumn('jaminan_sewa', 'created_at');

        foreach ($sewaTanpaJaminan as $sewaId) {
            $row = [
                'sewa_id' => $sewaId,
                'metode_jaminan' => 'ktp_tatap_muka',
                'jumlah_deposit' => 0,
                'status_verifikasi' => 'pending',
                'status_pengembalian_deposit' => 'belum',
            ];

            if ($punyaTimestamps) {
                $row['created_at'] = now();
                $row['updated_at'] = now();
            }

            DB::table('jaminan_sewa')->insert($row);
        }
    }

    public function down(): void
    {
        // Data backfill tidak dihapus saat rollback.
    }
};