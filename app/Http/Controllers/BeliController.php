<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Services\RajaOngkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BeliController extends Controller
{
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
        if (empty($user->kota_id)) $dataKurang[] = 'Kota (Kode RajaOngkir)';
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

        $varian = $beliData['varian'] ?? 'Emas Klasik';
        $subtotal = $produk->harga_jual * $qty;

        // ==== HITUNG ONGKIR REAL-TIME (KOMERCE) ====
        $ongkir = self::ONGKIR_DEFAULT;
        $daftarOngkir = [];

        try {
            $beratGram = max(1000, (int) ($produk->berat ?? 1000) * $qty);
            $destinationId = $user->kota_id ?? 31517; // fallback Sleman

            $service = app(RajaOngkir::class);

            $jne     = $service->hitungOngkir($destinationId, $beratGram, 'jne');
            $jnt     = $service->hitungOngkir($destinationId, $beratGram, 'jnt');
            $sicepat = $service->hitungOngkir($destinationId, $beratGram, 'sicepat');

            $daftarOngkir = array_merge($jne, $jnt, $sicepat);

            if (!empty($daftarOngkir)) {
                $termurah = collect($daftarOngkir)->sortBy('cost')->first();
                $ongkir = $termurah['cost'] ?? self::ONGKIR_DEFAULT;
            }
        } catch (\Throwable $e) {
            Log::warning('Ongkir error di checkout: ' . $e->getMessage());
        }
        // ==========================================

        $total = $subtotal + $ongkir;

        return view('customer.beli-checkout', compact(
            'produk', 'beliData', 'qty', 'varian', 'subtotal', 'ongkir', 'total', 'user', 'daftarOngkir'
        ));
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_penerima' => ['required', 'string', 'max:100'],
            'no_wa' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
            'metode_bayar' => ['required', 'in:qris,va,ewallet'],
            'kurir' => ['nullable', 'string', 'max:20'],
            'layanan_ongkir' => ['nullable', 'string', 'max:20'],
        ]);

        $beliData = session('beli_data');
        if (!$beliData) {
            return redirect()->route('koleksi.index')
                ->withErrors(['error' => 'Data pembelian tidak ditemukan.']);
        }

        $produk = Produk::findOrFail($beliData['produk_id']);
        $qty = $beliData['qty'] ?? 1;

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

        // ==== HITUNG ONGKIR FINAL (SERVER-SIDE) ====
        $ongkir = self::ONGKIR_DEFAULT;

        try {
            $beratGram = max(1000, (int) ($produk->berat ?? 1000) * $qty);
            $destinationId = auth()->user()->kota_id ?? 31517;

            $kurirDipilih = $validated['kurir'] ?? 'jne';
            $layananDipilih = $validated['layanan_ongkir'] ?? null;

            $service = app(RajaOngkir::class);
            $hasil = $service->hitungOngkir($destinationId, $beratGram, $kurirDipilih);

            if (!empty($hasil)) {
                if ($layananDipilih) {
                    $match = collect($hasil)->firstWhere('service', $layananDipilih);
                    $ongkir = $match['cost'] ?? collect($hasil)->sortBy('cost')->first()['cost'];
                } else {
                    $ongkir = collect($hasil)->sortBy('cost')->first()['cost'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Ongkir error saat store: ' . $e->getMessage());
        }
        // ==========================================

        $total = $subtotal + $ongkir;

        // Simpan transaksi
        $transaksi = Transaksi::create([
            'kode_transaksi' => 'ALN-BL-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => auth()->id(),
            'jenis' => 'beli',
            'kanal' => 'online',
            'tanggal' => now()->toDateString(),
            'total' => $total,
            'status_pembayaran' => 'pending',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // Simpan detail
        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => $produk->id,
            'jumlah' => $qty,
            'harga_satuan' => $produk->harga_jual,
        ]);

        // Simpan pembayaran
        Pembayaran::create([
            'transaksi_id' => $transaksi->id,
            'jenis_bayar' => 'lunas',
            'jumlah' => $total,
            'metode' => $validated['metode_bayar'],
            'status_midtrans' => 'pending',
            'tanggal_bayar' => now(),
        ]);

        // Simpan pengiriman (dengan ongkir dinamis)
        Pengiriman::create([
            'transaksi_id' => $transaksi->id,
            'provinsi_tujuan' => auth()->user()->provinsi_nama ?? '-',
            'kota_tujuan' => auth()->user()->kota_nama ?? '-',
            'kecamatan_tujuan' => auth()->user()->kecamatan ?? '-',
            'kode_pos_tujuan' => auth()->user()->kode_pos ?? null,
            'alamat_tujuan' => $validated['alamat'],
            'status_kirim' => 'menunggu',
            'kurir' => $validated['kurir'] ?? null,
            'layanan_ongkir' => $validated['layanan_ongkir'] ?? null,
            'biaya_kirim' => $ongkir,
        ]);

        session()->forget('beli_data');

        return redirect()->route('beli.konfirmasi', $transaksi->id)
            ->with('success', 'Pesanan dibuat. Silakan selesaikan pembayaran QRIS.');
    }

    public function konfirmasi($id)
    {
        $transaksi = Transaksi::with(['detail.produk', 'pembayaran', 'pengiriman'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $produk = $transaksi->detail->first()->produk;
        $qty = $transaksi->detail->first()->jumlah;
        $varian = 'Emas Klasik';
        $subtotal = $transaksi->detail->first()->harga_satuan * $qty;
        $ongkir = $transaksi->pengiriman->biaya_kirim ?? self::ONGKIR_DEFAULT;
        $total = $transaksi->total;

        return view('customer.beli-konfirmasi', compact(
            'transaksi', 'produk', 'qty', 'varian', 'subtotal', 'ongkir', 'total'
        ));
    }
}