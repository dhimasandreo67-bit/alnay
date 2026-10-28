<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengecekanBarang extends Model
{
    protected $table = 'pengecekan_barang';

    protected $fillable = [
        'sewa_id',
        'tahap',
        'catatan',
        'petugas',
    ];

    public function sewa()
    {
        return $this->belongsTo(Sewa::class);
    }
}