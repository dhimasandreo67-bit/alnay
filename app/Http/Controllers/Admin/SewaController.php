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
            'daftarSewa',
            'filter',
            'totalDipinjam',
            'totalTerlambat',
            'totalSelesai',
            'totalDeposit'
        ));
    }

    public function show($id)
    {
        $sewa = Sewa::with(['transaksi.user', 'transaksi.detail.produk', 'jaminan', 'pengecekan', 'denda'])
            ->findOrFail($id);

        $totalDenda = $sewa->denda->where('status', 'belum_lunas')->sum('jumlah');

        // --- BUAT DATA TIMELINE DINAMIS ---
        $timeline = [];

        // 1. Serah Terima (dari tanggal dibuatnya transaksi)
        $timeline[] = [
            'title' => 'Serah Terima & Fitting',
            'date' => $sewa->created_at,
            'desc' => 'Transaksi sewa dibuat dan jadwal disepakati.',
            'color' => 'bg-gray-400',
        ];

        // 2. Tenggat Waktu Pengembalian
        $isTerlambat = \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->isPast() && $sewa->status !== 'selesai';
        $timeline[] = [
            'title' => $isTerlambat ? 'Tenggat Waktu Telah Terlewati' : 'Tenggat Waktu Pengembalian',
            'date' => $sewa->tanggal_kembali_rencana,
            'desc' => 'Batas waktu pengembalian busana.',
            'color' => $isTerlambat ? 'bg-rose-500' : 'bg-amber-500',
        ];

        // 3. Jika sudah selesai, tambahkan tanggal kembali aktual
        if ($sewa->status === 'selesai' && $sewa->tanggal_kembali_aktual) {
            $timeline[] = [
                'title' => 'Busana Diterima Kembali',
                'date' => $sewa->tanggal_kembali_aktual,
                'desc' => 'Busana telah dikembalikan dan masuk proses QC.',
                'color' => 'bg-emerald-500',
            ];
        }

        return view('admin.sewa.show', compact('sewa', 'totalDenda', 'timeline'));
    }

    public function verifikasiJaminan(Request $request, $id)
    {
        $sewa = Sewa::with('jaminan')->findOrFail($id);

        $request->validate([
            'no_ktp' => 'required|string',
            'nama_sesuai_ktp' => 'required|string',
        ]);

        // Pakai jaminan yang ada; kalau belum ada (data lama), buat dulu.
        $jaminan = $sewa->jaminan ?? $sewa->jaminan()->make([
            'metode_jaminan' => 'ktp_tatap_muka',
            'jumlah_deposit' => 0,
            'status_pengembalian_deposit' => 'belum',
        ]);

        // Kolom di tabel: nomor_ktp & nama_ktp (input form: no_ktp & nama_sesuai_ktp)
        $jaminan->fill([
            'nomor_ktp' => $request->no_ktp,
            'nama_ktp' => $request->nama_sesuai_ktp,
            'status_verifikasi' => 'verified',
            'diverifikasi_oleh' => auth('admin')->id(),
            'tanggal_verifikasi' => now(),
        ])->save();

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
            'foto_bukti' => 'nullable|image|max:2048', // Opsional, max 2MB
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('qc', 'public');
        }

        $sewa->pengecekan()->create([
            'tahap' => $request->tahap,
            'catatan' => $request->catatan,
            'petugas' => auth('admin')->user()->name ?? 'Admin',
            'foto_bukti' => $fotoPath,
        ]);

        return back()->with('success', 'Catatan QC berhasil disimpan!')->with('active_tab', 'qc');
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
            'status' => 'belum_lunas',
        ]);

        return back()->with('success', 'Denda berhasil ditambahkan!');
    }

    public function lunasiDenda($id)
    {
        $denda = Denda::findOrFail($id);
        $denda->update(['status' => 'lunas']);

        return back()->with('success', 'Denda berhasil dilunasi!');
    }

    public function ubahStatus(Request $request, $id)
    {
        $sewa = Sewa::findOrFail($id);
        $sewa->update(['status' => $request->status]);

        return back()->with('success', 'Status sewa berhasil diubah menjadi ' . $request->status);
    }

    // Update Catatan QC
        public function updateQC(Request $request, $id)
    {
        $qc = \App\Models\PengecekanBarang::findOrFail($id);
        
        $request->validate([
            'tahap' => 'required|string',
            'catatan' => 'required|string',
            'foto_bukti' => 'nullable|image|max:2048',
        ]);

        $data = [
            'tahap' => $request->tahap,
            'catatan' => $request->catatan,
        ];

        if ($request->hasFile('foto_bukti')) {
            // Hapus foto lama jika ada
            if ($qc->foto_bukti) {
                \Storage::disk('public')->delete($qc->foto_bukti);
            }
            $data['foto_bukti'] = $request->file('foto_bukti')->store('qc', 'public');
        }

        $qc->update($data);

        return back()->with('success', 'Catatan QC berhasil diperbarui!')->with('active_tab', 'qc');
    }

    // Hapus Catatan QC
    public function hapusQC($id)
    {
        $qc = \App\Models\PengecekanBarang::findOrFail($id);
        $qc->delete();

        return back()->with('success', 'Catatan QC berhasil dihapus!')->with('active_tab', 'qc');
    }
}