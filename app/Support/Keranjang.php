<?php

namespace App\Support;

use App\Models\Produk;

/**
 * Menyegarkan isi keranjang beli dari database: harga (sudah termasuk diskon),
 * harga asli, persen diskon, dan stok yang tersedia untuk ukuran/warna pilihan.
 * Supaya keranjang & checkout selalu sama dengan halaman detail produk dan admin.
 */
class Keranjang
{
    public static function segarkan(array $cart): array
    {
        $ids = collect($cart)->pluck('id')->filter()->unique()->values();
        $produkMap = Produk::whereIn('id', $ids)->get()->keyBy('id');

        foreach ($cart as $key => $item) {
            $p = $produkMap->get($item['id'] ?? null);
            if (! $p) {
                unset($cart[$key]);
                continue;
            }

            $sewa = ($item['mode'] ?? 'beli') === 'sewa';

            $cart[$key]['harga']      = $sewa ? $p->hargaSewaNet() : $p->hargaJualNet();
            $cart[$key]['harga_asli'] = (int) round((float) ($sewa ? $p->harga_sewa : $p->harga_jual));
            $cart[$key]['diskon']     = $sewa ? $p->diskonSewa() : $p->diskonJual();
            $cart[$key]['tersedia']   = $p->stokTersedia($sewa ? 'sewa' : 'jual', $item['ukuran'] ?? null, $item['warna'] ?? null);
        }

        return $cart;
    }
}