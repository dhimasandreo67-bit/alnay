<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis'); // beli, sewa
            $table->date('tanggal');
            $table->decimal('total', 14, 2)->default(0);
            $table->text('catatan')->nullable();
// $table->foreignId('reservasi_fitting_id')
//       ->nullable()
//       ->constrained('reservasi_fitting')
//       ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};