<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('reservasi_fitting', function (Blueprint $table) {
        $table->foreignId('transaksi_id')->nullable()->constrained('transaksi')->nullOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi_fitting', function (Blueprint $table) {
            //
        });
    }
};
