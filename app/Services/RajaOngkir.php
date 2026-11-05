<?php

namespace App\Services;

use App\Models\Produk;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RajaOngkir
{
    /** Kurir yang ditampilkan di checkout. */
    public const KURIR = ['jne', 'sicepat', 'jnt'];

    private function client()
    {
        $base = rtrim(config('rajaongkir.base_url'), '/');
        if (! str_ends_with($base, '/v1')) {
            $base .= '/v1';
        }

        return Http::withHeaders(['Key' => config('rajaongkir.api_key')])
            ->acceptJson()
            ->timeout(config('rajaongkir.timeout', 10))
            ->baseUrl($base);
    }

    /** Cari kelurahan/kecamatan tujuan dari teks yang diketik pembeli. */
    public function cariTujuan(string $kata): array
    {
        $kata = trim($kata);
        if (mb_strlen($kata) < 3) {
            return [];
        }

        return Cache::remember('ro-tujuan-' . md5(mb_strtolower($kata)), now()->addDay(), function () use ($kata) {
            $res = $this->client()->get('destination/domestic-destination', [
                'search' => $kata,
                'limit'  => 10,
            ]);

            if (! $res->successful()) {
                throw new \RuntimeException('Pencarian tujuan gagal: ' . $res->json('meta.message'));
            }

            return collect($res->json('data') ?? [])
                ->map(fn ($d) => ['id' => (int) $d['id'], 'label' => $d['label'], 'zip_code' => $d['zip_code'] ?? null])
                ->values()
                ->all();
        });
    }

    /** Daftar kecamatan dalam satu kota (untuk dropdown profil). */
    public function daftarKecamatan(int $kotaId): array
    {
        return Cache::remember("ro-kecamatan-{$kotaId}", now()->addDays(30), function () use ($kotaId) {
            $res = $this->client()->get("destination/district/{$kotaId}");

            if (! $res->successful()) {
                throw new \RuntimeException('Gagal memuat kecamatan: ' . $res->json('meta.message'));
            }

            return collect($res->json('data') ?? [])
                ->map(fn ($d) => ['id' => (int) $d['id'], 'name' => $d['name']])
                ->values()
                ->all();
        });
    }

    /** Daftar provinsi untuk dropdown alamat. */
public function daftarProvinsi(): array
{
    return Cache::remember('ro-provinsi', now()->addDays(30), function () {
        $res = $this->client()->get('destination/province');

        if (! $res->successful()) {
            throw new \RuntimeException('Gagal memuat provinsi: ' . $res->json('meta.message'));
        }

        return collect($res->json('data') ?? [])
            ->map(fn ($d) => ['id' => (int) $d['id'], 'name' => $d['name']])
            ->values()
            ->all();
    });
}

/** Daftar kota/kabupaten dalam satu provinsi untuk dropdown alamat. */
public function daftarKota(int $provinsiId): array
{
    return Cache::remember("ro-kota-{$provinsiId}", now()->addDays(30), function () use ($provinsiId) {
        $res = $this->client()->get("destination/city/{$provinsiId}");

        if (! $res->successful()) {
            throw new \RuntimeException('Gagal memuat kota: ' . $res->json('meta.message'));
        }

        return collect($res->json('data') ?? [])
            ->map(fn ($d) => ['id' => (int) $d['id'], 'name' => $d['name']])
            ->values()
            ->all();
    });
}

    /** Hitung ongkir satu kurir. $beratGram dalam gram. */
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
                // Trucking (JTR*) hanya masuk akal untuk barang berat (>= 10 kg).
                ->reject(fn ($r) => $beratGram < 10000 && str_starts_with(strtoupper($r['service']), 'JTR'))
                ->map(fn ($r) => [
                    'kurir'     => strtolower($r['code']),
                    'nama'      => $r['name'],
                    'layanan'   => $r['service'],
                    'deskripsi' => $r['description'],
                    'biaya'     => (int) $r['cost'],
                    'etd'       => $r['etd'] ?? null,
                ])
                ->sortBy('biaya')
                ->values()
                ->all();
        });
    }

    /** Hitung semua kurir di KURIR, urut dari termurah. Kurir yang gagal dilewati. */
    public function hitungSemuaKurir(int $tujuanId, int $beratGram): array
    {
        $semua = [];

        foreach (self::KURIR as $kurir) {
            try {
                $semua = array_merge($semua, $this->hitungOngkir($tujuanId, $beratGram, $kurir));
            } catch (\Throwable $e) {
                \Log::warning('Ongkir kurir gagal', ['kurir' => $kurir, 'error' => $e->getMessage()]);
            }
        }

        usort($semua, fn ($a, $b) => $a['biaya'] <=> $b['biaya']);

        return $semua;
    }

    /**
     * Total berat keranjang (gram), dihitung dari database produk, bukan dari browser.
     * Produk tanpa berat dianggap 1000 gram.
     */
    public function beratKeranjang(array $cartItems): int
    {
        $ids   = collect($cartItems)->pluck('id')->filter()->unique()->all();
        $berat = Produk::whereIn('id', $ids)->pluck('berat', 'id');

        $total = 0;
        foreach ($cartItems as $item) {
            $gram = (int) round((float) ($berat[$item['id'] ?? 0] ?? 0));
            if ($gram <= 0) {
                $gram = 1000;
            }
            $total += $gram * max(1, (int) ($item['qty'] ?? 1));
        }

        return max(1, $total + (int) config('rajaongkir.base_weight', 0));
    }

        /** Pecah label "KELURAHAN, KECAMATAN, KOTA, PROVINSI, KODEPOS" dari domestic-destination. */
    public static function uraikanLabel(?string $label): array
    {
        $bagian = array_map('trim', explode(',', (string) $label));

        $zip = null;
        if ($bagian && preg_match('/^\d{5}$/', end($bagian))) {
            $zip = array_pop($bagian);
        }

        $provinsi  = array_pop($bagian);
        $kota      = array_pop($bagian);
        $kecamatan = array_pop($bagian);
        $kelurahan = implode(', ', $bagian) ?: null;

        return [
            'kelurahan' => $kelurahan,
            'kecamatan' => $kecamatan,
            'kota'      => $kota,
            'provinsi'  => $provinsi,
            'kode_pos'  => $zip,
        ];
    }
}