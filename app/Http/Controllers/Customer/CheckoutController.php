<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Pengiriman;
use App\Services\RajaOngkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = session('cart', []);
        if (empty($cartItems)) {
            return redirect()->route('keranjang.index')->with('error', 'Keranjang Anda kosong.');
        }

        $total = array_reduce($cartItems, fn($c, $i) => $c + ($i['harga'] * $i['qty']), 0);
        $user = auth()->user();

        return view('customer.checkout', compact('cartItems', 'total', 'user'));
    }

    public function store(Request $request, RajaOngkir $ro)
    {
        $request->validate([
            'metode_penerimaan' => 'required|in:ambil,kirim',
            'metode_bayar'      => 'required|in:qris,va,ewallet',
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
        $dipilih   = null;
        $berat     = null;
        $infoKirim = 'Ambil di studio';

        // Ongkir SELALU dihitung ulang di server, bukan dari angka di browser.
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
                return back()->withInput()->with('error', 'Layanan pengiriman yang dipilih tidak tersedia. Pilih ulang.');
            }

            $ongkir    = (int) $dipilih['biaya'];
            $infoKirim = 'Kirim ke: ' . $request->alamat
                . ' | Penerima: ' . $request->nama_penerima
                . ' | WA: ' . $request->no_wa
                . ' | Tujuan: ' . ($request->tujuan_label ?: $request->tujuan_id)
                . ' | ' . strtoupper($dipilih['kurir']) . ' ' . $dipilih['layanan']
                . ' (est. ' . ($dipilih['etd'] ?: '-') . ')'
                . ' | Berat: ' . $berat . ' gr'
                . ' | Ongkir: Rp ' . number_format($ongkir, 0, ',', '.');
        }

        $total = $subtotal + $ongkir;

        // Rincian ukuran & warna per item dicatat di catatan (belum ada kolom khusus di detail_transaksi)
        $rincian = collect($baris)->map(function ($b) {
            $spek = collect([$b['ukuran'] ? 'Ukuran ' . $b['ukuran'] : null, $b['warna'] ? 'Warna ' . $b['warna'] : null])
                ->filter()->implode(', ');

            return $b['nama'] . ($spek ? ' (' . $spek . ')' : '') . ' x' . $b['qty'];
        })->implode('; ');
        $infoKirim = 'Item: ' . $rincian . ' | ' . $infoKirim;

        $transaksi = DB::transaction(function () use ($request, $baris, $total, $infoKirim, $ongkir) {
            $transaksi = Transaksi::create([
                'kode_transaksi'    => 'ALN-BLI-' . strtoupper(substr(uniqid(), -8)),
                'user_id'           => Auth::id(),
                'jenis'             => 'beli',
                'kanal'             => 'online',
                'tanggal'           => now()->toDateString(),
                'total'             => $total,
                'status_pembayaran' => 'pending',
                // Dibatasi supaya tidak melebihi panjang kolom.
                'catatan'           => Str::limit($infoKirim, 250, ''),
            ]);

            foreach ($baris as $b) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id'    => $b['produk_id'],
                    'jumlah'       => $b['qty'],
                    'harga_satuan' => $b['harga'],
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

            $wilayah = RajaOngkir::uraikanLabel($request->tujuan_label);
            if ($dipilih) {
                Pengiriman::create([
                    'transaksi_id'     => $transaksi->id,
                    'provinsi_tujuan'  => $wilayah['provinsi'] ?: '-',
                    'kota_tujuan'      => $wilayah['kota'] ?: '-',
                    'kecamatan_tujuan' => Str::limit($request->tujuan_label ?: '-', 250, ''),
                    'kode_pos_tujuan'  => $wilayah['kode_pos'],
                    'alamat_tujuan'    => $request->alamat,
                    'status_kirim'     => 'menunggu',
                    // Nilai dari hasil API yang sudah dicocokkan, bukan dari input browser.
                    'kurir'            => $dipilih['kurir'],
                    'layanan_ongkir'   => $dipilih['layanan'],
                    'biaya_kirim'      => $ongkir,
                    // Data untuk pembuatan resi oleh admin.
                    'nama_penerima'    => $request->nama_penerima,
                    'no_wa_penerima'   => $request->no_wa,
                    'tujuan_id'        => (int) $request->tujuan_id,
                    'berat_gram'       => $berat,
                ]);
            }

            return $transaksi;
        });

        session()->forget('cart');

        return redirect()->route('beli.konfirmasi', $transaksi->id);
    }
}