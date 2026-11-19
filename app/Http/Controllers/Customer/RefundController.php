<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RefundController extends Controller
{
    /** Kelompok status untuk tab filter di halaman daftar. */
    private const KELOMPOK = [
        'diajukan' => ['diajukan'],
        'diproses' => ['disetujui', 'barang_dikirim', 'barang_diterima'],
        'selesai'  => ['selesai'],
        'ditutup'  => ['ditolak', 'dibatalkan'],
    ];

    public function index(Request $request)
    {
        $query = Refund::with(['transaksi.user', 'transaksi.detail.produk']);

        $filter = $request->get('status', 'semua');
        if (isset(self::KELOMPOK[$filter])) {
            $query->whereIn('status', self::KELOMPOK[$filter]);
        } else {
            $filter = 'semua';
        }

        if ($request->filled('search')) {
            $cari = $request->search;
            $query->whereHas('transaksi', function ($q) use ($cari) {
                $q->where('kode_transaksi', 'like', "%{$cari}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$cari}%"));
            });
        }

        $refundList = $query->latest()->paginate(10)->withQueryString();

        $hitung = ['semua' => Refund::count()];
        foreach (self::KELOMPOK as $kunci => $daftar) {
            $hitung[$kunci] = Refund::whereIn('status', $daftar)->count();
        }

        return view('admin.refund.index', compact('refundList', 'filter', 'hitung'));
    }

    public function show($id)
    {
        $refund = Refund::with([
            'transaksi.user', 'transaksi.detail.produk', 'transaksi.pengiriman', 'transaksi.pembayaran',
            'pengaju', 'pemroses',
        ])->findOrFail($id);

        $riwayat = $refund->riwayat()->with('pengubah')->orderBy('waktu_ubah', 'asc')->get();

        return view('admin.refund.show', compact('refund', 'riwayat'));
    }

    /** diajukan -> disetujui. Admin menentukan nominal dan cara barang kembali. */
    public function setujui(Request $request, $id)
    {
        $refund = Refund::with('transaksi.detail')->findOrFail($id);
        $maks = $refund->transaksi->total;

        $data = $request->validate([
            'jumlah'              => ['required', 'numeric', 'min:1', 'max:' . $maks],
            'metode_pengembalian' => ['required', 'in:dijemput,kirim_sendiri'],
            'kurir_penjemput'     => ['required_if:metode_pengembalian,dijemput', 'nullable', 'string', 'max:50'],
            'no_resi_penjemputan' => ['required_if:metode_pengembalian,dijemput', 'nullable', 'string', 'max:100'],
            'biaya_jemput'        => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'catatan_negosiasi'   => ['nullable', 'string', 'max:1000'],
        ], [
            'metode_pengembalian.required'  => 'Pilih cara barang kembali ke toko.',
            'kurir_penjemput.required_if' => 'Isi nama kurir yang menjemput (mis. J&T, JNE).',
            'no_resi_penjemputan.required_if' => 'No. resi penjemputan wajib diisi. Pesan kurir jemput dulu, lalu isi resinya.',
            'jumlah.max' => 'Nominal refund tidak boleh melebihi total transaksi (Rp ' . number_format($maks, 0, ',', '.') . ').',
        ]);

        $dijemput = $data['metode_pengembalian'] === 'dijemput';

        $ok = $refund->pindahKe('disetujui', [
            'jumlah'              => $data['jumlah'],
            'metode_pengembalian' => $data['metode_pengembalian'],
            'no_resi_penjemputan' => $dijemput ? ($data['no_resi_penjemputan'] ?? null) : null,
            'kurir_retur'         => $dijemput ? ($data['kurir_penjemput'] ?? null) : null,
            'biaya_jemput'        => $dijemput ? ($data['biaya_jemput'] ?? null) : null,
            'batas_kirim'         => now()->addDays(Refund::BATAS_KIRIM_HARI),
            'catatan_negosiasi'   => $data['catatan_negosiasi'] ?? null,
            'diproses_oleh'       => auth('admin')->id(),
        ], auth('admin')->id());

        return $ok
            ? back()->with('success', 'Refund disetujui. Pelanggan diminta mengirim barang kembali.')
            : back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
    }

    /** diajukan -> ditolak (alasan wajib, ditampilkan ke pelanggan). */
    public function tolak(Request $request, $id)
    {
        $data = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:5', 'max:500'],
        ], ['alasan_penolakan.required' => 'Isi alasan penolakan agar pelanggan tahu penyebabnya.']);

        $refund = Refund::findOrFail($id);

        $ok = $refund->pindahKe('ditolak', [
            'alasan_penolakan' => $data['alasan_penolakan'],
            'diproses_oleh'    => auth('admin')->id(),
        ], auth('admin')->id());

        return $ok
            ? back()->with('success', 'Pengajuan refund ditolak.')
            : back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
    }

    /** disetujui / barang_dikirim -> barang_diterima. Opsional kembalikan stok. */
    public function terimaBarang(Request $request, $id)
    {
        $request->validate([
            'restock_detail'   => ['nullable', 'array'],
            'restock_detail.*' => ['integer'],
            'ganti_ongkir'     => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);

        $refund = Refund::with('transaksi.detail')->findOrFail($id);

        // Stok hanya dikembalikan untuk produk yang dicentang admin (penting bila pesanan berisi banyak produk).
        $idRestock = collect($request->input('restock_detail', []))->map(fn ($v) => (int) $v)->all();
        $restock = ! empty($idRestock);
        $stokKembali = false;

        $ok = DB::transaction(function () use ($request, $refund, $restock, $idRestock, &$stokKembali) {
            $berhasil = $refund->pindahKe('barang_diterima', [
                'tanggal_barang_diterima' => now(),
                'ganti_ongkir'            => $refund->metode_pengembalian === 'kirim_sendiri'
                    ? (float) $request->input('ganti_ongkir', 0) : null,
                'diproses_oleh'           => auth('admin')->id(),
            ], auth('admin')->id());

            if (! $berhasil) {
                return false;
            }

            // Stok hanya dikembalikan sekali, dan hanya jika transaksi ini memang sudah memotong stok.
            if ($restock && ! $refund->stok_dikembalikan && $refund->transaksi->stokSudahDipotong()) {
                foreach ($refund->transaksi->detail->whereIn('id', $idRestock) as $d) {
                    $produk = Produk::lockForUpdate()->find($d->produk_id);
                    $produk?->tambahStok('jual', $d->ukuran, $d->warna, (int) $d->jumlah);
                }

                $refund->forceFill(['stok_dikembalikan' => true])->save();
                $stokKembali = true;
            }

            return true;
        });

        if (! $ok) {
            return back()->with('error', 'Status refund tidak bisa diubah ke "barang diterima" saat ini.');
        }

        return back()->with('success', 'Barang tercatat diterima.' . ($stokKembali ? ' Stok produk sudah dikembalikan.' : ''));
    }

    /** barang_diterima -> selesai. Wajib unggah bukti transfer. */
    public function selesai(Request $request, $id)
    {
        $request->validate([
            'bukti_transfer' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:3072'],
        ], ['bukti_transfer.required' => 'Unggah bukti transfer sebelum menyelesaikan refund.']);

        $refund = Refund::findOrFail($id);

        if (! $refund->bolehKe('selesai')) {
            return back()->with('error', 'Refund baru bisa diselesaikan setelah barang diterima.');
        }

        $path = $request->file('bukti_transfer')->store('refund/transfer', 'public');

        $ok = $refund->pindahKe('selesai', [
            'bukti_transfer'  => $path,
            'tanggal_selesai' => now(),
            'diproses_oleh'   => auth('admin')->id(),
        ], auth('admin')->id());

        if (! $ok) {
            Storage::disk('public')->delete($path);

            return back()->with('error', 'Refund ini sudah diselesaikan sebelumnya.');
        }

        return back()->with('success', 'Refund selesai. Bukti transfer tersimpan dan terlihat oleh pelanggan.');
    }
}