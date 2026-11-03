<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkir;
use Illuminate\Http\Request;

class OngkirController extends Controller
{
    public function tujuan(Request $request, RajaOngkir $ro)
    {
        try {
            return response()->json($ro->cariTujuan((string) $request->query('q', '')));
        } catch (\Throwable $e) {
            \Log::warning('RajaOngkir tujuan gagal', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Layanan ongkir sedang tidak tersedia.'], 503);
        }
    }

    public function hitung(Request $request, RajaOngkir $ro)
    {
        $data = $request->validate([
            'tujuan_id' => ['required', 'integer'],
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return response()->json(['message' => 'Keranjang kosong.'], 422);
        }

        try {
            $berat = $ro->beratKeranjang($cart);
            $opsi  = $ro->hitungSemuaKurir($data['tujuan_id'], $berat);

            if (empty($opsi)) {
                return response()->json(['message' => 'Tidak ada layanan pengiriman untuk tujuan ini.'], 422);
            }

            return response()->json(['berat' => $berat, 'opsi' => $opsi]);
        } catch (\Throwable $e) {
            \Log::warning('RajaOngkir hitung gagal', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menghitung ongkir. Coba lagi.'], 503);
        }
    }

    public function kecamatan(int $kotaId, RajaOngkir $ro)
    {
        try {
            return response()->json($ro->daftarKecamatan($kotaId));
        } catch (\Throwable $e) {
            \Log::warning('Kecamatan gagal dimuat', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Daftar kecamatan tidak tersedia.'], 503);
        }
    }
}