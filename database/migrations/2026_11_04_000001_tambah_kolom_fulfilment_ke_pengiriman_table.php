<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            if (! Schema::hasColumn('pengiriman', 'nama_penerima')) {
                $table->string('nama_penerima', 100)->nullable();
            }
            if (! Schema::hasColumn('pengiriman', 'no_wa_penerima')) {
                $table->string('no_wa_penerima', 20)->nullable();
            }
            if (! Schema::hasColumn('pengiriman', 'tujuan_id')) {
                $table->unsignedBigInteger('tujuan_id')->nullable();
            }
            if (! Schema::hasColumn('pengiriman', 'berat_gram')) {
                $table->unsignedInteger('berat_gram')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropColumn(['nama_penerima', 'no_wa_penerima', 'tujuan_id', 'berat_gram']);
        });
    }
};