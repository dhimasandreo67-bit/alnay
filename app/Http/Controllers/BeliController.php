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
        $beliData['checkout'] = $validated;
        session(['beli_data' => $beliData]);

        return redirect()->route('beli.konfirmasi');
    }

    public function konfirmasi()
    {
        $beliData = session('beli_data');
        if (!$beliData) return redirect()->route('koleksi.index');

        $produk = Produk::findOrFail($beliData['produk_id']);
        $qty = $beliData['qty'] ?? 1;
        $varian = $beliData['varian'] ?? 'Emas Klasik';
        $subtotal = $produk->harga_jual * $qty;
        $ongkir = self::ONGKIR_DEFAULT;
        $total = $subtotal + $ongkir;

        return view('customer.beli-konfirmasi', compact(
            'produk', 'beliData', 'qty', 'varian', 'subtotal', 'ongkir', 'total'
        ));
    }
}