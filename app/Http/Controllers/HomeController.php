<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Rating;
use App\Models\Transaksi;   // Tambahan untuk statistik transaksi
use App\Models\Pengiriman;  // Tambahan untuk statistik provinsi

class HomeController extends Controller
{
    public function index()
    {
        // =====================================================
        // DATA DINAMIS DARI DATABASE
        // =====================================================

        // 1. Data Kategori
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

        // 2. Produk Terbaru
        $produkTerbaru = Produk::latest()
            ->limit(5)
            ->get()
            ->map(fn ($p) => $this->produkKeArray($p))
            ->toArray();

        // 3. Produk Terpopuler (Best Seller)
        $produkTerpopuler = Produk::withCount(['detailTransaksi'])
            ->orderByDesc('detail_transaksi_count')
            ->orderByDesc('jumlah_rating')
            ->limit(5)
            ->get()
            ->map(fn ($p) => $this->produkKeArray($p))
            ->toArray();

        // 4. Testimoni (Rating & Ulasan)
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
                'foto'    => $r->produk ? $r->produk->sampulUrl() : null,
                'foto_user' => $r->user->foto_profil ?? null, 
            ])
            ->toArray();

        // =====================================================
        // STATISTIK DINAMIS (PENGGANTI DATA DUMMY)
        // =====================================================
        
        // Menghitung total ulasan & rata-rata bintang
        $totalRating = Rating::count();
        $rataRata = $totalRating > 0 ? Rating::avg('rating') : 0;

        // Menghitung total pesanan sukses (sesuaikan 'lunas' dengan nama status di DB Anda)
        $totalTransaksi = Transaksi::whereIn('status_pembayaran', ['lunas', 'success'])->count();

        // Menghitung total provinsi terjangkau dari tabel pengiriman
        $totalProvinsi = Pengiriman::distinct('provinsi_tujuan')->count('provinsi_tujuan');


        // =====================================================
        // KIRIM KE VIEW
        // =====================================================
        return view('customer.beranda', compact(
            'kategori', 
            'produkTerbaru', 
            'produkTerpopuler', 
            'testimoni',
            'totalRating',
            'rataRata',
            'totalTransaksi',
            'totalProvinsi'
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
            'bisa_disewa'          => $p->bisa_disewa, 
            'sampul_url'           => $p->sampulUrl(),
            'denda_per_hari_telat' => $p->denda_per_hari_telat,
            'rating_rata_rata'     => $p->rating_rata_rata, // accessor
            'jumlah_rating'        => $p->jumlah_rating,    // accessor
        ];
    }
}