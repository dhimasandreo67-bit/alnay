<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jaminan_sewa', function (Blueprint $table) {
            if (! Schema::hasColumn('jaminan_sewa', 'nomor_ktp')) {
                $table->string('nomor_ktp', 32)->nullable();
            }
            if (! Schema::hasColumn('jaminan_sewa', 'nama_ktp')) {
                $table->string('nama_ktp', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jaminan_sewa', function (Blueprint $table) {
            foreach (['nomor_ktp', 'nama_ktp'] as $kolom) {
                if (Schema::hasColumn('jaminan_sewa', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};