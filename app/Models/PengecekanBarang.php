<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengecekanBarang extends Model
{
    protected $table = 'pengecekan_barang';

    protected $fillable = [
        'sewa_id',
        'tahap',
        'kondisi',
        'tanggal_cek',
        'tingkat_kerusakan',
        'keterangan',
        'foto_bukti',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_cek' => 'date',
        ];
    }

    public function sewa()
    {
        return $this->belongsTo(Sewa::class);
    }
}