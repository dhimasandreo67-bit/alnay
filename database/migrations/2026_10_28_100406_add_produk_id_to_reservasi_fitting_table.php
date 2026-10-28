<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('reservasi_fitting', function (Blueprint $table) {
        $table->foreignId('produk_id')->nullable()->constrained('produk')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('reservasi_fitting', function (Blueprint $table) {
        $table->dropForeign(['produk_id']);
        $table->dropColumn('produk_id');
    });
}
};
