<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengiriman;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Support\Keranjang;
use App\Models\Transaksi;
use App\Models\Pengiriman;
use App\Services\RajaOngkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = Keranjang::segarkan(session('cart', []));
        if (empty($cartItems)) {
            return redirect()->route('keranjang.index')->with('error', 'Keranjang Anda kosong.');
        }

        $total = array_reduce($cartItems, fn($c, $i) => $c + ($i['harga'] * $i['qty']), 0);
        $user = auth()->user();

        // Alamat tersimpan di "Alamat Saya" (alamat utama tampil pertama) supaya tidak perlu input ulang
        $alamatList = AlamatPengiriman::where('user_id', $user->id)
            ->orderByDesc('is_utama')
            ->orderByDesc('updated_at')
            ->get();

        return view('customer.checkout', compact('cartItems', 'total', 'user', 'alamatList'));
    }

    /**
     * Simpan alamat yang diisi di checkout ke "Alamat Saya".
     * Aturan sama dengan AlamatController@store: alamat pertama otomatis menjadi utama.
     */
    private function simpanAlamatBaru(Request $request): AlamatPengiriman
    {
        $userId = Auth::id();
        $sudahAda = AlamatPengiriman::where('user_id', $userId)->exists();
        $jadiUtama = $sudahAda ? $request->boolean('jadikan_utama') : true;

        if ($jadiUtama) {
            AlamatPengiriman::where('user_id', $userId)->update(['is_utama' => false]);
        }

        return AlamatPengiriman::create([
            'user_id'             => $userId,
            'label'               => $request->input('label') ?: 'Rumah',
            'nama_penerima'       => $request->nama_penerima,
            'no_hp'               => $request->no_wa,
            'provinsi'            => $request->provinsi,
            'kota_kabupaten'      => $request->kota_kabupaten,
            'kecamatan'           => $request->kecamatan,
            'kode_pos'            => $request->kode_pos,
            'alamat'              => $request->alamat,
            'ongkir_tujuan_id'    => (int) $request->tujuan_id,
            'ongkir_tujuan_label' => $request->tujuan_label ?: (string) $request->tujuan_id,
            'is_utama'            => $jadiUtama,
        ]);
    }

    /**
     * Simpan alamat dari halaman checkout (AJAX) ke "Alamat Saya".
     * Mengembalikan JSON agar kartu alamat baru bisa langsung ditampilkan & dipilih.
     */
    public function simpanAlamat(Request $request)
    {
        $request->validate([
            'label'          => 'nullable|string|max:50',
            'nama_penerima'  => 'required|string|max:100',
            'no_wa'          => 'required|string|max:20',
            'provinsi'       => 'required|string|max:100',
            'kota_kabupaten' => 'required|string|max:100',
            'kecamatan'      => 'required|string|max:100',
            'kode_pos'       => 'nullable|string|max:10',
            'alamat'         => 'required|string|max:500',
            'tujuan_id'      => 'required|integer',
            'tujuan_label'   => 'nullable|string|max:255',
            'jadikan_utama'  => 'nullable|boolean',
        ], [
            'nama_penerima.required'  => 'Nama penerima wajib diisi.',
            'no_wa.required'          => 'No. WhatsApp wajib diisi.',
            'provinsi.required'       => 'Pilih provinsi.',
            'kota_kabupaten.required' => 'Pilih kota/kabupaten.',
            'kecamatan.required'      => 'Pilih kecamatan.',
            'alamat.required'         => 'Alamat lengkap wajib diisi.',
            'tujuan_id.required'      => 'Pilih kelurahan tujuan pengiriman.',
        ]);

        $alamat = DB::transaction(fn () => $this->simpanAlamatBaru($request));

        return response()->json([
            'message' => 'Alamat berhasil disimpan.',
            'alamat'  => [
                'id'             => $alamat->id,
                'label'          => $alamat->label,
                'nama_penerima'  => $alamat->nama_penerima,
                'no_hp'          => $alamat->no_hp,
                'alamat'         => $alamat->alamat,
                'kecamatan'      => $alamat->kecamatan,
                'kota_kabupaten' => $alamat->kota_kabupaten,
                'provinsi'       => $alamat->provinsi,
                'kode_pos'       => $alamat->kode_pos,
                'is_utama'       => (bool) $alamat->is_utama,
                'tujuan_id'      => $alamat->ongkir_tujuan_id,
                'tujuan_label'   => $alamat->ongkir_tujuan_label,
            ],
        ]);
    }

    public function store(Request $request, RajaOngkir $ro)
    {
        // Kalau user memilih alamat tersimpan, data penerima, alamat, dan tujuan ongkir diambil dari
        // database (bukan dari form), dan hanya alamat milik user yang sedang login yang boleh dipakai.
        $alamatDipilih = null;
        $idAlamat = $request->input('alamat_id');

        if ($request->input('metode_penerimaan') === 'kirim' && $idAlamat && $idAlamat !== 'baru') {
            $alamatDipilih = AlamatPengiriman::where('user_id', Auth::id())->find($idAlamat);

            if (! $alamatDipilih) {
                return back()->withInput()->with('error', 'Alamat pengiriman yang dipilih tidak ditemukan.');
            }

            $request->merge([
                'nama_penerima' => $alamatDipilih->nama_penerima,
                'no_wa'         => $alamatDipilih->no_hp,
                'alamat'        => $alamatDipilih->alamat,
                'tujuan_id'     => $alamatDipilih->ongkir_tujuan_id,
                'tujuan_label'  => $alamatDipilih->ongkir_tujuan_label,
            ]);
        }

        $request->validate([
            'alamat_id'         => 'nullable|string|max:20',
            'metode_penerimaan' => 'required|in:ambil,kirim',
            'metode_bayar'      => 'required|in:qris,va,ewallet,tunai',
            'nama_penerima'     => 'required_if:metode_penerimaan,kirim|nullable|string|max:100',
            'no_wa'             => 'required_if:metode_penerimaan,kirim|nullable|string|max:20',
            'alamat'            => 'required_if:metode_penerimaan,kirim|nullable|string|max:500',
            'tujuan_id'         => 'required_if:metode_penerimaan,kirim|nullable|integer',
            'tujuan_label'      => 'nullable|string|max:255',
            'kurir'             => 'required_if:metode_penerimaan,kirim|nullable|string|max:20',
            'layanan'           => 'required_if:metode_penerimaan,kirim|nullable|string|max:50',
        ], [
            'tujuan_id.required_if' => 'Pilih kelurahan/kecamatan tujuan pengiriman.',
            'kurir.required_if'     => 'Pilih layanan pengiriman.',
            'layanan.required_if'   => 'Pilih layanan pengiriman.',
        ]);

        // Tunai hanya boleh untuk ambil di toko
        if ($request->metode_bayar === 'tunai' && $request->metode_penerimaan !== 'ambil') {
            return back()->withInput()->with('error', 'Pembayaran tunai hanya tersedia untuk ambil di toko.');
        }

        // Alamat baru yang diisi manual: wilayah wajib lengkap seperti form di profil.
        $alamatManual = $request->metode_penerimaan === 'kirim' && ! $alamatDipilih;
        if ($alamatManual) {
            $request->validate([
                'label'          => 'nullable|string|max:50',
                'provinsi'       => 'required|string|max:100',
                'kota_kabupaten' => 'required|string|max:100',
                'kecamatan'      => 'required|string|max:100',
                'kode_pos'       => 'nullable|string|max:10',
                'simpan_alamat'  => 'nullable|boolean',
                'jadikan_utama'  => 'nullable|boolean',
            ], [
                'provinsi.required'       => 'Pilih provinsi.',
                'kota_kabupaten.required' => 'Pilih kota/kabupaten.',
                'kecamatan.required'      => 'Pilih kecamatan.',
            ]);
        }
        // Opsional: simpan alamat baru ke "Alamat Saya" (dilakukan di dalam transaksi di bawah)
        $simpanAlamat = $alamatManual && $request->boolean('simpan_alamat');

        $cartItems = session('cart', []);
        if (empty($cartItems)) {
            return redirect()->route('keranjang.index')->with('error', 'Keranjang kosong.');
        }

        // Harga & stok dihitung ulang dari database (harga sudah termasuk diskon), bukan dari session.
        $baris = [];
        foreach ($cartItems as $item) {
            $produk = Produk::find($item['id']);
            if (! $produk) {
                return redirect()->route('keranjang.index')->with('error', 'Ada produk di keranjang yang sudah tidak tersedia.');
            }

            $jenis    = ($item['mode'] ?? 'beli') === 'sewa' ? 'sewa' : 'jual';
            $tersedia = $produk->stokTersedia($jenis, $item['ukuran'] ?? null, $item['warna'] ?? null);
            if ($tersedia < (int) $item['qty']) {
                return redirect()->route('keranjang.index')->with('error',
                    $produk->nama . ': stok tidak mencukupi' . ($tersedia > 0 ? ' (tersedia ' . $tersedia . ' unit).' : ' (habis).'));
            }

            $baris[] = [
                'produk_id' => $produk->id,
                'nama'      => $produk->nama,
                'qty'       => (int) $item['qty'],
                'harga'     => $jenis === 'sewa' ? $produk->hargaSewaNet() : $produk->hargaJualNet(),
                'ukuran'    => $item['ukuran'] ?? '',
                'warna'     => $item['warna'] ?? '',
            ];
        }

        $subtotal = array_reduce($baris, fn ($c, $b) => $c + ($b['harga'] * $b['qty']), 0);
        $ongkir    = 0;
        $infoKirim = 'Ambil di toko';

        if ($request->metode_penerimaan === 'kirim') {
            try {
                $berat = $ro->beratKeranjang($cartItems);
                $opsi  = $ro->hitungOngkir((int) $request->tujuan_id, $berat, strtolower($request->kurir));
            } catch (\Throwable $e) {
                \Log::warning('Checkout: hitung ongkir gagal', ['error' => $e->getMessage()]);
                return back()->withInput()->with('error', 'Gagal menghitung ongkir. Coba lagi sebentar.');
            }

            $dipilih = collect($opsi)->first(fn($o) => strcasecmp($o['layanan'], $request->layanan) === 0);

            if (! $dipilih) {
                return back()->withInput()->with('error', 'Layanan pengiriman yang dipilih tidak tersedia.');
            }

            $ongkir    = (int) $dipilih['biaya'];
            $infoKirim = 'Kirim ke: ' . $request->alamat
                . ' | Penerima: ' . $request->nama_penerima
                . ' | WA: ' . $request->no_wa
                . ' | Tujuan: ' . ($request->tujuan_label ?: $request->tujuan_id)
                . ' | ' . strtoupper($dipilih['kurir']) . ' ' . $dipilih['layanan']
                . ' (est. ' . $dipilih['etd'] . ')'
                . ' | Berat: ' . $berat . ' gr'
                . ' | Ongkir: Rp ' . number_format($ongkir, 0, ',', '.');
        }

        $total = $subtotal + $ongkir;

        // Ukuran & warna per item dicatat di awal catatan, urut sama dengan detail transaksi:
        // "[Ukuran: M · Warna: Merah] [-] ..." (belum ada kolom khusus di detail_transaksi)
        $rincian = collect($baris)->map(function ($b) {
            $spek = collect([$b['ukuran'] ? 'Ukuran: ' . $b['ukuran'] : null, $b['warna'] ? 'Warna: ' . $b['warna'] : null])
                ->filter()->implode(' · ');

            return '[' . ($spek ?: '-') . ']';
        })->implode(' ');
        $infoKirim = $rincian . ' ' . $infoKirim;

        $transaksi = DB::transaction(function () use ($request, $baris, $total, $infoKirim, $ongkir, $alamatDipilih, $simpanAlamat) {
            if ($simpanAlamat) {
                $this->simpanAlamatBaru($request);
            }

            $transaksi = Transaksi::create([
                'kode_transaksi'    => 'ALN-BLI-' . strtoupper(substr(uniqid(), -8)),
                'user_id'           => Auth::id(),
                'jenis'             => 'beli',
                'kanal'             => 'online',
                'tanggal'           => now()->toDateString(),
                'total'             => $total,
                'status_pembayaran' => 'pending',
                'catatan'           => $infoKirim,
            ]);

            foreach ($baris as $b) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id'    => $b['produk_id'],
                    'jumlah'       => $b['qty'],
                    'harga_satuan' => $b['harga'],
                    'ukuran'       => $b['ukuran'] ? mb_substr((string) $b['ukuran'], 0, 20) : null,
                    'warna'        => $b['warna'] ? mb_substr((string) $b['warna'], 0, 100) : null,
                ]);
            }

            Pembayaran::create([
                'transaksi_id'    => $transaksi->id,
                'jenis_bayar'     => 'lunas',
                'jumlah'          => $total,
                'metode'          => $request->metode_bayar,
                'status_midtrans' => 'pending',
                'tanggal_bayar'   => now(),
            ]);

            if ($request->metode_penerimaan === 'kirim') {
                Pengiriman::create([
                    'transaksi_id'     => $transaksi->id,
                    'provinsi_tujuan'  => $alamatDipilih?->provinsi ?? $request->provinsi ?? auth()->user()->provinsi_nama ?? '-',
                    'kota_tujuan'      => $alamatDipilih?->kota_kabupaten ?? $request->kota_kabupaten ?? auth()->user()->kota_nama ?? '-',
                    'kecamatan_tujuan' => $request->tujuan_label ?? '-',
                    'kode_pos_tujuan'  => $alamatDipilih?->kode_pos ?? $request->kode_pos ?? auth()->user()->kode_pos ?? null,
                    'alamat_tujuan'    => $request->alamat,
                    'status_kirim'     => 'menunggu',
                    'kurir'            => $request->kurir,
                    'layanan_ongkir'   => $request->layanan,
                    'biaya_kirim'      => $ongkir,
                ]);
            }

            return $transaksi;
        });

        session()->forget('cart');

        // Tunai: tidak ada pembayaran online. Pesanan langsung tercatat (pending) di admin,
        // pelanggan membayar saat ambil di toko, lalu admin menekan "Tandai Lunas".
        if ($request->metode_bayar === 'tunai') {
            return redirect()->route('akun.dashboard')
                ->with('success', 'Pemesanan berhasil! Kode ' . $transaksi->kode_transaksi . '. Silakan bayar tunai saat mengambil pesanan di toko.');
        }

        return redirect()->route('beli.konfirmasi', $transaksi->id);
    }
}