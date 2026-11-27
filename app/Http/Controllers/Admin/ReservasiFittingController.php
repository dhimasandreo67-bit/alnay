<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservasiFitting;
use App\Models\SlotFitting;
use App\Models\Transaksi;
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
        if (! in_array($status, ['semua', 'belum_jadwal', 'terjadwal', 'hari_ini', 'hadir', 'batal'], true)) {
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
            // Terjadwal = sudah punya slot. Reservasi tanpa slot belum dijadwalkan pelanggan.
            'terjadwal' => $query->whereNotIn('status', self::STATUS_SELESAI)->whereNotNull('slot_id'),
            'hari_ini' => $query->whereNotIn('status', self::STATUS_SELESAI)
                ->whereHas('slot', fn ($q) => $q->whereDate('tanggal', today())),
            'belum_jadwal' => $query->whereRaw('0 = 1'),
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
            'terjadwal' => $aktif()->whereNotNull('slot_id')->count(),
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
        // Pesanan yang pelanggannya belum memilih jadwal ikut tampil di tabel (halaman 1) dengan status "Belum Isi Jadwal".
        $semuaMenunggu = $this->pesananMenungguJadwal();
        $hitung['belum_jadwal'] = $semuaMenunggu->count();
        $hitung['semua'] += $semuaMenunggu->count();

        $menungguJadwal = collect();
        if (in_array($status, ['semua', 'belum_jadwal'], true)
            && ! $request->filled('tanggal')
            && (int) $request->input('page', 1) <= 1) {
            $menungguJadwal = $this->saringMenunggu($semuaMenunggu, trim((string) $request->input('search', '')));
        }

        return view('admin.fitting.index', compact('reservasis', 'status', 'hitung', 'slotTutup', 'jamSlot', 'menungguJadwal'));
    }

    /**
     * Pesanan sewa (ambil di toko, DP sudah dibayar) yang pelanggannya BELUM mengisi jadwal fitting.
     * Mereka belum punya reservasi, jadi tidak punya tanggal/jam. Tampil terpisah supaya admin
     * tidak mengira sudah ada jadwal. Begitu pelanggan menyimpan jadwal, pesanan pindah ke tabel utama.
     */
    private function pesananMenungguJadwal()
    {
        return Transaksi::with(['user', 'detail.produk', 'sewa'])
            ->where('jenis', 'sewa')
            ->whereNotNull('user_id')
            ->whereIn('status_pembayaran', ['dp_dibayar', 'lunas'])
            ->whereDoesntHave('pengiriman')
            ->whereDoesntHave('reservasiFitting', fn ($q) => $q->whereIn('status', ['pending', 'disetujui', 'hadir']))
            ->whereHas('sewa', fn ($q) => $q->where('status', 'aktif')->whereDate('tanggal_pinjam', '>=', today()))
            ->get()
            ->sortBy(fn ($t) => $t->sewa?->tanggal_pinjam) // batas fitting terdekat paling atas
            ->values();
    }

    /** Terapkan kata kunci pencarian (kode pesanan, nama, nomor HP) ke daftar pesanan yang menunggu jadwal. */
    private function saringMenunggu($daftar, string $kata)
    {
        if ($kata === '') {
            return $daftar;
        }

        $inti = preg_replace('/^(62|0)+/', '', preg_replace('/\D+/', '', $kata));

        return $daftar->filter(function ($t) use ($kata, $inti) {
            return stripos((string) $t->kode_transaksi, $kata) !== false
                || stripos((string) ($t->user->name ?? ''), $kata) !== false
                || stripos((string) ($t->user->no_hp ?? ''), $kata) !== false
                || (strlen($inti) >= 4 && str_contains((string) ($t->user->no_hp ?? ''), $inti));
        })->values();
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
        $request->validate([
            'ids'   => ['required', 'array', 'max:200'],
            'ids.*' => ['integer'],
        ]);

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