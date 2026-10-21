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

        // Hitung total
        $durasi = $sewaData['durasi'];
        $hargaSewa = $produk->harga_sewa;
        $dendaPerHari = $produk->denda_per_hari_telat;

        $totalSewa = $hargaSewa + (max(0, $durasi - 1) * $dendaPerHari);
        $deposit = config('alnay.deposit_ktp', 200000);
        $dp = config('alnay.dp_sewa', 1000000);

        return view('customer.sewa-checkout', compact(
            'produk', 'sewaData', 'totalSewa', 'deposit', 'dp', 'durasi'
        ));
    }

    public function store(Request $request)
    {
        // Simpan data ke session (sementara, belum ke database)
        $validated = $request->validate([
            'metode_penerimaan' => ['required', 'in:ambil,kirim'],
            'metode_ktp' => ['required', 'in:fisik,digital'],
            'nama_penerima' => ['nullable', 'string', 'max:100'],
            'no_wa' => ['nullable', 'string', 'max:20'],
            'alamat' => ['required_if:metode_penerimaan,kirim', 'nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'metode_bayar' => ['required', 'in:qris,va,ewallet'],
        ]);

        // Gabungkan dengan data sewa dari session
        $sewaData = session('sewa_data');
        $sewaData['checkout'] = $validated;
        session(['sewa_data' => $sewaData]);

        // Redirect ke halaman konfirmasi
        return redirect()->route('sewa.konfirmasi');
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

    

    public function konfirmasi()
    {
        $sewaData = session('sewa_data');
        if (!$sewaData) {
            return redirect()->route('koleksi.index');
        }

        $produk = Produk::findOrFail($sewaData['produk_id']);

        // Hitung ulang total
        $durasi = $sewaData['durasi'];
        $hargaSewa = $produk->harga_sewa;
        $dendaPerHari = $produk->denda_per_hari_telat;
        $totalSewa = $hargaSewa + (max(0, $durasi - 1) * $dendaPerHari);
        $deposit = config('alnay.deposit_ktp', 200000);
        $dp = config('alnay.dp_sewa', 1000000);

        return view('customer.sewa-konfirmasi', compact(
            'produk', 'sewaData', 'totalSewa', 'deposit', 'dp', 'durasi'
        ));
    }
}