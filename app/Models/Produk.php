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

    /**
     * Total stok satu set (jual / sewa).
     *
     * Bila produk punya variasi ukuran, total SELALU dihitung dari variasi itu (stok per warna bila ada
     * rincian warna, selain itu stok ukuran) dan bukan dari kolom stok. Dengan begitu angka di Kelola
     * Produk, kasir, dan halaman pelanggan tidak bisa berbeda walau kolom stok belum sinkron.
     * Tanpa variasi ukuran, dipakai kolom stok.
     *
     * Untuk sewa ini adalah KAPASITAS (unit yang dimiliki); sisa pada tanggal tertentu
     * dihitung oleh Support\KetersediaanSewa.
     */
    public function stokUntuk(string $jenis): int
    {
        $adaVarian = false;
        $total = 0;

        foreach ($this->ukuranUntuk($jenis) as $r) {
            // Data lama berupa teks biasa / tanpa nama ukuran tidak punya angka stok
            if (! is_array($r) || trim((string) ($r['ukuran'] ?? '')) === '') {
                continue;
            }

            $adaVarian = true;
            $perWarna = array_filter(
                (array) ($r['warna'] ?? []),
                fn ($w) => is_array($w) && trim((string) ($w['nama'] ?? '')) !== ''
            );

            $total += $perWarna
                ? array_sum(array_map(fn ($w) => max(0, (int) ($w['stok'] ?? 0)), $perWarna))
                : max(0, (int) ($r['stok'] ?? 0));
        }

        return $adaVarian ? $total : (int) $this->{$this->kolomStok($jenis)['stok']};
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

    /**
     * Stok yang benar-benar tersedia untuk kombinasi ukuran (+ warna) pada satu jenis transaksi.
     * Mengikuti struktur yang disimpan form admin: ukuran[].warna[].stok.
     */
    public function stokTersedia(string $jenis, ?string $ukuran = null, ?string $warna = null): int
    {
        $baris = collect($this->ukuranUntuk($jenis));

        if ($baris->isEmpty()) {
            return max(0, $this->stokUntuk($jenis));
        }

        $u = $baris->first(fn ($r) => trim((string) (is_array($r) ? ($r['ukuran'] ?? '') : $r)) === trim((string) $ukuran));
        if (! $u) {
            return 0;
        }

        $perWarna = collect(is_array($u) ? ($u['warna'] ?? []) : []);
        if ($perWarna->isNotEmpty() && $warna) {
            $w = $perWarna->first(fn ($x) => ($x['nama'] ?? null) === $warna);

            return $w ? max(0, (int) ($w['stok'] ?? 0)) : 0;
        }

        return max(0, (int) (is_array($u) ? ($u['stok'] ?? 0) : 0));
    }

    /**
     * Kurangi stok setelah pembelian dibayar. Mengikuti struktur form admin:
     * ukuran[].warna[].stok; total ukuran = jumlah stok warna; total stok = jumlah semua ukuran.
     * Tanpa variasi (atau data lama tanpa ukuran) hanya total stok yang dikurangi.
     */
    public function kurangiStok(string $jenis, ?string $ukuran, ?string $warna, int $jumlah): void
    {
        $kol = $this->kolomStok($jenis);
        $baris = $this->{$kol['ukuran']};
        $baris = is_array($baris) ? $baris : [];
        $ukuran = trim((string) $ukuran);

        if ($baris && $ukuran !== '') {
            foreach ($baris as $i => $r) {
                if (! is_array($r) || trim((string) ($r['ukuran'] ?? '')) !== $ukuran) {
                    continue;
                }

                $perWarna = is_array($r['warna'] ?? null) ? $r['warna'] : [];
                if ($perWarna) {
                    $sisa = $jumlah;
                    foreach ($perWarna as $j => $w) {
                        // Warna dipilih: kurangi warna itu. Tanpa warna: kurangi berurutan dari yang ada stoknya.
                        if ($warna && ($w['nama'] ?? null) !== $warna) {
                            continue;
                        }
                        $ambil = $warna ? $sisa : min($sisa, (int) ($w['stok'] ?? 0));
                        $perWarna[$j]['stok'] = max(0, (int) ($w['stok'] ?? 0) - $ambil);
                        $sisa -= $ambil;
                        if ($sisa <= 0) {
                            break;
                        }
                    }
                    $r['warna'] = $perWarna;
                    $r['stok'] = array_sum(array_map(fn ($w) => (int) ($w['stok'] ?? 0), $perWarna));
                } else {
                    $r['stok'] = max(0, (int) ($r['stok'] ?? 0) - $jumlah);
                }

                $baris[$i] = $r;
                $this->{$kol['ukuran']} = $baris;
                $this->{$kol['stok']} = array_sum(array_map(fn ($x) => is_array($x) ? (int) ($x['stok'] ?? 0) : 0, $baris));
                $this->save();

                return;
            }
        }

        $this->{$kol['stok']} = max(0, (int) $this->{$kol['stok']} - $jumlah);
        $this->save();
    }

    /** Kebalikan kurangiStok(): tambahkan stok ke ukuran/warna tertentu. */
    public function tambahStok(string $jenis, ?string $ukuran, ?string $warna, int $jumlah): void
    {
        $kol = $this->kolomStok($jenis);
        $baris = $this->{$kol['ukuran']};
        $baris = is_array($baris) ? $baris : [];
        $ukuran = trim((string) $ukuran);

        if ($baris && $ukuran !== '') {
            foreach ($baris as $i => $r) {
                if (! is_array($r) || trim((string) ($r['ukuran'] ?? '')) !== $ukuran) {
                    continue;
                }

                $perWarna = is_array($r['warna'] ?? null) ? $r['warna'] : [];
                if ($perWarna) {
                    $sasaran = null;
                    foreach ($perWarna as $j => $w) {
                        if ($warna && ($w['nama'] ?? null) === $warna) {
                            $sasaran = $j;
                            break;
                        }
                    }
                    // Warna yang disewa tidak ada di rincian stok ukuran ini: dulu tidak ada stok warna
                    // yang terpotong, jadi tidak ada yang perlu dikembalikan.
                    if ($sasaran === null && $warna) {
                        return;
                    }
                    $sasaran ??= array_key_first($perWarna);
                    $perWarna[$sasaran]['stok'] = (int) ($perWarna[$sasaran]['stok'] ?? 0) + $jumlah;
                    $r['warna'] = $perWarna;
                    $r['stok'] = array_sum(array_map(fn ($w) => (int) ($w['stok'] ?? 0), $perWarna));
                } else {
                    $r['stok'] = (int) ($r['stok'] ?? 0) + $jumlah;
                }

                $baris[$i] = $r;
                $this->{$kol['ukuran']} = $baris;
                $this->{$kol['stok']} = array_sum(array_map(fn ($x) => is_array($x) ? (int) ($x['stok'] ?? 0) : 0, $baris));
                $this->save();

                return;
            }
        }

        $this->{$kol['stok']} = (int) $this->{$kol['stok']} + $jumlah;
        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Harga & diskon (rumus sama dengan form dan index admin: harga * (1 - diskon/100))
    |--------------------------------------------------------------------------
    */
    public function diskonSewa(): float
    {
        return max(0, min(100, (float) ($this->diskon_sewa_persen ?? 0)));
    }

    public function diskonJual(): float
    {
        return max(0, min(100, (float) ($this->diskon_jual_persen ?? 0)));
    }

    public function hargaSewaNet(): int
    {
        return (int) round((float) $this->harga_sewa * (1 - $this->diskonSewa() / 100));
    }

    public function hargaJualNet(): int
    {
        return (int) round((float) $this->harga_jual * (1 - $this->diskonJual() / 100));
    }

    /** Tarif per hari tambahan setelah hari pertama (tarif perpanjangan; cadangan: denda per hari untuk data lama). */
    public function tarifTambahanSewa(): int
    {
        $perpanjangan = (float) $this->harga_perpanjangan_per_hari;

        return (int) round($perpanjangan > 0 ? $perpanjangan : (float) $this->denda_per_hari_telat);
    }

    public function totalSewa(int $durasi): int
    {
        return $durasi < 1 ? 0 : $this->hargaSewaNet() + ($durasi - 1) * $this->tarifTambahanSewa();
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