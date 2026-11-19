<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Rating;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function create($detail_id)
    {
        $detail = $this->detailMilikSaya($detail_id);
        return view('customer.rating-create', compact('detail'));
    }

    public function store(Request $request, $detail_id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:500',
        ]);

        $detail = $this->detailMilikSaya($detail_id);

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

    /**
     * Hanya item dari pesanan milik pelanggan yang sedang login (404 kalau bukan miliknya).
     * Tanpa ini, siapa pun yang login bisa mengisi ulasan untuk pesanan orang lain.
     */
    private function detailMilikSaya($detail_id): DetailTransaksi
    {
        return DetailTransaksi::with('produk')
            ->whereIn('transaksi_id', Transaksi::where('user_id', Auth::id())->select('id'))
            ->findOrFail($detail_id);
    }
}