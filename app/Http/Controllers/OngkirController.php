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
            'berat'     => ['required', 'integer', 'min:1', 'max:50000'], // gram
            'kurir'     => ['nullable', 'string', 'max:50'],
        ]);

        try {
            return response()->json(
                $ro->hitungOngkir($data['tujuan_id'], $data['berat'], $data['kurir'] ?? 'jne')
            );
        } catch (\Throwable $e) {
            \Log::warning('RajaOngkir hitung gagal', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menghitung ongkir. Coba lagi.'], 503);
        }
    }
}