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
        'warna_sewa',
        'ukuran_sewa',
        'harga_jual',
        'harga_sewa',
        'dp',
        'stok',
        'stok_sewa',
        'status',
        'dapat_dijual',
        'bisa_disewa',
        'denda_per_hari_telat',
        'harga_perpanjangan_per_hari',
        'diskon_persen',
        'diskon_sewa_persen',
        'diskon_jual_persen',
        'rating_rata_rata',
        'jumlah_rating',
    ];

    protected $casts = [
        'foto_produk' => 'array',
        'warna' => 'array',       // Penting: mengubah JSON dari database kembali menjadi Array PHP
        'ukuran' => 'array',      // Penting: mengubah JSON dari database kembali menjadi Array PHP
        'warna_sewa' => 'array',
        'ukuran_sewa' => 'array',
        'bisa_disewa' => 'boolean',
        'dapat_dijual' => 'boolean',
        'berat' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'harga_sewa' => 'decimal:2',
        'dp' => 'decimal:2',
        'denda_per_hari_telat' => 'decimal:2',
        'harga_perpanjangan_per_hari' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'diskon_sewa_persen' => 'decimal:2',
        'diskon_jual_persen' => 'decimal:2',
        'rating_rata_rata' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Stok terpisah antara penjualan dan penyewaan
    |--------------------------------------------------------------------------
    | - Produk yang hanya dijual ATAU hanya disewa: tetap memakai kolom lama
    |   (stok, ukuran, warna).
    | - Produk yang bisa dijual DAN disewa: kolom lama = set untuk penjualan,
    |   kolom *_sewa (stok_sewa, ukuran_sewa, warna_sewa) = set untuk penyewaan.
    */
    public function pisahStok(): bool
    {
        return (bool) $this->bisa_disewa && (bool) $this->dapat_dijual;
    }

    /**
     * Nama kolom yang menyimpan stok untuk jenis transaksi ('sewa' atau selain itu = jual).
     *
     * @return array{stok: string, ukuran: string, warna: string}
     */
    public function kolomStok(string $jenis): array
    {
        if ($jenis === 'sewa' && $this->pisahStok()) {
            return ['stok' => 'stok_sewa', 'ukuran' => 'ukuran_sewa', 'warna' => 'warna_sewa'];
        }

        return ['stok' => 'stok', 'ukuran' => 'ukuran', 'warna' => 'warna'];
    }

    public function stokUntuk(string $jenis): int
    {
        return (int) $this->{$this->kolomStok($jenis)['stok']};
    }

    public function ukuranUntuk(string $jenis): array
    {
        $nilai = $this->{$this->kolomStok($jenis)['ukuran']};

        return is_array($nilai) ? $nilai : [];
    }

    public function warnaUntuk(string $jenis): array
    {
        $nilai = $this->{$this->kolomStok($jenis)['warna']};

        return is_array($nilai) ? $nilai : [];
    }

    /** Stok paling sedikit di antara set yang berlaku (dipakai untuk peringatan stok menipis). */
    public function stokTerendah(): int
    {
        return $this->pisahStok()
            ? min($this->stokUntuk('jual'), $this->stokUntuk('sewa'))
            : (int) $this->stok;
    }

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
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'images/')) {      // ← tambahkan ini
            return asset($path);
        }
        return asset('storage/' . $path);
    }

    // Relasi ke tabel rating
    public function rating()
    {
        return $this->hasMany(Rating::class, 'produk_id');
    }

    // Accessor: Rata-rata rating
    public function getRatingRataRataAttribute()
    {
        return $this->rating()->avg('rating') ?? 0;
    }

    // Accessor: Jumlah rating
    public function getJumlahRatingAttribute()
    {
        return $this->rating()->count();
    }
    // Relasi untuk menghitung produk terlaris (Best Seller)
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'produk_id');
    }
}
