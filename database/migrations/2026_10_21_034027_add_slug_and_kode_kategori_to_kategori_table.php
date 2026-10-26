<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('nama_kategori');
            }
            if (!Schema::hasColumn('kategori', 'kode_kategori')) {
                $table->string('kode_kategori')->nullable()->after('slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kategori', function (Blueprint $table) {
            $table->dropColumn(['slug', 'kode_kategori']);
        });
    }
};