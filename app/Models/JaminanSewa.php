<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JaminanSewa extends Model
{
    protected $table = 'jaminan_sewa';

    protected $fillable = [
        'sewa_id',
        'metode_jaminan',
        'no_ktp',
        'nama_sesuai_ktp',
        'foto_ktp',
        'jumlah_deposit',
        'status_pengembalian_deposit',
        'status_verifikasi',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_deposit' => 'decimal:2',
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    public function sewa()
    {
        return $this->belongsTo(Sewa::class);
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}