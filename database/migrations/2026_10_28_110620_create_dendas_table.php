<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('dendas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sewa_id')->constrained('sewa')->onDelete('cascade');
        $table->string('jenis'); // 'keterlambatan' atau 'kerusakan'
        $table->string('keterangan')->nullable();
        $table->decimal('jumlah', 12, 2);
        $table->string('status')->default('belum_lunas'); // 'belum_lunas' atau 'lunas'
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dendas');
    }
};
