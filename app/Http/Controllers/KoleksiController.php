<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;

class KoleksiController extends Controller
{
    public function index(Request $request)
    {
        // 1. Kategori untuk sidebar (dengan jumlah produk)
        $kategoriList = Kategori::withCount('produks')->orderBy('nama_kategori')->get();

        // 2. Daftar warna dinamis (warna jual + warna sewa)
        $warnaList = $this->daftarWarna();

        // 3. Query dasar
        $query = Produk::query()->with('kategori');

        // 4. PENCARIAN — tiap kata harus cocok (AND), tiap kata dicari di beberapa kolom (OR)
        if ($request->filled('search')) {
            $terms = preg_split('/\s+/', trim($request->string('search')->toString()), -1, PREG_SPLIT_NO_EMPTY);

            foreach (array_slice($terms, 0, 6) as $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';

                $query->where(function ($q) use ($like) {
                    $q->where('nama', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('deskripsi', 'like', $like)
                        ->orWhere('motif', 'like', $like)
                        ->orWhere('bahan', 'like', $like)
                        ->orWhere('warna', 'like', $like)
                        ->orWhere('warna_sewa', 'like', $like)
                        ->orWhereHas('kategori', fn ($k) => $k->where('nama_kategori', 'like', $like));
                });
            }
        }

        // 5. Kategori (multi-select)
        if ($request->filled('kategori')) {
            $kategoris = (array) $request->kategori;
            $query->whereHas('kategori', fn ($q) => $q->whereIn('slug', $kategoris));
        }

        // 6. Warna — kolom `warna` / `warna_sewa` berisi JSON array (mis. ["Hitam Legam","Merah Marun"]),
        //    jadi harus dicek "mengandung", bukan sama persis. Data non-JSON (string biasa) tetap didukung.
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

        // 7. Jenis transaksi
        if ($request->filled('jenis')) {
            if ($request->jenis === 'sewa') {
                $query->where('harga_sewa', '>', 0);
            } elseif ($request->jenis === 'beli') {
                $query->where('dapat_dijual', true);
            }
        }

        // 8. Rentang harga bebas (harga_min / harga_max), diisi sendiri oleh pelanggan
        $min = $this->angka($request->input('harga_min'));
        $max = $this->angka($request->input('harga_max'));

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        if ($min !== null || $max !== null) {
            // Kalau sedang memilih jenis tertentu, cocokkan ke harga jenis itu saja
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

        // 9. Sorting (default: terbaru)
        $sort = $request->get('sort', 'terbaru');
        match ($sort) {
            'termurah' => $query->orderBy('harga_jual', 'asc')->orderBy('harga_sewa', 'asc'),
            'termahal' => $query->orderBy('harga_jual', 'desc')->orderBy('harga_sewa', 'desc'),
            'populer'  => $query->orderByDesc('jumlah_rating'),
            default    => $query->latest(),
        };

        // 10. Pagination
        $produkList = $query->paginate(12)->withQueryString();

        return view('customer.koleksi', compact('produkList', 'kategoriList', 'warnaList'));
    }

    public function showKategori($kode)
    {
        $kategori = Kategori::where('kode_kategori', $kode)->firstOrFail();

        $kategoriList = Kategori::withCount('produks')->orderBy('nama_kategori')->get();

        // View koleksi membutuhkan $warnaList (sebelumnya tidak dikirim di sini)
        $warnaList = $this->daftarWarna();

        $produkList = Produk::with('kategori')
            ->where('kategori_id', $kategori->id)
            ->latest()
            ->paginate(12);

        return view('customer.koleksi', compact('produkList', 'kategoriList', 'kategori', 'warnaList'));
    }

    /** Kumpulkan semua nama warna unik dari kolom warna & warna_sewa (JSON array atau string biasa). */
    private function daftarWarna()
    {
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
            ->values();
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

    /** "1.500.000" / "Rp 1500000" -> 1500000 ; kosong -> null */
    private function angka($nilai): ?int
    {
        $digit = preg_replace('/\D/', '', (string) $nilai);

        return $digit === '' ? null : (int) substr($digit, 0, 12);
    }
}