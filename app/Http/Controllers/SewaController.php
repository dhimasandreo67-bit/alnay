<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class SewaController extends Controller
{
    public function checkout(Request $request)
    {
        // Cek apakah user sudah login
        if (!auth()->check()) {
            return redirect()->route('masuk')
                ->with('error', 'Silakan masuk terlebih dahulu untuk melanjutkan sewa.');
        }

        $user = auth()->user();
        // Cek kelengkapan data profil (no_hp & alamat wajib)
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

        // Ambil data dari session
        $sewaData = session('sewa_data');

        if (!$sewaData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data sewa tidak ditemukan. Silakan pilih produk kembali.']);
        }

        $produk = Produk::with('kategori')->findOrFail($sewaData['produk_id']);

        $durasi = $sewaData['durasi'];
        $hargaSewa = $produk->harga_sewa;
        $dendaPerHari = $produk->denda_per_hari_telat;

        $totalSewa = $hargaSewa + (max(0, $durasi - 1) * $dendaPerHari);
        $deposit = config('alnay.deposit_ktp', 200000);
        $dp = config('alnay.dp_sewa', 1000000);

        // TAMBAHKAN INI
        $total = $totalSewa;

        return view('customer.sewa-checkout', compact(
            'produk', 'sewaData', 'totalSewa', 'deposit', 'dp', 'durasi', 'total'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'metode_penerimaan' => ['required', 'in:ambil,kirim'],
            'metode_ktp' => ['required', 'in:fisik,digital'],
            'nama_penerima' => ['nullable', 'string', 'max:100'],
            'no_wa' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'metode_bayar' => ['required', 'in:qris,va,ewallet'],
        ]);

        $sewaData = session('sewa_data');
        if (!$sewaData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data sewa tidak ditemukan.']);
        }

        $produk = \App\Models\Produk::findOrFail($sewaData['produk_id']);
        $durasi = $sewaData['durasi'];
        $hargaSewa = $produk->harga_sewa;
        $dendaPerHari = $produk->denda_per_hari_telat;
        $totalSewa = $hargaSewa + (max(0, $durasi - 1) * $dendaPerHari);
        $deposit = $validated['metode_ktp'] === 'digital' ? config('alnay.deposit_ktp', 200000) : 0;
        $dp = config('alnay.dp_sewa', 1000000);

        // Simpan transaksi
        $transaksi = \App\Models\Transaksi::create([
            'kode_transaksi' => 'ALN-SW-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => auth()->id(),
            'jenis' => 'sewa',
            'kanal' => 'online',
            'tanggal' => now()->toDateString(),
            'total' => $totalSewa + $deposit,
            'status_pembayaran' => 'dp',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // Simpan detail transaksi
        \App\Models\DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => $produk->id,
            'jumlah' => 1,
            'harga_satuan' => $hargaSewa,
        ]);

        // Simpan sewa
        \App\Models\Sewa::create([
            'transaksi_id' => $transaksi->id,
            'tanggal_pinjam' => $sewaData['tanggal_ambil'],
            'tanggal_kembali_rencana' => $sewaData['tanggal_kembali'],
            'status' => 'aktif',
        ]);

        // Simpan jaminan
        \App\Models\JaminanSewa::create([
            'sewa_id' => $transaksi->sewa->id,
            'metode_jaminan' => $validated['metode_ktp'] === 'fisik' ? 'ktp_tatap_muka' : 'ktp_upload_deposit',
            'jumlah_deposit' => $deposit,
            'status_verifikasi' => 'pending',
            'status_pengembalian_deposit' => 'belum',
        ]);

        // Simpan pembayaran DP
        \App\Models\Pembayaran::create([
            'transaksi_id' => $transaksi->id,
            'jenis_bayar' => 'dp',
            'jumlah' => $dp,
            'metode' => $validated['metode_bayar'],
            'status_midtrans' => 'pending',
            'tanggal_bayar' => now(),
        ]);

        // Simpan pengiriman (jika dikirim)
        if ($validated['metode_penerimaan'] === 'kirim' && !empty($validated['alamat'])) {
            \App\Models\Pengiriman::create([
                'transaksi_id' => $transaksi->id,
                'provinsi_tujuan' => auth()->user()->provinsi ?? '-',
                'kota_tujuan' => auth()->user()->kota_kabupaten ?? '-',
                'kecamatan_tujuan' => auth()->user()->kecamatan ?? '-',
                'kode_pos_tujuan' => auth()->user()->kode_pos ?? null,
                'alamat_tujuan' => $validated['alamat'],
                'status_kirim' => 'menunggu',
            ]);
        }

        // Hapus session (opsional)
        session()->forget('sewa_data');

        return redirect()->route('sewa.konfirmasi', $transaksi->id)
            ->with('success', 'Transaksi sewa berhasil dibuat.');
    }

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

    

    public function konfirmasi($id)
    {
        $transaksi = \App\Models\Transaksi::with(['detail.produk', 'sewa', 'pembayaran'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $produk = $transaksi->detail->first()->produk;
        $total = $transaksi->total;
        $dp = $transaksi->pembayaran->where('jenis_bayar', 'dp')->sum('jumlah');
        $deposit = $transaksi->sewa->jaminan->jumlah_deposit ?? 0;

        return view('customer.sewa-konfirmasi', compact('transaksi', 'produk', 'total', 'dp', 'deposit'));
    }
}