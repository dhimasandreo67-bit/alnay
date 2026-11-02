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
        'channel',
        'nama_tamu',
        'no_hp_tamu',
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

    /*
    |--------------------------------------------------------------------------
    | Data pelanggan (satu sumber untuk Transaksi, Sewa, dan Jaminan)
    |--------------------------------------------------------------------------
    | Pelanggan terdaftar -> dari relasi user
    | Pelanggan tamu      -> dari kolom nama_tamu / no_hp_tamu
    */
    public function getIsTamuAttribute(): bool
    {
        return $this->user === null;
    }

    public function getNamaPelangganAttribute(): string
    {
        return $this->user?->name ?: ($this->nama_tamu ?: '-');
    }

    public function getNoHpPelangganAttribute(): ?string
    {
        return $this->user?->no_hp ?: ($this->no_hp_tamu ?: null);
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