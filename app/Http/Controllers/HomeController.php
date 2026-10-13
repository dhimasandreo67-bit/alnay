<?php

namespace App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| HomeController — Beranda
|--------------------------------------------------------------------------
|
| ⚠️ Data di method index() adalah DUMMY.
| Ganti dengan query Eloquent saat tabel produk siap.
|
| Bentuk array SAMA dengan kolom ERD supaya penggantian nanti cukup
| mengganti sumber data, bukan mengubah view.
|
| CATATAN ERD (bisa berubah selama development):
|   - Produk: id, kategori_id, sku, nama, deskripsi, foto_produk,
|             bahan, berat, ukuran, harga_jual, harga_sewa, stok,
|             status, dapat_dijual, denda_per_hari_telat
|   - Kategori: id, nama_kategori, kode_kategori
|   - Testimoni: dari tabel rating (bentuk final belum pasti)
|
| Event TIDAK diambil dari sini — event sistem terpisah.
*/

class HomeController extends Controller
{
    public function index()
    {
        // DUMMY: ganti dengan query Eloquent saat tabel produk siap.

        $kategori = [
            ['id' => 1, 'nama_kategori' => 'Jas Beskap',   'kode_kategori' => 'JSB'],
            ['id' => 2, 'nama_kategori' => 'Gown',         'kode_kategori' => 'GWN'],
            ['id' => 3, 'nama_kategori' => 'Kebaya',       'kode_kategori' => 'KBY'],
            ['id' => 4, 'nama_kategori' => 'Aksesoris',    'kode_kategori' => 'AKS'],
            ['id' => 5, 'nama_kategori' => 'Kain Songket', 'kode_kategori' => 'SNG'],
            ['id' => 6, 'nama_kategori' => 'Batik',        'kode_kategori' => 'BTK'],
        ];

        $produkTerbaru = [
            $this->dummyProduk(1, 1, 'JSB-001', 'Jas Beskap Klasik',   true,  4.9, 120),
            $this->dummyProduk(2, 2, 'GWN-001', 'Gown Modern',         false, 4.8, 80),
            $this->dummyProduk(3, 3, 'KBY-001', 'Kebaya Pengantin',    true,  5.0, 45),
            $this->dummyProduk(4, 5, 'SNG-001', 'Kain Songket Premium', false, 4.7, 60),
        ];

        $produkTerpopuler = [
            $this->dummyProduk(5, 1, 'JSB-002', 'Jas Beskap Modern', true,  4.9, 340),
            $this->dummyProduk(6, 2, 'GWN-002', 'Gown Pesta',        false, 4.8, 210),
            $this->dummyProduk(7, 3, 'KBY-002', 'Kebaya Modern',     true,  4.9, 180),
            $this->dummyProduk(8, 6, 'BTK-001', 'Batik Tulis',       false, 4.6, 150),
        ];

        $testimoni = [
            ['nama' => 'Nama Klien 1', 'konteks' => 'Sewa Jas Beskap', 'teks' => 'Pelayanan ramah, hasil sesuai ekspektasi. (placeholder)', 'bintang' => 5],
            ['nama' => 'Nama Klien 2', 'konteks' => 'Beli Kebaya',     'teks' => 'Kualitas bahan bagus dan jahitan rapi. (placeholder)',    'bintang' => 5],
            ['nama' => 'Nama Klien 3', 'konteks' => 'Sewa Gown',       'teks' => 'Proses fitting mudah dan cepat. (placeholder)',           'bintang' => 4],
        ];

        return view('customer.beranda', compact(
            'kategori', 'produkTerbaru', 'produkTerpopuler', 'testimoni'
        ));
    }

    /**
     * DUMMY: hapus saat tabel produk sudah siap.
     * Bentuk array sesuai kolom ERD PRODUK.
     */
    private function dummyProduk(
        int $id,
        int $kategoriId,
        string $sku,
        string $nama,
        bool $dapatDijual,
        float $rating,
        int $jumlahRating
    ): array {
        return [
            'id'                => $id,
            'kategori_id'       => $kategoriId,
            'sku'               => $sku,
            'nama'              => $nama,
            'deskripsi'         => 'Deskripsi produk placeholder.',
            'foto_produk'       => null,
            'bahan'             => 'Bahan placeholder',
            'berat'             => 1000,
            'ukuran'            => 'M, L, XL',
            'harga_jual'        => 1500000,
            'harga_sewa'        => 250000,
            'stok'              => 3,
            'status'            => 'tersedia',
            'dapat_dijual'      => $dapatDijual,
            'denda_per_hari_telat' => 50000,
            'rating_rata_rata'  => $rating,
            'jumlah_rating'     => $jumlahRating,
        ];
    }
}