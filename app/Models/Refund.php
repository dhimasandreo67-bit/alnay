<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $table = 'refund';

    protected $fillable = [
        'transaksi_id',
        'diajukan_oleh',
        'diproses_oleh',
        'alasan',
        'catatan_negosiasi',
        'jumlah',
        'status',
    ];

    protected function casts(): array
    {
        return ['jumlah' => 'decimal:2'];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function pemroses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}