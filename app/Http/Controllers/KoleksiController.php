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

        // 2. Query dasar produk
        $query = Produk::query()->with('kategori');

        // 3. Filter berdasarkan Kategori (multi-select pakai checkbox)
        if ($request->filled('kategori')) {
            $kategoris = (array) $request->kategori;
            $query->whereHas('kategori', function ($q) use ($kategoris) {
                $q->whereIn('slug', $kategoris);
            });
        }

        // 4. Filter Warna
        if ($request->filled('warna')) {
            $query->where('warna', $request->warna);
        }

        // 5. Filter berdasarkan Jenis Transaksi
        if ($request->filled('jenis')) {
            if ($request->jenis === 'sewa') {
                $query->where('harga_sewa', '>', 0);
            } elseif ($request->jenis === 'beli') {
                $query->where('dapat_dijual', true);
            }
        }

        // 6. Filter berdasarkan Rentang Harga
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

        // 7. Sorting (default: Terbaru)
        $sort = $request->get('sort', 'terbaru');
        match ($sort) {
            'termurah' => $query->orderBy('harga_jual', 'asc')->orderBy('harga_sewa', 'asc'),
            'termahal' => $query->orderBy('harga_jual', 'desc')->orderBy('harga_sewa', 'desc'),
            'populer'  => $query->orderByDesc('jumlah_rating'),
            default    => $query->latest(),
        };

        // 8. Pagination
        $produkList = $query->paginate(12)->withQueryString();

        return view('customer.koleksi', compact('produkList', 'kategoriList'));
    }
}