<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KoleksiController extends Controller
{
    private const SORT_VALID = ['terbaru', 'populer', 'termurah', 'termahal'];

    public function index(Request $request)
    {
        // Produk habis TIDAK dicampur dengan produk tersedia: selalu dimuat terpisah dan
        // ditampilkan di bawah daftar lewat tombol "Lihat produk habis (jumlah)".

        $kategoriList = $this->daftarKategori();
        $warnaList = $this->daftarWarna();

        // Query dasar + semua filter (dipakai bersama oleh daftar tersedia & daftar habis)
        $dasar = Produk::query()->with('kategori');
        $this->terapkanFilter($dasar, $request);

        $produkList = (clone $dasar)
            ->sembunyikanHabis()
            ->paginate(12)
            ->withQueryString();

        $produkHabis = $this->ambilProdukHabis($dasar);

        return view('customer.koleksi', compact('produkList', 'produkHabis', 'kategoriList', 'warnaList'));
    }

    /**
     * /koleksi/kategori/{kode} -> dialihkan ke halaman koleksi dengan filter kategori aktif,
     * supaya semua filter (warna, harga, jenis, dll.) tetap bekerja dan kategori tidak hilang
     * saat filter diubah.
     */
    public function showKategori(Request $request, $kode)
    {
        $kategori = Kategori::where('kode_kategori', $kode)->firstOrFail();

        return redirect()->route('koleksi.index', array_merge(
            $request->query(),
            ['kategori' => [$kategori->slug]]
        ));
    }

    /**
     * Terapkan pencarian, kategori, warna, jenis, rentang harga, dan urutan ke query.
     */
    private function terapkanFilter($query, Request $request): void
    {
        // PENCARIAN & RELEVANSI
        if ($request->filled('search')) {
            $rawSearch = mb_substr(trim($request->string('search')->toString()), 0, 100);
            $terms = preg_split('/\s+/', $rawSearch, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            // Setiap kata harus cocok (AND)
            foreach (array_slice($terms, 0, 6) as $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';

                $query->where(function ($q) use ($like, $term) {
                    $q->where('nama', 'like', $like)
                      ->orWhere('sku', 'like', $like);

                    // Kata angka murni (mis. "2") hanya dicari di Nama & SKU.
                    if (!is_numeric($term)) {
                        $q->orWhere('deskripsi', 'like', $like)
                          ->orWhere('motif', 'like', $like)
                          ->orWhere('bahan', 'like', $like)
                          ->orWhere('warna', 'like', $like)
                          ->orWhere('warna_sewa', 'like', $like)
                          ->orWhereHas('kategori', fn ($k) => $k->where('nama_kategori', 'like', $like));
                    }
                });
            }

            // PRIORITAS (relevansi) — memakai parameter binding, bukan string interpolation
            if ($terms !== []) {
                $likeRaw = '%' . addcslashes($rawSearch, '%_\\') . '%';
                $likeTerm1 = '%' . addcslashes($terms[0], '%_\\') . '%';

                $query->orderByRaw(
                    'CASE WHEN nama LIKE ? THEN 0 WHEN sku LIKE ? THEN 1 WHEN nama LIKE ? THEN 2 ELSE 3 END ASC',
                    [$likeRaw, $likeRaw, $likeTerm1]
                );
            }
        }

        // Kategori (multi-select)
        if ($request->filled('kategori')) {
            $kategoris = array_values(array_filter((array) $request->input('kategori'), 'is_string'));
            if ($kategoris) {
                $query->whereHas('kategori', fn ($q) => $q->whereIn('slug', $kategoris));
            }
        }

        // Warna — kolom `warna` / `warna_sewa` berisi JSON array
        if ($request->filled('warna')) {
            $warna = $request->string('warna')->toString();
            $query->where(function ($q) use ($warna) {
                foreach (['warna', 'warna_sewa'] as $kolom) {
                    $q->orWhereRaw(
                        "CASE WHEN JSON_VALID(`{$kolom}`) THEN JSON_CONTAINS(`{$kolom}`, ?) ELSE `{$kolom}` = ? END",
                        [json_encode($warna), $warna]
                    );
                }
            });
        }

        // Jenis transaksi
        if ($request->filled('jenis')) {
            if ($request->input('jenis') === 'sewa') {
                $query->where('harga_sewa', '>', 0);
            } elseif ($request->input('jenis') === 'beli') {
                $query->where('dapat_dijual', true);
            }
        }

        // Rentang harga bebas
        $min = $this->angka($request->input('harga_min'));
        $max = $this->angka($request->input('harga_max'));

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        if ($min !== null || $max !== null) {
            $kolomHarga = match ($request->input('jenis')) {
                'sewa'  => ['harga_sewa'],
                'beli'  => ['harga_jual'],
                default => ['harga_jual', 'harga_sewa'],
            };

            $query->where(function ($q) use ($kolomHarga, $min, $max) {
                foreach ($kolomHarga as $kolom) {
                    $q->orWhere(function ($qq) use ($kolom, $min, $max) {
                        $qq->where($kolom, '>', 0);
                        if ($min !== null) {
                            $qq->where($kolom, '>=', $min);
                        }
                        if ($max !== null) {
                            $qq->where($kolom, '<=', $max);
                        }
                    });
                }
            });
        }

        // Sorting (whitelist)
        $sort = $request->input('sort', 'terbaru');
        if (!is_string($sort) || !in_array($sort, self::SORT_VALID, true)) {
            $sort = 'terbaru';
        }

        // Kolom harga yang diurutkan: harga 0/NULL dianggap "tidak ada" dan diletakkan paling bawah.
        // Nilainya dari match() dengan string tetap, jadi aman dari injeksi.
        $kolomUrut = match ($request->input('jenis')) {
            'sewa'  => 'NULLIF(harga_sewa, 0)',
            'beli'  => 'NULLIF(harga_jual, 0)',
            default => 'COALESCE(NULLIF(harga_jual, 0), NULLIF(harga_sewa, 0))',
        };

        match ($sort) {
            'termurah' => $query->orderByRaw("$kolomUrut IS NULL, $kolomUrut ASC"),
            'termahal' => $query->orderByRaw("$kolomUrut IS NULL, $kolomUrut DESC"),
            'populer'  => $query->orderByDesc('jumlah_rating'),
            default    => $query->latest(),
        };

        // Tie-breaker supaya urutan & paginasi stabil
        $query->orderByDesc('produk.id');
    }

    /**
     * Produk stok habis (sesuai filter aktif), dipaginasi terpisah dari daftar utama.
     */
    private function ambilProdukHabis($dasar)
    {
        return (clone $dasar)
            ->whereIn('produk.id', Produk::idHabis())
            ->paginate(8, ['*'], 'hal_habis')
            ->withQueryString();
    }

    /**
     * Kategori + jumlah produk tersedia. Di-cache singkat agar tidak query ulang tiap request.
     *
     * PENTING (Laravel 13): cache hanya boleh berisi data primitif (array/string/angka).
     * Objek seperti Collection/Model akan kembali sebagai __PHP_Incomplete_Class
     * karena opsi `serializable_classes` di config/cache.php.
     *
     * (Hapus cache saat produk/stok berubah: Cache::forget('koleksi.kategori.v2');)
     */
    private function daftarKategori()
    {
        $data = Cache::remember('koleksi.kategori.v2', 60, function () {
            $idHabis = Produk::idHabis();

            return Kategori::withCount([
                'produks' => fn ($q) => $q->whereNotIn('produk.id', $idHabis),
            ])->orderBy('nama_kategori')->get()
                ->map(fn ($k) => [
                    'id' => $k->id,
                    'slug' => $k->slug,
                    'nama_kategori' => $k->nama_kategori,
                    'produks_count' => $k->produks_count,
                ])
                ->all();
        });

        // Dibangun ulang jadi objek sederhana supaya view tetap bisa memakai $k->slug dst.
        return collect(is_array($data) ? $data : [])->map(fn ($row) => (object) $row);
    }

    /**
     * Daftar warna unik dari semua produk. Di-cache karena harus membaca seluruh tabel produk.
     * (Hapus cache saat produk berubah: Cache::forget('koleksi.warna.v2');)
     */
    private function daftarWarna()
    {
        $data = Cache::remember('koleksi.warna.v2', 600, function () {
            return Produk::query()
                ->get(['id', 'warna', 'warna_sewa'])
                ->flatMap(fn ($p) => array_merge(
                    $this->pecahWarna($p->getRawOriginal('warna')),
                    $this->pecahWarna($p->getRawOriginal('warna_sewa'))
                ))
                ->map(fn ($w) => trim($w))
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all(); // array string biasa, aman untuk cache
        });

        return collect(is_array($data) ? array_values(array_filter($data, 'is_string')) : []);
    }

    private function pecahWarna($mentah): array
    {
        if (!is_string($mentah) || trim($mentah) === '') {
            return [];
        }
        $decoded = json_decode($mentah, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, 'is_string'));
        }
        return is_string($decoded) ? [$decoded] : [$mentah];
    }

    private function angka($nilai): ?int
    {
        $digit = preg_replace('/\D/', '', (string) $nilai);
        return $digit === '' ? null : (int) substr($digit, 0, 12);
    }
}