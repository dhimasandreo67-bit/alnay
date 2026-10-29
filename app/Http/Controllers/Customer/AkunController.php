<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ReservasiFitting;
use Illuminate\Http\Request;

class AkunController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Statistik
        $totalFitting = ReservasiFitting::where('user_id', $user->id)
            ->where('status', '!=', 'batal')
            ->count();

        $fittingAktif = ReservasiFitting::with('slot')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Sementara: pesanan dari session (belum database)
        $pesananSession = [];
        if (session('sewa_data')) {
            $pesananSession[] = ['jenis' => 'sewa', 'data' => session('sewa_data')];
        }
        if (session('beli_data')) {
            $pesananSession[] = ['jenis' => 'beli', 'data' => session('beli_data')];
        }

        return view('customer.akun.index', compact(
            'user',
            'totalFitting',
            'fittingAktif',
            'pesananSession'
        ));
    }

    public function pesanan(Request $request)
    {
        $filter = $request->get('jenis', 'sewa');

        $query = \App\Models\Transaksi::where('user_id', auth()->id())
            ->with(['detail.produk', 'sewa']);

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
        $transaksi = \App\Models\Transaksi::with([
            'detail.produk',
            'sewa.pengecekan',
            'pembayaran',
            'pengiriman'
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('customer.akun.pesanan-detail', compact('transaksi'));
    }

    public function fitting()
    {
        $fittingList = ReservasiFitting::with('slot')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.akun.fitting', compact('fittingList'));
    }

    public function refund()
    {
        return view('customer.akun.refund');
    }

        public function ajukanRefund(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $transaksi = \App\Models\Transaksi::where('user_id', auth()->id())->findOrFail($id);

        \App\Models\Refund::create([
            'transaksi_id' => $transaksi->id,
            'diajukan_oleh' => auth()->id(),
            'alasan' => $request->alasan,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pengajuan refund berhasil dikirim!');
    }

        public function terimaPengiriman($id)
    {
        $pengiriman = \App\Models\Pengiriman::whereHas('transaksi', function($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        $pengiriman->update(['status_kirim' => 'diterima']);

        return back()->with('success', 'Terima kasih! Konfirmasi penerimaan berhasil.');
    }
}
