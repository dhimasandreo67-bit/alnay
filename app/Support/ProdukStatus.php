<?php

namespace App\Support;

/*
|--------------------------------------------------------------------------
| Pemetaan Label Status Produk
|--------------------------------------------------------------------------
| Satu tempat saja — jangan tersebar di Blade.
| Aturan bisnis:
|   - dapat_dijual = false → "Khusus Sewa"
|   - status lain → sesuai nilai kolom status
*/

class ProdukStatus
{
    public static function label(string $status, bool $dapatDijual): string
    {
        if (! $dapatDijual) {
            return 'Khusus Sewa';
        }

        return match ($status) {
            'tersedia' => 'Tersedia',
            'disewa'   => 'Disewa',
            'rusak'    => 'Rusak',
            'dicuci'   => 'Dicuci',
            default    => ucfirst($status),
        };
    }

    public static function variant(string $status, bool $dapatDijual): string
    {
        if (! $dapatDijual) {
            return 'ink';
        }

        return match ($status) {
            'tersedia' => 'success',
            'disewa'   => 'warning',
            'rusak'    => 'danger',
            'dicuci'   => 'muted',
            default    => 'muted',
        };
    }
}