<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'bintang'   => ['nullable', 'integer', 'between:1,5'],
            'produk_id' => ['nullable', 'integer'],
            'komentar'  => ['nullable', 'in:ada,tanpa'],
            'urut'      => ['nullable', 'in:terbaru,terlama,tertinggi,terendah'],
            'dari'      => ['nullable', 'date'],
            'sampai'    => ['nullable', 'date'],
            'q'         => ['nullable', 'string', 'max:100'],
        ]);

        // Daftar ulasan: semua filter (termasuk bintang) + urutan
        $query = $this->terapkanFilter($request, true)->with(['user', 'produk']);

        switch ($request->input('urut', 'terbaru')) {
            case 'terlama':
                $query->oldest();
                break;
            case 'tertinggi':
                $query->orderByDesc('rating')->latest();
                break;
            case 'terendah':
                $query->orderBy('rating')->latest();
                break;
            default:
                $query->latest();
        }

        $ratings = $query->paginate(10)->withQueryString();

        // Statistik mengikuti filter (produk, pencarian, komentar, tanggal) kecuali bintang,
        // supaya chip bintang tetap menampilkan jumlah tiap bintang untuk hasil filter yang sama.
        $dasar = $this->terapkanFilter($request, false);

        $perBintang = (clone $dasar)
            ->selectRaw('rating, COUNT(*) as jml')
            ->groupBy('rating')
            ->pluck('jml', 'rating')
            ->map(fn ($n) => (int) $n)
            ->all();

        $totalUlasan = array_sum($perBintang);
        $rataRata    = (clone $dasar)->avg('rating') ?? 0;
        $bintang5    = $perBintang[5] ?? 0;

        // Hanya produk yang sudah punya ulasan
        $produkList = Produk::whereHas('rating')->orderBy('nama')->get(['id', 'nama', 'sku']);

        return view('admin.rating.index', compact(
            'ratings', 'rataRata', 'totalUlasan', 'bintang5', 'perBintang', 'produkList'
        ));
    }

    /** Hapus satu ulasan. Dipanggil via AJAX dari halaman Rating & Ulasan (balasan JSON). */
    public function destroy(Request $request, $id)
    {
        Rating::findOrFail($id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Ulasan berhasil dihapus.']);
        }

        return redirect()->route('admin.rating.index')->with('success', 'Ulasan berhasil dihapus.');
    }

    /** Filter bersama untuk daftar dan statistik. */
    private function terapkanFilter(Request $request, bool $denganBintang)
    {
        $query = Rating::query();

        if ($request->filled('produk_id')) {
            $query->where('produk_id', (int) $request->produk_id);
        }

        if ($denganBintang && $request->filled('bintang')) {
            $query->where('rating', (int) $request->bintang);
        }

        if ($request->filled('komentar')) {
            $request->komentar === 'ada'
                ? $query->whereNotNull('ulasan')->where('ulasan', '!=', '')
                : $query->where(fn ($w) => $w->whereNull('ulasan')->orWhere('ulasan', ''));
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        // Cari di isi ulasan, nama pelanggan, atau nama produk
        if ($request->filled('q')) {
            $kata = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($request->q)) . '%';
            $query->where(function ($w) use ($kata) {
                $w->where('ulasan', 'like', $kata)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $kata))
                    ->orWhereHas('produk', fn ($p) => $p->where('nama', 'like', $kata));
            });
        }

        return $query;
    }
}