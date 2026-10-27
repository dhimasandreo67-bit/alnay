<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $query = Rating::with(['user', 'produk'])->latest();

        // Filter Rating Bintang
        if ($request->filled('bintang') && $request->bintang !== '') {
            $query->where('rating', $request->bintang);
        }

        $ratings = $query->paginate(10)->withQueryString();

        // Statistik
        $rataRata = Rating::avg('rating') ?? 0;
        $totalUlasan = Rating::count();
        $bintang5 = Rating::where('rating', 5)->count();

        return view('admin.rating.index', compact('ratings', 'rataRata', 'totalUlasan', 'bintang5'));
    }
}