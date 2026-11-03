<?php

namespace App\Console\Commands;

use App\Models\RajaOngkirKota;
use App\Models\RajaOngkirProvinsi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SinkronRajaOngkir extends Command
{
    protected $signature = 'rajaongkir:sync';
    protected $description = 'Sinkronisasi provinsi & kota dari API RajaOngkir (Komerce) ke database';

    private function client()
    {
        $base = rtrim(config('rajaongkir.base_url'), '/');
        if (! str_ends_with($base, '/v1')) {
            $base .= '/v1';
        }

        return Http::withHeaders(['Key' => config('rajaongkir.api_key')])
            ->acceptJson()
            ->timeout(60)
            ->retry(3, 3000)
            ->baseUrl($base);
    }

    public function handle()
    {
        if (! config('rajaongkir.api_key')) {
            $this->error('RAJAONGKIR_API_KEY belum diisi di .env');
            return 1;
        }

        $this->info('Mengambil data provinsi...');
        $res = $this->client()->get('destination/province');

        if ($res->failed()) {
            $this->error('Gagal mengambil provinsi: ' . $res->body());
            return 1;
        }

        $provinsi = $res->json('data') ?? [];
        foreach ($provinsi as $p) {
            RajaOngkirProvinsi::updateOrCreate(
                ['province_id' => $p['id']],
                ['province' => $p['name']]
            );
        }
        $this->info('Berhasil sinkron ' . count($provinsi) . ' provinsi.');

        $this->info('Mengambil data kota per provinsi...');
        $totalKota = 0;

        foreach ($provinsi as $p) {
            $res = $this->client()->get('destination/city/' . $p['id']);

            if ($res->failed()) {
                $this->warn("Lewati {$p['name']}: " . $res->status() . ' ' . $res->body());
                continue;
            }

            foreach ($res->json('data') ?? [] as $k) {
                RajaOngkirKota::updateOrCreate(
                    ['city_id' => $k['id']],
                    [
                        'province_id' => $p['id'],
                        'type'        => '-',
                        'city_name'   => $k['name'],
                        'postal_code' => $k['zip_code'] ?? null,
                    ]
                );
                $totalKota++;
            }
        }

        $this->info("Berhasil sinkron {$totalKota} kota.");
        $this->info('Selesai!');
        return 0;
    }
}