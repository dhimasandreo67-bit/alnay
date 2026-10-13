<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'kategori_id',
        'sku',
        'nama',
        'deskripsi',
        'foto_produk',
        'bahan',
        'berat',
        'ukuran',
        'harga_jual',
        'harga_sewa',
        'stok',
        'status',
        'dapat_dijual',
        'denda_per_hari_telat',
        'rating_rata_rata',
        'jumlah_rating',
    ];

    protected function casts(): array
    {
        return [
            'berat' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'harga_sewa' => 'decimal:2',
            'denda_per_hari_telat' => 'decimal:2',
            'rating_rata_rata' => 'decimal:2',
            'dapat_dijual' => 'boolean',
        ];
    }

    // Relasi
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function rating()
    {
        return $this->hasMany(Rating::class);
    }
}