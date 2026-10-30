<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengecekan_barang', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn([
                'kondisi',
                'tanggal_cek',
                'tingkat_kerusakan',
                'keterangan',
                'foto_bukti',
            ]);

            // Tambah kolom baru
            $table->text('catatan')->nullable()->after('tahap');
            $table->string('petugas')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('pengecekan_barang', function (Blueprint $table) {
            $table->string('kondisi')->nullable()->after('tahap');
            $table->date('tanggal_cek')->nullable()->after('kondisi');
            $table->string('tingkat_kerusakan')->nullable()->after('tanggal_cek');
            $table->text('keterangan')->nullable()->after('tingkat_kerusakan');
            $table->string('foto_bukti')->nullable()->after('keterangan');

            $table->dropColumn(['catatan', 'petugas']);
        });
    }
};