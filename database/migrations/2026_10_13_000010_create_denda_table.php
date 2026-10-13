<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('denda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sewa_id')->constrained('sewa')->cascadeOnDelete();
            $table->string('jenis'); // telat, kerusakan, kehilangan
            $table->decimal('jumlah', 12, 2);
            $table->string('status_bayar')->default('belum'); // belum, lunas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denda');
    }
};