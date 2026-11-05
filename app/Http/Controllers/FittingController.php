<?php

namespace App\Http\Controllers;

use App\Models\ReservasiFitting;
use App\Models\SlotFitting;
use App\Models\Sewa;
use App\Models\Produk;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FittingController extends Controller
{
    public function create(Request $request) 
    {
        $transaksiId = $request->query('transaksi_id');
        
        $tanggalPenuh = [];
        $slotJam = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];
        $daftarProduk = Produk::where('bisa_disewa', true)->orderBy('nama')->get();

        $sewaAktif = null;
        if ($transaksiId) {
            // KUNCI UTAMA: Ambil sewa spesifik berdasarkan transaksi_id dari URL
            $sewaAktif = Sewa::where('transaksi_id', $transaksiId)->first();
        } else {
            // Fallback jika tidak ada transaksi_id di URL
            $sewaAktif = Sewa::whereHas('transaksi', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->where('status', 'aktif')
                ->whereDate('tanggal_pinjam', '>=', now())
                ->latest()
                ->first();
        }

        // Format tanggal maksimal ke Y-m-d agar cocok dengan JavaScript
        $tanggalMaksimal = $sewaAktif ? Carbon::parse($sewaAktif->tanggal_pinjam)->format('Y-m-d') : null;

        $slotTerisi = $this->getSlotTerisi();

        return view('customer.fitting', compact(
            'tanggalPenuh', 'slotJam', 'slotTerisi', 'daftarProduk', 'sewaAktif', 'tanggalMaksimal', 'transaksiId'
        ));
    }

    public function index()
    {
        $reservasis = ReservasiFitting::with('slot')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.fitting-riwayat', compact('reservasis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', 'string'],
            'nama' => ['required', 'string', 'max:100'],
            'no_wa' => ['required', 'string', 'max:20'],
            'produk' => ['nullable', 'string', 'max:200'],
            'catatan' => ['nullable', 'string'],
            'transaksi_id' => ['nullable', 'exists:transaksi,id'],
        ]);

        // ==== VALIDASI: Cek Tanggal Fitting vs Tanggal Sewa ====
        $sewaAktif = null;
        if (!empty($validated['transaksi_id'])) {
            $sewaAktif = Sewa::where('transaksi_id', $validated['transaksi_id'])->first();
        } else {
            $sewaAktif = Sewa::whereHas('transaksi', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->where('status', 'aktif')
                ->whereDate('tanggal_pinjam', '>=', now())
                ->latest()
                ->first();
        }

        if ($sewaAktif && Carbon::parse($validated['tanggal'])->greaterThan(Carbon::parse($sewaAktif->tanggal_pinjam))) {
            return back()->withErrors([
                'tanggal' => 'Tanggal fitting tidak boleh melebihi tanggal pengambilan busana Anda (' .
                    Carbon::parse($sewaAktif->tanggal_pinjam)->format('d M Y') . ').'
            ])->withInput();
        }

        // ==== VALIDASI: Cek Kapasitas Slot ====
        $slot = SlotFitting::firstOrCreate(
            ['tanggal' => $validated['tanggal'], 'jam_mulai' => $validated['jam']],
            ['kapasitas' => 4]
        );

        $terisi = ReservasiFitting::where('slot_id', $slot->id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->count();

        if ($terisi >= $slot->kapasitas) {
            return back()->withErrors([
                'jam' => 'Maaf, slot jam ' . $validated['jam'] . ' di tanggal ' .
                    Carbon::parse($validated['tanggal'])->format('d M Y') .
                    ' sudah penuh. Silakan pilih jam atau tanggal lain.'
            ])->withInput();
        }

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
            'transaksi_id' => $validated['transaksi_id'] ?? null,
            'slot_id' => $slot->id,
            'produk_id' => $produkId,
            'tanggal_daftar' => now()->toDateString(),
            'status' => 'pending',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('fitting.show', $reservasi->id)
            ->with('success', 'Booking fitting berhasil dibuat!');
    }

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

    public function cancel($id)
    {
        $reservasi = ReservasiFitting::where('user_id', auth()->id())->findOrFail($id);
        $reservasi->update(['status' => 'batal']);

        return back()->with('success', 'Booking fitting berhasil dibatalkan.');
    }

    public function edit($id)
    {
        $reservasi = ReservasiFitting::with('slot')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $tanggalPenuh = [];
        $slotJam = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];
        $daftarProduk = Produk::where('bisa_disewa', true)->orderBy('nama')->get();

        $slotTerisi = $this->getSlotTerisi($reservasi->id);

        $sewaAktif = Sewa::whereHas('transaksi', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->where('status', 'aktif')
            ->whereDate('tanggal_pinjam', '>=', now())
            ->latest()
            ->first();

        $tanggalMaksimal = $sewaAktif ? Carbon::parse($sewaAktif->tanggal_pinjam)->format('Y-m-d') : null;

        return view('customer.fitting-edit', compact(
            'reservasi', 'tanggalPenuh', 'slotJam', 'slotTerisi', 'daftarProduk', 'sewaAktif', 'tanggalMaksimal'
        ));
    }

    public function update(Request $request, $id)
    {
        $reservasi = ReservasiFitting::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', 'string'],
        ]);

        $sewaAktif = null;
        if ($reservasi->transaksi_id) {
            $sewaAktif = Sewa::where('transaksi_id', $reservasi->transaksi_id)->first();
        } else {
            $sewaAktif = Sewa::whereHas('transaksi', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->where('status', 'aktif')
                ->whereDate('tanggal_pinjam', '>=', now())
                ->latest()
                ->first();
        }

        if ($sewaAktif && Carbon::parse($validated['tanggal'])->greaterThan(Carbon::parse($sewaAktif->tanggal_pinjam))) {
            return back()->withErrors([
                'tanggal' => 'Tanggal fitting tidak boleh melebihi tanggal pengambilan busana Anda (' .
                    Carbon::parse($sewaAktif->tanggal_pinjam)->format('d M Y') . ').'
            ])->withInput();
        }

        $slot = SlotFitting::firstOrCreate(
            ['tanggal' => $validated['tanggal'], 'jam_mulai' => $validated['jam']],
            ['kapasitas' => 4]
        );

        $terisi = ReservasiFitting::where('slot_id', $slot->id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->where('id', '!=', $reservasi->id)
            ->count();

        if ($terisi >= $slot->kapasitas) {
            return back()->withErrors([
                'jam' => 'Maaf, slot jam ' . $validated['jam'] . ' di tanggal ' .
                    Carbon::parse($validated['tanggal'])->format('d M Y') .
                    ' sudah penuh. Silakan pilih jam atau tanggal lain.'
            ])->withInput();
        }

        $reservasi->update([
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        return redirect()->route('fitting.show', $reservasi->id)
            ->with('success', 'Jadwal fitting berhasil diubah!');
    }

    private function getSlotTerisi($kecualiReservasiId = null)
    {
        $query = ReservasiFitting::with('slot')
            ->whereIn('status', ['pending', 'disetujui'])
            ->whereHas('slot', function ($q) {
                $q->whereDate('tanggal', '>=', now());
            });

        if ($kecualiReservasiId) {
            $query->where('id', '!=', $kecualiReservasiId);
        }

        $reservasiTerisi = $query->get();

        $slotTerisi = [];
        foreach ($reservasiTerisi as $r) {
            if ($r->slot) {
                $tanggal = $r->slot->tanggal instanceof Carbon 
                    ? $r->slot->tanggal->format('Y-m-d') 
                    : $r->slot->tanggal;
                
                $jam = Carbon::parse($r->slot->jam_mulai)->format('H:i');
                
                if (!isset($slotTerisi[$tanggal])) {
                    $slotTerisi[$tanggal] = [];
                }
                if (!isset($slotTerisi[$tanggal][$jam])) {
                    $slotTerisi[$tanggal][$jam] = 0;
                }
                $slotTerisi[$tanggal][$jam]++;
            }
        }

        return $slotTerisi;
    }
}