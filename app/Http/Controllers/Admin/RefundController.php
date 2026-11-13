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

        $riwayat = $refund->riwayat()->with('pengubah')->get();

        return view('admin.refund.show', compact('refund', 'riwayat'));
    }

    /** diajukan -> disetujui. Admin menentukan nominal dan cara barang kembali. */
    public function setujui(Request $request, $id)
    {
        $refund = Refund::with('transaksi.detail')->findOrFail($id);
        $maks = $refund->transaksi->total;

        $data = $request->validate([
            'jumlah'              => ['required', 'numeric', 'min:1', 'max:' . $maks],
            'no_resi_penjemputan' => ['nullable', 'string', 'max:100'],
            'catatan_negosiasi'   => ['nullable', 'string', 'max:1000'],
        ], [
            'jumlah.max' => 'Nominal refund tidak boleh melebihi total transaksi (Rp ' . number_format($maks, 0, ',', '.') . ').',
        ]);

        $ok = $refund->pindahKe('disetujui', [
            'jumlah'              => $data['jumlah'],
            'no_resi_penjemputan' => $data['no_resi_penjemputan'] ?? null,
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
        $request->validate(['restock' => ['nullable', 'boolean']]);

        $refund = Refund::with('transaksi.detail')->findOrFail($id);
        $restock = $request->boolean('restock');
        $stokKembali = false;

        $ok = DB::transaction(function () use ($refund, $restock, &$stokKembali) {
            $berhasil = $refund->pindahKe('barang_diterima', [
                'tanggal_barang_diterima' => now(),
                'diproses_oleh'           => auth('admin')->id(),
            ], auth('admin')->id());

            if (! $berhasil) {
                return false;
            }

            // Stok hanya dikembalikan sekali, dan hanya jika transaksi ini memang sudah memotong stok.
            if ($restock && ! $refund->stok_dikembalikan && $refund->transaksi->stokSudahDipotong()) {
                foreach ($refund->transaksi->detail as $d) {
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