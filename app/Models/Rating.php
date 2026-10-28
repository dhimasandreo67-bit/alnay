<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = 'rating';

    protected $fillable = [
        'detail_transaksi_id',
        'produk_id',
        'user_id',
        'rating',
        'ulasan',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function detailTransaksi()
    {
        return $this->belongsTo(DetailTransaksi::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}