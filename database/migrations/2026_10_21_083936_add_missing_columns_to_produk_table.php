<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            if (!Schema::hasColumn('produk', 'motif')) {
                $table->string('motif')->nullable()->after('bahan');
            }
            if (!Schema::hasColumn('produk', 'berat')) {
                $table->decimal('berat', 10, 2)->nullable()->after('motif');
            }
            if (!Schema::hasColumn('produk', 'warna')) {
                $table->json('warna')->nullable()->after('berat');
            }
            if (!Schema::hasColumn('produk', 'ukuran')) {
                $table->json('ukuran')->nullable()->after('warna');
            }
            if (!Schema::hasColumn('produk', 'denda_per_hari_telat')) {
                $table->decimal('denda_per_hari_telat', 15, 2)->default(0)->after('bisa_disewa');
            }
            if (!Schema::hasColumn('produk', 'diskon_persen')) {
                $table->decimal('diskon_persen', 5, 2)->default(0)->after('denda_per_hari_telat');
            }
            if (!Schema::hasColumn('produk', 'rating_rata_rata')) {
                $table->decimal('rating_rata_rata', 3, 2)->default(0)->after('diskon_persen');
            }
            if (!Schema::hasColumn('produk', 'jumlah_rating')) {
                $table->unsignedInteger('jumlah_rating')->default(0)->after('rating_rata_rata');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $columns = [
                'motif', 'berat', 'warna', 'ukuran', 
                'denda_per_hari_telat', 'diskon_persen', 
                'rating_rata_rata', 'jumlah_rating'
            ];
            
            foreach ($columns as $col) {
                if (Schema::hasColumn('produk', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};