<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel yang dipakai model Denda adalah "dendas" (bukan "denda").
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dendas', function (Blueprint $table) {
            if (!Schema::hasColumn('dendas', 'otomatis')) {
                $table->boolean('otomatis')->default(false);
            }
            if (!Schema::hasColumn('dendas', 'jumlah_hari')) {
                $table->unsignedInteger('jumlah_hari')->nullable();
            }
            if (!Schema::hasColumn('dendas', 'tarif_per_hari')) {
                $table->decimal('tarif_per_hari', 12, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dendas', function (Blueprint $table) {
            foreach (['otomatis', 'jumlah_hari', 'tarif_per_hari'] as $kolom) {
                if (Schema::hasColumn('dendas', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};