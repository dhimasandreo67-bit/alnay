<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlamatPengiriman extends Model
{
    protected $table = 'alamat_pengiriman';

    protected $fillable = [
        'user_id',
        'label',
        'nama_penerima',
        'no_hp',
        'provinsi',
        'kota_kabupaten',
        'kecamatan',
        'kode_pos',
        'alamat',
        'ongkir_tujuan_id',
        'ongkir_tujuan_label',
        'is_utama',
    ];

    protected $casts = [
        'is_utama' => 'boolean',
        'ongkir_tujuan_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}