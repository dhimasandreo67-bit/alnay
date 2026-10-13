<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStatus extends Model
{
    protected $table = 'riwayat_status';

    protected $fillable = [
        'tabel_terkait',
        'record_id',
        'status_lama',
        'status_baru',
        'diubah_oleh',
        'waktu_ubah',
    ];

    protected function casts(): array
    {
        return ['waktu_ubah' => 'datetime'];
    }

    public function pengubah()
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
