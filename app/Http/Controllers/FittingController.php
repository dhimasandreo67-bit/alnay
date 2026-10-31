<?php

namespace App\Http\Controllers;

use App\Models\ReservasiFitting;
use App\Models\SlotFitting;
use App\Models\Sewa;
use App\Models\Produk;
use Illuminate\Http\Request;

class FittingController extends Controller
{
    /**
     * Menampilkan form booking fitting.
     */
    public function create()
    {
        $tanggalPenuh = [];
        $slotJam = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];
        $daftarProduk = Produk::where('bisa_disewa', true)->orderBy('nama')->get();

        // Ambil sewa aktif user untuk membatasi tanggal fitting
        $sewaAktif = Sewa::whereHas('transaksi', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->where('status', 'aktif')
            ->whereDate('tanggal_kembali_rencana', '>=', now())
            ->latest()
            ->first();

        // Ambil slot yang sudah terisi
        $slotTerisi = $this->getSlotTerisi();

        return view('customer.fitting', compact(
            'tanggalPenuh', 'slotJam', 'slotTerisi', 'daftarProduk', 'sewaAktif'
        ));
    }

    /**
     * Riwayat booking fitting user.
     */
    public function index()
    {
        $reservasis = ReservasiFitting::with('slot')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.fitting-riwayat', compact('reservasis'));
    }

