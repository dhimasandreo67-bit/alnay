<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // true = akun dibuat manual oleh admin (boleh diedit admin).
            // false = akun dibuat sendiri oleh pengguna lewat daftar/OTP/Google (tidak boleh diedit admin).
            $table->boolean('dibuat_admin')->default(false)->after('status_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dibuat_admin');
        });
    }
};