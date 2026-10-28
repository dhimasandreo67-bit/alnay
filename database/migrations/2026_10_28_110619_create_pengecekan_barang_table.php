<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('pengecekan_barang', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sewa_id')->constrained('sewa')->onDelete('cascade');
        $table->string('tahap'); // 'sebelum_cuci' atau 'setelah_cuci'
        $table->text('catatan');
        $table->string('petugas');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengecekan_barang');
    }
};
