<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $query = Refund::with(['transaksi.user', 'transaksi.detail.produk']);

        // Filter Status
        $filter = $request->get('status', 'semua');
        if ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'disetujui') {
            $query->where('status', 'disetujui');
        } elseif ($filter === 'ditolak') {
            $query->where('status', 'ditolak');
        }

        $refundList = $query->latest()->paginate(10)->withQueryString();

        // Statistik
        $totalPending = Refund::where('status', 'pending')->count();
        $totalDisetujui = Refund::where('status', 'disetujui')->count();
        $totalDitolak = Refund::where('status', 'ditolak')->count();

        return view('admin.refund.index', compact(
            'refundList', 'filter', 'totalPending', 'totalDisetujui', 'totalDitolak'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $refund->update(['status' => $request->status]);

        return back()->with('success', 'Status refund berhasil diperbarui!');
    }
}