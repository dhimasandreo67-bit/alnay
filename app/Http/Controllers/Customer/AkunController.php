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
            'user', 'totalFitting', 'fittingAktif', 'pesananSession'
        ));
    }

    public function pesanan()
    {
        $pesananList = \App\Models\Transaksi::with(['detail.produk', 'sewa', 'pembayaran'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.akun.pesanan', compact('pesananList'));
    }

    public function pesananDetail($id)
    {
        $transaksi = \App\Models\Transaksi::with([
            'detail.produk',
            'sewa.jaminan',
            'pembayaran',
            'pengiriman',
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
}