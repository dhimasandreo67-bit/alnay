<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreignId('reservasi_fitting_id')
                  ->nullable()
                  ->after('catatan')
                  ->constrained('reservasi_fitting')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['reservasi_fitting_id']);
            $table->dropColumn('reservasi_fitting_id');
        });
    }
};