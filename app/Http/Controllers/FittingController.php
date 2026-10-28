<?php

namespace App\Http\Controllers;

use App\Models\ReservasiFitting;
use App\Models\SlotFitting;
use Illuminate\Http\Request;

class FittingController extends Controller
{
    public function create()
    {
        // Ambil tanggal yang sudah penuh (misal: jika kapasitas > 4 di tanggal itu)
        // Untuk sementara kita kosongkan dulu agar semua tanggal bisa dipilih
        $tanggalPenuh = [];

        // Ambil jam yang sudah dibooking dari database untuk HARI INI (sebagai contoh)
        // (Idealnya ini diambil berdasarkan tanggal yang dipilih user via AJAX, tapi untuk sekarang kita kosongkan dulu)
        $slotTerisi = [];

        $slotJam = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];

        // Ambil daftar produk untuk dropdown
        $daftarProduk = \App\Models\Produk::where('bisa_disewa', true)->orderBy('nama')->get();

        return view('customer.fitting', compact('tanggalPenuh', 'slotJam', 'slotTerisi', 'daftarProduk'));
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
            'produk' => ['nullable', 'string', 'max:200'], // Ini masih teks dari form
            'catatan' => ['nullable', 'string'],
        ]);

        $slot = SlotFitting::firstOrCreate(
            ['tanggal' => $validated['tanggal'], 'jam_mulai' => $validated['jam']],
            ['kapasitas' => 4]
        );

        // Cari ID produk berdasarkan nama yang dikirim dari form
        $produkId = null;
        if (!empty($validated['produk'])) {
            $produk = \App\Models\Produk::where('nama', $validated['produk'])->first();
            if ($produk) $produkId = $produk->id;
        }

        $kodeBooking = 'ALN-FT-' . strtoupper(substr(uniqid(), -5));

        $reservasi = ReservasiFitting::create([
            'kode_booking' => $kodeBooking,
            'user_id' => auth()->id(),
            'slot_id' => $slot->id,
            'produk_id' => $produkId, // <--- Simpan ID Produk
            'tanggal_daftar' => now()->toDateString(),
            'status' => 'pending',
            'catatan' => $validated['catatan'] ?? null, // Catatan murni tanpa embel-embel produk
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

        // Data untuk kalender
        $tanggalPenuh = [];
        $slotJam = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];
        $slotTerisi = [];
        $daftarProduk = \App\Models\Produk::where('bisa_disewa', true)->orderBy('nama')->get();

        return view('customer.fitting-edit', compact('reservasi', 'tanggalPenuh', 'slotJam', 'slotTerisi', 'daftarProduk'));
    }

    public function update(Request $request, $id)
    {
        $reservasi = ReservasiFitting::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', 'string'],
        ]);

        // Cari atau buat slot baru
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
}
