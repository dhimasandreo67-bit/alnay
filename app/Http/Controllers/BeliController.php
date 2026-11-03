<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
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

        $dataKurang = [];
        if (empty($user->no_hp)) $dataKurang[] = 'Nomor WhatsApp';
        if (empty($user->alamat)) $dataKurang[] = 'Alamat Lengkap';
        if (empty($user->provinsi_nama)) $dataKurang[] = 'Provinsi';
        if (empty($user->kota_nama)) $dataKurang[] = 'Kota/Kabupaten';
        if (empty($user->kecamatan)) $dataKurang[] = 'Kecamatan';
        if (empty($user->kode_pos)) $dataKurang[] = 'Kode Pos';

        if (!empty($dataKurang)) {
            return redirect()->route('akun.profil.edit')
                ->with('error', 'Lengkapi data profil Anda terlebih dahulu: ' . implode(', ', $dataKurang) . '.');
        }

        $beliData = session('beli_data');
        if (!$beliData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data pembelian tidak ditemukan. Silakan pilih produk kembali.']);
        }

        $produk = Produk::with('kategori')->findOrFail($beliData['produk_id']);
        $qty = $beliData['qty'] ?? 1;

        // ==== VALIDASI STOK ====
        if ($produk->stok <= 0) {
            session()->forget('beli_data');
            return redirect()->route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli'])
                ->withErrors(['stok' => 'Maaf, stok produk ini sudah habis. Silakan pilih produk lain.']);
        }
        if ($produk->stok < $qty) {
            return redirect()->route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli'])
                ->withErrors(['stok' => 'Stok tidak mencukupi. Tersedia: ' . $produk->stok . ' unit.']);
        }

        $varian   = $beliData['varian'] ?? 'Emas Klasik';
        $subtotal = $produk->harga_jual * $qty;

        // Ongkir belum dihitung di sini: pembeli memilih tujuan dulu di halaman checkout,
        // lalu daftar layanan diambil lewat route beli.ongkir.
        $ongkir       = 0;
        $daftarOngkir = [];
        $total        = $subtotal;

        return view('customer.beli-checkout', compact(
            'produk', 'beliData', 'qty', 'varian', 'subtotal', 'ongkir', 'total', 'user', 'daftarOngkir'
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
            'varian' => ['nullable', 'string'],
        ]);

        session(['beli_data' => $validated]);

        return redirect()->route('beli.checkout');
    }

    public function store(Request $request, RajaOngkir $ro)
    {
        $validated = $request->validate([
            'nama_penerima'  => ['required', 'string', 'max:100'],
            'no_wa'          => ['required', 'string', 'max:20'],
            'alamat'         => ['required', 'string', 'max:500'],
            'catatan'        => ['nullable', 'string'],
            'metode_bayar'   => ['required', 'in:qris,va,ewallet'],
            'tujuan_id'      => ['required', 'integer'],
            'tujuan_label'   => ['nullable', 'string', 'max:255'],
            'kurir'          => ['required', 'string', 'max:20'],
            'layanan_ongkir' => ['required', 'string', 'max:20'],
        ], [
            'tujuan_id.required'      => 'Pilih kelurahan/kecamatan tujuan pengiriman.',
            'kurir.required'          => 'Pilih kurir dan layanan pengiriman.',
            'layanan_ongkir.required' => 'Pilih kurir dan layanan pengiriman.',
        ]);

        $beliData = session('beli_data');
        if (!$beliData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data pembelian tidak ditemukan.']);
        }

        $produk = Produk::findOrFail($beliData['produk_id']);
        $qty    = (int) ($beliData['qty'] ?? 1);

        // ==== VALIDASI STOK FINAL ====
        if ($produk->stok <= 0) {
            session()->forget('beli_data');
            return redirect()->route('koleksi.index')
                ->withErrors(['stok' => 'Maaf, stok produk ini sudah habis.']);
        }
        if ($produk->stok < $qty) {
            return redirect()->route('koleksi.index')
                ->withErrors(['stok' => 'Stok tidak mencukupi. Tersedia: ' . $produk->stok . ' unit.']);
        }

        $subtotal = $produk->harga_jual * $qty;

        // ==== HITUNG ULANG ONGKIR DI SERVER (angka dari browser tidak dipercaya) ====
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
        $total  = $subtotal + $ongkir;

        $transaksi = DB::transaction(function () use ($validated, $produk, $qty, $total, $ongkir, $dipilih) {
            $transaksi = Transaksi::create([
                'kode_transaksi'    => 'ALN-BL-' . strtoupper(substr(uniqid(), -8)),
                'user_id'           => auth()->id(),
                'jenis'             => 'beli',
                'kanal'             => 'online',
                'tanggal'           => now()->toDateString(),
                'total'             => $total,
                'status_pembayaran' => 'pending',
                'catatan'           => $validated['catatan'] ?? null,
            ]);

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id'    => $produk->id,
                'jumlah'       => $qty,
                'harga_satuan' => $produk->harga_jual,
            ]);

            Pembayaran::create([
                'transaksi_id'    => $transaksi->id,
                'jenis_bayar'     => 'lunas',
                'jumlah'          => $total,
                'metode'          => $validated['metode_bayar'],
                'status_midtrans' => 'pending',
                'tanggal_bayar'   => now(),
            ]);

            $tujuan = $validated['tujuan_label'] ?? null;

            Pengiriman::create([
                'transaksi_id'     => $transaksi->id,
                'provinsi_tujuan'  => auth()->user()->provinsi_nama ?? '-',
                'kota_tujuan'      => auth()->user()->kota_nama ?? '-',
                'kecamatan_tujuan' => auth()->user()->kecamatan ?? '-',
                'kode_pos_tujuan'  => auth()->user()->kode_pos ?? null,
                'alamat_tujuan'    => $validated['alamat'] . ($tujuan ? ' | Tujuan: ' . $tujuan : ''),
                'status_kirim'     => 'menunggu',
                'kurir'            => $dipilih['kurir'],
                'layanan_ongkir'   => $dipilih['layanan'],
                'biaya_kirim'      => $ongkir,
            ]);

            return $transaksi;
        });

        session()->forget('beli_data');

        return redirect()->route('beli.konfirmasi', $transaksi->id)
            ->with('success', 'Pesanan dibuat. Silakan selesaikan pembayaran QRIS.');
    }

    public function konfirmasi($id)
    {
        $transaksi = Transaksi::with(['detail.produk', 'pembayaran', 'pengiriman'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $produk   = $transaksi->detail->first()->produk;
        $qty      = $transaksi->detail->first()->jumlah;
        $varian   = 'Emas Klasik';
        $subtotal = $transaksi->detail->first()->harga_satuan * $qty;
        $ongkir   = $transaksi->pengiriman->biaya_kirim ?? self::ONGKIR_DEFAULT;
        $total    = $transaksi->total;

        return view('customer.beli-konfirmasi', compact(
            'transaksi', 'produk', 'qty', 'varian', 'subtotal', 'ongkir', 'total'
        ));
    }
}