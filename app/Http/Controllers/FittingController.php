<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ReservasiFitting;
use App\Models\Sewa;
use App\Models\SlotFitting;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FittingController extends Controller
{
    private const SLOT_JAM = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];

    /** Maksimal tamu per jam kedatangan. Kuota dihitung per jam, tidak ada konsep "hari penuh". */
    private const KAPASITAS_SLOT = 2;

    /**
     * Booking fitting langsung terjadwal begitu dibuat (tidak ada tahap persetujuan admin).
     * Nilai di database tetap 'pending' agar kompatibel dengan halaman admin & laporan yang sudah ada;
     * di semua tampilan statusnya disebut "Terjadwal".
     */
    private const STATUS_TERJADWAL = 'pending';

    /** Status reservasi yang masih menghitung kapasitas slot. */
    private const STATUS_ISI_SLOT = ['pending', 'disetujui'];

    /** Status yang berarti pesanan ini SUDAH punya fitting (tidak boleh booking lagi). */
    private const STATUS_BERJALAN = ['pending', 'disetujui', 'hadir'];

    /*
    |--------------------------------------------------------------------------
    | Helper alur: fitting = langkah lanjutan dari pesanan sewa
    |--------------------------------------------------------------------------
    */

    /** Ambil transaksi sewa milik user yang sedang login (404 kalau bukan miliknya). */
    private function transaksiMilikSaya($id): Transaksi
    {
        return Transaksi::with(['detail.produk', 'sewa', 'pengiriman', 'reservasiFitting.slot'])
            ->where('user_id', auth()->id())
            ->where('jenis', 'sewa')
            ->findOrFail($id);
    }

    /**
     * Syarat booking fitting untuk sebuah pesanan.
     * Return RedirectResponse kalau belum memenuhi syarat, null kalau boleh lanjut.
     */
    private function cekSyarat(Transaksi $transaksi)
    {
        if (! $transaksi->perluFitting()) {
            return redirect()->route('akun.pesanan.detail', $transaksi->id)
                ->with('error', 'Pesanan ini dikirim ke alamat Anda, jadi tidak memerlukan sesi fitting di toko.');
        }

        if (! $transaksi->dpSudahDibayar()) {
            return redirect()->route('sewa.konfirmasi', $transaksi->id)
                ->with('error', 'Selesaikan pembayaran DP terlebih dahulu sebelum menjadwalkan fitting.');
        }

        return null;
    }

    /** Pesanan sewa (ambil di toko, DP lunas) milik user yang belum punya jadwal fitting. */
    private function transaksiMenungguFitting(): ?Transaksi
    {
        return Transaksi::query()
            ->where('user_id', auth()->id())
            ->where('jenis', 'sewa')
            ->whereIn('status_pembayaran', ['dp_dibayar', 'lunas'])
            ->whereDoesntHave('pengiriman')
            ->whereDoesntHave('reservasiFitting', fn ($q) => $q->whereIn('status', self::STATUS_BERJALAN))
            ->whereHas('sewa', fn ($q) => $q->where('status', 'aktif')->whereDate('tanggal_pinjam', '>=', now()))
            ->latest()
            ->first();
    }

    /**
     * Sewa yang membatasi tanggal fitting.
     * Kalau ada transaksi_id -> sewa pesanan itu. Kalau tidak -> sewa aktif terbaru user (fitting umum).
     */
    private function sewaTerkait(?int $transaksiId): ?Sewa
    {
        if ($transaksiId) {
            return Sewa::where('transaksi_id', $transaksiId)->first();
        }

        return Sewa::whereHas('transaksi', fn ($q) => $q->where('user_id', auth()->id()))
            ->where('status', 'aktif')
            ->whereDate('tanggal_pinjam', '>=', now())
            ->latest()
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Halaman & aksi
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $transaksiId = $request->query('transaksi_id');

        // Dibuka dari menu tanpa konteks pesanan: kalau ada pesanan yang masih menunggu
        // jadwal fitting, langsung arahkan ke sana supaya produk & tanggal terisi otomatis.
        if (! $transaksiId && ($menunggu = $this->transaksiMenungguFitting())) {
            return redirect()->route('fitting.create', ['transaksi_id' => $menunggu->id]);
        }

        $transaksi = null;

        if ($transaksiId) {
            $transaksi = $this->transaksiMilikSaya($transaksiId);

            if ($tolak = $this->cekSyarat($transaksi)) {
                return $tolak;
            }

            if ($ada = $transaksi->fittingAktif()) {
                return redirect()->route('fitting.show', $ada->id)
                    ->with('success', 'Fitting untuk pesanan ini sudah terjadwal. Anda bisa mengubah jadwalnya di sini.');
            }
        }

        $sewaAktif = $this->sewaTerkait($transaksi?->id);
        $tanggalMaksimal = $sewaAktif ? Carbon::parse($sewaAktif->tanggal_pinjam)->format('Y-m-d') : null;

        $tanggalPenuh = [];
        $slotJam = self::SLOT_JAM;
        $slotTerisi = $this->getSlotTerisi();
        $kapasitas = self::KAPASITAS_SLOT;
        $daftarProduk = Produk::where('bisa_disewa', true)->orderBy('nama')->get();

        return view('customer.fitting', compact(
            'tanggalPenuh', 'slotJam', 'slotTerisi', 'daftarProduk', 'sewaAktif', 'tanggalMaksimal', 'transaksi', 'transaksiId', 'kapasitas'
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
            'jam' => ['required', Rule::in(self::SLOT_JAM)],
            'nama' => ['required', 'string', 'max:100'],
            'no_wa' => ['required', 'string', 'max:20'],
            'produk' => ['nullable', 'string', 'max:200'],
            'catatan' => ['nullable', 'string'],
            'transaksi_id' => ['nullable', 'exists:transaksi,id'],
        ]);

        // ==== Konteks pesanan ====
        $transaksi = null;
        if (! empty($validated['transaksi_id'])) {
            $transaksi = $this->transaksiMilikSaya($validated['transaksi_id']);

            if ($tolak = $this->cekSyarat($transaksi)) {
                return $tolak;
            }

            if ($ada = $transaksi->fittingAktif()) {
                return redirect()->route('fitting.show', $ada->id)
                    ->with('error', 'Pesanan ini sudah punya jadwal fitting. Ubah jadwalnya lewat halaman ini.');
            }
        }

        // ==== Tanggal fitting tidak boleh melewati tanggal ambil busana ====
        $sewaAktif = $this->sewaTerkait($transaksi?->id);

        if ($sewaAktif && Carbon::parse($validated['tanggal'])->greaterThan(Carbon::parse($sewaAktif->tanggal_pinjam))) {
            return back()->withErrors([
                'tanggal' => 'Tanggal fitting tidak boleh melebihi tanggal pengambilan busana Anda ('
                    . Carbon::parse($sewaAktif->tanggal_pinjam)->format('d M Y') . ').',
            ])->withInput();
        }

        if (Carbon::parse($validated['tanggal'] . ' ' . $validated['jam'])->isPast()) {
            return back()->withErrors(['jam' => 'Jam ' . $validated['jam'] . ' hari ini sudah lewat. Silakan pilih jam lain.'])->withInput();
        }

        // ==== Kapasitas slot ====
        $slot = SlotFitting::firstOrCreate(
            ['tanggal' => $validated['tanggal'], 'jam_mulai' => $validated['jam']],
            ['kapasitas' => self::KAPASITAS_SLOT]
        );

        $terisi = ReservasiFitting::where('slot_id', $slot->id)
            ->whereIn('status', self::STATUS_ISI_SLOT)
            ->count();

        if ($terisi >= self::KAPASITAS_SLOT) {
            return back()->withErrors([
                'jam' => 'Maaf, slot jam ' . $validated['jam'] . ' di tanggal '
                    . Carbon::parse($validated['tanggal'])->format('d M Y')
                    . ' sudah penuh. Silakan pilih jam atau tanggal lain.',
            ])->withInput();
        }

        // ==== Produk: dari pesanan kalau ada, kalau tidak dari pilihan manual ====
        $produkId = null;
        $catatan = $validated['catatan'] ?? null;

        if ($transaksi) {
            $produkId = $transaksi->detail->first()?->produk_id;

            // reservasi_fitting hanya punya satu produk_id; sisanya dicatat di catatan.
            $namaSemua = $transaksi->detail->map(fn ($d) => $d->produk?->nama)->filter()->unique()->values();
            if ($namaSemua->count() > 1) {
                $catatan = trim(($catatan ? $catatan . "\n" : '') . 'Busana dalam pesanan: ' . $namaSemua->implode(', '));
            }
        } elseif (! empty($validated['produk'])) {
            $produkId = Produk::where('nama', $validated['produk'])->value('id');
        }

        $reservasi = ReservasiFitting::create([
            'kode_booking' => 'ALN-FT-' . strtoupper(substr(uniqid(), -5)),
            'user_id' => auth()->id(),
            'transaksi_id' => $transaksi?->id,
            'slot_id' => $slot->id,
            'produk_id' => $produkId,
            'tanggal_daftar' => now()->toDateString(),
            'status' => self::STATUS_TERJADWAL,
            'catatan' => $catatan,
        ]);

        return redirect()->route('fitting.show', $reservasi->id)
            ->with('success', 'Jadwal fitting Anda sudah terdaftar. Silakan datang ke toko sesuai jadwal.');
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

        public function destroy($id)
    {
        $reservasi = ReservasiFitting::where('user_id', auth()->id())->findOrFail($id);

        if (!in_array($reservasi->status, ['pending', 'batal'])) {
            return back()->with('error', 'Reservasi yang sudah disetujui/hadir tidak bisa dihapus.');
        }

        $reservasi->delete();

        return back()->with('success', 'Reservasi fitting berhasil dihapus.');
    }

    public function edit($id)
    {
        $reservasi = ReservasiFitting::with('slot')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $tanggalPenuh = [];
        $slotJam = self::SLOT_JAM;
        $daftarProduk = Produk::where('bisa_disewa', true)->orderBy('nama')->get();
        $slotTerisi = $this->getSlotTerisi($reservasi->id);
        $kapasitas = self::KAPASITAS_SLOT;

        // Batas tanggal harus dari pesanan milik reservasi ini, bukan "sewa aktif terbaru".
        $sewaAktif = $this->sewaTerkait($reservasi->transaksi_id);
        $tanggalMaksimal = $sewaAktif ? Carbon::parse($sewaAktif->tanggal_pinjam)->format('Y-m-d') : null;

        return view('customer.fitting-edit', compact(
            'reservasi', 'tanggalPenuh', 'slotJam', 'slotTerisi', 'daftarProduk', 'sewaAktif', 'tanggalMaksimal', 'kapasitas'
        ));
    }

    public function update(Request $request, $id)
    {
        $reservasi = ReservasiFitting::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', Rule::in(self::SLOT_JAM)],
        ]);

        $sewaAktif = $this->sewaTerkait($reservasi->transaksi_id);

        if ($sewaAktif && Carbon::parse($validated['tanggal'])->greaterThan(Carbon::parse($sewaAktif->tanggal_pinjam))) {
            return back()->withErrors([
                'tanggal' => 'Tanggal fitting tidak boleh melebihi tanggal pengambilan busana Anda ('
                    . Carbon::parse($sewaAktif->tanggal_pinjam)->format('d M Y') . ').',
            ])->withInput();
        }

        if (Carbon::parse($validated['tanggal'] . ' ' . $validated['jam'])->isPast()) {
            return back()->withErrors(['jam' => 'Jam ' . $validated['jam'] . ' hari ini sudah lewat. Silakan pilih jam lain.'])->withInput();
        }

        $slot = SlotFitting::firstOrCreate(
            ['tanggal' => $validated['tanggal'], 'jam_mulai' => $validated['jam']],
            ['kapasitas' => self::KAPASITAS_SLOT]
        );

        $terisi = ReservasiFitting::where('slot_id', $slot->id)
            ->whereIn('status', self::STATUS_ISI_SLOT)
            ->where('id', '!=', $reservasi->id)
            ->count();

        if ($terisi >= self::KAPASITAS_SLOT) {
            return back()->withErrors([
                'jam' => 'Maaf, slot jam ' . $validated['jam'] . ' di tanggal '
                    . Carbon::parse($validated['tanggal'])->format('d M Y')
                    . ' sudah penuh. Silakan pilih jam atau tanggal lain.',
            ])->withInput();
        }

        $reservasi->update([
            'slot_id' => $slot->id,
            'status' => self::STATUS_TERJADWAL,
        ]);

        return redirect()->route('fitting.show', $reservasi->id)
            ->with('success', 'Jadwal fitting berhasil diubah. Silakan datang ke toko sesuai jadwal baru.');
    }

    private function getSlotTerisi($kecualiReservasiId = null)
    {
        $query = ReservasiFitting::with('slot')
            ->whereIn('status', self::STATUS_ISI_SLOT)
            ->whereHas('slot', fn ($q) => $q->whereDate('tanggal', '>=', now()));

        if ($kecualiReservasiId) {
            $query->where('id', '!=', $kecualiReservasiId);
        }

        $slotTerisi = [];
        foreach ($query->get() as $r) {
            if (! $r->slot) {
                continue;
            }

            $tanggal = $r->slot->tanggal instanceof Carbon
                ? $r->slot->tanggal->format('Y-m-d')
                : $r->slot->tanggal;
            $jam = Carbon::parse($r->slot->jam_mulai)->format('H:i');

            $slotTerisi[$tanggal][$jam] = ($slotTerisi[$tanggal][$jam] ?? 0) + 1;
        }

        return $slotTerisi;
    }
}