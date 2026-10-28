<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Siapa pelakunya. Nama & peran disimpan sebagai salinan (snapshot) supaya
            // log tetap terbaca walau akunnya nanti dihapus atau diganti nama.
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('pelaku')->nullable();
            $table->string('peran', 20)->nullable()->index(); // owner | admin | pelanggan | tamu
            $table->string('guard', 20)->nullable();          // admin | web

            // Apa yang terjadi.
            $table->string('aksi', 40)->index();              // created | updated | deleted | login | logout | login_gagal | status | ...
            $table->string('modul', 80)->nullable()->index(); // Produk, Transaksi, Pengguna, Autentikasi, ...
            $table->string('subjek_type')->nullable();
            $table->string('subjek_id', 64)->nullable();
            $table->string('subjek_label')->nullable();
            $table->text('deskripsi');
            $table->json('perubahan')->nullable();            // [{field, lama, baru}, ...]

            // Dari mana.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable();

            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['subjek_type', 'subjek_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};