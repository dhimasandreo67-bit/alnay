<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';

    protected $fillable = [
        'transaksi_id',
        'provinsi_tujuan',
        'kota_tujuan',
        'kecamatan_tujuan',
        'kode_pos_tujuan',
        'alamat_tujuan',
        'kurir',
        'no_resi',
        'biaya_kirim',
        'status_kirim',
        'tanggal_kirim',
        'tanggal_diterima',
    ];

    protected function casts(): array
    {
        return [
            'biaya_kirim' => 'decimal:2',
            'tanggal_kirim' => 'date',
            'tanggal_diterima' => 'date',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}