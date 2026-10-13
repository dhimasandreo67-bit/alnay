<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'no_hp',
        'jenis_kelamin',
        'tanggal_lahir',
        'provinsi',
        'kota_kabupaten',
        'kecamatan',
        'kode_pos',
        'alamat',
        'foto_profil',
        'status_aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tanggal_lahir' => 'date',
            'password' => 'hashed',
            'status_aktif' => 'boolean',
        ];
    }

    // Relasi
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function rating()
    {
        return $this->hasMany(Rating::class);
    }

    public function reservasiFitting()
    {
        return $this->hasMany(ReservasiFitting::class);
    }
}