<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     *
     * Menyimpan daftar menu panel admin yang boleh diakses seorang admin,
     * contoh isi kolom: ["kategori", "produk", "transaksi"].
     * Khusus role 'owner', kolom ini tidak dipakai — owner selalu full access
     * (lihat App\Models\User::canAccessAdminMenu()).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('admin_permissions')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_permissions');
        });
    }
};