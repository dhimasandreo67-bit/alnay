<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $t) {
            $t->decimal('harga_perpanjangan_per_hari', 12, 2)->default(0)->after('denda_per_hari_telat');
            $t->decimal('diskon_sewa_persen', 5, 2)->default(0)->after('diskon_persen');
            $t->decimal('diskon_jual_persen', 5, 2)->default(0)->after('diskon_sewa_persen');
        });

        // Data lama: diskon lama dianggap berlaku untuk sewa dan jual
        DB::table('produk')->update([
            'diskon_sewa_persen' => DB::raw('diskon_persen'),
            'diskon_jual_persen' => DB::raw('diskon_persen'),
        ]);
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $t) {
            $t->dropColumn(['harga_perpanjangan_per_hari', 'diskon_sewa_persen', 'diskon_jual_persen']);
        });
    }
};