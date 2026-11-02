<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JaminanSewa;
use Illuminate\Http\Request;

class JaminanController extends Controller
{
    public function index(Request $request)
    {
        $query = JaminanSewa::with(['sewa.transaksi.user', 'sewa.transaksi.detail.produk']);

        // Filter status
        $filter = $request->get('status', 'semua');
        if ($filter === 'pending') {
            $query->where('status_verifikasi', 'pending');
        } elseif ($filter === 'verified') {
            $query->where('status_verifikasi', 'verified');
        }

        $jaminanList = $query->latest()->paginate(10)->withQueryString();

        // Statistik
        $totalPending = JaminanSewa::where('status_verifikasi', 'pending')->count();
        $totalVerified = JaminanSewa::where('status_verifikasi', 'verified')->count();
        $totalDeposit = JaminanSewa::where('status_pengembalian_deposit', 'belum')->sum('jumlah_deposit');

        return view('admin.jaminan.index', compact('jaminanList', 'filter', 'totalPending', 'totalVerified', 'totalDeposit'));
    }

    public function show($id)
    {
        $jaminan = \App\Models\JaminanSewa::with([
            'sewa.transaksi.user',
            'sewa.transaksi.detail.produk',
            'sewa.transaksi.pembayaran',
            'verifikator',
        ])->findOrFail($id);

        return view('admin.jaminan.show', compact('jaminan'));
    }
}