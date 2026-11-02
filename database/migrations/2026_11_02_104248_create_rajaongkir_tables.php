<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rajaongkir_provinsi', function (Blueprint $table) {
            $table->id();
            $table->string('province_id')->unique();
            $table->string('province');
            $table->timestamps();
        });

        Schema::create('rajaongkir_kota', function (Blueprint $table) {
            $table->id();
            $table->string('city_id')->unique();
            $table->string('province_id');
            $table->string('type'); // Kota / Kabupaten
            $table->string('city_name');
            $table->string('postal_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rajaongkir_kota');
        Schema::dropIfExists('rajaongkir_provinsi');
    }
};