<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris jejak aktivitas. Hanya ditulis lewat App\Support\Audit.
 * Tidak diubah dari aplikasi; hanya dihapus lewat menu "Bersihkan Log" atau pemangkasan otomatis.
 *
 * Pemangkasan otomatis: log lebih tua dari SIMPAN_HARI dihapus oleh `php artisan model:prune`
 * (dijadwalkan harian, lihat routes/console.php). MassPrunable menghapus bertahap dan tanpa memuat model ke memori.
 */
class AuditLog extends Model
{
    use MassPrunable;

    /** Lama log disimpan (hari). Ubah angka ini kalau mau lebih lama / lebih singkat. */
    public const SIMPAN_HARI = 180;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<', now()->subDays(self::SIMPAN_HARI));
    }
}