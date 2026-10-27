<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    // Menyimpan ulasan
    public function store(Request $request)
    {
        $request->validate([
            'transaksi_id' => 'required|exists:transaksi,id',
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:500',
        ]);

        $transaksi = Transaksi::where('user_id', Auth::id())->findOrFail($request->transaksi_id);
        $produk_id = $transaksi->detail->first()->produk_id;

        // Cek apakah sudah pernah memberi rating
        $cek = Rating::where('transaksi_id', $transaksi->id)->first();
        if ($cek) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk pesanan ini.');
        }

        Rating::create([
            'user_id' => Auth::id(),
            'produk_id' => $produk_id,
            'transaksi_id' => $transaksi->id,
            'rating' => $request->rating,
            'ulasan' => $request->ulasan,
        ]);

        return back()->with('success', 'Terima kasih! Ulasan Anda sangat berharga bagi kami.');
    }
}