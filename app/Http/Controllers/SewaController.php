<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sewa;
use App\Models\Denda;
use App\Support\DendaTelat;
use Carbon\Carbon;
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
            $query->where('status', 'aktif')->where('tanggal_kembali_rencana', '<', now()->toDateString());
        } elseif ($filter === 'dikembalikan') {
            $query->where('status', 'selesai');
        }

        $daftarSewa = $query->latest()->paginate(10)->withQueryString();

        $totalDipinjam = Sewa::where('status', 'aktif')->count();
        $totalTerlambat = Sewa::where('status', 'aktif')->where('tanggal_kembali_rencana', '<', now()->toDateString())->count();
        $totalSelesai = Sewa::where('status', 'selesai')->count();
        $totalDeposit = \App\Models\JaminanSewa::where('status_pengembalian_deposit', 'belum')->sum('jumlah_deposit');

        return view('admin.sewa.index', compact('daftarSewa', 'filter', 'totalDipinjam', 'totalTerlambat', 'totalSelesai', 'totalDeposit'));
    }

    public function show($id)
    {
        $sewa = Sewa::with([
            'transaksi.user', 'transaksi.detail.produk', 'transaksi.pembayaran', 'jaminan.verifikator', 'pengecekan', 'denda',
        ])->findOrFail($id);

        DendaTelat::sinkron($sewa);
        $sewa->load('denda');

        $totalDenda = $sewa->denda->where('status', 'belum_lunas')->sum('jumlah');

        $timeline = [];
        $timeline[] = ['title' => 'Serah Terima & Fitting', 'date' => $sewa->created_at, 'desc' => 'Transaksi sewa dibuat dan jadwal disepakati.', 'color' => 'bg-gray-400'];

        $isTerlambat = \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->isPast() && $sewa->status !== 'selesai';
        $timeline[] = ['title' => $isTerlambat ? 'Tenggat Waktu Telah Terlewati' : 'Tenggat Waktu Pengembalian', 'date' => $sewa->tanggal_kembali_rencana, 'desc' => 'Batas waktu pengembalian busana.', 'color' => $isTerlambat ? 'bg-rose-500' : 'bg-amber-500'];

        if ($sewa->status === 'selesai' && $sewa->tanggal_kembali_aktual) {
            $timeline[] = ['title' => 'Busana Diterima Kembali', 'date' => $sewa->tanggal_kembali_aktual, 'desc' => 'Busana telah dikembalikan dan masuk proses QC.', 'color' => 'bg-emerald-500'];
        }

        return view('admin.sewa.show', compact('sewa', 'totalDenda', 'timeline'));
    }

    public function verifikasiJaminan(Request $request, $id)
    {
        $sewa = Sewa::with(['jaminan', 'transaksi.user'])->findOrFail($id);
        $jaminan = $sewa->jaminan ?? $sewa->jaminan()->make(['metode_jaminan' => 'ktp_tatap_muka', 'jumlah_deposit' => 0, 'status_pengembalian_deposit' => 'belum']);

        if ($jaminan->status_verifikasi === 'verified') {
            return back()->with('error', 'Jaminan ini sudah terverifikasi.')->with('active_tab', 'jaminan')->withFragment('tab-jaminan');
        }

        $request->validate(['konfirmasi_sesuai' => 'accepted'], ['konfirmasi_sesuai.accepted' => 'Centang dulu konfirmasi kecocokan KTP sebelum verifikasi.']);

        $jaminan->fill([
            'nama_ktp' => $jaminan->nama_ktp ?: $sewa->nama_pelanggan,
            'status_verifikasi' => 'verified',
            'diverifikasi_oleh' => auth('admin')->id(),
            'tanggal_verifikasi' => now(),
        ])->save();

        return back()->with('success', 'Jaminan berhasil diverifikasi!')->with('active_tab', 'jaminan')->withFragment('tab-jaminan');
    }

    public function kembalikanDeposit($id)
    {
        $sewa = Sewa::with(['jaminan', 'denda'])->findOrFail($id);

        if ($sewa->status !== 'selesai') {
            return back()->with('error', 'Barang belum ditandai kembali. Tandai sewa selesai dulu sebelum mengembalikan deposit.')->with('active_tab', 'denda')->withFragment('tab-denda');
        }

        if ($sewa->denda->where('status', 'belum_lunas')->isNotEmpty()) {
            return back()->with('error', 'Masih ada denda yang belum lunas. Lunasi semua denda dulu sebelum mengembalikan deposit.')->with('active_tab', 'denda')->withFragment('tab-denda');
        }

        if ($sewa->jaminan) {
            $sewa->jaminan->update(['status_pengembalian_deposit' => 'dikembalikan']);
        }

        return back()->with('success', 'Deposit sewa berhasil dikembalikan penuh!')->with('active_tab', 'denda')->withFragment('tab-denda');
    }

    // ================= FITUR QC =================
    public function simpanQC(Request $request, $id)
    {
        $sewa = Sewa::findOrFail($id);

        $request->validate([
            'tahap' => 'required|string',
            'catatan' => 'required|string',
            'foto_bukti' => 'nullable|image|max:2048',
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

        return back()->with('success', 'Catatan QC berhasil disimpan!')->with('active_tab', 'qc')->withFragment('tab-qc');
    }

    public function updateQC(Request $request, $id)
    {
        $qc = \App\Models\PengecekanBarang::findOrFail($id);

        $request->validate([
            'tahap' => 'required|string',
            'catatan' => 'required|string',
            'foto_bukti' => 'nullable|image|max:2048',
        ]);

        $data = ['tahap' => $request->tahap, 'catatan' => $request->catatan];

        if ($request->hasFile('foto_bukti')) {
            if ($qc->foto_bukti) {
                \Storage::disk('public')->delete($qc->foto_bukti);
            }
            $data['foto_bukti'] = $request->file('foto_bukti')->store('qc', 'public');
        }

        $qc->update($data);

        return back()->with('success', 'Catatan QC berhasil diperbarui!')->with('active_tab', 'qc')->withFragment('tab-qc');
    }

    public function hapusQC($id)
    {
        $qc = \App\Models\PengecekanBarang::findOrFail($id);
        $qc->delete();

        return back()->with('success', 'Catatan QC berhasil dihapus!')->with('active_tab', 'qc')->withFragment('tab-qc');
    }

    // ================= FITUR DENDA =================
    public function tambahDenda(Request $request, $id)
    {
        $sewa = Sewa::findOrFail($id);

        $request->validate([
            'jenis' => 'required|string',
            'jumlah' => 'required|string',
        ]);

        // Menghapus titik dari input nominal sebelum disimpan ke database
        $jumlahBersih = (float) str_replace('.', '', $request->jumlah);

        $sewa->denda()->create([
            'jenis' => $request->jenis,
            'jumlah' => $jumlahBersih,
            'status' => 'belum_lunas',
        ]);

        return back()->with('success', 'Denda berhasil ditambahkan!')->with('active_tab', 'denda')->withFragment('tab-denda');
    }

    public function lunasiDenda($id)
    {
        $denda = Denda::findOrFail($id);
        $denda->update(['status' => 'lunas']);

        return back()->with('success', 'Denda berhasil dilunasi!')->with('active_tab', 'denda')->withFragment('tab-denda');
    }

    public function hapusDenda($id)
    {
        $denda = Denda::findOrFail($id);
        $denda->delete();

        return back()->with('success', 'Data denda berhasil dihapus!')->with('active_tab', 'denda')->withFragment('tab-denda');
    }

    public function sinkronDendaTelat($id)
    {
        $sewa = Sewa::with(['transaksi.detail.produk', 'denda'])->findOrFail($id);

        DendaTelat::sinkron($sewa);

        return back()->with('success', 'Denda keterlambatan dihitung ulang.')->with('active_tab', 'denda')->withFragment('tab-denda');
    }

    public function ubahStatus(Request $request, $id)
    {
        $sewa = Sewa::findOrFail($id);
        $data = ['status' => $request->status];

        if ($request->status === 'selesai' && !$sewa->tanggal_kembali_aktual) {
            $data['tanggal_kembali_aktual'] = now();
        }

        $sewa->update($data);

        if ($request->status === 'selesai') {
            $sewa->refresh()->load('transaksi.detail.produk', 'denda');
            DendaTelat::sinkron($sewa, Carbon::parse($sewa->tanggal_kembali_aktual ?? now()));
        }

        return back()->with('success', 'Status sewa berhasil diubah menjadi ' . $request->status);
    }
}