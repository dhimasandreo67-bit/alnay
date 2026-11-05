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

        // Harga net, DP, dan stok dihitung ulang dari database supaya sama dengan detail produk & admin.
        $sumber = ! empty($keranjangSewa) ? array_values($keranjangSewa) : [$sewaData];
        $items = [];
        foreach ($sumber as $data) {
            $produk = Produk::with('kategori')->findOrFail($data['produk_id']);

            $tersedia = $produk->bisa_disewa
                ? $produk->stokTersedia('sewa', $data['ukuran'] ?? null, $data['warna'] ?? null)
                : 0;
            if ($tersedia <= 0) {
                $tujuan = ! empty($keranjangSewa)
                    ? redirect()->route('sewa.keranjang.index')
                    : redirect()->route('produk.detail', ['sku' => $produk->sku]);

                return $tujuan->with('error', $produk->nama . ': stok sewa untuk pilihan ini sudah habis. Hapus item atau pilih ukuran/warna lain.');
            }

            $durasi = (int) $data['durasi'];
            $items[] = [
                'produk' => $produk,
                'ukuran' => $data['ukuran'] ?? '',
                'warna' => $data['warna'] ?? '',
                'tanggal_ambil' => $data['tanggal_ambil'],
                'tanggal_kembali' => $data['tanggal_kembali'],
                'durasi' => $durasi,
                'total_sewa' => $produk->totalSewa($durasi),
                'dp' => (float) ($produk->dp ?? 0),
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
            'warna' => ['nullable', 'string'],
            'durasi' => ['required', 'integer', 'min:1'],
            'total_sewa' => ['required', 'numeric'],
        ]);

        // ==== VALIDASI STOK (stok sewa, per ukuran & warna) ====
        $produk = Produk::findOrFail($validated['produk_id']);
        if (! $produk->bisa_disewa || $produk->stokTersedia('sewa', $validated['ukuran'], $validated['warna'] ?? null) <= 0) {
            return back()->withErrors(['stok' => 'Maaf, produk ini sedang tidak tersedia untuk disewa.']);
        }
        // =======================================================

        // Hitung ulang durasi & total di server (harga sudah termasuk diskon), jangan percaya angka dari browser.
        $validated['durasi'] = max(1, (int) \Carbon\Carbon::parse($validated['tanggal_ambil'])->startOfDay()
            ->diffInDays(\Carbon\Carbon::parse($validated['tanggal_kembali'])->startOfDay(), true));
        $validated['total_sewa'] = $produk->totalSewa($validated['durasi']);

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
            'catatan' => ['nullable', 'string', 'max:255'],
            'setuju' => ['accepted'],
        ]);

        $keranjangSewa = session('keranjang_sewa', []);
        $sewaData = session('sewa_data');

        if (empty($keranjangSewa) && !$sewaData) {
            return redirect()->route('koleksi.index')->withErrors(['error' => 'Data sewa tidak ditemukan.']);
        }

        // Tentukan items yang akan diproses
        $items = !empty($keranjangSewa) ? collect($keranjangSewa)->map(fn($i) => (object)$i)->toArray() : [(object)$sewaData];

        // Hitung ulang dari database (jangan percaya angka di session) + cek stok sewa final
        $rincianCatatan = [];
        foreach ($items as $item) {
            $produk = Produk::findOrFail($item->produk_id);

            if (! $produk->bisa_disewa || $produk->stokTersedia('sewa', $item->ukuran ?? null, $item->warna ?? null) <= 0) {
                return redirect()->route('sewa.keranjang.index')
                    ->with('error', $produk->nama . ': stok sewa untuk pilihan ini sudah habis.');
            }

            $item->total_sewa = $produk->totalSewa((int) $item->durasi);
            $item->dp = (float) ($produk->dp ?? 0);

            // Ukuran, warna & tanggal dicatat di catatan transaksi (belum ada kolom khusus di detail_transaksi)
            $spek = collect([
                ! empty($item->ukuran) ? 'Ukuran: ' . $item->ukuran : null,
                ! empty($item->warna) ? 'Warna: ' . $item->warna : null,
                \Carbon\Carbon::parse($item->tanggal_ambil)->format('d/m/Y') . '–' . \Carbon\Carbon::parse($item->tanggal_kembali)->format('d/m/Y'),
            ])->filter()->implode(' · ');
            $rincianCatatan[] = '[' . $spek . ']';
        }
        $catatanTransaksi = trim(implode(' ', $rincianCatatan) . ' ' . ($validated['catatan'] ?? ''));

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
            'catatan' => $catatanTransaksi ?: null,
        ]);

        // 2. Loop Simpan Detail & Sewa
        foreach ($items as $item) {
            $produk = Produk::findOrFail($item->produk_id);

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'jumlah' => 1,
                'harga_satuan' => $produk->hargaSewaNet(),
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
        $dp = array_sum(array_map(fn($i) => $i->dp ?? 0, $items));
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

        // Segarkan dari database: total (harga net), DP, diskon, dan stok sewa pilihan
        $produkMap = Produk::whereIn('id', collect($keranjangSewa)->pluck('produk_id')->unique())->get()->keyBy('id');
        foreach ($keranjangSewa as $key => $item) {
            $p = $produkMap->get($item['produk_id'] ?? null);
            if (! $p || ! $p->bisa_disewa) {
                unset($keranjangSewa[$key]);
                continue;
            }
            $keranjangSewa[$key]['total_sewa'] = $p->totalSewa((int) $item['durasi']);
            $keranjangSewa[$key]['dp'] = (float) ($p->dp ?? 0);
            $keranjangSewa[$key]['diskon'] = $p->diskonSewa();
            $keranjangSewa[$key]['tersedia'] = $p->stokTersedia('sewa', $item['ukuran'] ?? null, $item['warna'] ?? null);
        }
        session()->put('keranjang_sewa', $keranjangSewa);

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
            'warna' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'total_sewa' => 'required|numeric|min:0',
        ]);

        $produk = Produk::findOrFail($request->produk_id);

        if (! $produk->bisa_disewa || $produk->stokTersedia('sewa', $request->ukuran, $request->warna) <= 0) {
            return back()->withErrors(['stok' => 'Maaf, produk ini sedang tidak tersedia untuk disewa.']);
        }

        // Hitung ulang durasi & total di server (harga sudah termasuk diskon).
        $durasi = max(1, (int) \Carbon\Carbon::parse($request->tanggal_ambil)->startOfDay()
            ->diffInDays(\Carbon\Carbon::parse($request->tanggal_kembali)->startOfDay(), true));

        // 2. Ambil data keranjang sewa dari session
        $keranjangSewa = session()->get('keranjang_sewa', []);

        // 3. Buat ID unik untuk item (kombinasi produk + tanggal + ukuran)
        $itemId = $produk->id . '_' . $request->tanggal_ambil . '_' . $request->ukuran . '_' . ($request->warna ?? '');

        // 4. Simpan ke session
        $keranjangSewa[$itemId] = [
            'produk_id' => $produk->id,
            'nama' => $produk->nama,
            'foto_produk' => $produk->foto_produk,
            'ukuran' => $request->ukuran,
            'warna' => $request->warna,
            'tanggal_ambil' => $request->tanggal_ambil,
            'tanggal_kembali' => $request->tanggal_kembali,
            'durasi' => $durasi,
            'harga_sewa' => $produk->hargaSewaNet(),
            'total_sewa' => $produk->totalSewa($durasi),
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