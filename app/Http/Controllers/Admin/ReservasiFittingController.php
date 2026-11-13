<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservasiFitting;
use App\Models\SlotFitting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservasiFittingController extends Controller
{
    /** Status yang sudah "selesai" (tidak lagi memakai kuota). 'pending'/'disetujui' = Terjadwal. */
    private const STATUS_SELESAI = ['hadir', 'batal'];

    public function index(Request $request)
    {
        $status = $request->input('status', 'semua');
        if (! in_array($status, ['semua', 'terjadwal', 'hari_ini', 'hadir', 'batal'], true)) {
            $status = 'semua';
        }

        $with = ['user', 'slot', 'produk'];
        // Relasi transaksi (ukuran, warna, tanggal ambil) hanya dimuat kalau sudah ada di model ReservasiFitting.
        if (method_exists(ReservasiFitting::class, 'transaksi')) {
            $with[] = 'transaksi.detail.produk';
            $with[] = 'transaksi.sewa';
        }

        $query = ReservasiFitting::with($with);

        // Pencarian: kode booking, nama, atau nomor HP (08xx / 628xx / 8xx sama-sama cocok)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $inti = preg_replace('/^(62|0)+/', '', preg_replace('/\D+/', '', $search));

            $query->where(function ($q) use ($search, $inti) {
                $q->where('kode_booking', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($u) use ($search, $inti) {
                        $u->where('name', 'like', '%' . $search . '%')
                            ->orWhere('no_hp', 'like', '%' . $search . '%');

                        if (strlen($inti) >= 4) {
                            $u->orWhere('no_hp', 'like', '%' . $inti . '%');
                        }
                    });
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereHas('slot', fn ($q) => $q->whereDate('tanggal', $request->tanggal));
        }

        match ($status) {
            'terjadwal' => $query->whereNotIn('status', self::STATUS_SELESAI),
            'hari_ini' => $query->whereNotIn('status', self::STATUS_SELESAI)
                ->whereHas('slot', fn ($q) => $q->whereDate('tanggal', today())),
            'hadir', 'batal' => $query->where('status', $status),
            default => null,
        };

        // Yang akan datang diurutkan dari jadwal terdekat; lainnya dari yang terbaru.
        if (in_array($status, ['terjadwal', 'hari_ini'], true)) {
            $tgl = SlotFitting::select('tanggal')->whereColumn('slot_fitting.id', 'reservasi_fitting.slot_id');
            $jam = SlotFitting::select('jam_mulai')->whereColumn('slot_fitting.id', 'reservasi_fitting.slot_id');
            $query->orderBy($tgl)->orderBy($jam);
        } else {
            $query->latest();
        }

        $reservasis = $query->paginate(15)->withQueryString();

        $aktif = fn () => ReservasiFitting::whereNotIn('status', self::STATUS_SELESAI);
        $hitung = [
            'semua' => ReservasiFitting::count(),
            'hari_ini' => $aktif()->whereHas('slot', fn ($q) => $q->whereDate('tanggal', today()))->count(),
            'terjadwal' => $aktif()->count(),
            'hadir' => ReservasiFitting::where('status', 'hadir')->count(),
            'batal' => ReservasiFitting::where('status', 'batal')->count(),
        ];

        // Jam yang sedang ditutup (mulai hari ini), dikelompokkan per tanggal
        $slotTutup = SlotFitting::where('kapasitas', '<=', 0)
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->get()
            ->groupBy(fn ($s) => $s->tanggal->format('Y-m-d'));

        $jamSlot = SlotFitting::JAM;

        return view('admin.fitting.index', compact('reservasis', 'status', 'hitung', 'slotTutup', 'jamSlot'));
    }

    /** Ubah status: Terjadwal / Hadir / Batal (Batal wajib beralasan). */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,hadir,batal',
            'alasan' => 'required_if:status,batal|nullable|string|min:3|max:200',
        ], [
            'alasan.required_if' => 'Alasan pembatalan wajib diisi agar pelanggan tahu penyebabnya.',
        ]);

        $reservasi = ReservasiFitting::with('slot')->findOrFail($id);
        $baru = $request->status;
        $lama = strtolower((string) $reservasi->status);

        if ($baru === 'batal') {
            // forceFill: kolom baru tidak bergantung pada $fillable di model
            $reservasi->forceFill([
                'status' => 'batal',
                'alasan_batal' => trim($request->alasan),
                'dibatalkan_oleh' => 'admin',
            ])->save();

            return back()->with('success', 'Reservasi dibatalkan. Hubungi pelanggan lewat tombol WhatsApp agar memilih jadwal baru.');
        }

        // Mengaktifkan kembali reservasi yang sudah batal: pastikan jamnya masih buka dan ada kuota.
        if ($baru === 'pending' && $lama === 'batal' && $reservasi->slot) {
            $slot = $reservasi->slot;

            if ($slot->ditutup()) {
                return back()->with('error', 'Jam ini sedang ditutup, reservasi tidak bisa diaktifkan kembali.');
            }

            $terisi = ReservasiFitting::where('slot_id', $slot->id)
                ->whereIn('status', ['pending', 'disetujui'])
                ->where('id', '!=', $reservasi->id)
                ->count();

            if ($terisi >= $slot->kapasitasTersedia()) {
                return back()->with('error', 'Jam ini sudah penuh, reservasi tidak bisa diaktifkan kembali.');
            }
        }

        $reservasi->forceFill([
            'status' => $baru,
            'alasan_batal' => null,
            'dibatalkan_oleh' => null,
        ])->save();

        // Respon JSON untuk AJAX
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Status diperbarui.']);
        }

        return back()->with('success', 'Status reservasi fitting berhasil diperbarui.');
    }

    // Fungsi untuk menghapus reservasi (Sudah mendukung AJAX)
    public function destroy(Request $request, $id)
    {
        $reservasi = ReservasiFitting::findOrFail($id);
        
        // Proteksi: Cegah hapus data yang statusnya sudah HADIR
        if ($reservasi->status === 'hadir') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Reservasi HADIR tidak boleh dihapus.']);
            }
            return back()->with('error', 'Reservasi yang sudah HADIR tidak boleh dihapus.');
        }

        $reservasi->delete();

        // Respon JSON untuk AJAX
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Reservasi dihapus.']);
        }

        return back()->with('success', 'Reservasi fitting berhasil dihapus permanen.');
    }

    // Fungsi untuk hapus massal
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);
        }

        $deletedCount = ReservasiFitting::whereIn('id', $ids)
            ->where('status', '!=', 'hadir')
            ->delete();

        return response()->json([
            'success' => true, 
            'message' => $deletedCount . ' reservasi berhasil dihapus.'
        ]);
    }

    /** Tutup seharian atau jam tertentu. Booking yang sudah ada bisa dibatalkan sekaligus. */
    public function tutupSlot(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'seharian' => ['nullable', 'boolean'],
            'jam' => ['nullable', 'array'],
            'jam.*' => [Rule::in(SlotFitting::JAM)],
            'alasan' => ['required', 'string', 'min:3', 'max:200'],
            'batalkan_booking' => ['nullable', 'boolean'],
        ], [
            'alasan.required' => 'Alasan penutupan wajib diisi.',
        ]);

        $daftarJam = $request->boolean('seharian') ? SlotFitting::JAM : array_values($data['jam'] ?? []);

        if (! $daftarJam) {
            return back()->withInput()->with('error', 'Pilih minimal satu jam, atau centang "Tutup seharian".');
        }

        $hasil = DB::transaction(function () use ($data, $daftarJam, $request) {
            $slotIds = [];
            foreach ($daftarJam as $jam) {
                $slotIds[] = SlotFitting::firstOrCreate(
                    ['tanggal' => $data['tanggal'], 'jam_mulai' => $jam],
                    ['kapasitas' => SlotFitting::KAPASITAS_DEFAULT]
                )->id;
            }

            $terdampak = ReservasiFitting::whereIn('slot_id', $slotIds)
                ->whereIn('status', ['pending', 'disetujui'])
                ->get();

            if ($terdampak->isNotEmpty() && ! $request->boolean('batalkan_booking')) {
                return ['ditolak' => $terdampak->count()];
            }

            foreach ($terdampak as $r) {
                $r->forceFill([
                    'status' => 'batal',
                    'alasan_batal' => 'Jadwal fitting ditiadakan: ' . trim($data['alasan']),
                    'dibatalkan_oleh' => 'admin',
                ])->save();
            }

            SlotFitting::whereIn('id', $slotIds)->update(['kapasitas' => 0]);

            return ['jam' => count($slotIds), 'batal' => $terdampak->count()];
        });

        if (isset($hasil['ditolak'])) {
            return back()->withInput()->with('buka_panel', true)->with('error',
                'Ada ' . $hasil['ditolak'] . ' booking aktif di jam yang akan ditutup. Centang "Batalkan booking yang sudah ada" '
                . 'atau batalkan manual lebih dulu.');
        }

        $pesan = $hasil['jam'] . ' jam pada ' . \Carbon\Carbon::parse($data['tanggal'])->format('d M Y') . ' berhasil ditutup.';
        if ($hasil['batal'] > 0) {
            $pesan .= ' ' . $hasil['batal'] . ' booking dibatalkan, hubungi pelanggannya lewat WhatsApp agar memilih jadwal baru.';
        }

        return back()->with('success', $pesan)->with('buka_panel', true);
    }

    /** Buka kembali: satu jam (tanggal + jam) atau semua jam yang ditutup pada satu tanggal. */
    public function bukaSlot(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam' => ['nullable', Rule::in(SlotFitting::JAM)],
        ]);

        $jumlah = SlotFitting::whereDate('tanggal', $data['tanggal'])
            ->where('kapasitas', '<=', 0)
            ->when($data['jam'] ?? null, fn ($q, $jam) => $q->where('jam_mulai', $jam))
            ->update(['kapasitas' => SlotFitting::KAPASITAS_DEFAULT]);

        return back()->with('success', $jumlah . ' jam dibuka kembali.')->with('buka_panel', true);
    }
}