<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('foto_produk')->nullable();
            $table->string('bahan')->nullable();
            $table->decimal('berat', 8, 2)->nullable(); // gram
            $table->string('ukuran')->nullable(); // "M, L, XL"
            $table->decimal('harga_jual', 12, 2)->nullable();
            $table->decimal('harga_sewa', 12, 2)->nullable();
            $table->integer('stok')->default(0);
            $table->string('status')->default('tersedia'); // tersedia, disewa, rusak, dicuci
            $table->boolean('dapat_dijual')->default(false);
            $table->decimal('denda_per_hari_telat', 12, 2)->nullable();
            $table->decimal('rating_rata_rata', 3, 2)->default(0);
            $table->integer('jumlah_rating')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};