<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Models\AlamatPengiriman;
use App\Services\RajaOngkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BeliController extends Controller
{
    /** Hanya dipakai untuk menampilkan pesanan lama yang belum menyimpan biaya kirim. */
    private const ONGKIR_DEFAULT = 45000;

    public function checkout(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('masuk')
                ->with('error', 'Silakan masuk terlebih dahulu untuk melanjutkan pembelian.');
        }

        $user = auth()->user();

        // Alamat tersimpan (utama tampil pertama). Tidak wajib: pembeli yang memilih
        // "Ambil di Toko" tidak perlu punya alamat, dan yang memilih kirim bisa mengisi manual.
        $alamatList = AlamatPengiriman::where('user_id', $user->id)
            ->orderByDesc('is_utama')
            ->orderByDesc('id')
            ->get();
        $alamatUtama = $alamatList->first();

        $beliData = session('beli_data');
        if (!$beliData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data pembelian tidak ditemukan. Silakan pilih produk kembali.']);
        }

        $produk = Produk::with('kategori')->findOrFail($beliData['produk_id']);
        $qty = $beliData['qty'] ?? 1;

        $ukuran = $beliData['ukuran'] ?? null;
        $varian = $beliData['varian'] ?? null;   // warna pilihan

        // ==== VALIDASI STOK (stok jual, per ukuran & warna) ====
        $stok = $produk->dapat_dijual ? $produk->stokTersedia('jual', $ukuran, $varian) : 0;
        if ($stok <= 0) {
            session()->forget('beli_data');
            return redirect()->route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli'])
                ->withErrors(['stok' => 'Maaf, stok pilihan ini sudah habis. Silakan pilih ukuran/warna lain.']);
        }
        if ($stok < $qty) {
            return redirect()->route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli'])
                ->withErrors(['stok' => 'Stok tidak mencukupi. Tersedia: ' . $stok . ' unit.']);
        }

        // Harga sudah termasuk diskon jual
        $subtotal = $produk->hargaJualNet() * $qty;

        $ongkir       = 0;
        $daftarOngkir = [];
        $total        = $subtotal;

        // Mengirimkan alamat tersimpan ke view
        return view('customer.beli-checkout', compact(
            'produk', 'beliData', 'qty', 'varian', 'subtotal', 'ongkir', 'total', 'user', 'daftarOngkir', 'alamatUtama', 'alamatList'
        ));
    }

    /** POST /beli/ongkir  -> daftar layanan untuk tujuan terpilih (berat dihitung di server). */
    public function ongkir(Request $request, RajaOngkir $ro)
    {
        $data = $request->validate([
            'tujuan_id' => ['required', 'integer'],
        ]);

        $beliData = session('beli_data');
        if (!$beliData) {
            return response()->json(['message' => 'Data pembelian tidak ditemukan.'], 422);
        }

        $produk = Produk::findOrFail($beliData['produk_id']);
        $qty    = (int) ($beliData['qty'] ?? 1);

        try {
            $berat = $ro->beratKeranjang([['id' => $produk->id, 'qty' => $qty]]);
            $opsi  = $ro->hitungSemuaKurir((int) $data['tujuan_id'], $berat);

            if (empty($opsi)) {
                return response()->json(['message' => 'Tidak ada layanan pengiriman untuk tujuan ini.'], 422);
            }

            return response()->json(['berat' => $berat, 'opsi' => $opsi]);
        } catch (\Throwable $e) {
            Log::warning('Ongkir beli gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghitung ongkir. Coba lagi.'], 503);
        }
    }

    public function simpanSession(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'ukuran' => ['nullable', 'string', 'max:50'],
            'varian' => ['nullable', 'string', 'max:100'],   // warna
        ]);

        $produk = Produk::findOrFail($validated['produk_id']);
        $stok = $produk->dapat_dijual
            ? $produk->stokTersedia('jual', $validated['ukuran'] ?? null, $validated['varian'] ?? null)
            : 0;

        if ($stok <= 0) {
            return back()->withErrors(['stok' => 'Maaf, stok pilihan ini sedang habis.']);
        }
        if ($validated['qty'] > $stok) {
            return back()->withErrors(['stok' => 'Jumlah melebihi stok yang tersedia (' . $stok . ' unit).']);
        }

        session(['beli_data' => $validated]);

        return redirect()->route('beli.checkout');
    }

    public function store(Request $request, RajaOngkir $ro)
    {
        $validated = $request->validate([
            'metode_penerimaan' => ['required', 'in:ambil,kirim'],
            'nama_penerima'     => ['required', 'string', 'max:100'],
            'no_wa'             => ['required', 'string', 'max:20'],
            'alamat'            => ['required_if:metode_penerimaan,kirim', 'nullable', 'string', 'max:500'],
            'catatan'           => ['nullable', 'string'],
            'metode_bayar'      => ['required', 'in:qris,va,ewallet,tunai'],
            'tujuan_id'         => ['required_if:metode_penerimaan,kirim', 'nullable', 'integer'],
            'tujuan_label'      => ['nullable', 'string', 'max:255'],
            'kurir'             => ['required_if:metode_penerimaan,kirim', 'nullable', 'string', 'max:20'],
            'layanan_ongkir'    => ['required_if:metode_penerimaan,kirim', 'nullable', 'string', 'max:50'],
        ], [
            'alamat.required_if'         => 'Alamat lengkap wajib diisi untuk pengiriman.',
            'tujuan_id.required_if'      => 'Pilih kelurahan/kecamatan tujuan pengiriman.',
            'kurir.required_if'          => 'Pilih kurir dan layanan pengiriman.',
            'layanan_ongkir.required_if' => 'Pilih kurir dan layanan pengiriman.',
        ]);

        $ambil = $validated['metode_penerimaan'] === 'ambil';

        // Tunai hanya boleh untuk ambil di toko
        if ($validated['metode_bayar'] === 'tunai' && ! $ambil) {
            return back()->withInput()
                ->withErrors(['metode_bayar' => 'Pembayaran tunai hanya tersedia untuk ambil di toko.']);
        }

        $beliData = session('beli_data');
        if (!$beliData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data pembelian tidak ditemukan.']);
        }

        $produk = Produk::findOrFail($beliData['produk_id']);
        $qty    = (int) ($beliData['qty'] ?? 1);

        $ukuran = $beliData['ukuran'] ?? null;
        $varian = $beliData['varian'] ?? null;

        // ==== VALIDASI STOK AWAL (stok jual, per ukuran & warna) ====
        // Pengecekan final yang mengikat dilakukan lagi di dalam transaksi (dengan kunci baris).
        $stok = $produk->dapat_dijual ? $produk->stokTersedia('jual', $ukuran, $varian) : 0;
        if ($stok <= 0) {
            session()->forget('beli_data');
            return redirect()->route('koleksi.index')
                ->withErrors(['stok' => 'Maaf, stok pilihan ini sudah habis.']);
        }
        if ($stok < $qty) {
            return redirect()->route('koleksi.index')
                ->withErrors(['stok' => 'Stok tidak mencukupi. Tersedia: ' . $stok . ' unit.']);
        }

        $subtotal = $produk->hargaJualNet() * $qty;

        // Ukuran & warna dicatat di awal catatan transaksi
        $spesifikasi = collect([
            $ukuran ? 'Ukuran: ' . $ukuran : null,
            $varian ? 'Warna: ' . $varian : null,
        ])->filter()->implode(' · ');
        $spesifikasi = $spesifikasi !== '' ? '[' . $spesifikasi . ']' : '';

        // ==== HITUNG ULANG ONGKIR DI SERVER (hanya bila dikirim) ====
        $ongkir  = 0;
        $dipilih = null;

        if (! $ambil) {
            try {
                $berat = $ro->beratKeranjang([['id' => $produk->id, 'qty' => $qty]]);
                $opsi  = $ro->hitungOngkir(
                    (int) $validated['tujuan_id'],
                    $berat,
                    strtolower($validated['kurir'])
                );
            } catch (\Throwable $e) {
                Log::warning('Ongkir error saat store: ' . $e->getMessage());
                return back()->withInput()
                    ->withErrors(['kurir' => 'Gagal menghitung ongkir. Coba lagi sebentar.']);
            }

            $dipilih = collect($opsi)->first(
                fn ($o) => strcasecmp($o['layanan'], $validated['layanan_ongkir']) === 0
            );

            if (!$dipilih) {
                return back()->withInput()
                    ->withErrors(['kurir' => 'Layanan pengiriman yang dipilih tidak tersedia. Pilih ulang.']);
            }

            $ongkir = (int) $dipilih['biaya'];
        }

        $total = $subtotal + $ongkir;

        // Catatan transaksi: ambil di toko tidak punya baris pengiriman, jadi info penerima ikut dicatat di sini.
        $catatanUser = trim((string) ($validated['catatan'] ?? ''));
        $catatan = $ambil
            ? trim($spesifikasi . ' Ambil di toko | Penerima: ' . $validated['nama_penerima']
                . ' | WA: ' . $validated['no_wa']
                . ($catatanUser !== '' ? ' | Catatan: ' . $catatanUser : ''))
            : (trim($spesifikasi . ' ' . $catatanUser) ?: null);

        $transaksi = DB::transaction(function () use ($validated, $produk, $qty, $total, $ongkir, $dipilih, $catatan, $ukuran, $varian, $ambil) {
            // Kunci baris produk lalu cek ulang stok: dua pembeli tidak bisa mengambil stok terakhir bersamaan.
            $terkunci = Produk::whereKey($produk->id)->lockForUpdate()->first();
            if (! $terkunci || $terkunci->stokTersedia('jual', $ukuran ?: null, $varian ?: null) < $qty) {
                return null;
            }

            $transaksi = Transaksi::create([
                'kode_transaksi'    => 'ALN-BL-' . strtoupper(substr(uniqid(), -8)),
                'user_id'           => auth()->id(),
                'jenis'             => 'beli',
                'kanal'             => 'online',
                'tanggal'           => now()->toDateString(),
                'total'             => $total,
                'status_pembayaran' => 'pending',
                'catatan'           => $catatan,
            ]);

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id'    => $produk->id,
                'jumlah'       => $qty,
                'harga_satuan' => $produk->hargaJualNet(),
                'ukuran'       => $ukuran ? mb_substr((string) $ukuran, 0, 20) : null,
                'warna'        => $varian ? mb_substr((string) $varian, 0, 100) : null,
            ]);

            Pembayaran::create([
                'transaksi_id'    => $transaksi->id,
                'jenis_bayar'     => 'lunas',
                'jumlah'          => $total,
                'metode'          => $validated['metode_bayar'],
                'status_midtrans' => 'pending',
                'tanggal_bayar'   => now(),
            ]);

            if (! $ambil) {
                $tujuan = $validated['tujuan_label'] ?? null;

                // Ekstrak kota & provinsi dari tujuan_label (jika formatnya "Kecamatan, Kota, Provinsi")
                $bagianLabel = array_map('trim', explode(',', (string) $tujuan));
                $kecamatanTujuan = count($bagianLabel) >= 1 ? $bagianLabel[0] : auth()->user()->kecamatan;
                $kotaTujuan = count($bagianLabel) >= 2 ? $bagianLabel[1] : auth()->user()->kota_nama;
                $provinsiTujuan = count($bagianLabel) >= 3 ? $bagianLabel[2] : auth()->user()->provinsi_nama;

                Pengiriman::create([
                    'transaksi_id'     => $transaksi->id,
                    'provinsi_tujuan'  => $provinsiTujuan ?? '-',
                    'kota_tujuan'      => $kotaTujuan ?? '-',
                    'kecamatan_tujuan' => $kecamatanTujuan ?? '-',
                    'kode_pos_tujuan'  => auth()->user()->kode_pos ?? null,
                    'alamat_tujuan'    => $validated['alamat'] . ($tujuan ? ' | Tujuan: ' . $tujuan : ''),
                    'status_kirim'     => 'menunggu',
                    'kurir'            => $dipilih['kurir'],
                    'layanan_ongkir'   => $dipilih['layanan'],
                    'biaya_kirim'      => $ongkir,
                    'nama_penerima'    => $validated['nama_penerima'],
                    'no_wa_penerima'   => $validated['no_wa'],
                ]);
            }

            // TAHAN STOK saat checkout (walau belum bayar), supaya pelanggan lain langsung
            // melihat stok yang sudah berkurang. Dikembalikan otomatis bila pesanan gagal/kedaluwarsa
            // (lihat command pesanan:lepas-stok dan MidtransQris::terapkan).
            $transaksi->tahanStok();

            return $transaksi;
        });

        if (! $transaksi) {
            return redirect()->route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli'])
                ->withErrors(['stok' => 'Maaf, stok pilihan ini baru saja habis atau berubah. Silakan periksa kembali.']);
        }

        session()->forget('beli_data');

        // Tunai: tidak ada pembayaran online. Pesanan tercatat (pending), pelanggan membayar saat
        // ambil di toko, lalu admin menandai lunas (sama seperti checkout keranjang).
        if ($validated['metode_bayar'] === 'tunai') {
            return redirect()->route('akun.dashboard')
                ->with('success', 'Pemesanan berhasil! Kode ' . $transaksi->kode_transaksi . '. Silakan bayar tunai saat mengambil pesanan di toko.');
        }

        return redirect()->route('beli.konfirmasi', $transaksi->id)
            ->with('success', $ambil
                ? 'Pesanan dibuat. Silakan selesaikan pembayaran, lalu ambil pesanan di toko.'
                : 'Pesanan dibuat. Silakan selesaikan pembayaran QRIS.');
    }

    public function konfirmasi($id)
    {
        $transaksi = Transaksi::with(['detail.produk', 'pembayaran', 'pengiriman'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $produk   = $transaksi->detail->first()->produk;
        $qty      = $transaksi->detail->first()->jumlah;

        $spesifikasi = [];
        if (preg_match('/^(?:\s*\[[^\]]*\])+/u', (string) $transaksi->catatan, $awal)) {
            preg_match_all('/\[([^\]]*)\]/u', $awal[0], $mm);
            $spesifikasi = $mm[1];
        }
        $subtotal = $transaksi->detail->first()->harga_satuan * $qty;
        // Ambil di toko tidak punya baris pengiriman -> ongkir 0. Default lama hanya untuk pesanan kirim tanpa biaya tersimpan.
        $ongkir   = $transaksi->pengiriman
            ? ($transaksi->pengiriman->biaya_kirim ?? self::ONGKIR_DEFAULT)
            : 0;
        $total    = $transaksi->total;

        return view('customer.beli-konfirmasi', compact(
            'transaksi', 'produk', 'qty', 'spesifikasi', 'subtotal', 'ongkir', 'total'
        ));
    }
}