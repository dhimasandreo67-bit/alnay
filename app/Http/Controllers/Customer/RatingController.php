<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function create($detail_id)
    {
        $detail = DetailTransaksi::with('produk')->findOrFail($detail_id);
        return view('customer.rating-create', compact('detail'));
    }

    public function store(Request $request, $detail_id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:500',
        ]);

        $detail = DetailTransaksi::findOrFail($detail_id);

        Rating::create([
            'detail_transaksi_id' => $detail->id,
            'produk_id' => $detail->produk_id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'ulasan' => $request->ulasan,
            'tanggal' => now()->toDateString(),
        ]);

        return redirect()->route('akun.pesanan.detail', $detail->transaksi_id)
                 ->with('success', 'Terima kasih atas ulasan Anda!');
    }
}