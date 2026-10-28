<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            // Menambahkan kolom 'dp' setelah kolom 'harga_sewa'
            $table->decimal('dp', 14, 2)->nullable()->after('harga_sewa');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('dp');
        });
    }
};