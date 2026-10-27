<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Tampilkan halaman keranjang
     */
        public function index()
    {
        // Ambil data dari session (tanpa dummy)
        $cartItems = session('cart', []);

        // Hitung subtotal & total
        $subtotal = collect($cartItems)->sum(fn($item) => $item['harga'] * $item['qty']);
        $ongkir = $subtotal > 0 ? 15000 : 0; // Ongkir hanya jika ada barang
        $total = $subtotal + $ongkir;

        return view('customer.cart', compact('cartItems', 'subtotal', 'ongkir', 'total'));
    }


    public function add(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'qty' => 'required|integer|min:1',
            'mode' => 'required|in:sewa,beli',
            'ukuran' => 'nullable|string',
            'tanggal_ambil' => 'required_if:mode,sewa|nullable|date',
            'tanggal_kembali' => 'required_if:mode,sewa|nullable|date|after:tanggal_ambil',
        ]);

        $produk = \App\Models\Produk::findOrFail($request->produk_id);
        
        // Tentukan harga berdasarkan mode (sewa/beli)
        $harga = $request->mode === 'sewa' ? $produk->harga_sewa : $produk->harga_jual;
        
        // Buat ID unik untuk item keranjang (karena 1 produk bisa disewa & dibeli sekaligus)
        $cartKey = $produk->id . '-' . $request->mode . '-' . ($request->ukuran ?? 'all');

        $cart = session('cart', []);

        // Jika item sudah ada di keranjang, tambahkan qty-nya
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty'] += $request->qty;
        } else {
            // Jika belum ada, buat item baru
            $cart[$cartKey] = [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'harga' => $harga,
                'foto' => $produk->foto_produk,
                'qty' => $request->qty,
                'mode' => $request->mode,
                'ukuran' => $request->ukuran,
                'tanggal_ambil' => $request->tanggal_ambil,
                'tanggal_kembali' => $request->tanggal_kembali,
            ];
        }

        session(['cart' => $cart]);

        return redirect()->route('keranjang.index')->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Update quantity item
     */
    public function updateQuantity(Request $request, $id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1|max:10',
        ]);

        $cart = session('cart', []);
        
        if (isset($cart[$id])) {
            $cart[$id]['qty'] = $request->qty;
            session(['cart' => $cart]);
        }

        return redirect()->route('keranjang.index')->with('success', 'Quantity berhasil diupdate');
    }

    /**
     * Hapus satu item dari keranjang
     */
    public function remove($id)
    {
        $cart = session('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session(['cart' => $cart]);
        }

        return redirect()->route('keranjang.index')->with('success', 'Item berhasil dihapus dari keranjang');
    }

    /**
     * Hapus multiple items yang dipilih
     */
    public function removeSelected(Request $request)
    {
        $ids = $request->input('ids', []);
        $cart = session('cart', []);

        foreach ($ids as $id) {
            if (isset($cart[$id])) {
                unset($cart[$id]);
            }
        }

        session(['cart' => $cart]);

        return redirect()->route('keranjang.index')->with('success', count($ids) . ' item berhasil dihapus');
    }
}