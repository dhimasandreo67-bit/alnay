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
        // Ambil data dari session
        $cartItems = session('cart', []);

        // Jika kosong, isi dengan data dummy untuk demo
        if (empty($cartItems)) {
            $cartItems = [
                1 => [
                    'id' => 1,
                    'nama' => 'Kain Batik Tulis Sutera Primissima Kencana Puspita (Sutra Asli)',
                    'harga' => 3850000,
                    'foto' => 'images/produk/batik-sutera.jpg',
                    'qty' => 1,
                ],
                2 => [
                    'id' => 2,
                    'nama' => 'Mahkota Siger Sunda Kencana Sepuh',
                    'harga' => 2450000,
                    'foto' => 'images/produk/mahkota-siger.jpg',
                    'qty' => 1,
                ],
            ];
            session(['cart' => $cartItems]);
        }

        // Hitung subtotal & total
        $subtotal = collect($cartItems)->sum(fn($item) => $item['harga'] * $item['qty']);
        $ongkir = 15000;
        $total = $subtotal + $ongkir;

        return view('customer.cart', compact('cartItems', 'subtotal', 'ongkir', 'total'));
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