    /**
     * Simpan booking fitting baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', 'string'],
            'nama' => ['required', 'string', 'max:100'],
            'no_wa' => ['required', 'string', 'max:20'],
            'produk' => ['nullable', 'string', 'max:200'],
            'catatan' => ['nullable', 'string'],
        ]);

        // ==== VALIDASI: Cek Tanggal Fitting vs Tanggal Sewa ====
        $sewaAktif = Sewa::whereHas('transaksi', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->where('status', 'aktif')
            ->whereDate('tanggal_kembali_rencana', '>=', now())
            ->latest()
            ->first();

        if ($sewaAktif && $validated['tanggal'] > $sewaAktif->tanggal_kembali_rencana) {
            return back()->withErrors([
                'tanggal' => 'Tanggal fitting tidak boleh melebihi tanggal selesai sewa Anda (' .
                    \Carbon\Carbon::parse($sewaAktif->tanggal_kembali_rencana)->format('d M Y') . ').'
            ])->withInput();
        }

        // ==== VALIDASI: Cek Slot Sudah Terisi ====
        $slotCek = ReservasiFitting::whereHas('slot', function ($q) use ($validated) {
                $q->where('tanggal', $validated['tanggal'])
                    ->where('jam_mulai', $validated['jam']);
            })
            ->whereIn('status', ['pending', 'disetujui'])
            ->exists();

        if ($slotCek) {
            return back()->withErrors([
                'jam' => 'Maaf, slot jam ' . $validated['jam'] . ' di tanggal ' .
                    \Carbon\Carbon::parse($validated['tanggal'])->format('d M Y') .
                    ' sudah dibooking. Silakan pilih jam atau tanggal lain.'
            ])->withInput();
        }
        // =========================================

        $slot = SlotFitting::firstOrCreate(
            ['tanggal' => $validated['tanggal'], 'jam_mulai' => $validated['jam']],
            ['kapasitas' => 4]
        );

        // Cari ID produk berdasarkan nama
        $produkId = null;
        if (!empty($validated['produk'])) {
            $produk = Produk::where('nama', $validated['produk'])->first();
            if ($produk) {
                $produkId = $produk->id;
            }
        }

        $kodeBooking = 'ALN-FT-' . strtoupper(substr(uniqid(), -5));

        $reservasi = ReservasiFitting::create([
            'kode_booking' => $kodeBooking,
            'user_id' => auth()->id(),
            'slot_id' => $slot->id,
            'produk_id' => $produkId,
            'tanggal_daftar' => now()->toDateString(),
            'status' => 'pending',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('fitting.show', $reservasi->id)
            ->with('success', 'Booking fitting berhasil dibuat!');
    }

    /**
     * Detail satu reservasi fitting.
     */
    public function show($id)
    {
        $reservasi = ReservasiFitting::with(['slot', 'user', 'produk'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $namaProduk = $reservasi->produk ? $reservasi->produk->nama : 'Sesi Fitting Umum';
        $fotoProduk = null;

        if ($reservasi->produk) {
            $fotoProduk = $reservasi->produk->foto_produk;
            if (is_array($fotoProduk)) {
                $fotoProduk = $fotoProduk[0] ?? null;
            }
        }

        return view('customer.fitting-detail', compact('reservasi', 'namaProduk', 'fotoProduk'));
    }

    /**
     * Batalkan booking fitting.
     */
    public function cancel($id)
    {
        $reservasi = ReservasiFitting::where('user_id', auth()->id())->findOrFail($id);
        $reservasi->update(['status' => 'batal']);

        return back()->with('success', 'Booking fitting berhasil dibatalkan.');
    }

    /**
     * Form edit jadwal fitting.
     */
    public function edit($id)
    {
        $reservasi = ReservasiFitting::with('slot')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $tanggalPenuh = [];
        $slotJam = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];
        $daftarProduk = Produk::where('bisa_disewa', true)->orderBy('nama')->get();

        // Ambil slot terisi (kecuali milik reservasi ini sendiri)
        $slotTerisi = $this->getSlotTerisi($reservasi->id);

        // Data sewa aktif (untuk validasi tanggal)
        $sewaAktif = Sewa::whereHas('transaksi', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->where('status', 'aktif')
            ->whereDate('tanggal_kembali_rencana', '>=', now())
            ->latest()
            ->first();

        return view('customer.fitting-edit', compact(
            'reservasi', 'tanggalPenuh', 'slotJam', 'slotTerisi', 'daftarProduk', 'sewaAktif'
        ));
    }

    /**
     * Update jadwal fitting.
     */
    public function update(Request $request, $id)
    {
        $reservasi = ReservasiFitting::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', 'string'],
        ]);

        // ==== VALIDASI: Cek Tanggal Fitting vs Sewa ====
        $sewaAktif = Sewa::whereHas('transaksi', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->where('status', 'aktif')
            ->whereDate('tanggal_kembali_rencana', '>=', now())
            ->latest()
            ->first();

        if ($sewaAktif && $validated['tanggal'] > $sewaAktif->tanggal_kembali_rencana) {
            return back()->withErrors([
                'tanggal' => 'Tanggal fitting tidak boleh melebihi tanggal selesai sewa Anda (' .
                    \Carbon\Carbon::parse($sewaAktif->tanggal_kembali_rencana)->format('d M Y') . ').'
            ])->withInput();
        }

        // ==== VALIDASI: Cek Slot Terisi (Kecuali milik sendiri) ====
        $slotCek = ReservasiFitting::whereHas('slot', function ($q) use ($validated) {
                $q->where('tanggal', $validated['tanggal'])
                    ->where('jam_mulai', $validated['jam']);
            })
            ->whereIn('status', ['pending', 'disetujui'])
            ->where('id', '!=', $reservasi->id) // Kecualikan reservasi ini sendiri
            ->exists();

        if ($slotCek) {
            return back()->withErrors([
                'jam' => 'Maaf, slot jam ' . $validated['jam'] . ' di tanggal ' .
                    \Carbon\Carbon::parse($validated['tanggal'])->format('d M Y') .
                    ' sudah dibooking oleh user lain. Silakan pilih jam atau tanggal lain.'
            ])->withInput();
        }
        // =============================================================

        $slot = SlotFitting::firstOrCreate(
            ['tanggal' => $validated['tanggal'], 'jam_mulai' => $validated['jam']],
            ['kapasitas' => 4]
        );

        $reservasi->update([
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        return redirect()->route('fitting.show', $reservasi->id)
            ->with('success', 'Jadwal fitting berhasil diubah!');
    }

    /**
     * Helper: Ambil slot terisi dari database.
     * Return format: ['2024-11-01' => ['09:00', '13:30'], ...]
     */
    private function getSlotTerisi($kecualiReservasiId = null)
    {
        $query = ReservasiFitting::with('slot')
            ->whereIn('status', ['pending', 'disetujui'])
            ->whereHas('slot', function ($q) {
                $q->whereDate('tanggal', '>=', now());
            });

        // Jika sedang edit, kecualikan reservasi milik sendiri (agar slotnya sendiri tidak dianggap terisi)
        if ($kecualiReservasiId) {
            $query->where('id', '!=', $kecualiReservasiId);
        }

        $reservasiTerisi = $query->get();

        $slotTerisi = [];
        foreach ($reservasiTerisi as $r) {
            if ($r->slot) {
                // Konversi Carbon ke string agar bisa jadi key array
                $tanggal = $r->slot->tanggal instanceof \Carbon\Carbon 
                    ? $r->slot->tanggal->format('Y-m-d') 
                    : $r->slot->tanggal;
                
                $jam = $r->slot->jam_mulai;
                if (!isset($slotTerisi[$tanggal])) {
                    $slotTerisi[$tanggal] = [];
                }
                $slotTerisi[$tanggal][] = \Carbon\Carbon::parse($jam)->format('H:i');
            }
        }

        return $slotTerisi;
    }
}