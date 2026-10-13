<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status', function (Blueprint $table) {
            $table->id();
            $table->string('tabel_terkait');
            $table->unsignedBigInteger('record_id');
            $table->string('status_lama')->nullable();
            $table->string('status_baru');
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waktu_ubah');
            $table->timestamps();

            $table->index(['tabel_terkait', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status');
    }
};