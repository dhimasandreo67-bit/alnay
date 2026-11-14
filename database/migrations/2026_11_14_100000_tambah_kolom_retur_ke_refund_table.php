<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KOLOM = [
        'metode_pengembalian', 'kurir_retur', 'no_resi_retur', 'bukti_pengiriman', 'tanggal_dikirim', 'batas_kirim',
    ];

    public function up(): void
    {
        // Pengecekan hasColumn: aman dijalankan ulang / di database yang sebagian kolomnya sudah ada.
        Schema::table('refund', function (Blueprint $table) {
            if (! Schema::hasColumn('refund', 'metode_pengembalian')) {
                $table->string('metode_pengembalian', 20)->nullable();
            }
            if (! Schema::hasColumn('refund', 'kurir_retur')) {
                $table->string('kurir_retur', 50)->nullable();
            }
            if (! Schema::hasColumn('refund', 'no_resi_retur')) {
                $table->string('no_resi_retur', 100)->nullable();
            }
            if (! Schema::hasColumn('refund', 'bukti_pengiriman')) {
                $table->string('bukti_pengiriman')->nullable();
            }
            if (! Schema::hasColumn('refund', 'tanggal_dikirim')) {
                $table->timestamp('tanggal_dikirim')->nullable();
            }
            if (! Schema::hasColumn('refund', 'batas_kirim')) {
                $table->timestamp('batas_kirim')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('refund', function (Blueprint $table) {
            $ada = array_filter(self::KOLOM, fn ($k) => Schema::hasColumn('refund', $k));
            if ($ada) {
                $table->dropColumn(array_values($ada));
            }
        });
    }
};