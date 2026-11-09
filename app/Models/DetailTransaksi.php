<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'jumlah',
        'harga_satuan',
        'ukuran',
        'warna',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
            'tanggal_pinjam' => 'date',
            'tanggal_kembali_rencana' => 'date',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function rating()
    {
        return $this->hasMany(Rating::class);
    }
}