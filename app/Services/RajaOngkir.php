<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RajaOngkir
{
    private function client()
    {
        $base = rtrim(config('rajaongkir.base_url'), '/');
        if (! str_ends_with($base, '/v1')) {
            $base .= '/v1';
        }

        $key = config('rajaongkir.api_key');

        return Http::withHeaders([
                'Key'       => $key,   // RajaOngkir-style
                'x-api-key' => $key,   // Komerce-style
            ])
            ->acceptJson()
            ->timeout(config('rajaongkir.timeout', 10))
            ->baseUrl($base);
    }

    public function cariTujuan(string $kata): array
    {
        $kata = trim($kata);
        if (mb_strlen($kata) < 3) return [];

        return Cache::remember('ro-tujuan-' . md5(mb_strtolower($kata)), now()->addDay(), function () use ($kata) {
            $res = $this->client()->get('destination/domestic-destination', [
                'search' => $kata,
                'limit'  => 10,
            ]);

            if (! $res->successful()) {
                throw new \RuntimeException('Pencarian tujuan gagal: ' . $res->json('meta.message'));
            }

            return collect($res->json('data') ?? [])
                ->map(fn ($d) => ['id' => (int) $d['id'], 'label' => $d['label']])
                ->values()
                ->all();
        });
    }

    public function hitungOngkir(int $tujuanId, int $beratGram, string $kurir = 'jne'): array
    {
        $beratGram = max(1, $beratGram);
        $asal      = (int) config('rajaongkir.origin');

        $kunci = "ro-cost-{$asal}-{$tujuanId}-{$beratGram}-" . md5($kurir);

        return Cache::remember($kunci, now()->addMinutes(10), function () use ($asal, $tujuanId, $beratGram, $kurir) {
            $res = $this->client()->asForm()->post('calculate/domestic-cost', [
                'origin'      => $asal,
                'destination' => $tujuanId,
                'weight'      => $beratGram,
                'courier'     => $kurir,
            ]);

            if (! $res->successful()) {
                throw new \RuntimeException('Hitung ongkir gagal: ' . $res->json('meta.message'));
            }

            return collect($res->json('data') ?? [])
                ->reject(fn ($r) => $beratGram < 10000 && str_starts_with(strtoupper($r['service']), 'JTR'))
                ->map(fn ($r) => [
                    'service'     => $r['service'],
                    'description' => $r['description'],
                    'cost'        => (int) $r['cost'],
                    'etd'         => $r['etd'] ?? null,
                    'courier'     => $r['code'],
                ])
                ->sortBy('cost')
                ->values()
                ->all();
        });
    }
}