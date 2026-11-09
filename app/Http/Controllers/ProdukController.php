<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Support\KetersediaanSewa;

class ProdukController extends Controller
{
    public function show($sku)
    {
        $produk = Produk::with('kategori')->where('sku', $sku)->firstOrFail();

        // Ambil produk serupa (kategori sama, exclude dirinya)
        $serupa = Produk::where('kategori_id', $produk->kategori_id)
            ->where('id', '!=', $produk->id)
            ->take(4)
            ->get();

        // Kapasitas & jumlah unit terbooking per tanggal, per kombinasi ukuran/warna (online + kasir).
        $ketersediaan = $produk->bisa_disewa ? KetersediaanSewa::ringkasan($produk) : [];

        // Daftar tanggal yang sudah penuh per kombinasi (dipakai kalender di halaman detail)
        $tanggalPenuh = $produk->bisa_disewa ? KetersediaanSewa::tanggalPenuh($produk) : [];

        return view('customer.produk-detail', compact('produk', 'serupa', 'ketersediaan', 'tanggalPenuh'));
    }
}