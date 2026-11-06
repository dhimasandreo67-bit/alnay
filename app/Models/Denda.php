<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory;

    /**
     * Tabel di database bernama "dendas" (plural bawaan Laravel).
     */
    protected $table = 'dendas';

    protected $fillable = [
        'sewa_id',
        'jenis',
        'keterangan',
        'jumlah',
        'status',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'otomatis' => 'boolean',
    ];

    public function sewa()
    {
        return $this->belongsTo(Sewa::class, 'sewa_id');
    }

    public function scopeBelumLunas($query)
    {
        return $query->where('status', 'belum_lunas');
    }

    public function tandaiLunas(): void
    {
        $this->update(['status' => 'lunas']);
    }
}