<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';

    /** Urutan status pengiriman yang dipakai di alur admin. */
    public const STATUS = ['menunggu', 'dikemas', 'dikirim', 'diterima'];

    protected $fillable = [
        'transaksi_id',
        'provinsi_tujuan',
        'kota_tujuan',
        'kecamatan_tujuan',
        'kode_pos_tujuan',
        'alamat_tujuan',
        'kurir',
        'layanan_ongkir',
        'no_resi',
        'biaya_kirim',
        'status_kirim',
        'tanggal_kirim',
        'tanggal_diterima',
        'nama_penerima',
        'no_wa_penerima',
        'tujuan_id',
        'berat_gram',
    ];

    protected function casts(): array
    {
        return [
            'biaya_kirim' => 'decimal:2',
            'tanggal_kirim' => 'date',
            'tanggal_diterima' => 'date',
            'tujuan_id' => 'integer',
            'berat_gram' => 'integer',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}