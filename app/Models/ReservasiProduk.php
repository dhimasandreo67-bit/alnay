<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservasiProduk extends Model
{
    protected $table = 'reservasi_produk';

    protected $fillable = ['reservasi_id', 'produk_id'];

    public function reservasi()
    {
        return $this->belongsTo(ReservasiFitting::class, 'reservasi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}