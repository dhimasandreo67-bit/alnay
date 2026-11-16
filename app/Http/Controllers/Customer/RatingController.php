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

        // Satu ulasan per item pesanan per pelanggan. Tanpa ini, klik ganda pada tombol kirim,
        // refresh/back-forward browser, atau request yang terkirim dua kali akan membuat
        // dua baris rating, dan Rating::created memicu dua notifikasi "Ulasan baru" ke admin.
        $rating = Rating::firstOrCreate(
            [
                'detail_transaksi_id' => $detail->id,
                'user_id' => Auth::id(),
            ],
            [
                'produk_id' => $detail->produk_id,
                'rating' => $request->rating,
                'ulasan' => $request->ulasan,
                'tanggal' => now()->toDateString(),
            ]
        );

        $pesan = $rating->wasRecentlyCreated
            ? 'Terima kasih atas ulasan Anda!'
            : 'Anda sudah memberikan ulasan untuk produk ini.';

        return redirect()->route('akun.pesanan.detail', $detail->transaksi_id)
                 ->with('success', $pesan);
    }
}