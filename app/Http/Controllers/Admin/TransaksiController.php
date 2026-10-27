<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data dengan relasi
        $query = Transaksi::with(['user', 'sewa', 'detail.produk'])->latest();

        // 2. Filter Pencarian (Kode Transaksi atau Nama User)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('kode_transaksi', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // 3. Filter Status Pembayaran
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status_pembayaran', $request->status);
        }

        $transaksis = $query->paginate(10)->withQueryString();

        // 4. Hitung Statistik untuk Card
        $totalPendapatan = Transaksi::whereIn('status_pembayaran', ['dp', 'lunas', 'dp_dibayar'])->sum('total');
        $pesananBaru = Transaksi::where('status_pembayaran', 'pending')->count();
        $pesananDiproses = Transaksi::whereIn('status_pembayaran', ['dp', 'dp_dibayar'])->count();

        return view('admin.transaksi.index', compact('transaksis', 'totalPendapatan', 'pesananBaru', 'pesananDiproses'));
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['user', 'detail.produk', 'sewa', 'pembayaran', 'pengiriman'])
            ->findOrFail($id);
            
        return view('admin.transaksi.show', compact('transaksi'));
    }

        // Fungsi untuk melunasi transaksi secara manual (oleh Admin)
    public function lunasi($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        
        // Ubah status transaksi
        $transaksi->update(['status_pembayaran' => 'lunas']);

        // Ubah status di tabel pembayaran (jika ada tagihan DP/Pending)
        $transaksi->pembayaran()->update(['status_midtrans' => 'success']);

        return redirect()->back()->with('success', 'Status pembayaran berhasil diubah menjadi LUNAS.');
    }

    // Fungsi untuk menyelesaikan masa sewa (baju sudah dikembalikan)
    public function selesaikanSewa($id)
    {
        $transaksi = Transaksi::with('sewa')->findOrFail($id);

        if ($transaksi->sewa) {
            $transaksi->sewa->update([
                'status' => 'selesai',
                'tanggal_kembali_aktual' => now()->toDateString(), // Otomatis catat tanggal hari ini
            ]);
        }

        return redirect()->back()->with('success', 'Masa sewa telah berhasil diselesaikan.');
    }
}