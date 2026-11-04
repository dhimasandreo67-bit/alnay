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

        // Bersihkan session
        $validItems = [];
        foreach ($cartItems as $key => $item) {
            if (is_string($key) && is_array($item) && isset($item['id'], $item['harga'], $item['qty'])) {
                $validItems[$key] = $item;
            }
        }
        if (count($validItems) !== count($cartItems)) {
            session(['cart' => $validItems]);
        }
        $cartItems = $validItems;

        $subtotal = collect($cartItems)->sum(fn($item) => $item['harga'] * $item['qty']);
        
        // ==== AMBIL ONGKIR REAL-TIME DARI API (SAMA SEPERTI CHECKOUT) ====
        $ongkir = 0;
        $user = auth()->user();

        if ($subtotal > 0 && $user && $user->kota_id) {
            try {
                // Hitung berat total (asumsi berat default 1000 gram per item jika tidak ada)
                $beratTotal = collect($cartItems)->sum(fn($item) => (isset($item['berat']) ? $item['berat'] : 1000) * $item['qty']);
                
                $service = app(\App\Services\RajaOngkir::class);
                $hasil = $service->hitungOngkir($user->kota_id, $beratTotal, 'jne'); // Default JNE
                
                if (!empty($hasil)) {
                    $termurah = collect($hasil)->sortBy('cost')->first();
                    $ongkir = $termurah['cost'] ?? 0;
                }
            } catch (\Throwable $e) {
                \Log::warning('Ongkir error di keranjang: ' . $e->getMessage());
                $ongkir = 0; // Fallback jika API error
            }
        }
        // =================================================================

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
            'warna' => 'nullable|string',
            'tanggal_ambil' => 'required_if:mode,sewa|nullable|date',
            'tanggal_kembali' => 'required_if:mode,sewa|nullable|date|after:tanggal_ambil',
        ]);

        $produk = Produk::findOrFail($request->produk_id);
        $jenis = $request->mode === 'sewa' ? 'sewa' : 'jual';

        // Stok sesuai jenis (sewa/jual), per ukuran & warna, sama seperti yang tampil di detail produk
        $tersedia = $produk->stokTersedia($jenis, $request->ukuran, $request->warna);
        if ($tersedia <= 0) {
            return back()->withErrors(['stok' => 'Maaf, stok pilihan ini sedang habis.']);
        }
        if ($request->mode === 'beli' && $request->qty > $tersedia) {
            return back()->withErrors(['stok' => 'Jumlah melebihi stok yang tersedia (' . $tersedia . ').']);
        }

        // Harga sudah termasuk diskon
        $harga = $request->mode === 'sewa' ? $produk->hargaSewaNet() : $produk->hargaJualNet();

        $cartKey = $produk->id . '-' . $request->mode . '-' . ($request->ukuran ?? 'all') . '-' . ($request->warna ?? '');
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
                'warna' => $request->warna ?? '',
                'tanggal_ambil' => $request->tanggal_ambil,
                'tanggal_kembali' => $request->tanggal_kembali,
            ];
        }

        session(['cart' => $cart]);
        
        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
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