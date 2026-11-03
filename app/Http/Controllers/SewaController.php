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
        if (!Auth::check()) {
            return redirect()->route('masuk')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $user = Auth::user();
        $dataKurang = [];
        if (empty($user->no_hp)) $dataKurang[] = 'Nomor WhatsApp';
        if (empty($user->alamat)) $dataKurang[] = 'Alamat Lengkap';

        if (!empty($dataKurang)) {
            return redirect()->route('akun.profil.edit')
                ->with('error', 'Lengkapi data profil Anda: ' . implode(', ', $dataKurang));
        }

        // Cek apakah dari Keranjang atau Langsung Beli
        $keranjangSewa = session('keranjang_sewa', []);
        $sewaData = session('sewa_data');

        if (empty($keranjangSewa) && !$sewaData) {
            return redirect()->route('koleksi.index')->withErrors(['error' => 'Keranjang kosong. Silakan pilih produk.']);
        }

        // Jika dari keranjang, gunakan data keranjang
        $items = [];
        if (!empty($keranjangSewa)) {
            foreach ($keranjangSewa as $key => $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                $items[] = [
                    'produk' => $produk,
                    'ukuran' => $item['ukuran'],
                    'tanggal_ambil' => $item['tanggal_ambil'],
                    'tanggal_kembali' => $item['tanggal_kembali'],
                    'durasi' => $item['durasi'],
                    'total_sewa' => $item['total_sewa'],
                    'dp' => $item['dp'],
                ];
            }
        } else {
            // Jika langsung beli (1 item)
            $produk = Produk::with('kategori')->findOrFail($sewaData['produk_id']);
            $items[] = [
                'produk' => $produk,
                'ukuran' => $sewaData['ukuran'],
                'tanggal_ambil' => $sewaData['tanggal_ambil'],
                'tanggal_kembali' => $sewaData['tanggal_kembali'],
                'durasi' => $sewaData['durasi'],
                'total_sewa' => $sewaData['total_sewa'],
                'dp' => $produk->dp ?? 0,
            ];
        }

        $totalKeseluruhan = collect($items)->sum('total_sewa');
        $deposit = config('alnay.deposit_ktp', 200000);

        return view('customer.sewa-checkout', compact('items', 'totalKeseluruhan', 'deposit'));
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

        // ==== VALIDASI STOK ====
        $produk = Produk::findOrFail($validated['produk_id']);
        if ($produk->stok <= 0) {
            return back()->withErrors(['stok' => 'Maaf, produk ini sedang tidak tersedia.']);
        }
        // =========================

        session(['sewa_data' => $validated]);

        return redirect()->route('sewa.checkout');
    }

    /**
     * Memproses Checkout & Menyimpan Transaksi ke Database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'metode_penerimaan' => ['required', 'in:ambil,kirim'],
            'metode_ktp' => ['required', 'in:fisik,digital'],
            'foto_ktp' => ['required_if:metode_ktp,digital', 'nullable', 'image', 'max:2048'],
            'nama_penerima' => ['required_if:metode_penerimaan,kirim', 'nullable', 'string'],
            'no_wa' => ['required_if:metode_penerimaan,kirim', 'nullable', 'string'],
            'alamat' => ['required_if:metode_penerimaan,kirim', 'nullable', 'string'],
            'metode_bayar' => ['required', 'in:qris,va,ewallet'],
            'setuju' => ['accepted'],
        ]);

        $keranjangSewa = session('keranjang_sewa', []);
        $sewaData = session('sewa_data');

        if (empty($keranjangSewa) && !$sewaData) {
            return redirect()->route('koleksi.index')->withErrors(['error' => 'Data sewa tidak ditemukan.']);
        }

        // Tentukan items yang akan diproses
        $items = !empty($keranjangSewa) ? collect($keranjangSewa)->map(fn($i) => (object)$i)->toArray() : [(object)$sewaData];

        $totalSewaKeseluruhan = 0;
        $deposit = $validated['metode_ktp'] === 'digital' ? config('alnay.deposit_ktp', 200000) : 0;

        // Hitung total
        foreach ($items as $item) {
            $totalSewaKeseluruhan += $item->total_sewa;
        }

        // Upload KTP
        $fotoKtpPath = $request->hasFile('foto_ktp') ? $request->file('foto_ktp')->store('ktp', 'public') : null;

        // 1. Buat Transaksi Induk
        $transaksi = Transaksi::create([
            'kode_transaksi' => 'ALN-SW-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => Auth::id(),
            'jenis' => 'sewa',
            'kanal' => 'online',
            'tanggal' => now()->toDateString(),
            'total' => $totalSewaKeseluruhan + $deposit,
            'status_pembayaran' => 'dp',
        ]);

        // 2. Loop Simpan Detail & Sewa
        foreach ($items as $item) {
            $produk = Produk::findOrFail($item->produk_id);

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'jumlah' => 1,
                'harga_satuan' => $produk->harga_sewa,
            ]);

            $sewa = Sewa::create([
                'transaksi_id' => $transaksi->id,
                'tanggal_pinjam' => $item->tanggal_ambil,
                'tanggal_kembali_rencana' => $item->tanggal_kembali,
                'status' => 'aktif',
            ]);

            JaminanSewa::create([
                'sewa_id' => $sewa->id,
                'metode_jaminan' => $validated['metode_ktp'] === 'fisik' ? 'ktp_tatap_muka' : 'ktp_upload_deposit',
                'foto_ktp' => $fotoKtpPath,
                'jumlah_deposit' => $deposit / count($items), // Bagi rata deposit per item
                'status_verifikasi' => 'pending',
            ]);
        }

        // 3. Simpan Pembayaran
        $dp = array_sum(array_map(fn($i) => $i->dp, $items));
        Pembayaran::create([
            'transaksi_id' => $transaksi->id,
            'jenis_bayar' => 'dp',
            'jumlah' => $dp + $deposit,
            'metode' => $validated['metode_bayar'],
            'status_midtrans' => 'pending',
            'tanggal_bayar' => now(),
        ]);

        // 4. Pengiriman
        if ($validated['metode_penerimaan'] === 'kirim') {
            Pengiriman::create([
                'transaksi_id' => $transaksi->id,
                'provinsi_tujuan' => Auth::user()->provinsi_nama ?? '-',
                'kota_tujuan' => Auth::user()->kota_nama ?? '-',
                'kecamatan_tujuan' => Auth::user()->kecamatan ?? '-',
                'alamat_tujuan' => $validated['alamat'],
                'status_kirim' => 'menunggu',
            ]);
        }

        // 5. Bersihkan Session
        session()->forget(['keranjang_sewa', 'sewa_data']);

        return redirect()->route('sewa.konfirmasi', $transaksi->id)->with('success', 'Transaksi sewa berhasil!');
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

    public function show($id)
    {
        $sewa = Sewa::with(['transaksi.user', 'transaksi.detail.produk', 'jaminan'])
            ->findOrFail($id);

        return view('admin.sewa.show', compact('sewa'));
    }

    /**
     * Menghapus item dari Keranjang Sewa.
     */
    public function hapusKeranjang($id)
    {
        // 1. Ambil session keranjang sewa
        $keranjangSewa = session()->get('keranjang_sewa', []);

        // 2. Cek apakah item ada di keranjang
        if (isset($keranjangSewa[$id])) {
            unset($keranjangSewa[$id]); // Hapus item
            session()->put('keranjang_sewa', $keranjangSewa); // Simpan kembali session
        }

        // 3. Kembalikan ke halaman keranjang sewa dengan pesan sukses
        return redirect()->back()->with('success', 'Item berhasil dihapus dari Keranjang Sewa.');
    }

    /**
     * Menghapus beberapa item sekaligus dari Keranjang Sewa.
     */
    public function hapusPilihanKeranjang(Request $request)
    {
        $ids = $request->input('ids', []);
        $keranjangSewa = session()->get('keranjang_sewa', []);

        foreach ($ids as $id) {
            if (isset($keranjangSewa[$id])) {
                unset($keranjangSewa[$id]);
            }
        }

        session()->put('keranjang_sewa', $keranjangSewa);
        return redirect()->back()->with('success', count($ids) . ' item berhasil dihapus dari Keranjang Sewa.');
    }
}
