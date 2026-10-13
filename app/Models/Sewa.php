<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sewa extends Model
{
    protected $table = 'sewa';

    protected $fillable = [
        'transaksi_id',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali_rencana' => 'date',
            'tanggal_kembali_aktual' => 'date',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function jaminan()
    {
        return $this->hasOne(JaminanSewa::class);
    }

    public function pengecekan()
    {
        return $this->hasMany(PengecekanBarang::class);
    }

    public function denda()
    {
        return $this->hasMany(Denda::class);
    }
}