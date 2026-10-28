<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservasiFitting extends Model
{
    protected $table = 'reservasi_fitting';

    protected $fillable = [
        'kode_booking',
        'user_id',
        'slot_id',
        'produk_id',
        'tanggal_daftar',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return ['tanggal_daftar' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot()
    {
        return $this->belongsTo(SlotFitting::class, 'slot_id');
    }
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}