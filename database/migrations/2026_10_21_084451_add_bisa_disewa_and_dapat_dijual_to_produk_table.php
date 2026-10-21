<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            if (!Schema::hasColumn('produk', 'bisa_disewa')) {
                $table->boolean('bisa_disewa')->default(false)->after('status');
            }
            if (!Schema::hasColumn('produk', 'dapat_dijual')) {
                $table->boolean('dapat_dijual')->default(false)->after('bisa_disewa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            if (Schema::hasColumn('produk', 'bisa_disewa')) {
                $table->dropColumn('bisa_disewa');
            }
            if (Schema::hasColumn('produk', 'dapat_dijual')) {
                $table->dropColumn('dapat_dijual');
            }
        });
    }
};