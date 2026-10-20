<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WilayahController extends Controller
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('rajaongkir.base_url', 'https://rajaongkir.komerce.id/api');
        $this->apiKey = config('rajaongkir.api_key') ?? env('RAJAONGKIR_API_KEY');
    }

    public function provinces()
    {
        return $this->proxy('/destination/province');
    }

    public function cities($provinceId)
    {
        return $this->proxy("/destination/city/{$provinceId}");
    }

    public function districts($cityId)
    {
        return $this->proxy("/destination/district/{$cityId}");
    }

    private function proxy(string $endpoint)
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->timeout(10)->get($this->baseUrl . $endpoint);

            if ($response->failed()) {
                Log::error('RajaOngkir error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['error' => 'API RajaOngkir gagal'], 500);
            }

            return response()->json($response->json('data') ?? []);
        } catch (\Exception $e) {
            Log::error('RajaOngkir exception', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}