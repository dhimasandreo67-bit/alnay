<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservasi_fitting', function (Blueprint $table) {
            $table->string('kode_booking')->unique()->after('id');
            $table->text('catatan')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('reservasi_fitting', function (Blueprint $table) {
            $table->dropColumn(['kode_booking', 'catatan']);
        });
    }
};
