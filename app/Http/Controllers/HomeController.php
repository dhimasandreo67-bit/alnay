<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Rating;

/*
|--------------------------------------------------------------------------
| HomeController — Beranda
|--------------------------------------------------------------------------
|
| ⚠️ Data di method index() masih DUMMY (OPSI 1).
| Ganti ke OPSI 2 (database) saat tabel produk sudah terisi.
|
| Bentuk array SAMA dengan kolom ERD, jadi saat pindah ke database
| cukup mengganti sumber data — view (customer/beranda.blade.php) tidak berubah.
|
| ERD:
|   - Produk   : id, kategori_id, sku, nama, deskripsi, foto_produk,
|                bahan, berat, ukuran, harga_jual, harga_sewa, stok,
|                status, dapat_dijual, denda_per_hari_telat,
|                rating_rata_rata, jumlah_rating
|   - Kategori : id, nama_kategori, kode_kategori
|   - Rating   : produk_id, user_id, rating, ulasan, tanggal
|
| Event TIDAK diambil dari sini — sistem event terpisah (event.alnay.id).
*/

class HomeController extends Controller
{
    public function index()
    {
        // Mengambil data asli dari database
        // Foto kategori dicocokkan lewat kode (tabel kategori belum punya kolom foto)
$fotoKategori = [
    'JSB' => 'images/kategori/jas-beskap.jpg',
    'GWN' => 'images/kategori/gown-kebaya.jpg',
    'KBY' => 'images/kategori/gown-kebaya.jpg',
    'AKS' => 'images/kategori/aksesoris.jpg',
    'SNG' => 'images/kategori/tenun-songket.jpg',
    'BTK' => 'images/kategori/kemeja-batik.jpg',
];

$kategori = Kategori::where('is_active', true)
    ->orderBy('id')
    ->get()
    ->map(fn ($k) => [
        'nama_kategori' => $k->nama_kategori,
        'kode_kategori' => $k->kode_kategori,
        'subjudul'      => null,
        'foto'          => $fotoKategori[$k->kode_kategori] ?? null,
    ]);
        
        $produkTerbaru = \App\Models\Produk::where('stok', '>', 0)
                            ->latest()
                            ->take(4)
                            ->get();
                            
        $produkTerpopuler = \App\Models\Produk::where('stok', '>', 0)
                                ->orderByDesc('jumlah_rating')
                                ->take(4)
                                ->get();

        // Testimoni = ulasan bintang 4–5 yang ada teksnya.
        // (Tabel rating belum punya kolom "tampilkan_di_beranda".)
        $testimoni = Rating::with(['user', 'produk'])
            ->where('rating', '>=', 4)
            ->whereNotNull('ulasan')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($r) => [
                'nama'    => $r->user->name ?? 'Pelanggan', 
                'konteks' => $r->produk->nama ?? 'Produk ALNAY',
                'teks'    => $r->ulasan,           
                'bintang' => $r->rating,
            ])
            ->toArray();

        return view('customer.beranda', compact(
            'kategori', 'produkTerbaru', 'produkTerpopuler', 'testimoni'
        ));
    }

    /**
     * DUMMY: hapus saat tabel produk sudah siap.
     * Field yang tidak diisi memakai nilai default di bawah.
     */
    private function dummyProduk(array $data): array
    {
        return array_merge([
            'id'                   => 0,
            'kategori_id'          => 1,
            'sku'                  => 'SKU-000',
            'nama'                 => 'Produk',
            'deskripsi'            => 'Deskripsi produk placeholder.',
            'foto_produk'          => null,
            'bahan'                => null,
            'berat'                => 1000,
            'ukuran'               => 'M, L, XL',
            'harga_jual'           => null,
            'harga_sewa'           => 250000,
            'stok'                 => 3,
            'status'               => 'tersedia',
            'dapat_dijual'         => false,
            'denda_per_hari_telat' => 50000,
            'rating_rata_rata'     => 0,
            'jumlah_rating'        => 0,
        ], $data);
    }

    /**
     * Dipakai OPSI 2: ubah model Produk menjadi array (bentuk sama dengan dummy).
     */
    private function produkKeArray(Produk $p): array
    {
        return [
            'id'                   => $p->id,
            'kategori_id'          => $p->kategori_id,
            'sku'                  => $p->sku,
            'nama'                 => $p->nama,
            'deskripsi'            => $p->deskripsi,
            // Foto upload admin disimpan di storage/app/public → diakses lewat /storage
            // (butuh `php artisan storage:link`)
            'foto_produk'          => $p->foto_produk ? 'storage/' . $p->foto_produk : null,
            'bahan'                => $p->bahan,
            'berat'                => $p->berat,
            'ukuran'               => $p->ukuran,
            'harga_jual'           => $p->harga_jual,
            'harga_sewa'           => $p->harga_sewa,
            'stok'                 => $p->stok,
            'status'               => $p->status,
            'dapat_dijual'         => $p->dapat_dijual,
            'denda_per_hari_telat' => $p->denda_per_hari_telat,
            'rating_rata_rata'     => $p->rating_rata_rata,
            'jumlah_rating'        => $p->jumlah_rating,
        ];
    }
}