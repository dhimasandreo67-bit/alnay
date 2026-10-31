<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use Carbon\Carbon;

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

        // --- LOGIKA MENCARI TANGGAL YANG SUDAH DIBOOKING ---
        $dibooking = [];

        // Ambil semua transaksi yang memiliki produk ini, statusnya aktif, dan memiliki data sewa
        $transaksiAktif = Transaksi::whereHas('detail', function ($query) use ($produk) {
                $query->where('produk_id', $produk->id);
            })
            ->whereIn('status_pembayaran', ['pending', 'dp', 'dp_dibayar', 'lunas', 'success', 'dibayar'])
            ->whereHas('sewa', function($q) {
                $q->whereIn('status', ['aktif', 'pending']); // Hanya sewa yang masih aktif
            })
            ->with('sewa')
            ->get();

        foreach ($transaksiAktif as $trx) {
            if ($trx->sewa) {
                $start = Carbon::parse($trx->sewa->tanggal_pinjam);
                $end = Carbon::parse($trx->sewa->tanggal_kembali_rencana);
                
                // Masukkan semua tanggal dalam rentang sewa ke array
                while ($start->lte($end)) {
                    $dibooking[] = $start->format('Y-m-d');
                    $start->addDay();
                }
            }
        }

        // Bersihkan duplikat dan urutkan
        $dibooking = array_values(array_unique($dibooking));
        sort($dibooking);

        return view('customer.produk-detail', compact('produk', 'serupa', 'dibooking'));
    }
}