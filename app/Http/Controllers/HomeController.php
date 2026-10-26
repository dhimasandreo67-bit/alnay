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
        // =====================================================
        // OPSI 1 — DATA DUMMY/STATIS (AKTIF SEKARANG)
        //
        // Path foto dummy relatif ke folder public/, contoh:
        //   'image/produk/produk1.jpg'  →  public/image/produk/produk1.jpg
        // (di view dipanggil dengan asset($path))
        // =====================================================

        // 'foto' & 'subjudul' BELUM ada di ERD tabel kategori.
        // Bersifat opsional di view: kalau kosong, tampil huruf awal.
        $kategori = [
            ['id' => 1, 'nama_kategori' => 'Jas & Beskap',   'kode_kategori' => 'JSB', 'subjudul' => 'Pria & Pengantin', 'foto' => 'images/kategori/jas-beskap.jpg'],
            ['id' => 2, 'nama_kategori' => 'Gown & Kebaya',  'kode_kategori' => 'GWN', 'subjudul' => 'Akad & Resepsi',   'foto' => 'images/kategori/gown-kebaya.jpg'],
            ['id' => 3, 'nama_kategori' => 'Dress',          'kode_kategori' => 'DRS', 'subjudul' => 'Adat Lengkap',     'foto' => 'images/kategori/dress.jpg'],
            ['id' => 4, 'nama_kategori' => 'Aksesoris',      'kode_kategori' => 'AKS', 'subjudul' => 'Mahkota & Bros',   'foto' => 'images/kategori/aksesoris.jpg'],
            ['id' => 5, 'nama_kategori' => 'Tenun & Songket', 'kode_kategori' => 'SNG', 'subjudul' => 'Kain Warisan Asli', 'foto' => 'images/kategori/tenun-songket.jpg'],
            ['id' => 6, 'nama_kategori' => 'Kemeja Batik',   'kode_kategori' => 'BTK', 'subjudul' => 'Berbagai Motif',   'foto' => 'images/kategori/kemeja-batik.jpg'],
        ];

        // dapat_dijual = false → di kartu otomatis jadi "Khusus Sewa" & hanya tombol Sewa.
        $produkTerbaru = [
            $this->dummyProduk([
                'id' => 1, 'kategori_id' => 3, 'sku' => 'DRS-001',
                'nama' => 'Long Dress Hitam Marun', 'deskripsi' => 'Kebaya ekor bordir emas & mahkota',
                'foto_produk' => 'images/produk/produk1.PNG',
                'harga_sewa' => 2500000, 'harga_jual' => 9500000, 'dapat_dijual' => true,
                'rating_rata_rata' => 4.9, 'jumlah_rating' => 120,
            ]),
            $this->dummyProduk([
                'id' => 2, 'kategori_id' => 1, 'sku' => 'JSB-001',
                'nama' => 'Jas Hitam Tenun Gold', 'deskripsi' => 'Beskap furing sutera & kain tenun',
                'foto_produk' => 'images/produk/produk2.jpg',
                'harga_sewa' => 1800000, 'harga_jual' => 5900000, 'dapat_dijual' => true,
                'rating_rata_rata' => 4.8, 'jumlah_rating' => 80,
            ]),
            $this->dummyProduk([
                'id' => 3, 'kategori_id' => 2, 'sku' => 'GWN-001',
                'nama' => 'Gown Merah Maroon Pengantin', 'deskripsi' => 'Lengkap jas, gaun, mahkota, dan selop',
                'foto_produk' => 'images/produk/produk3.jpg', 'status' => 'disewa',
                'harga_sewa' => 3500000, 'harga_jual' => null, 'dapat_dijual' => false,
                'rating_rata_rata' => 5.0, 'jumlah_rating' => 45,
            ]),
            $this->dummyProduk([
                'id' => 4, 'kategori_id' => 4, 'sku' => 'AKS-001',
                'nama' => 'Mahkota Suntiang Padang 9 Tingkat', 'deskripsi' => 'Sepuh emas asli dengan kembang goyang',
                'foto_produk' => 'images/produk/produk4.jpg',
                'harga_sewa' => 850000, 'harga_jual' => null, 'dapat_dijual' => false,
                'rating_rata_rata' => 4.7, 'jumlah_rating' => 60,
            ]),
        ];

        $produkTerpopuler = [
            $this->dummyProduk([
                'id' => 5, 'kategori_id' => 1, 'sku' => 'JSB-002',
                'nama' => 'Set Pasangan Sunda Siger Emas', 'deskripsi' => 'Kebaya payet & beskap kancing kuningan',
                'foto_produk' => 'images/produk/produk5.jpg',
                'harga_sewa' => 3200000, 'harga_jual' => null, 'dapat_dijual' => false,
                'rating_rata_rata' => 5.0, 'jumlah_rating' => 340,
            ]),
            $this->dummyProduk([
                'id' => 6, 'kategori_id' => 1, 'sku' => 'JSB-003',
                'nama' => 'Jas Beskap Beludru Jawa Klasik', 'deskripsi' => 'Bordir ornamen garuda & jarik prada',
                'foto_produk' => 'images/produk/produk3.jpg',
                'harga_sewa' => 1600000, 'harga_jual' => 6500000, 'dapat_dijual' => true,
                'rating_rata_rata' => 4.9, 'jumlah_rating' => 215,
            ]),
            $this->dummyProduk([
                'id' => 7, 'kategori_id' => 2, 'sku' => 'GWN-002',
                'nama' => 'Gaun Pengantin Brokat Payet', 'deskripsi' => 'Jubah veil renda 3 meter & korset presisi',
                'foto_produk' => 'images/produk/produk2.jpg',
                'harga_sewa' => 2400000, 'harga_jual' => 8900000, 'dapat_dijual' => true,
                'rating_rata_rata' => 4.9, 'jumlah_rating' => 184,
            ]),
            $this->dummyProduk([
                'id' => 8, 'kategori_id' => 4, 'sku' => 'AKS-002',
                'nama' => 'Set Aksesoris Tanjak & Keris', 'deskripsi' => 'Ornamen adat Melayu & Palembang',
                'foto_produk' => 'images/produk/produk1.PNG',
                'harga_sewa' => 750000, 'harga_jual' => null, 'dapat_dijual' => false,
                'rating_rata_rata' => 4.8, 'jumlah_rating' => 152,
            ]),
        ];

        $testimoni = [
            [
                'nama'    => 'Raditya & Anindya',
                'konteks' => 'Pernikahan Adat Lampung di Jakarta',
                'teks'    => 'Sewa set Pengantin Lampung Saibatin untuk resepsi sangat mewah dan gagahnya pas dipakai. Kopiah dan sigernya surprisingly nyaman tidak berat di kepala. Pengiriman ke Jakarta tepat waktu dan wangi sekali!',
                'bintang' => 5,
            ],
            [
                'nama'    => 'dr. Fathia Maheswari',
                'konteks' => 'Akad Nikah Minang di Bukittinggi',
                'teks'    => 'Suntiang Minang-nya benar-benar standar adibusana! Semua keluarga besar Padang memuji kerapian songket dan detail aksesorisnya. Terima kasih tim ALNAY.ID yang sigap bantu fitting via video call.',
                'bintang' => 5,
            ],
            [
                'nama'    => 'Dian Sastroatmodjo',
                'konteks' => 'Direktur Komunitas Budaya Nusantara',
                'teks'    => 'Kami menyewa 16 pasang baju adat untuk karnaval delegasi seni internasional. Dari Bugis, Dayak, sampai Bali semuanya lengkap dengan properti tombak dan mahkotanya. Nilai budaya yang sangat terawat!',
                'bintang' => 5,
            ],
        ];

        /*
        // =====================================================
        // OPSI 2 — DATA DINAMIS DARI DATABASE (NONAKTIF)
        // Aktifkan blok ini (dan comment OPSI 1 di atas) setelah
        // database & halaman admin sudah tersedia.
        // =====================================================

        $kategori = Kategori::orderBy('nama_kategori')
                ->get()
                ->map(fn ($k) => [
                    'id'            => $k->id,
                    'nama_kategori' => $k->nama_kategori,
                    'kode_kategori' => $k->kode_kategori,
                    'subjudul'      => $k->subjudul ?? null,
                    'foto'          => $k->foto ? 'images/kategori/' . $k->foto : null,
                ])
                ->toArray();

        $produkTerbaru = Produk::latest()
            ->limit(4)
            ->get()
            ->map(fn ($p) => $this->produkKeArray($p))
            ->toArray();

        $produkTerpopuler = Produk::orderByDesc('jumlah_rating')
            ->orderByDesc('rating_rata_rata')
            ->limit(4)
            ->get()
            ->map(fn ($p) => $this->produkKeArray($p))
            ->toArray();

        // Testimoni = ulasan bintang 4–5 yang ada teksnya.
        // (Tabel rating belum punya kolom "tampilkan_di_beranda".)
        $testimoni = Rating::with(['user', 'produk'])
            ->where('rating', '>=', 4)
            ->whereNotNull('ulasan')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($r) => [
                'nama'    => $r->user->name,       // kolom users: "name", bukan "nama"
                'konteks' => $r->produk->nama,
                'teks'    => $r->ulasan,           // kolom rating: "ulasan", bukan "komentar"
                'bintang' => $r->rating,
            ])
            ->toArray();
        */

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