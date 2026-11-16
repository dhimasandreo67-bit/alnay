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
        // Dikirim sebagai model Produk utuh (bukan array) supaya <x-product-card> bisa membaca
        // stok per ukuran/warna dan diskon lewat method model (stokUntuk, hargaJualNet, dst).
        $produkTerbaru = Produk::latest()
            ->limit(5)
            ->get();

        // 3. Produk Terpopuler (Best Seller)
        $produkTerpopuler = Produk::withCount(['detailTransaksi'])
            ->orderByDesc('detail_transaksi_count')
            ->orderByDesc('jumlah_rating')
            ->limit(5)
            ->get();

        // 4. Testimoni (Rating & Ulasan)
        $testimoni = Rating::with(['user', 'produk'])
            ->where('rating', '>=', 4)
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($r) => [
                'nama'    => $r->user->name ?? 'User',
                'konteks' => $r->produk->nama ?? 'Produk',
                // Jika ulasan kosong, beri teks default agar tidak terlihat aneh di beranda
                'teks'    => $r->ulasan ?: 'Pelanggan memberikan rating ' . $r->rating . ' bintang.',
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
}