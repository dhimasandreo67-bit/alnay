<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';

    protected $fillable = [
        'nama_kategori',
        'kode_kategori',
        'slug',
        'is_active',
    ];

    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}