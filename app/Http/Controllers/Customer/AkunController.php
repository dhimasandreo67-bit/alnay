<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengiriman;
use App\Models\Pengiriman;
use App\Models\Refund;
use App\Models\ReservasiFitting;
use App\Models\Sewa;
use App\Models\Transaksi;
use App\Services\SinkronAlamatUtama;
use Illuminate\Http\Request;

class AkunController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();

        // Self-healing: kalau no_hp profil masih kosong tapi sudah punya alamat,
        // isi otomatis dari alamat utama lalu muat ulang data user.
        if (blank($user->no_hp) && AlamatPengiriman::where('user_id', $user->id)->exists()) {
            SinkronAlamatUtama::untuk($user->id);
            $user = $user->fresh();
        }

        $status = $request->get('status', 'semua');
        $cari   = trim((string) $request->get('q', ''));

        // --- 1. STATISTIK ---
        $totalFitting = ReservasiFitting::where('user_id', $user->id)
            ->where('status', '!=', 'batal')
            ->count();

        $fittingAktif = ReservasiFitting::with('slot')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $pesananAktif = Transaksi::where('user_id', $user->id)
            ->where('jenis', 'beli')
            ->whereNotIn('status_pembayaran', ['batal', 'lunas'])
            ->count();

        $penyewaanAktif = Transaksi::where('user_id', $user->id)
            ->where('jenis', 'sewa')
            ->whereNotIn('status_pembayaran', ['batal', 'lunas'])
            ->count();

        $refundAktif = Refund::where('diajukan_oleh', $user->id)
            ->aktif()
            ->count();

        // --- 2. DAFTAR PESANAN ---
        // Menggunakan nama variabel $pesananList agar sesuai dengan View
        $pesananList = Transaksi::with(['detail.produk', 'pengiriman'])
            ->where('user_id', $user->id)
            ->where('jenis', 'beli')
            ->latest()
            ->take(10)
            ->get();

        $penyewaanList = Sewa::with(['transaksi.detail.produk'])
            ->whereHas('transaksi', fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(10)
            ->get();

        $fittingList = ReservasiFitting::with('slot')
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $refundList = Refund::with('transaksi')
            ->where('diajukan_oleh', $user->id)
            ->latest()
            ->get();

        // --- 3. ALAMAT ---
        $alamatList = AlamatPengiriman::where('user_id', $user->id)
            ->orderByDesc('is_utama')
            ->latest()
            ->get();

        $adaAktivitas = $pesananAktif > 0 || $penyewaanAktif > 0 || $fittingAktif;

        return view('customer.akun.index', compact(
            'user', 'totalFitting', 'fittingAktif', 'pesananAktif', 'penyewaanAktif', 'refundAktif',
            'alamatList', 'adaAktivitas', 'pesananList', 'penyewaanList', 'fittingList', 'refundList'
        ));
    }

    public function pesanan(Request $request)
    {
        $filter = $request->get('jenis', 'sewa');
        $query = Transaksi::where('user_id', auth()->id())->with(['detail.produk', 'sewa']);

        if ($filter === 'sewa') {
            $query->where('jenis', 'sewa');
        } elseif ($filter === 'beli') {
            $query->where('jenis', 'beli');
        }

        $pesananList = $query->latest()->get();
        return view('customer.akun.pesanan', compact('pesananList', 'filter'));
    }

    public function pesananDetail($id)
    {
        $transaksi = Transaksi::with(['detail.produk', 'sewa.pengecekan', 'pembayaran', 'pengiriman'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $transaksi->load(['detail.produk', 'sewa.pengecekan', 'pembayaran', 'pengiriman']);

// Refund terbaru untuk pesanan ini + apakah pelanggan masih boleh mengajukan (aturan ada di Transaksi::cekBisaRefund)
$transaksi->load('refund');
$refund = $transaksi->refund->sortByDesc('id')->first();
[$bisaRefund, $alasanTakBisaRefund] = $transaksi->cekBisaRefund();

$sudahFitting = \App\Models\ReservasiFitting::where('transaksi_id', $transaksi->id)
    ->where('status', '!=', 'batal')
    ->exists();

$detailPertama = $transaksi->detail->first();
$ratingDetailId = $detailPertama?->id;
$sudahRating = $detailPertama
    ? \App\Models\Rating::where('detail_transaksi_id', $detailPertama->id)->exists()
    : true;

return view('customer.akun.pesanan-detail', compact(
    'transaksi', 'refund', 'bisaRefund', 'alasanTakBisaRefund', 'sudahFitting', 'ratingDetailId', 'sudahRating'
));
    }

    public function fitting()
    {
        $fittingList = ReservasiFitting::with('slot')->where('user_id', auth()->id())->latest()->get();
        return view('customer.akun.fitting', compact('fittingList'));
    }

    public function terimaPengiriman($id)
    {
        $pengiriman = Pengiriman::whereHas('transaksi', fn ($q) => $q->where('user_id', auth()->id()))
            ->findOrFail($id);

        if ($pengiriman->status_kirim !== 'dikirim') {
            return back()->with('error', 'Pesanan belum dikirim, jadi belum bisa dikonfirmasi diterima.');
        }

        $pengiriman->update([
            'status_kirim'     => 'diterima',
            'tanggal_diterima' => $pengiriman->tanggal_diterima ?? now(),
        ]);

        return back()->with('success', 'Terima kasih! Konfirmasi penerimaan berhasil.');
    }
}