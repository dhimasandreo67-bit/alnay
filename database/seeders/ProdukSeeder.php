<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriIds = Kategori::pluck('id', 'kode_kategori');

        $produk = [
            // 4 Produk Terbaru
            [
                'kategori_id' => $kategoriIds['JSB'],
                'sku' => 'JSB-001', 'nama' => 'Jas Beskap Klasik',
                'deskripsi' => 'Jas beskap dengan bahan wol premium, cocok untuk acara formal adat Jawa.',
                'bahan' => 'Wol Premium', 'berat' => 1200, 'ukuran' => 'M, L, XL',
                'harga_jual' => 1500000, 'harga_sewa' => 250000,
                'stok' => 3, 'status' => 'tersedia', 'dapat_dijual' => true,
                'denda_per_hari_telat' => 50000,
                'rating_rata_rata' => 4.9, 'jumlah_rating' => 120,
            ],
            [
                'kategori_id' => $kategoriIds['GWN'],
                'sku' => 'GWN-001', 'nama' => 'Gown Modern',
                'deskripsi' => 'Gown modern dengan siluet elegan, cocok untuk pesta malam.',
                'bahan' => 'Satin', 'berat' => 800, 'ukuran' => 'S, M, L',
                'harga_jual' => null, 'harga_sewa' => 350000,
                'stok' => 2, 'status' => 'tersedia', 'dapat_dijual' => false,
                'denda_per_hari_telat' => 75000,
                'rating_rata_rata' => 4.8, 'jumlah_rating' => 80,
            ],
            [
                'kategori_id' => $kategoriIds['KBY'],
                'sku' => 'KBY-001', 'nama' => 'Kebaya Pengantin',
                'deskripsi' => 'Kebaya pengantin dengan detail bordir tangan dan bahan brocade.',
                'bahan' => 'Brocade', 'berat' => 900, 'ukuran' => 'S, M',
                'harga_jual' => 2500000, 'harga_sewa' => 450000,
                'stok' => 1, 'status' => 'tersedia', 'dapat_dijual' => true,
                'denda_per_hari_telat' => 100000,
                'rating_rata_rata' => 5.0, 'jumlah_rating' => 45,
            ],
            [
                'kategori_id' => $kategoriIds['SNG'],
                'sku' => 'SNG-001', 'nama' => 'Kain Songket Premium',
                'deskripsi' => 'Kain songket sutra dengan motif tradisional Palembang.',
                'bahan' => 'Sutra', 'berat' => 500, 'ukuran' => '2m x 1m',
                'harga_jual' => null, 'harga_sewa' => 150000,
                'stok' => 5, 'status' => 'tersedia', 'dapat_dijual' => false,
                'denda_per_hari_telat' => 25000,
                'rating_rata_rata' => 4.7, 'jumlah_rating' => 60,
            ],

            // 4 Produk Terpopuler (jumlah_rating lebih tinggi)
            [
                'kategori_id' => $kategoriIds['JSB'],
                'sku' => 'JSB-002', 'nama' => 'Jas Beskap Modern',
                'deskripsi' => 'Jas beskap dengan potongan modern, tetap mempertahankan unsur adat.',
                'bahan' => 'Wol Premium', 'berat' => 1100, 'ukuran' => 'M, L, XL',
                'harga_jual' => 1800000, 'harga_sewa' => 300000,
                'stok' => 2, 'status' => 'tersedia', 'dapat_dijual' => true,
                'denda_per_hari_telat' => 50000,
                'rating_rata_rata' => 4.9, 'jumlah_rating' => 340,
            ],
            [
                'kategori_id' => $kategoriIds['GWN'],
                'sku' => 'GWN-002', 'nama' => 'Gown Pesta Elegan',
                'deskripsi' => 'Gown pesta dengan bahan chiffon yang jatuh lembut.',
                'bahan' => 'Chiffon', 'berat' => 700, 'ukuran' => 'S, M',
                'harga_jual' => null, 'harga_sewa' => 400000,
                'stok' => 3, 'status' => 'tersedia', 'dapat_dijual' => false,
                'denda_per_hari_telat' => 75000,
                'rating_rata_rata' => 4.8, 'jumlah_rating' => 210,
            ],
            [
                'kategori_id' => $kategoriIds['KBY'],
                'sku' => 'KBY-002', 'nama' => 'Kebaya Modern',
                'deskripsi' => 'Kebaya modern dengan bahan lace dan potongan yang lebih ramping.',
                'bahan' => 'Lace', 'berat' => 600, 'ukuran' => 'S, M, L',
                'harga_jual' => 1200000, 'harga_sewa' => 250000,
                'stok' => 4, 'status' => 'tersedia', 'dapat_dijual' => true,
                'denda_per_hari_telat' => 50000,
                'rating_rata_rata' => 4.9, 'jumlah_rating' => 180,
            ],
            [
                'kategori_id' => $kategoriIds['BTK'],
                'sku' => 'BTK-001', 'nama' => 'Batik Tulis Solo',
                'deskripsi' => 'Batik tulis asli Solo dengan motif parang dan sogan.',
                'bahan' => 'Katun Primisima', 'berat' => 400, 'ukuran' => '2m x 1m',
                'harga_jual' => null, 'harga_sewa' => 100000,
                'stok' => 8, 'status' => 'tersedia', 'dapat_dijual' => false,
                'denda_per_hari_telat' => 20000,
                'rating_rata_rata' => 4.6, 'jumlah_rating' => 150,
            ],
        ];

        foreach ($produk as $p) {
            Produk::create($p);
        }
    }
}
