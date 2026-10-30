<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('refund', function (Blueprint $table) {
        $table->string('no_resi_penjemputan')->nullable()->after('alasan');
    });
}

public function down(): void
{
    Schema::table('refund', function (Blueprint $table) {
        $table->dropColumn('no_resi_penjemputan');
    });
}
};
