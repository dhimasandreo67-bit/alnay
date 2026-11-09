<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TambahTanggalKeDetailTransaksi extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->date('tanggal_pinjam')->nullable()->after('warna');
            $table->date('tanggal_kembali_rencana')->nullable()->after('tanggal_pinjam');
        });

        // Data lama: satu transaksi = satu rentang tanggal (sewa.transaksi_id unik),
        // jadi semua itemnya ikut rentang itu.
        DB::statement(
            'UPDATE detail_transaksi d
             JOIN sewa s ON s.transaksi_id = d.transaksi_id
             SET d.tanggal_pinjam = s.tanggal_pinjam,
                 d.tanggal_kembali_rencana = s.tanggal_kembali_rencana'
        );
    }

    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn(['tanggal_pinjam', 'tanggal_kembali_rencana']);
        });
    }
}