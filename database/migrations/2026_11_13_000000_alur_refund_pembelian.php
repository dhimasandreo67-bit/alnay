<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund', function (Blueprint $table) {
            $table->text('alasan_penolakan')->nullable()->after('alasan');
            $table->json('bukti_foto')->nullable()->after('alasan_penolakan');

            // Tujuan transfer dana dari pelanggan
            $table->string('bank_tujuan', 50)->nullable()->after('bukti_foto');
            $table->string('no_rekening', 50)->nullable()->after('bank_tujuan');
            $table->string('atas_nama', 100)->nullable()->after('no_rekening');

            $table->string('bukti_transfer')->nullable()->after('jumlah');
            $table->boolean('stok_dikembalikan')->default(false)->after('bukti_transfer');
            $table->timestamp('tanggal_barang_diterima')->nullable()->after('stok_dikembalikan');
            $table->timestamp('tanggal_selesai')->nullable()->after('tanggal_barang_diterima');
        });

        // Satukan istilah status: 'pending' (kode lama) -> 'diajukan' (default tabel)
        DB::table('refund')->where('status', 'pending')->update(['status' => 'diajukan']);
    }

    public function down(): void
    {
        DB::table('refund')->where('status', 'diajukan')->update(['status' => 'pending']);

        Schema::table('refund', function (Blueprint $table) {
            $table->dropColumn([
                'alasan_penolakan', 'bukti_foto', 'bank_tujuan', 'no_rekening', 'atas_nama',
                'bukti_transfer', 'stok_dikembalikan', 'tanggal_barang_diterima', 'tanggal_selesai',
            ]);
        });
    }
};