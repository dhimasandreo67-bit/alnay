<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Sewa;
use App\Models\Pembayaran;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        // Ambil data keranjang dari session (sesuaikan dengan struktur session keranjang Anda)
        // Asumsi: session('cart') berisi array id_produk => ['qty' => x, 'harga' => y, 'nama' => z, 'foto' => w, 'is_sewa' => bool]
        $cartItems = session('cart', []);
        
        if (empty($cartItems)) {
            return redirect()->route('keranjang.index')->with('error', 'Keranjang Anda kosong.');
        }

        $total = array_reduce($cartItems, function($carry, $item) {
            return $carry + ($item['harga'] * $item['qty']);
        }, 0);

        return view('customer.checkout', compact('cartItems', 'total'));
    }

            public function store(Request $request)
    {
        $request->validate([
            'metode_penerimaan' => 'required|in:ambil,kirim',
            'metode_bayar' => 'required|in:qris,va,ewallet',
            'alamat' => 'required_if:metode_penerimaan,kirim|nullable|string',
        ]);

        $cartItems = session('cart', []);
        if (empty($cartItems)) {
            return redirect()->route('keranjang.index')->with('error', 'Keranjang kosong.');
        }

        $total = array_reduce($cartItems, function($carry, $item) {
            return $carry + ($item['harga'] * $item['qty']);
        }, 0);

        // 1. Buat Transaksi
        $transaksi = Transaksi::create([
            'kode_transaksi' => 'ALN-BLI-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => Auth::id(),
            'jenis' => 'beli',
            'kanal' => 'online',
            'tanggal' => now()->toDateString(),
            'total' => $total,
            'status_pembayaran' => 'pending',
            'catatan' => $request->metode_penerimaan === 'kirim' ? 'Kirim ke: ' . $request->alamat : 'Ambil di studio',
        ]);

        // 2. Simpan Detail Transaksi
                // 2. Simpan Detail Transaksi
        foreach ($cartItems as $id => $item) {
            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $item['id'],
                'jumlah' => $item['qty'],
                'harga_satuan' => $item['harga'],
            ]);
        }

        // 3. Buat Tagihan Pembayaran
        Pembayaran::create([
            'transaksi_id' => $transaksi->id,
            'jenis_bayar' => 'lunas',
            'jumlah' => $total,
            'metode' => $request->metode_bayar,
            'status_midtrans' => 'pending',
            'tanggal_bayar' => now(),
        ]);

        // 4. Hapus Keranjang
        session()->forget('cart');

        // 5. Arahkan ke Halaman Konfirmasi Pembayaran (Menggunakan halaman Sewa yg sudah ada)
        return redirect()->route('sewa.konfirmasi', $transaksi->id);
    }
}