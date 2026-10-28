<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris jejak aktivitas. Hanya ditulis lewat App\Support\Audit,
 * tidak pernah diubah atau dihapus dari aplikasi.
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
            'created_at' => 'datetime',
        ];
    }
}