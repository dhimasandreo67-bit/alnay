<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transaksi', 'stok_ditahan')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->boolean('stok_ditahan')->default(false)->after('catatan');
            });
        }

        // Pesanan beli online yang sudah lunas sebelum fitur ini ada: stoknya sudah terpotong
        // lewat alur lama (saat pembayaran). Tandai true supaya lepasStok()/hapus transaksi
        // tahu stok memang sudah terpotong, dan tidak terpotong dua kali.
        DB::table('transaksi')
            ->where('jenis', 'beli')
            ->where(function ($q) {
                $q->whereNull('channel')->orWhere('channel', '!=', 'kasir');
            })
            ->whereIn('status_pembayaran', ['lunas', 'settlement', 'success'])
            ->update(['stok_ditahan' => true]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaksi', 'stok_ditahan')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropColumn('stok_ditahan');
            });
        }
    }
};