<?php

namespace App\Console\Commands;

use App\Models\RajaOngkirKota;
use App\Models\RajaOngkirProvinsi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SinkronRajaOngkir extends Command
{
    protected $signature = 'rajaongkir:sync';
    protected $description = 'Sinkronisasi data provinsi & kota dari API RajaOngkir ke database';

    public function handle()
    {
        $apiKey = config('services.rajaongkir.api_key');
        $url = config('services.rajaongkir.url');

        if (!$apiKey) {
            $this->error('RAJAONGKIR_API_KEY belum diisi di .env');
            return 1;
        }

        $this->info('Mengambil data provinsi...');
        $response = Http::withHeaders(['key' => $apiKey])
        ->timeout(60)
        ->retry(3, 3000)
        ->get($url . '/province');

        if ($response->failed()) {
            $this->error('Gagal mengambil data provinsi: ' . $response->body());
            return 1;
        }

        $provinsiList = $response->json('rajaongkir.results') ?? [];
        $countProvinsi = 0;

        foreach ($provinsiList as $p) {
            RajaOngkirProvinsi::updateOrCreate(
                ['province_id' => $p['province_id']],
                ['province' => $p['province']]
            );
            $countProvinsi++;
        }
        $this->info("Berhasil sinkron {$countProvinsi} provinsi.");

        $this->info('Mengambil data kota...');
        $response = Http::withHeaders(['key' => $apiKey])
        ->timeout(60)
        ->retry(3, 3000)
        ->get($url . '/city');

        if ($response->failed()) {
            $this->error('Gagal mengambil data kota: ' . $response->body());
            return 1;
        }

        $kotaList = $response->json('rajaongkir.results') ?? [];
        $countKota = 0;

        foreach ($kotaList as $k) {
            RajaOngkirKota::updateOrCreate(
                ['city_id' => $k['city_id']],
                [
                    'province_id' => $k['province_id'],
                    'type' => $k['type'],
                    'city_name' => $k['city_name'],
                    'postal_code' => $k['postal_code'] ?? null,
                ]
            );
            $countKota++;
        }
        $this->info("Berhasil sinkron {$countKota} kota.");

        $this->info('Selesai!');
        return 0;
    }
}