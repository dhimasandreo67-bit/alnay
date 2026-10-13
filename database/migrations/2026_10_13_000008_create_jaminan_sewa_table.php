<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jaminan_sewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sewa_id')->constrained('sewa')->cascadeOnDelete();
            $table->string('metode_jaminan'); // ktp_tatap_muka, ktp_upload_deposit
            $table->string('no_ktp')->nullable();
            $table->string('nama_sesuai_ktp')->nullable();
            $table->string('foto_ktp')->nullable();
            $table->decimal('jumlah_deposit', 12, 2)->default(0);
            $table->string('status_pengembalian_deposit')->default('belum'); // belum, dikembalikan
            $table->string('status_verifikasi')->default('pending'); // pending, terverifikasi, ditolak
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaminan_sewa');
    }
};