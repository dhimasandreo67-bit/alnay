<?php

namespace App\Support;

/**
 * Daftar terpusat menu/halaman di panel admin yang hak aksesnya bisa diatur
 * per akun admin lewat halaman "Kelola Admin".
 *
 * Dipakai oleh:
 * - App\Http\Middleware\EnsureAdminPermission (mengecek akses per route)
 * - App\Http\Controllers\Admin\AdminController (validasi & render checkbox)
 * - resources/views/admin/kelola-admin/index.blade.php
 *
 * 'key' pada setiap route HARUS sama dengan segmen pertama setelah 'admin.'
 * pada nama route di routes/admin.php, misal:
 *   'admin.kategori.index' -> key 'kategori'
 *   'admin.dashboard'      -> key 'dashboard'
 */
class AdminMenu
{
    public static function all(): array
    {
        return [
            'dashboard'    => ['label' => 'Dashboard',             'group' => 'Utama'],
            'kategori'     => ['label' => 'Kelola Kategori',       'group' => 'Master Data'],
            'produk'       => ['label' => 'Kelola Produk',         'group' => 'Master Data'],
            'pengguna'     => ['label' => 'Pengguna / Pelanggan',  'group' => 'Master Data'],
            'transaksi'    => ['label' => 'Transaksi',             'group' => 'Transaksi'],
            'sewa'         => ['label' => 'Sewa',                  'group' => 'Transaksi'],
            'jaminan'      => ['label' => 'Jaminan',               'group' => 'Transaksi'],
            'refund'       => ['label' => 'Refund',                'group' => 'Layanan Atelier'],
            'pengiriman'   => ['label' => 'Pengiriman',            'group' => 'Layanan Atelier'],
            'fitting'      => ['label' => 'Fitting',               'group' => 'Layanan Atelier'],
            'rating'       => ['label' => 'Rating',                'group' => 'Layanan Atelier'],
            'audit'        => ['label' => 'Audit Log',             'group' => 'Sistem'],
            'kelola-admin' => ['label' => 'Kelola Admin',          'group' => 'Sistem'],
        ];
    }

    /** Semua key menu yang valid, untuk validasi input. */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /** Menu dikelompokkan per grup, untuk dirender jadi checkbox per seksi. */
    public static function grouped(): array
    {
        $grouped = [];
        foreach (self::all() as $key => $item) {
            $grouped[$item['group']][$key] = $item['label'];
        }
        return $grouped;
    }

    public static function label(string $key): string
    {
        return self::all()[$key]['label'] ?? $key;
    }

    /**
     * Ambil menu key dari nama route saat ini.
     * 'admin.kategori.index' -> 'kategori', 'admin.dashboard' -> 'dashboard'.
     * Mengembalikan null kalau route bukan route panel admin (mis. login/logout).
     */
    public static function keyFromRouteName(?string $routeName): ?string
    {
        if (!$routeName || !str_starts_with($routeName, 'admin.')) {
            return null;
        }

        $rest = substr($routeName, strlen('admin.'));
        $segments = explode('.', $rest);

        return $segments[0] ?: null;
    }
}