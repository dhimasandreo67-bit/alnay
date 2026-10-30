<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| TestimoniController — Halaman /testimoni
|--------------------------------------------------------------------------
|
| Sumber data: tabel `rating` (ulasan pelanggan yang ada teksnya).
| Kolom: produk_id, user_id, rating (1–5), ulasan, tanggal.
| Filter bintang lewat query string: /testimoni?bintang=5
*/

class TestimoniController extends Controller
{
    public function index(Request $request)
    {
        $bintang = $request->integer('bintang');
        $bintang = ($bintang >= 1 && $bintang <= 5) ? $bintang : null;

        $ulasan = $ulasan = Rating::with(['user:id,name,foto_profil', 'produk:id,sku,nama,foto_produk'])
            ->whereNotNull('ulasan')
            ->when($bintang, fn ($q) => $q->where('rating', $bintang))
            ->latest('tanggal')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        // Ringkasan memakai semua rating (tidak ikut terfilter)
        $totalRating = Rating::count();
        $rataRata    = $totalRating > 0 ? round((float) Rating::avg('rating'), 1) : 0;

        // [bintang => jumlah], contoh: [5 => 12, 4 => 3, ...]
        $sebaran = Rating::selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->all();

        return view('customer.testimoni', compact(
            'ulasan', 'bintang', 'totalRating', 'rataRata', 'sebaran'
        ));
    }
}