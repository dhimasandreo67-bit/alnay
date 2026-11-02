<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use Illuminate\Http\Request;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengiriman::with(['transaksi.user', 'transaksi.detail.produk']);

        // Filter Status
        $filter = $request->get('status', 'semua');
        if ($filter === 'menunggu') {
            $query->where('status_kirim', 'menunggu');
        } elseif ($filter === 'dikirim') {
            $query->where('status_kirim', 'dikirim');
        } elseif ($filter === 'diterima') {
            $query->where('status_kirim', 'diterima');
        }

        // Filter Pencarian (Kode Transaksi atau Nama User)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('transaksi', function($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $pengirimanList = $query->latest()->paginate(10)->withQueryString();

        // Statistik
        $totalMenunggu = Pengiriman::where('status_kirim', 'menunggu')->count();
        $totalDikirim = Pengiriman::where('status_kirim', 'dikirim')->count();
        $totalDiterima = Pengiriman::where('status_kirim', 'diterima')->count();

        return view('admin.pengiriman.index', compact(
            'pengirimanList', 'filter', 'totalMenunggu', 'totalDikirim', 'totalDiterima'
        ));
    }

        public function updateStatus(Request $request, $id)
    {
        $pengiriman = Pengiriman::findOrFail($id);
        
        $data = ['status_kirim' => $request->status_kirim];
        
        // Jika status berubah menjadi 'dikirim', wajib isi kurir & resi
        if ($request->status_kirim === 'dikirim') {
            $request->validate([
                'kurir' => 'required|string|max:50',
                'layanan_ongkir' => 'required|string|max:30',
                'no_resi' => 'required|string|max:100',
            ]);
            $data['kurir'] = $request->kurir;
            $data['layanan_ongkir'] = $request->layanan_ongkir;
            $data['no_resi'] = $request->no_resi;
        }
                
        $pengiriman->update($data);

        return back()->with('success', 'Status pengiriman berhasil diperbarui!');
    }

        public function batalkanTerima($id)
    {
        $pengiriman = Pengiriman::findOrFail($id);
        
        if ($pengiriman->status_kirim === 'diterima') {
            $pengiriman->update(['status_kirim' => 'dikirim']);
            return back()->with('success', 'Konfirmasi penerimaan berhasil dibatalkan. Status kembali ke "Dikirim".');
        }

        return back()->with('error', 'Status pengiriman bukan "Diterima".');
    }
}