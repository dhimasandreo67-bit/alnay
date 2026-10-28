<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sewa;
use App\Models\Transaksi;
use App\Models\Denda;
use Illuminate\Http\Request;

class SewaController extends Controller
{
    public function index(Request $request)
    {
        $query = Sewa::with(['transaksi.user', 'transaksi.detail.produk']);

        $filter = $request->get('status', 'semua');
        
        if ($filter === 'dipinjam') {
            $query->where('status', 'aktif');
        } elseif ($filter === 'terlambat') {
            $query->where('status', 'aktif')
                  ->where('tanggal_kembali_rencana', '<', now()->toDateString());
        } elseif ($filter === 'dikembalikan') {
            $query->where('status', 'selesai');
        }

        $daftarSewa = $query->latest()->paginate(10)->withQueryString();

        $totalDipinjam = Sewa::where('status', 'aktif')->count();
        $totalTerlambat = Sewa::where('status', 'aktif')
            ->where('tanggal_kembali_rencana', '<', now()->toDateString())
            ->count();
        $totalSelesai = Sewa::where('status', 'selesai')->count(); 
        $totalDeposit = \App\Models\JaminanSewa::where('status_pengembalian_deposit', 'belum')->sum('jumlah_deposit');

        return view('admin.sewa.index', compact(
            'daftarSewa', 'filter', 'totalDipinjam', 'totalTerlambat', 'totalSelesai', 'totalDeposit'
        ));
    }

    public function show($id)
    {
        $sewa = Sewa::with([
            'transaksi.user', 
            'transaksi.detail.produk', 
            'jaminan', 
            'pengecekan', 
            'denda'
        ])->findOrFail($id);

        $totalDenda = $sewa->denda->where('status_bayar', 'belum_lunas')->sum('jumlah');

        return view('admin.sewa.show', compact('sewa', 'totalDenda'));
    }

    public function verifikasiJaminan(Request $request, $id)
    {
        $sewa = Sewa::with('jaminan')->findOrFail($id);

        $request->validate([
            'no_ktp' => 'required|string',
            'nama_sesuai_ktp' => 'required|string',
        ]);

        if ($sewa->jaminan) {
            $sewa->jaminan->update([
                'no_ktp' => $request->no_ktp,
                'nama_sesuai_ktp' => $request->nama_sesuai_ktp,
                'status_verifikasi' => 'verified',
                'diverifikasi_oleh' => auth('admin')->id(),
                'tanggal_verifikasi' => now(),
            ]);
        }

        return back()->with('success', 'Jaminan fisik berhasil diverifikasi!');
    }

    public function kembalikanDeposit($id)
    {
        $sewa = Sewa::with('jaminan')->findOrFail($id);

        if ($sewa->jaminan) {
            $sewa->jaminan->update([
                'status_pengembalian_deposit' => 'dikembalikan',
            ]);
        }

        return back()->with('success', 'Deposit sewa berhasil dikembalikan penuh!');
    }

    // ================= FITUR QC =================
        public function simpanQC(Request $request, $id)
    {
        $sewa = Sewa::findOrFail($id);
        
        $request->validate([
            'tahap' => 'required|string',
            'catatan' => 'required|string',
        ]);

        $sewa->pengecekan()->create([
            'tahap' => $request->tahap,
            'catatan' => $request->catatan,
            'petugas' => auth('admin')->user()->name ?? 'Admin',
        ]);

        return back()->with('success', 'Catatan QC berhasil disimpan!');
    }

    // ================= FITUR DENDA =================
    public function tambahDenda(Request $request, $id)
    {
        $sewa = Sewa::findOrFail($id);
        
        $request->validate([
            'jenis' => 'required|string',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $sewa->denda()->create([
            'jenis' => $request->jenis,
            'jumlah' => $request->jumlah,
            'status_bayar' => 'belum_lunas',
        ]);

        return back()->with('success', 'Denda berhasil ditambahkan!');
    }

    public function lunasiDenda($id)
    {
        $denda = Denda::findOrFail($id);
        $denda->update(['status_bayar' => 'lunas']);

        return back()->with('success', 'Denda berhasil dilunasi!');
    }
}