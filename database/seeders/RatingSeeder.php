<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Rating;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = User::where('role', 'pelanggan')->orderBy('id')->take(3)->get();

        // 3 produk yang bisa dibeli (dapat_dijual = true)
        $produk = Produk::where('dapat_dijual', true)->take(3)->get();

        $ulasan = [
            'Sewa set pengantin Lampung Sabtu untuk resepsi. Hasilnya melebihi ekspektasi, fitting pas dan tim sangat membantu!',
            'Sunting Melayu-nya benar-benar standar adat Sumatera. Bahan berkualitas, motif sesuai. Pengiriman cepat!',
            'Kami menyewa 16 pasang baju adat untuk acara budaya. Semua pas dan sesuai jadwal. Sangat direkomendasikan.',
        ];

        $bintang = [5, 5, 4];

        foreach ($pelanggan as $i => $user) {
            $item = $produk[$i] ?? $produk[0];
            $harga = $item->harga_jual ?? 1500000;

            // Transaksi
            $transaksi = Transaksi::create([
                'kode_transaksi' => 'TRX-' . now()->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'jenis' => 'beli',
                'tanggal' => Carbon::now()->subDays(rand(7, 30)),
                'total' => $harga,
                'catatan' => 'Transaksi contoh dari seeder',
            ]);

            // Detail transaksi
            $detail = DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $item->id,
                'jumlah' => 1,
                'harga_satuan' => $harga,
            ]);

            // Rating
            Rating::create([
                'detail_transaksi_id' => $detail->id,
                'produk_id' => $item->id,
                'user_id' => $user->id,
                'rating' => $bintang[$i] ?? 5,
                'ulasan' => $ulasan[$i] ?? $ulasan[0],
                'tanggal' => Carbon::now()->subDays(rand(1, 7)),
            ]);
        }
    }
}
