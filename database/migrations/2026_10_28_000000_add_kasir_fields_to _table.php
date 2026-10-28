<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CATATAN:
     * - Sesuaikan 'transaksi' kalau nama tabelmu beda.
     * - Kalau ->change() di bawah error "doctrine/dbal not found", jalankan dulu:
     *   composer require doctrine/dbal
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Penanda asal transaksi: 'online' (dari web) atau 'kasir' (diinput admin)
            $table->string('channel', 20)->default('online')->after('status_pembayaran');

            // Data pembeli walk-in (hanya diisi kalau channel = kasir & tidak pilih user terdaftar)
            $table->string('nama_tamu')->nullable()->after('channel');
            $table->string('no_hp_tamu', 30)->nullable()->after('nama_tamu');

            // user_id dibuat nullable supaya kasir bisa mencatat pembeli tanpa akun
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['channel', 'nama_tamu', 'no_hp_tamu']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};