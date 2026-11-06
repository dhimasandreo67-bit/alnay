<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kapan akun dinonaktifkan (null = tidak pernah / masih aktif)
            $table->timestamp('deactivated_at')
                  ->nullable()
                  ->after('is_active');

            // Siapa yang menonaktifkan:
            //   null  => sistem (auto via scheduled command)
            //   <id>  => admin yang menekan tombol nonaktifkan
            $table->foreignId('deactivated_by')
                  ->nullable()
                  ->after('deactivated_at')
                  ->constrained('users')
                  ->nullOnDelete();

            // Catatan alasan, mis. "Auto: tidak aktif ≥360 hari"
            // atau "Dinonaktifkan manual oleh admin" / alasan bebas dari form.
            $table->string('deactivated_note', 255)
                  ->nullable()
                  ->after('deactivated_by');

            // Index bantu query "siapa saja yang dinonaktifkan otomatis"
            // dan filter di halaman admin.
            $table->index(['deactivated_by', 'deactivated_at'], 'users_deactivated_idx');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop index dulu sebelum drop kolom (urutan penting di MySQL)
            $table->dropIndex('users_deactivated_idx');

            // Drop foreign key sebelum drop kolomnya
            $table->dropForeign(['deactivated_by']);

            $table->dropColumn([
                'deactivated_at',
                'deactivated_by',
                'deactivated_note',
            ]);
        });
    }
};