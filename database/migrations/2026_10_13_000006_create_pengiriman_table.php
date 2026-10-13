<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $table->string('provinsi_tujuan');
            $table->string('kota_tujuan');
            $table->string('kecamatan_tujuan');
            $table->string('kode_pos_tujuan')->nullable();
            $table->text('alamat_tujuan');
            $table->string('kurir')->nullable();
            $table->string('no_resi')->nullable();
            $table->decimal('biaya_kirim', 12, 2)->default(0);
            $table->string('status_kirim')->default('menunggu');
            $table->date('tanggal_kirim')->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};