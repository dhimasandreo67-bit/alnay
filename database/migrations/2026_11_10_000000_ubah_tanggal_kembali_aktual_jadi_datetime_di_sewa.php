<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan jam pengembalian busana (sebelumnya hanya tanggal, sehingga jam selalu 00:00).
     * Data lama tetap aman: tanggalnya dipertahankan dengan jam 00:00.
     */
    public function up(): void
    {
        Schema::table('sewa', function (Blueprint $table) {
            $table->dateTime('tanggal_kembali_aktual')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sewa', function (Blueprint $table) {
            $table->date('tanggal_kembali_aktual')->nullable()->change();
        });
    }
};