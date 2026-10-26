<?php

namespace App\Http\Controllers;

use App\Models\ReservasiFitting;
use App\Models\SlotFitting;
use Illuminate\Http\Request;

class FittingController extends Controller
{
    public function create()
    {
        $tanggalPenuh = [
            '2026-09-14', '2026-09-15', '2026-09-16', '2026-09-17',
            '2026-09-18', '2026-09-19', '2026-09-20', '2026-09-21',
            '2026-09-22', '2026-09-23', '2026-09-24', '2026-09-25',
            '2026-09-26', '2026-09-27',
        ];
        $slotJam = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];
        $slotTerisi = ['09:00', '12:00', '15:00', '18:00'];

        return view('customer.fitting', compact('tanggalPenuh', 'slotJam', 'slotTerisi'));
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
        ]);

        // Cari atau buat slot fitting
        $slot = SlotFitting::firstOrCreate(
            [
                'tanggal' => $validated['tanggal'],
                'jam_mulai' => $validated['jam'],
            ],
            ['kapasitas' => 4]
        );

        // Generate kode booking
        $kodeBooking = 'ALN-FT-' . strtoupper(substr(uniqid(), -5));

        // Simpan reservasi
        $reservasi = ReservasiFitting::create([
            'kode_booking' => $kodeBooking,
            'user_id' => auth()->id(),
            'slot_id' => $slot->id,
            'tanggal_daftar' => now()->toDateString(),
            'status' => 'pending',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('fitting.show', $reservasi->id)
            ->with('success', 'Booking fitting berhasil!');
    }

    public function show($id)
    {
        $reservasi = ReservasiFitting::with(['slot', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('customer.fitting-detail', compact('reservasi'));
    }

    public function cancel($id)
    {
        $reservasi = ReservasiFitting::where('user_id', auth()->id())->findOrFail($id);
        $reservasi->update(['status' => 'batal']);

        return back()->with('success', 'Booking fitting berhasil dibatalkan.');
    }
}