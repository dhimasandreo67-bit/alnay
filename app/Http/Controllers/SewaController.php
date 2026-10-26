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
        $dp = config('alnay.dp_sewa', 1000000);

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

        public function webhook(Request $request)
    {
        // 1. Pastikan data yang dikirim lengkap
        if (!$request->has('order_id') || !$request->has('status_code') || !$request->has('gross_amount')) {
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        // 2. Validasi Keaslian Request
        if ($hashed == $request->signature_key) {
            
            // 3. Ambil kode transaksi dengan aman
            $orderIdParts = explode('-', $request->order_id);
            
            // Jika format order_id tidak sesuai (misal saat test dari Midtrans), abaikan saja
            if (count($orderIdParts) < 3) {
                return response('OK', 200)->header('Content-Type', 'text/plain');
            }

            $kodeTransaksi = $orderIdParts[0] . '-' . $orderIdParts[1] . '-' . $orderIdParts[2];

            $transaksi = Transaksi::where('kode_transaksi', $kodeTransaksi)->first();

            if ($transaksi) {
                $status = $request->transaction_status;
                $type = $request->payment_type;

                if ($status == 'capture' || $status == 'settlement') {
                    $transaksi->update(['status_pembayaran' => 'dp_dibayar']);
                    $transaksi->pembayaran()->where('jenis_bayar', 'dp')->update([
                        'status_midtrans' => 'success',
                        'metode' => $type,
                    ]);
                } elseif ($status == 'pending') {
                    $transaksi->update(['status_pembayaran' => 'pending']);
                } elseif ($status == 'deny' || $status == 'expire' || $status == 'cancel') {
                    $transaksi->update(['status_pembayaran' => 'gagal']);
                    $transaksi->pembayaran()->where('jenis_bayar', 'dp')->update([
                        'status_midtrans' => 'failed',
                    ]);
                }
            }
        }

        // 4. WAJIB: Balas dengan teks "OK" agar Midtrans hijau
        return response('OK', 200)->header('Content-Type', 'text/plain');
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
}