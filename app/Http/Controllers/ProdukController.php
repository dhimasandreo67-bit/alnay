<?php

namespace App\Http\Controllers;

use App\Models\Produk;

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

        return view('customer.produk-detail', compact('produk', 'serupa'));
    }
}