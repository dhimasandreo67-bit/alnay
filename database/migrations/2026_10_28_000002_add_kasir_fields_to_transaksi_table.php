<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration ini AMAN dijalankan berkali-kali: setiap kolom dicek dulu
     * apakah sudah ada sebelum ditambahkan (soalnya percobaan migration
     * sebelumnya sempat gagal di tengah jalan dan menyisakan beberapa
     * kolom yang sudah terlanjur ditambahkan).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('transaksi', 'channel')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->string('channel', 20)->default('online')->after('status_pembayaran');
            });
        }

        if (! Schema::hasColumn('transaksi', 'nama_tamu')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->string('nama_tamu')->nullable()->after('channel');
            });
        }

        if (! Schema::hasColumn('transaksi', 'no_hp_tamu')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->string('no_hp_tamu', 30)->nullable()->after('nama_tamu');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `transaksi` MODIFY `user_id` BIGINT UNSIGNED NULL');
        } else {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi', 'channel')) {
                $table->dropColumn('channel');
            }
            if (Schema::hasColumn('transaksi', 'nama_tamu')) {
                $table->dropColumn('nama_tamu');
            }
            if (Schema::hasColumn('transaksi', 'no_hp_tamu')) {
                $table->dropColumn('no_hp_tamu');
            }
        });

        DB::statement('ALTER TABLE `transaksi` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
    }
};
