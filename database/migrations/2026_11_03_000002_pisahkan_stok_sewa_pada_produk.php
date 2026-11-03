<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // warna & ukuran tadinya varchar(255): JSON variasi ukuran + warna mudah melebihi itu,
        // apalagi sekarang ada dua set (jual & sewa). Dijadikan text.
        Schema::table('produk', function (Blueprint $table) {
            $table->text('warna')->nullable()->change();
            $table->text('ukuran')->nullable()->change();
        });

        Schema::table('produk', function (Blueprint $table) {
            if (! Schema::hasColumn('produk', 'warna_sewa')) {
                $table->text('warna_sewa')->nullable()->after('ukuran');
            }
            if (! Schema::hasColumn('produk', 'ukuran_sewa')) {
                $table->text('ukuran_sewa')->nullable()->after('warna_sewa');
            }
            if (! Schema::hasColumn('produk', 'stok_sewa')) {
                $table->unsignedInteger('stok_sewa')->default(0)->after('stok');
            }
        });

        // Produk yang bisa dijual DAN disewa: stok sewa awal disalin dari stok lama
        // supaya tidak mendadak habis. Admin tinggal menyesuaikan di halaman edit produk.
        DB::table('produk')
            ->where('bisa_disewa', 1)
            ->where('dapat_dijual', 1)
            ->update([
                'warna_sewa'  => DB::raw('warna'),
                'ukuran_sewa' => DB::raw('ukuran'),
                'stok_sewa'   => DB::raw('stok'),
            ]);
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            foreach (['warna_sewa', 'ukuran_sewa', 'stok_sewa'] as $kolom) {
                if (Schema::hasColumn('produk', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};