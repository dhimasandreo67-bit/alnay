<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Rating;

class HomeController extends Controller
{
    public function index()
    {
        // =====================================================
        // OPSI 2 — DATA DINAMIS DARI DATABASE (AKTIF)
        // =====================================================

        $kategori = Kategori::orderBy('nama_kategori')
                ->get()
                ->map(fn ($k) => [
                    'id'            => $k->id,
                    'nama_kategori' => $k->nama_kategori,
                    'kode_kategori' => $k->kode_kategori,
                    'subjudul'      => null,
                    'foto'          => $k->foto ? asset('storage/' . $k->foto) : null,
                ])
                ->toArray();

        $produkTerbaru = Produk::latest()
            ->limit(4)
            ->get()
            ->map(fn ($p) => $this->produkKeArray($p))
            ->toArray();

        $produkTerpopuler = Produk::orderByDesc('jumlah_rating')
            ->orderByDesc('rating_rata_rata')
            ->limit(4)
            ->get()
            ->map(fn ($p) => $this->produkKeArray($p))
            ->toArray();

        $testimoni = Rating::with(['user', 'produk'])
            ->where('rating', '>=', 4)
            ->whereNotNull('ulasan')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($r) => [
                'nama'    => $r->user->name ?? 'User',
                'konteks' => $r->produk->nama ?? 'Produk',
                'teks'    => $r->ulasan,
                'bintang' => $r->rating,
            ])
            ->toArray();

        return view('customer.beranda', compact(
            'kategori', 'produkTerbaru', 'produkTerpopuler', 'testimoni'
        ));
    }

    /**
     * Ubah model Produk menjadi array (bentuk sama dengan dummy).
     */
    private function produkKeArray(Produk $p): array
    {
        return [
            'id'                   => $p->id,
            'kategori_id'          => $p->kategori_id,
            'sku'                  => $p->sku,
            'nama'                 => $p->nama,
            'deskripsi'            => $p->deskripsi,
            'foto_produk'          => $p->foto_produk ? 'storage/' . (is_array($p->foto_produk) ? ($p->foto_produk[0] ?? '') : $p->foto_produk) : null,
            'bahan'                => $p->bahan,
            'berat'                => $p->berat,
            'ukuran'               => $p->ukuran,
            'harga_jual'           => $p->harga_jual,
            'harga_sewa'           => $p->harga_sewa,
            'stok'                 => $p->stok,
            'status'               => $p->status,
            'dapat_dijual'         => $p->dapat_dijual,
            'denda_per_hari_telat' => $p->denda_per_hari_telat,
            'rating_rata_rata'     => $p->rating_rata_rata, // accessor
            'jumlah_rating'        => $p->jumlah_rating,    // accessor
        ];
    }
}