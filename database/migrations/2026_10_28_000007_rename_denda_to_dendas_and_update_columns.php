<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename tabel denda → dendas
        Schema::rename('denda', 'dendas');

        Schema::table('dendas', function (Blueprint $table) {
            $table->renameColumn('status_bayar', 'status');
            $table->string('keterangan')->nullable()->after('jenis');
        });

        // Ubah default status
        Schema::table('dendas', function (Blueprint $table) {
            $table->string('status')->default('belum_lunas')->change();
        });
    }

    public function down(): void
    {
        Schema::table('dendas', function (Blueprint $table) {
            $table->dropColumn('keterangan');
            $table->renameColumn('status', 'status_bayar');
        });

        Schema::table('dendas', function (Blueprint $table) {
            $table->string('status_bayar')->default('belum')->change();
        });

        Schema::rename('dendas', 'denda');
    }
};