<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan pengingat yang sudah terkirim (mis. "sewa:12:telat-3"), supaya satu tahap pengingat
 * hanya terkirim sekali per sewa. Sengaja terpisah dari tabel notifications: menghapus notifikasi
 * atau membersihkan cache tidak boleh membuat pengingat terkirim ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pengingat_admin')) {
            return;
        }

        Schema::create('pengingat_admin', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 100)->unique();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengingat_admin');
    }
};