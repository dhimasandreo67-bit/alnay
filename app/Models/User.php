<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
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
    // `role`, `admin_permissions`, `status_aktif`, dan `dibuat_admin` sengaja TIDAK di sini: isi lewat forceFill()
    // di kode yang tepercaya (AdminController, PenggunaController, BuatOwnerCommand, dll).
    protected $fillable = [
        'name',
        'email',
        'password',
        'no_hp',
        'jenis_kelamin',
        'tanggal_lahir',
        'provinsi',
        'kota_kabupaten',
        'kecamatan',
        'kode_pos',
        'alamat',
        'foto_profil',

        'provinsi_id',
        'provinsi_nama',
        'kota_id',
        'kota_nama',
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
            'dibuat_admin' => 'boolean',
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

    // Status akun (aktif / dinonaktifkan)

    /**
     * Akun boleh dipakai login? Mengikuti kolom `is_active` yang dikelola
     * lewat halaman Kelola Pengguna. Kolom kosong (null) dianggap aktif.
     */
    public function akunAktif(): bool
    {
        $nilai = $this->attributes['is_active'] ?? null;

        return $nilai === null ? true : (bool) $nilai;
    }

    /**
     * Info yang ditampilkan di halaman "Akun Dinonaktifkan".
     * Alasan "tidak aktif" hanya dipakai kalau selisih aktivitas terakhir sampai
     * akun dinonaktifkan >= config('akun.batas_hari_tidak_aktif') (default 365 hari).
     */
    public function infoNonaktif(): array
    {
        $batas = (int) config('akun.batas_hari_tidak_aktif', 365);
        $terakhir = $this->attributes['last_active_at'] ?? $this->created_at;
        $dinonaktifkan = ! empty($this->attributes['dinonaktifkan_pada'])
            ? Carbon::parse($this->attributes['dinonaktifkan_pada'])
            : now();

        $hari = $terakhir ? (int) abs(Carbon::parse($terakhir)->diffInDays($dinonaktifkan)) : 0;
        $fmt = fn ($v) => $v ? Carbon::parse($v)->locale('id')->translatedFormat('d F Y') : null;

        return [
            'name' => $this->name,
            'email' => $this->email,
            'karena_tidak_aktif' => $hari >= $batas,
            'hari_tidak_aktif' => $hari,
            'tahun_tidak_aktif' => intdiv($hari, 365),
            'terakhir_aktif' => $fmt($terakhir),
            'dinonaktifkan_pada' => $fmt($dinonaktifkan),
        ];
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