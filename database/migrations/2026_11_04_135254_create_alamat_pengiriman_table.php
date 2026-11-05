<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alamat_pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label', 50)->default('Rumah');
            $table->string('nama_penerima', 100);
            $table->string('no_hp', 20);
            $table->string('provinsi', 100);
            $table->string('kota_kabupaten', 100);
            $table->string('kecamatan', 100);
            $table->string('kode_pos', 10)->nullable();
            $table->text('alamat');
            $table->unsignedInteger('ongkir_tujuan_id')->nullable();
            $table->string('ongkir_tujuan_label', 255)->nullable();
            $table->boolean('is_utama')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat_pengiriman');
    }
};