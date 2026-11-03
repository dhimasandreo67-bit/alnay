<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;

class KoleksiController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil semua kategori untuk sidebar (dengan jumlah produk)
        $kategoriList = Kategori::withCount('produks')->orderBy('nama_kategori')->get();

        // 2. Ambil daftar WARNA DINAMIS dari database (hanya warna yang ada produknya)
                // 2. Ambil daftar WARNA DINAMIS dari database
        // Kita ambil semua warna, lalu pecah jika bentuknya array/JSON
        $warnaList = Produk::pluck('warna')
            ->map(function ($warna) {
                // Jika warna berupa string JSON (misal: '["Hitam","Merah"]'), decode dulu
                if (is_string($warna) && str_starts_with(trim($warna), '[')) {
                    $decoded = json_decode($warna, true);
                    return is_array($decoded) ? $decoded : [$warna];
                }
                return [$warna];
            })
            ->flatten() // Ratakan array agar jadi satu list panjang
            ->filter(fn($w) => !empty($w) && is_string($w)) // Buang yang kosong/bukan string
            ->unique() // Hilangkan duplikat
            ->sort()
            ->values();

        // 3. Query dasar produk
        $query = Produk::query()->with('kategori');

        // 4. LOGIC PENCARIAN (SEARCH)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // 5. Filter berdasarkan Kategori (multi-select pakai checkbox)
        if ($request->filled('kategori')) {
            $kategoris = (array) $request->kategori;
            $query->whereHas('kategori', function ($q) use ($kategoris) {
                $q->whereIn('slug', $kategoris);
            });
        }

        // 6. Filter Warna
        if ($request->filled('warna')) {
            $query->where('warna', $request->warna);
        }

        // 7. Filter berdasarkan Jenis Transaksi
        if ($request->filled('jenis')) {
            if ($request->jenis === 'sewa') {
                $query->where('harga_sewa', '>', 0);
            } elseif ($request->jenis === 'beli') {
                $query->where('dapat_dijual', true);
            }
        }

        // 8. Filter berdasarkan Rentang Harga
        if ($request->filled('harga')) {
            switch ($request->harga) {
                case '0-1000000':
                    $query->where(function($q) {
                        $q->where('harga_jual', '<', 1000000)
                          ->orWhere('harga_sewa', '<', 1000000);
                    });
                    break;
                case '1000000-3000000':
                    $query->where(function($q) {
                        $q->whereBetween('harga_jual', [1000000, 3000000])
                          ->orWhereBetween('harga_sewa', [1000000, 3000000]);
                    });
                    break;
                case '3000000-5000000':
                    $query->where(function($q) {
                        $q->whereBetween('harga_jual', [3000000, 5000000])
                          ->orWhereBetween('harga_sewa', [3000000, 5000000]);
                    });
                    break;
                case '5000000+':
                    $query->where(function($q) {
                        $q->where('harga_jual', '>', 5000000)
                          ->orWhere('harga_sewa', '>', 5000000);
                    });
                    break;
            }
        }

        // 9. Sorting (default: Terbaru)
        $sort = $request->get('sort', 'terbaru');
        match ($sort) {
            'termurah' => $query->orderBy('harga_jual', 'asc')->orderBy('harga_sewa', 'asc'),
            'termahal' => $query->orderBy('harga_jual', 'desc')->orderBy('harga_sewa', 'desc'),
            'populer'  => $query->orderByDesc('jumlah_rating'),
            default    => $query->latest(),
        };

        // 10. Pagination (withQueryString WAJIB agar search & filter tidak hilang saat pindah halaman)
        $produkList = $query->paginate(12)->withQueryString();

        // 11. Kirim semua variabel ke view (termasuk $warnaList)
        return view('customer.koleksi', compact('produkList', 'kategoriList', 'warnaList'));
    }

    public function showKategori($kode)
    {
        // 1. Cari kategori berdasarkan kode_kategori (misal: JSB, GWN)
        $kategori = Kategori::where('kode_kategori', $kode)->firstOrFail();

        // 2. Ambil semua kategori untuk sidebar
        $kategoriList = Kategori::withCount('produks')->orderBy('nama_kategori')->get();

        // 3. Ambil produk dari kategori tersebut
        $produkList = Produk::with('kategori')
            ->where('kategori_id', $kategori->id)
            ->latest()
            ->paginate(12);

        return view('customer.koleksi', compact('produkList', 'kategoriList', 'kategori'));
    }
}