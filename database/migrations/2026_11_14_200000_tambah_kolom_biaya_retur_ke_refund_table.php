<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund', function (Blueprint $table) {
            // Biaya jemput yang dibayar toko ke kurir (catatan pembukuan, tidak mengurangi dana pelanggan).
            if (! Schema::hasColumn('refund', 'biaya_jemput')) {
                $table->decimal('biaya_jemput', 12, 2)->nullable();
            }
            // Ongkir yang dibayar pelanggan sendiri saat mengantar ke agen kurir (diisi pelanggan).
            if (! Schema::hasColumn('refund', 'biaya_ongkir_retur')) {
                $table->decimal('biaya_ongkir_retur', 12, 2)->nullable();
            }
            // Ongkir yang diganti toko, diputuskan admin saat barang diterima (ditambahkan ke transfer).
            if (! Schema::hasColumn('refund', 'ganti_ongkir')) {
                $table->decimal('ganti_ongkir', 12, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('refund', function (Blueprint $table) {
            foreach (['biaya_jemput', 'biaya_ongkir_retur', 'ganti_ongkir'] as $k) {
                if (Schema::hasColumn('refund', $k)) {
                    $table->dropColumn($k);
                }
            }
        });
    }
};