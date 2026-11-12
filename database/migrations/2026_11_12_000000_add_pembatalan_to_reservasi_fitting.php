<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservasi_fitting', function (Blueprint $table) {
            $table->text('alasan_batal')->nullable()->after('catatan');
            $table->string('dibatalkan_oleh', 20)->nullable()->after('alasan_batal'); // 'admin' | 'pelanggan'
        });
    }

    public function down(): void
    {
        Schema::table('reservasi_fitting', function (Blueprint $table) {
            $table->dropColumn(['alasan_batal', 'dibatalkan_oleh']);
        });
    }
};