<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RajaOngkirKota;
use App\Models\RajaOngkirProvinsi;

class WilayahController extends Controller
{
    public function provinces()
    {
        return RajaOngkirProvinsi::select('province_id as id', 'province as name')
            ->orderBy('province')
            ->get();
    }

    public function cities($provinceId)
    {
        return RajaOngkirKota::where('province_id', $provinceId)
            ->select('city_id as id', 'city_name as name', 'type', 'postal_code')
            ->orderBy('city_name')
            ->get();
    }

    public function districts($cityId)
    {
        // Komerce tidak punya data kecamatan.
        // Untuk sementara, kembalikan array kosong.
        return [];
    }
}