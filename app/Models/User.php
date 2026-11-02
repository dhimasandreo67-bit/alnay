<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Nilai `role` yang dianggap staf panel admin (owner/admin), bukan pelanggan.
     * Dipakai oleh scope pelanggan() di bawah, dan oleh controller yang perlu
     * daftar yang sama (mis. PenggunaController, TransaksiController).
     */
    public const ROLE_STAF = ['owner', 'admin', 'superadmin', 'super_admin'];

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

        // ==== TAMBAHKAN 4 BARIS INI ====
        'provinsi_id',
        'provinsi_nama',
        'kota_id',
        'kota_nama',
        // ==============================
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

    // Scope

    /**
     * Scope: hanya akun pelanggan (bukan staf/owner/admin).
     *
     * Staf dikenali dari kolom `role` (nilai di ROLE_STAF) atau kolom boolean
     * is_admin / is_owner kalau ada di tabel `users`. Scope ini jadi satu-satunya
     * sumber kebenaran, dipakai bareng oleh PenggunaController (halaman Kelola
     * Pengguna) dan TransaksiController (dropdown pelanggan di halaman Kasir),
     * supaya daftar pelanggan selalu konsisten di seluruh panel admin.
     *
     * Pemakaian: User::pelanggan()->... atau $query->pelanggan() kalau $query
     * sudah berupa Builder.
     */
    public function scopePelanggan($query)
    {
        if (Schema::hasColumn('users', 'role')) {
            $query->where(function ($w) {
                $w->whereNull('role')->orWhereNotIn('role', self::ROLE_STAF);
            });
        }

        foreach (['is_admin', 'is_owner'] as $kolom) {
            if (Schema::hasColumn('users', $kolom)) {
                $query->where(function ($w) use ($kolom) {
                    $w->whereNull($kolom)->orWhere($kolom, false);
                });
            }
        }

        return $query;
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