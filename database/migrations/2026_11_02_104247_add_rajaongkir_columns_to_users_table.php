<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('provinsi_id')->nullable()->after('alamat');
            $table->integer('kota_id')->nullable()->after('provinsi_id');
            $table->string('kota_nama')->nullable()->after('kota_id');
            $table->string('provinsi_nama')->nullable()->after('kota_nama');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provinsi_id', 'kota_id', 'kota_nama', 'provinsi_nama']);
        });
    }
};