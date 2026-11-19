<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sewa;
use App\Models\Transaksi;
use App\Models\Denda;
use App\Support\DendaTelat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        // Kolom tanggal_kembali_rencana hanya menyimpan TANGGAL (tanpa jam), sehingga dulu tampil 00:00.
        // Sistem menganggap sewa baru terlambat setelah tanggal tenggat berakhir, jadi tenggat = akhir hari itu (23:59).
        $tenggat = Carbon::parse($sewa->tanggal_kembali_rencana)->endOfDay();
        $isTerlambat = $tenggat->isPast() && $sewa->status !== 'selesai';
        $timeline[] = ['title' => $isTerlambat ? 'Tenggat Waktu Telah Terlewati' : 'Tenggat Waktu Pengembalian', 'date' => $tenggat, 'desc' => 'Batas waktu pengembalian busana.', 'color' => $isTerlambat ? 'bg-rose-500' : 'bg-amber-500'];

        if ($sewa->status === 'selesai' && $sewa->tanggal_kembali_aktual) {
            // Cast 'date' selalu memotong jam ke 00:00, jadi ambil nilai mentah dari database.
            $mentah = (string) $sewa->getRawOriginal('tanggal_kembali_aktual');
            $tanggalKembali = Carbon::parse($mentah);
            $waktuKembali = (strlen($mentah) > 10 && !str_ends_with($mentah, '00:00:00')) ? $tanggalKembali : null;

            // Data lama (kolom hanya menyimpan tanggal): baris sewa terakhir diubah saat admin menandai
            // barang kembali, jadi pakai updated_at kalau jatuh di hari yang sama.
            if (!$waktuKembali && $sewa->updated_at && $sewa->updated_at->isSameDay($tanggalKembali)) {
                $waktuKembali = $sewa->updated_at;
            }

            $timeline[] = [
                'title' => 'Busana Diterima Kembali',
                'date' => $waktuKembali ?? $tanggalKembali,
                'tampil_jam' => (bool) $waktuKembali,
                'desc' => 'Busana telah dikembalikan dan masuk proses QC.',
                'color' => 'bg-emerald-500',
            ];
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
            return back()->with('error', 'Barang belum ditandai kembali.')->with('active_tab', 'denda')->withFragment('tab-denda');
        }

        $deposit = $sewa->jaminan->jumlah_deposit ?? 0;
        $dendaBelumLunas = $sewa->denda->where('status', 'belum_lunas');
        $totalDenda = $dendaBelumLunas->sum('jumlah');

        // Cek jika denda melebihi deposit
        if ($totalDenda > $deposit) {
            return back()->with('error', 'Total denda (Rp '.number_format($totalDenda,0,',','.').') melebihi deposit (Rp '.number_format($deposit,0,',','.').'). Silakan tagih kekurangan denda secara manual (Tandai Lunas) terlebih dahulu.')->with('active_tab', 'denda')->withFragment('tab-denda');
        }

        // Jika deposit cukup atau lebih, lunasi denda otomatis
        if ($totalDenda > 0) {
            foreach ($dendaBelumLunas as $denda) {
                $denda->update(['status' => 'lunas']);
            }
        }

        // Tandai deposit sudah dikembalikan
        if ($sewa->jaminan) {
            $sewa->jaminan->update(['status_pengembalian_deposit' => 'dikembalikan']);
        }

        return back()->with('success', 'Settlement berhasil! Denda telah dipotong dari deposit, dan sisa deposit telah dicairkan.')->with('active_tab', 'denda')->withFragment('tab-denda');
    }

    // ================= FITUR QC =================
    public function simpanQC(Request $request, $id)
    {
        $sewa = Sewa::findOrFail($id);

        $request->validate([
            'tahap' => 'required|string',
            'catatan' => 'required|string',
            'foto_bukti' => 'nullable|image|max:2048',
            'kerusakan' => 'nullable|array',
            'kerusakan.*.label' => 'nullable|string|max:200',
            'kerusakan.*.jumlah' => 'nullable|numeric|min:0|max:1000000000',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('qc', 'public');
        }

        // Kerusakan yang dicentang di form QC -> otomatis jadi denda (status belum lunas)
        $dendaBaru = collect($request->input('kerusakan', []))
            ->filter(fn ($k) => !empty($k['pakai']) && !empty($k['label']) && (float) ($k['jumlah'] ?? 0) > 0)
            ->map(fn ($k) => [
                'jenis' => Str::limit('Kerusakan: ' . trim($k['label']), 250, ''),
                'jumlah' => (float) $k['jumlah'],
                'status' => 'belum_lunas',
            ])
            ->values();

        // QC dan denda disimpan sekaligus: kalau salah satu gagal, keduanya dibatalkan
        DB::transaction(function () use ($sewa, $request, $fotoPath, $dendaBaru) {
            $sewa->pengecekan()->create([
                'tahap' => $request->tahap,
                'catatan' => $request->catatan,
                'petugas' => auth('admin')->user()->name ?? 'Admin',
                'foto_bukti' => $fotoPath,
            ]);

            foreach ($dendaBaru as $denda) {
                $sewa->denda()->create($denda);
            }
        });

        $pesan = 'Catatan QC berhasil disimpan!';
        if ($dendaBaru->isNotEmpty()) {
            $pesan .= ' ' . $dendaBaru->count() . ' denda kerusakan (total Rp '
                . number_format($dendaBaru->sum('jumlah'), 0, ',', '.')
                . ') otomatis ditambahkan ke tab Kelola Denda & Settlement.';
        }

        return back()->with('success', $pesan)->with('active_tab', 'qc')->withFragment('tab-qc');
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
        if ($qc->foto_bukti) {
            \Storage::disk('public')->delete($qc->foto_bukti);
        }
        $qc->delete();

        return back()->with('success', 'Catatan QC berhasil dihapus!')->with('active_tab', 'qc')->withFragment('tab-qc');
    }

    // ================= FITUR DENDA =================
    public function tambahDenda(Request $request, $id)
    {
        $sewa = Sewa::findOrFail($id);
        $request->validate([
            'jenis'  => 'required|string|max:250',
            'jumlah' => ['required', 'string', 'regex:/^[0-9.]+$/'],
        ], ['jumlah.regex' => 'Nominal denda hanya boleh berisi angka.']);

        // Menghapus titik dari input nominal sebelum disimpan ke database
        $jumlahBersih = (float) str_replace('.', '', $request->jumlah);

        if ($jumlahBersih <= 0 || $jumlahBersih > 1000000000) {
            return back()->with('error', 'Nominal denda tidak valid.')->with('active_tab', 'denda')->withFragment('tab-denda');
        }

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
        $request->validate(['status' => ['required', 'string', 'max:30']]);
        $data = ['status' => $request->status];

        if ($request->status === 'selesai' && !$sewa->tanggal_kembali_aktual) {
            $data['tanggal_kembali_aktual'] = now();
        }

        $lunasOtomatis = false;

        DB::transaction(function () use ($sewa, $data, $request, &$lunasOtomatis) {
            $sewa->update($data);

            // Barang sudah dikembalikan -> pembayaran transaksi otomatis LUNAS.
            // Transaksi online multi-item punya satu baris Sewa per item, jadi
            // baru dilunasi kalau SEMUA item sudah dikembalikan.
            if ($request->status === 'selesai') {
                $masihAdaYangAktif = Sewa::where('transaksi_id', $sewa->transaksi_id)
                    ->where('status', '!=', 'selesai')
                    ->exists();

                if (! $masihAdaYangAktif) {
                    $lunasOtomatis = Transaksi::find($sewa->transaksi_id)?->tandaiLunas() ?? false;
                }
            }
        });

        if ($request->status === 'selesai') {
            $sewa->refresh()->load('transaksi.detail.produk', 'denda');
            DendaTelat::sinkron($sewa, Carbon::parse($sewa->tanggal_kembali_aktual ?? now()));
        }

        return back()->with(
            'success',
            'Status sewa berhasil diubah menjadi ' . $request->status
                . ($lunasOtomatis ? '. Pembayaran transaksi otomatis ditandai LUNAS.' : '')
        );
    }
}