<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['nama_kategori' => 'Jas Beskap',   'kode_kategori' => 'JSB'],
            ['nama_kategori' => 'Gown',         'kode_kategori' => 'GWN'],
            ['nama_kategori' => 'Kebaya',       'kode_kategori' => 'KBY'],
            ['nama_kategori' => 'Aksesoris',    'kode_kategori' => 'AKS'],
            ['nama_kategori' => 'Kain Songket', 'kode_kategori' => 'SNG'],
            ['nama_kategori' => 'Batik',        'kode_kategori' => 'BTK'],
        ];

        foreach ($kategori as $k) {
            Kategori::create($k);
        }
    }
}