<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Rating;

class HomeController extends Controller
{
    public function index()
    {
        $kategori = Kategori::orderBy('id')->get();

        $produkTerbaru = Produk::latest()->take(4)->get();

        $produkTerpopuler = Produk::orderByDesc('jumlah_rating')
                                   ->take(4)
                                   ->get();

        $testimoni = Rating::with(['user', 'produk'])
                           ->where('rating', '>=', 4)
                           ->latest()
                           ->take(3)
                           ->get();

        return view('customer.beranda', compact(
            'kategori', 'produkTerbaru', 'produkTerpopuler', 'testimoni'
        ));
    }
}