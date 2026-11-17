<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status notifikasi lonceng PELANGGAN (bukan admin).
 * Notifikasinya sendiri tetap berasal dari tabel transaksi; tabel ini hanya menyimpan
 * apakah pelanggan sudah membukanya (dibaca) dan apakah ia sudah menghapusnya (disembunyikan).
 * Menghapus notifikasi TIDAK menghapus transaksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_pelanggan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transaksi_id');
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamp('disembunyikan_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'transaksi_id']);
            $table->index('transaksi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_pelanggan');
    }
};