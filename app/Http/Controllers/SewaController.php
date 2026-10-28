<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Sewa;
use App\Models\JaminanSewa;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SewaController extends Controller
{
    /**
     * Menampilkan halaman checkout sewa.
     */
    public function checkout(Request $request)
    {
        // 1. Cek Login
        if (!Auth::check()) {
            return redirect()->route('masuk')
                ->with('error', 'Silakan masuk terlebih dahulu untuk melanjutkan sewa.');
        }

        $user = Auth::user();

        // 2. Cek Kelengkapan Profil (No HP & Alamat)
        $dataKurang = [];
        if (empty($user->no_hp)) {
            $dataKurang[] = 'Nomor WhatsApp';
        }
        if (empty($user->alamat)) {
            $dataKurang[] = 'Alamat Lengkap';
        }

        if (!empty($dataKurang)) {
            return redirect()->route('akun.profil.edit')
                ->with('error', 'Lengkapi data profil Anda terlebih dahulu: ' . implode(', ', $dataKurang) . '.');
        }

        // 3. Ambil Data dari Session (dikirim dari halaman detail produk)
        $sewaData = session('sewa_data');

        if (!$sewaData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data sewa tidak ditemukan. Silakan pilih produk kembali.']);
        }

        // 4. Ambil Data Produk & Hitung Biaya
        $produk = Produk::with('kategori')->findOrFail($sewaData['produk_id']);

        $durasi = $sewaData['durasi'];
        $hargaSewa = $produk->harga_sewa;
        $dendaPerHari = $produk->denda_per_hari_telat;

        // Rumus: Harga 1 hari pertama + (sisa hari * denda per hari)
        $totalSewa = $hargaSewa + (max(0, $durasi - 1) * $dendaPerHari);
        
        // Ambil config dari config/alnay.php
        $deposit = config('alnay.deposit_ktp', 200000);
        $dp = $produk->dp ?? 0;

        $total = $totalSewa; // Total sebelum deposit/DP

        return view('customer.sewa-checkout', compact(
            'produk', 'sewaData', 'totalSewa', 'deposit', 'dp', 'durasi', 'total'
        ));
    }

    /**
     * Menyimpan data dari halaman Detail Produk ke Session.
     */
    public function simpanSession(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'tanggal_ambil' => ['required', 'date'],
            'tanggal_kembali' => ['required', 'date', 'after:tanggal_ambil'],
            'ukuran' => ['required', 'string'],
            'durasi' => ['required', 'integer', 'min:1'],
            'total_sewa' => ['required', 'numeric'],
        ]);

        session(['sewa_data' => $validated]);

        return redirect()->route('sewa.checkout');
    }

    /**
     * Memproses Checkout & Menyimpan Transaksi ke Database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'metode_penerimaan' => ['required', 'in:ambil,kirim'],
            'metode_ktp' => ['required', 'in:fisik,digital'],
            'foto_ktp' => ['required_if:metode_ktp,digital', 'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'nama_penerima' => ['required_if:metode_penerimaan,kirim', 'nullable', 'string', 'max:100'],
            'no_wa' => ['required_if:metode_penerimaan,kirim', 'nullable', 'string', 'max:20'],
            'alamat' => ['required_if:metode_penerimaan,kirim', 'nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'metode_bayar' => ['required', 'in:qris,va,ewallet'],
            'setuju' => ['accepted'],
        ]);

        $sewaData = session('sewa_data');
        if (!$sewaData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data sewa tidak ditemukan. Silakan ulangi dari awal.']);
        }

        // 2. Hitung Ulang Biaya (Server-Side)
        $produk = Produk::findOrFail($sewaData['produk_id']);
        $durasi = $sewaData['durasi'];
        $hargaSewa = $produk->harga_sewa;
        $dendaPerHari = $produk->denda_per_hari_telat;
        
        $totalSewa = $hargaSewa + (max(0, $durasi - 1) * $dendaPerHari);
        
        $deposit = $validated['metode_ktp'] === 'digital' ? config('alnay.deposit_ktp', 200000) : 0;
        $dp = config('alnay.dp_sewa', 1000000);

        // 3. Proses Upload Foto KTP (Jika Digital)
        $fotoKtpPath = null;
        if ($request->hasFile('foto_ktp')) {
            $fotoKtpPath = $request->file('foto_ktp')->store('ktp', 'public');
        }

        // 4. Simpan ke Tabel Transaksi
        $transaksi = Transaksi::create([
            'kode_transaksi' => 'ALN-SW-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => Auth::id(),
            'jenis' => 'sewa',
            'kanal' => 'online',
            'tanggal' => now()->toDateString(),
            'total' => $totalSewa + $deposit, // Total keseluruhan (Sewa + Deposit)
            'status_pembayaran' => 'dp',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // 5. Simpan Detail Transaksi
        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => $produk->id,
            'jumlah' => 1,
            'harga_satuan' => $hargaSewa,
        ]);

        // 6. Simpan ke Tabel Sewa
        $sewa = Sewa::create([
            'transaksi_id' => $transaksi->id,
            'tanggal_pinjam' => $sewaData['tanggal_ambil'],
            'tanggal_kembali_rencana' => $sewaData['tanggal_kembali'],
            'status' => 'aktif',
        ]);

        // 7. Simpan Jaminan & Foto KTP
        JaminanSewa::create([
            'sewa_id' => $sewa->id,
            'metode_jaminan' => $validated['metode_ktp'] === 'fisik' ? 'ktp_tatap_muka' : 'ktp_upload_deposit',
            'foto_ktp' => $fotoKtpPath, // Pastikan kolom ini ada di tabel jaminan_sewas
            'jumlah_deposit' => $deposit,
            'status_verifikasi' => 'pending',
            'status_pengembalian_deposit' => 'belum',
        ]);

        // 8. Simpan Tagihan DP
        Pembayaran::create([
            'transaksi_id' => $transaksi->id,
            'jenis_bayar' => 'dp',
            'jumlah' => $dp,
            'metode' => $validated['metode_bayar'],
            'status_midtrans' => 'pending',
            'tanggal_bayar' => now(),
        ]);

        // 9. Simpan Data Pengiriman (Jika Dikirim)
        if ($validated['metode_penerimaan'] === 'kirim') {
            Pengiriman::create([
                'transaksi_id' => $transaksi->id,
                'provinsi_tujuan' => Auth::user()->provinsi ?? '-',
                'kota_tujuan' => Auth::user()->kota_kabupaten ?? '-',
                'kecamatan_tujuan' => Auth::user()->kecamatan ?? '-',
                'kode_pos_tujuan' => Auth::user()->kode_pos ?? null,
                'alamat_tujuan' => $validated['alamat'],
                'status_kirim' => 'menunggu',
            ]);
        }

        // 10. Hapus Session & Redirect ke Halaman Konfirmasi
        session()->forget('sewa_data');

        return redirect()->route('sewa.konfirmasi', $transaksi->id)
            ->with('success', 'Transaksi sewa berhasil dibuat. Silakan lanjutkan pembayaran.');
    }

    /**
     * Webhook Midtrans (Sewa & Beli) — logikanya ada di MidtransQris.
     */
    public function webhook(Request $request)
    {
        return app(\App\Services\MidtransQris::class)->tanganiWebhook($request);
    }

    /**
     * Menampilkan Halaman Konfirmasi setelah Checkout.
     */
    public function konfirmasi($id)
    {
        $transaksi = Transaksi::with(['detail.produk', 'sewa', 'pembayaran'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $produk = $transaksi->detail->first()->produk;
        $total = $transaksi->total;
        $dp = $transaksi->pembayaran->where('jenis_bayar', 'dp')->sum('jumlah');
        
        // Ambil data deposit dari relasi JaminanSewa (pastikan relasi di model Sewa sudah diset)
        $deposit = $transaksi->sewa->jaminan->jumlah_deposit ?? 0;

        return view('customer.sewa-konfirmasi', compact('transaksi', 'produk', 'total', 'dp', 'deposit'));
    }

        public function charge($id)
    {
        try {
            $transaksi = Transaksi::findOrFail($id);
            
            // Panggil Service MidtransQris yang baru
            $qrData = app(\App\Services\MidtransQris::class)->qr($transaksi);

            if (isset($qrData['paid']) && $qrData['paid']) {
                return response()->json(['paid' => true]);
            }

            return response()->json([
                'qr_url' => $qrData['qr_url'] ?? null,
                'expires_in' => $qrData['expires_in'] ?? 0,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

        public function cekStatus($id)
    {
        try {
            $transaksi = Transaksi::findOrFail($id);
            $midtrans = app(\App\Services\MidtransQris::class);
            
            // Sinkronisasi status dari Midtrans ke database
            $status = $midtrans->sync($transaksi);
            
            return response()->json(['status' => $status]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

        /**
     * Tampilkan halaman Keranjang Sewa.
     */
    public function keranjangIndex()
    {
        $keranjangSewa = session()->get('keranjang_sewa', []);
        
        // Hitung total keseluruhan
        $totalKeseluruhan = collect($keranjangSewa)->sum('total_sewa');

        // Arahkan ke view yang sesuai (buat file ini nanti)
        return view('customer.sewa-keranjang', compact('keranjangSewa', 'totalKeseluruhan'));
    }

    /**
     * Tambah produk ke Keranjang Sewa dari halaman detail produk.
     */
    public function tambahKeKeranjang(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'tanggal_ambil' => 'required|date',
            'tanggal_kembali' => 'required|date|after:tanggal_ambil',
            'ukuran' => 'required|string',
            'durasi' => 'required|integer|min:1',
            'total_sewa' => 'required|numeric|min:0',
        ]);

        $produk = Produk::findOrFail($request->produk_id);

        // 2. Ambil data keranjang sewa dari session
        $keranjangSewa = session()->get('keranjang_sewa', []);

        // 3. Buat ID unik untuk item (kombinasi produk + tanggal + ukuran)
        $itemId = $produk->id . '_' . $request->tanggal_ambil . '_' . $request->ukuran;

        // 4. Simpan ke session
        $keranjangSewa[$itemId] = [
            'produk_id' => $produk->id,
            'nama' => $produk->nama,
            'foto_produk' => $produk->foto_produk, 
            'ukuran' => $request->ukuran,
            'tanggal_ambil' => $request->tanggal_ambil,
            'tanggal_kembali' => $request->tanggal_kembali,
            'durasi' => $request->durasi,
            'harga_sewa' => $produk->harga_sewa,
            'total_sewa' => $request->total_sewa,
            'dp' => $produk->dp ?? 0, // Ambil DP dinamis dari produk!
        ];

        session()->put('keranjang_sewa', $keranjangSewa);

        // 5. Redirect ke halaman Keranjang Sewa
        return redirect()->route('sewa.keranjang.index')->with('success', 'Produk berhasil ditambahkan ke Keranjang Sewa!');
    }
}
