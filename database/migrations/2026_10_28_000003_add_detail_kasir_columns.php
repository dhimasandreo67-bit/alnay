<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_transaksi', 'ukuran')) {
                $table->string('ukuran', 20)->nullable()->after('harga_satuan');
            }
            if (! Schema::hasColumn('detail_transaksi', 'warna')) {
                $table->string('warna', 100)->nullable()->after('ukuran');
            }
        });

        Schema::table('transaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('transaksi', 'catatan')) {
                $table->string('catatan', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn(['ukuran', 'warna']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }
};