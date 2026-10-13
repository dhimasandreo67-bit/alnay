<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    protected $table = 'denda';

    protected $fillable = [
        'sewa_id',
        'jenis',
        'jumlah',
        'status_bayar',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
        ];
    }

    public function sewa()
    {
        return $this->belongsTo(Sewa::class);
    }
}