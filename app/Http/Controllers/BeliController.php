<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class BeliController extends Controller
{
    // Ongkir dummy sementara
    private const ONGKIR_DEFAULT = 45000;

    public function checkout(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('masuk')
                ->with('error', 'Silakan masuk terlebih dahulu untuk melanjutkan pembelian.');
        }

        $user = auth()->user();

        $dataKurang = [];
        if (empty($user->no_hp)) $dataKurang[] = 'Nomor WhatsApp';
        if (empty($user->alamat)) $dataKurang[] = 'Alamat Lengkap';

        if (!empty($dataKurang)) {
            return redirect()->route('akun.profil.edit')
                ->with('error', 'Lengkapi data profil Anda terlebih dahulu: ' . implode(', ', $dataKurang) . '.');
        }

        $beliData = session('beli_data');
        if (!$beliData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data pembelian tidak ditemukan. Silakan pilih produk kembali.']);
        }

        $produk = Produk::with('kategori')->findOrFail($beliData['produk_id']);
        $qty = $beliData['qty'] ?? 1;
        $varian = $beliData['varian'] ?? 'Emas Klasik';
        $subtotal = $produk->harga_jual * $qty;
        $ongkir = self::ONGKIR_DEFAULT;
        $total = $subtotal + $ongkir;

        return view('customer.beli-checkout', compact(
            'produk', 'beliData', 'qty', 'varian', 'subtotal', 'ongkir', 'total'
        ));
    }

    public function simpanSession(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'varian' => ['nullable', 'string'],
        ]);

        session(['beli_data' => $validated]);

        return redirect()->route('beli.checkout');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_penerima' => ['required', 'string', 'max:100'],
            'no_wa' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
            'metode_bayar' => ['required', 'in:qris,va,ewallet'],
        ]);

        $beliData = session('beli_data');
        if (!$beliData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data pembelian tidak ditemukan.']);
        }

        $produk = \App\Models\Produk::findOrFail($beliData['produk_id']);
        $qty = $beliData['qty'] ?? 1;
        $subtotal = $produk->harga_jual * $qty;
        $ongkir = self::ONGKIR_DEFAULT;
        $total = $subtotal + $ongkir;

        // Simpan transaksi
        $transaksi = \App\Models\Transaksi::create([
            'kode_transaksi' => 'ALN-BL-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => auth()->id(),
            'jenis' => 'beli',
            'kanal' => 'online',
            'tanggal' => now()->toDateString(),
            'total' => $total,
            'status_pembayaran' => 'pending', // menunggu bayar; jadi 'lunas' hanya lewat Midtrans
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // Simpan detail transaksi
        \App\Models\DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => $produk->id,
            'jumlah' => $qty,
            'harga_satuan' => $produk->harga_jual,
        ]);

        // Simpan pembayaran
        \App\Models\Pembayaran::create([
            'transaksi_id' => $transaksi->id,
            'jenis_bayar' => 'lunas',
            'jumlah' => $total,
            'metode' => $validated['metode_bayar'],
            'status_midtrans' => 'pending',
            'tanggal_bayar' => now(),
        ]);

        // Simpan pengiriman
        \App\Models\Pengiriman::create([
            'transaksi_id' => $transaksi->id,
            'provinsi_tujuan' => auth()->user()->provinsi ?? '-',
            'kota_tujuan' => auth()->user()->kota_kabupaten ?? '-',
            'kecamatan_tujuan' => auth()->user()->kecamatan ?? '-',
            'kode_pos_tujuan' => auth()->user()->kode_pos ?? null,
            'alamat_tujuan' => $validated['alamat'],
            'status_kirim' => 'menunggu',
        ]);

        session()->forget('beli_data');

        return redirect()->route('beli.konfirmasi', $transaksi->id)
            ->with('success', 'Pesanan dibuat. Silakan selesaikan pembayaran QRIS.');
    }

    public function konfirmasi($id)
    {
        $transaksi = \App\Models\Transaksi::with(['detail.produk', 'pembayaran', 'pengiriman'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $produk = $transaksi->detail->first()->produk;
        $qty = $transaksi->detail->first()->jumlah;
        $varian = 'Emas Klasik';
        $subtotal = $transaksi->detail->first()->harga_satuan * $qty;
        $ongkir = $transaksi->pengiriman->biaya_kirim ?? self::ONGKIR_DEFAULT;
        $total = $transaksi->total;

        return view('customer.beli-konfirmasi', compact(
            'transaksi', 'produk', 'qty', 'varian', 'subtotal', 'ongkir', 'total'
        ));
    }
}