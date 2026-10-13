<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'transaksi_id',
        'jenis_bayar',
        'jumlah',
        'metode',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'payment_type',
        'status_midtrans',
        'snap_token',
        'tanggal_bayar',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal_bayar' => 'datetime',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}