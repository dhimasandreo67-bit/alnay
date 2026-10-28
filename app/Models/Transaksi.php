<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

        protected $fillable = [
        'kode_transaksi',
        'user_id',
        'jenis',
        'kanal',
        'tanggal',
        'total',
        'status_pembayaran',
        'catatan',
        'reservasi_fitting_id',
    ];

    protected function casts(): array   
    {
        return [
            'tanggal' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class);
    }

    public function sewa()
    {
        return $this->hasOne(Sewa::class);
    }

    public function refund()
    {
        return $this->hasMany(Refund::class);
    }

    public function pengembalian()
    {
        return $this->hasMany(Pengembalian::class);
    }

        public function rating()
    {
        return $this->hasManyThrough(
            Rating::class,
            DetailTransaksi::class,
            'transaksi_id', // Foreign key on detail_transaksi table
            'detail_transaksi_id', // Foreign key on rating table
            'id', // Local key on transaksi table
            'id' // Local key on detail_transaksi table
        );
    }
}