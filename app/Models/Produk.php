<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'kategori_id',
        'sku',
        'nama',
        'deskripsi',
        'foto_produk',
        'bahan',
        'motif',
        'warna',
        'berat',
        'ukuran',
        'harga_jual',
        'harga_sewa',
        'dp',
        'stok',
        'status',
        'dapat_dijual',
        'bisa_disewa',
        'denda_per_hari_telat',
        'diskon_persen',
        'rating_rata_rata',
        'jumlah_rating',
    ];

    protected $casts = [
        'foto_produk' => 'array',
        'warna' => 'array',       // Penting: mengubah JSON dari database kembali menjadi Array PHP
        'ukuran' => 'array',      // Penting: mengubah JSON dari database kembali menjadi Array PHP
        'bisa_disewa' => 'boolean',
        'dapat_dijual' => 'boolean',
        'berat' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'harga_sewa' => 'decimal:2',
        'dp' => 'decimal:2',
        'denda_per_hari_telat' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'rating_rata_rata' => 'decimal:2',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    // Method statis untuk opsi form (jika belum ada)
    public static function opsiStatus(): array
    {
        return [
            'tersedia' => 'Tersedia',
            'disewa' => 'Sedang Disewa',
            'dicuci' => 'Dalam Pencucian',
            'rusak' => 'Rusak / Perawatan',
        ];
    }

    public static function opsiUkuran(): array
    {
        return ['S', 'M', 'L', 'XL', 'XXL', 'All Size', 'Custom'];
    }

    public static function paletWarna(): array
    {
        return [
            'Emas Antik' => '#D4AF37',
            'Cokelat Tua' => '#4B3621',
            'Hitam Legam' => '#1A1A1A',
            'Krem Gading' => '#FFFFF0',
            'Merah Marun' => '#800000',
            'Hijau Lumut' => '#2E4F3F',
            'Biru Nila' => '#1C3F94',
            'Ungu Wijaya' => '#4B2E5A',
        ];
    }

    public function sampulUrl(): ?string
    {
        foreach ((array) ($this->foto_produk ?? []) as $path) {
            if ($url = $this->fotoUrlDariPath($path)) return $url;
        }
        return null;
    }

    public function fotoUrl(int $index): ?string
    {
        return $this->fotoUrlDariPath($this->foto_produk[$index] ?? null);
    }

    /** @return string[] */
    public function semuaFotoUrl(): array
    {
        $urls = [];
        foreach ((array) ($this->foto_produk ?? []) as $path) {
            if ($url = $this->fotoUrlDariPath($path)) $urls[] = $url;
        }
        return $urls;
    }

    protected function fotoUrlDariPath(?string $path): ?string
    {
        if (!$path) return null;
        // Data legacy: sudah berupa URL penuh di database
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . $path);
    }
}