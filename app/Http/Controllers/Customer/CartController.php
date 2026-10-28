<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = session('cart', []);

        // Bersihkan session dari data yang tidak valid (Array kosong, bukan string, dll)
        $validItems = [];
        foreach ($cartItems as $key => $item) {
            if (is_string($key) && is_array($item) && isset($item['id'], $item['harga'], $item['qty'])) {
                $validItems[$key] = $item;
            }
        }
        
        // Jika ada data rusak yang dibersihkan, simpan kembali session yang sudah bersih
        if (count($validItems) !== count($cartItems)) {
            session(['cart' => $validItems]);
        }
        $cartItems = $validItems;

        $subtotal = collect($cartItems)->sum(fn($item) => $item['harga'] * $item['qty']);
        $ongkir = $subtotal > 0 ? 15000 : 0;
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

        $produk = Produk::findOrFail($request->produk_id);
        $harga = $request->mode === 'sewa' ? $produk->harga_sewa : $produk->harga_jual;
        
        // Kunci unik: ID produk - Mode - Ukuran
        $cartKey = $produk->id . '-' . $request->mode . '-' . ($request->ukuran ?? 'all');
        $cart = session('cart', []);

        if (isset($cart[$cartKey]) && is_array($cart[$cartKey])) {
            $cart[$cartKey]['qty'] += $request->qty;
        } else {
            $cart[$cartKey] = [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'harga' => (int) $harga,
                'foto' => $produk->foto_produk,
                'qty' => (int) $request->qty,
                'mode' => $request->mode,
                'ukuran' => $request->ukuran ?? '',
                'tanggal_ambil' => $request->tanggal_ambil,
                'tanggal_kembali' => $request->tanggal_kembali,
            ];
        }

        session(['cart' => $cart]);
        return redirect()->route('keranjang.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function updateQuantity(Request $request, $id)
    {
        $request->validate(['qty' => 'required|integer|min:1|max:10']);
        $cart = session('cart', []);
        
        if (isset($cart[$id]) && is_array($cart[$id])) {
            $cart[$id]['qty'] = (int) $request->qty;
            session(['cart' => $cart]);
        }

        return redirect()->route('keranjang.index')->with('success', 'Quantity berhasil diupdate');
    }

    public function remove($id)
    {
        $cart = session('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session(['cart' => $cart]);
        }
        return redirect()->route('keranjang.index')->with('success', 'Item berhasil dihapus');
    }

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
        return redirect()->route('keranjang.index')->with('success', count($ids) . ' item dihapus');
    }
}