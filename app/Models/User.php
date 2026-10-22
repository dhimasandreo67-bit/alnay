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
        'admin_permissions',
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
            'admin_permissions' => 'array',
        ];
    }

    // Hak akses panel admin

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdminPanelUser(): bool
    {
        return in_array($this->role, ['admin', 'owner'], true);
    }

    /**
     * Apakah user ini boleh membuka menu/halaman panel admin tertentu.
     * Owner selalu diizinkan; admin hanya kalau $key ada di admin_permissions-nya.
     */
    public function canAccessAdminMenu(string $key): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return in_array($key, $this->admin_permissions ?? [], true);
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