<?php

namespace App\Support;

use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\Rating;
use App\Models\Refund;
use App\Models\ReservasiFitting;
use App\Models\Sewa;
use App\Models\Transaksi;
use App\Models\User;
use App\Notifications\AdminNotifikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Pusat notifikasi panel admin.
 *
 * Cara kerja:
 *  1. daftarkan() memasang pendengar event model (Transaksi, ReservasiFitting, Refund, User, Rating).
 *     Jadi controller pelanggan TIDAK perlu diubah, dan jalur lain (webhook Midtrans, login Google, dst.)
 *     ikut tertangkap otomatis.
 *  2. Event dari panel admin sendiri (admin menandai lunas, mengubah status, dll.) TIDAK menghasilkan
 *     notifikasi, hanya aksi dari sisi pelanggan/sistem yang membutuhkan perhatian admin.
 *  3. Setiap kejadian dikirim ke admin yang punya akses menu terkait (owner selalu menerima),
 *     memakai notifikasi database bawaan Laravel, jadi status "sudah dibaca" tersimpan per admin.
 *  4. Pengiriman notifikasi tidak pernah boleh menggagalkan proses pelanggan: semua dibungkus try/catch.
 */
class NotifikasiAdmin
{
    /** Path ikon (gaya heroicons outline, viewBox 24). Dipakai lonceng & halaman riwayat. */
    public const IKON = [
        'bell'     => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'bag'      => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        'cash'     => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'clock'    => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'undo'     => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
        'user-add' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
        'star'     => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        'x-circle' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'alert'    => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'check-circle' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'truck'    => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
    ];

    /**
     * Jenis data yang dirujuk notifikasi -> model + nama yang ditampilkan.
     * Dipakai untuk mendeteksi data yang sudah dihapus setelah notifikasinya terkirim.
     */
    private const REF = [
        'transaksi'  => [Transaksi::class, 'Transaksi'],
        'sewa'       => [Sewa::class, 'Data sewa'],
        'fitting'    => [ReservasiFitting::class, 'Reservasi fitting'],
        'refund'     => [Refund::class, 'Pengajuan refund'],
        'pengguna'   => [User::class, 'Akun pelanggan'],
        'rating'     => [Rating::class, 'Ulasan'],
        'pengiriman' => [Pengiriman::class, 'Data pengiriman'],
    ];

    /** Sewa yang sudah telat lebih lama dari ini dianggap data lama dan diabaikan (cegah banjir notifikasi). */
    public const BATAS_HARI_TELAT = 30;

    private const STATUS_SEWA_SELESAI = ['selesai', 'dibatalkan', 'batal'];
    private const STATUS_BAYAR       = ['lunas', 'settlement', 'success', 'dp_dibayar'];
    private const STATUS_MIDTRANS_OK = ['success', 'settlement', 'capture', 'lunas'];

    private static bool $terdaftar = false;

    /** Cache per-request supaya composer yang dipanggil berkali-kali tidak mengulang query. */
    private static ?array $ringkasanRequest = null;

    /* ======================================================================
     |  PENDAFTARAN EVENT
     * ==================================================================== */

    public static function daftarkan(): void
    {
        if (self::$terdaftar) {
            return;
        }
        self::$terdaftar = true;

        // --- Pesanan beli / sewa baru (online) ---
        Transaksi::created(function (Transaksi $t) {
            if (self::dariPanelAdmin()) {
                return;
            }
            self::nanti(fn () => self::pesananBaru($t->id));
        });

        // --- Pembayaran masuk (webhook Midtrans / cek status QRIS dari pelanggan) ---
        Transaksi::updated(function (Transaksi $t) {
            if (self::dariPanelAdmin() || ! $t->wasChanged('status_pembayaran')) {
                return;
            }
            $status = strtolower((string) $t->status_pembayaran);
            self::nanti(fn () => self::pembayaranMasuk($t->id, $status));
        });

        // --- Pelanggan menekan "Pesanan Diterima" ---
        Pengiriman::updated(function (Pengiriman $p) {
            if (self::dariPanelAdmin() || ! $p->wasChanged('status_kirim') || $p->status_kirim !== 'diterima') {
                return;
            }
            self::nanti(fn () => self::pesananDiterima($p->id));
        });

        // --- Fitting: booking baru, jadwal diubah, dibatalkan pelanggan ---
        ReservasiFitting::created(function (ReservasiFitting $r) {
            if (self::dariPanelAdmin()) {
                return;
            }
            self::nanti(fn () => self::fittingBaru($r->id));
        });

        ReservasiFitting::updated(function (ReservasiFitting $r) {
            if (self::dariPanelAdmin()) {
                return;
            }
            $batalOlehPelanggan = $r->wasChanged('status')
                && $r->status === 'batal'
                && ($r->dibatalkan_oleh ?? null) === 'pelanggan';
            $jadwalBerubah = $r->wasChanged('slot_id');

            if ($batalOlehPelanggan) {
                self::nanti(fn () => self::fittingBerubah($r->id, 'batal'));
            } elseif ($jadwalBerubah) {
                self::nanti(fn () => self::fittingBerubah($r->id, 'jadwal'));
            }
        });

        // --- Refund: pengajuan baru, barang dikirim pelanggan, dibatalkan pelanggan ---
        Refund::created(function (Refund $r) {
            if (self::dariPanelAdmin()) {
                return;
            }
            self::nanti(fn () => self::refundBaru($r->id));
        });

        Refund::updated(function (Refund $r) {
            if (self::dariPanelAdmin() || ! $r->wasChanged('status')) {
                return;
            }
            $status = $r->status;
            if (in_array($status, ['barang_dikirim', 'dibatalkan'], true)) {
                self::nanti(fn () => self::refundBerubah($r->id, $status));
            }
        });

        // --- Pelanggan baru mendaftar (form, OTP, maupun Google) ---
        User::created(function (User $u) {
            if (self::dariPanelAdmin() || in_array($u->role, User::ROLE_STAF, true)) {
                return;
            }
            self::nanti(fn () => self::pelangganBaru($u->id));
        });

        // --- Ulasan / rating baru ---
        Rating::created(function (Rating $r) {
            if (self::dariPanelAdmin()) {
                return;
            }
            self::nanti(fn () => self::ratingBaru($r->id));
        });
    }

    /* ======================================================================
     |  PEMBUAT PESAN
     * ==================================================================== */

    private static function pesananBaru(int $id): void
    {
        $t = Transaksi::with(['user', 'detail', 'pembayaran', 'pengiriman', 'sewa.jaminan'])->find($id);
        if (! $t || self::dariKasir($t) || ! in_array($t->jenis, ['beli', 'sewa'], true)) {
            return;
        }

        $nama = $t->nama_pelanggan;

        if ($t->jenis === 'sewa') {
            $sewa = $t->sewa;
            $jaminan = $sewa?->jaminan;
            $butuhKtp = $jaminan && $jaminan->status_verifikasi === 'pending' && $jaminan->foto_ktp;

            $bagian = [$nama, $t->kode_transaksi];
            if ($sewa?->tanggal_pinjam && $sewa?->tanggal_kembali_rencana) {
                $bagian[] = self::tgl($sewa->tanggal_pinjam) . '–' . self::tgl($sewa->tanggal_kembali_rencana);
            }
            $bagian[] = $t->detail->count() . ' busana';
            if ($butuhKtp) {
                $bagian[] = 'KTP perlu diverifikasi';
            }

            self::kirim('sewa', [
                'kategori'  => 'pesanan',
                'ikon'      => 'calendar',
                'nada'      => 'amber',
                'prioritas' => $butuhKtp ? 'tinggi' : 'normal',
                'judul'     => 'Pesanan sewa baru',
                'pesan'     => implode(' · ', $bagian),
                'url'       => $sewa
                    ? route('admin.sewa.show', $sewa->id, false)
                    : route('admin.transaksi.show', $t->id, false),
                'ref'       => $sewa ? self::ref('sewa', $sewa->id) : self::ref('transaksi', $t->id),
            ]);

            return;
        }

        // Beli
        $kirim = $t->pengiriman
            ? 'Dikirim' . ($t->pengiriman->kurir ? ' (' . strtoupper($t->pengiriman->kurir) . ')' : '')
            : 'Ambil di toko';
        $tunai = $t->pembayaran->contains(fn ($p) => $p->metode === 'tunai');

        $bagian = [$nama, $t->kode_transaksi, self::rp($t->total), $kirim];
        if ($tunai) {
            $bagian[] = 'bayar tunai di toko';
        }

        self::kirim('transaksi', [
            'kategori'  => 'pesanan',
            'ikon'      => 'bag',
            'nada'      => 'amber',
            'prioritas' => 'normal',
            'judul'     => 'Pesanan beli baru',
            'pesan'     => implode(' · ', $bagian),
            'url'       => route('admin.transaksi.show', $t->id, false),
            'ref'       => self::ref('transaksi', $t->id),
        ]);
    }

    private static function pembayaranMasuk(int $id, string $status): void
    {
        $lunas = in_array($status, ['lunas', 'settlement', 'success'], true);
        $dp    = $status === 'dp_dibayar';
        if (! $lunas && ! $dp) {
            return;
        }

        $t = Transaksi::with(['user', 'sewa'])->find($id);
        if (! $t || self::dariKasir($t) || ! in_array($t->jenis, ['beli', 'sewa'], true)) {
            return;
        }

        $sewa = $t->jenis === 'sewa';
        $judul = $dp ? 'DP sewa dibayar' : 'Pembayaran lunas';
        $akhir = $sewa
            ? ($dp ? 'jadwalkan fitting / siapkan busana' : 'siapkan busana')
            : 'siap diproses';

        self::kirim($sewa ? 'sewa' : 'transaksi', [
            'kategori'  => 'pesanan',
            'ikon'      => 'cash',
            'nada'      => 'emerald',
            'prioritas' => 'tinggi',
            'judul'     => $judul,
            'pesan'     => implode(' · ', [$t->nama_pelanggan, $t->kode_transaksi, self::rp($t->total), $akhir]),
            'url'       => ($sewa && $t->sewa)
                ? route('admin.sewa.show', $t->sewa->id, false)
                : route('admin.transaksi.show', $t->id, false),
            'ref'       => ($sewa && $t->sewa) ? self::ref('sewa', $t->sewa->id) : self::ref('transaksi', $t->id),
        ]);
    }

    private static function fittingBaru(int $id): void
    {
        $r = ReservasiFitting::with(['user', 'slot'])->find($id);
        if (! $r) {
            return;
        }

        self::kirim('fitting', [
            'kategori'  => 'fitting',
            'ikon'      => 'clock',
            'nada'      => 'violet',
            'prioritas' => 'normal',
            'judul'     => 'Booking fitting baru',
            'pesan'     => implode(' · ', array_filter([$r->user?->name, $r->kode_booking, self::jadwalFitting($r)])),
            'url'       => route('admin.fitting.index', [], false),
            'ref'       => self::ref('fitting', $r->id),
        ]);
    }

    private static function fittingBerubah(int $id, string $jenis): void
    {
        $r = ReservasiFitting::with(['user', 'slot'])->find($id);
        if (! $r) {
            return;
        }

        $batal = $jenis === 'batal';

        self::kirim('fitting', [
            'kategori'  => 'fitting',
            'ikon'      => $batal ? 'x-circle' : 'clock',
            'nada'      => $batal ? 'rose' : 'violet',
            'prioritas' => 'normal',
            'judul'     => $batal ? 'Fitting dibatalkan pelanggan' : 'Jadwal fitting diubah',
            'pesan'     => implode(' · ', array_filter([
                $r->user?->name,
                $r->kode_booking,
                $batal ? null : 'jadwal baru: ' . self::jadwalFitting($r),
            ])),
            'url'       => route('admin.fitting.index', [], false),
            'ref'       => self::ref('fitting', $r->id),
        ]);
    }

    private static function refundBaru(int $id): void
    {
        $r = Refund::with('transaksi.user')->find($id);
        if (! $r) {
            return;
        }

        self::kirim('refund', [
            'kategori'  => 'refund',
            'ikon'      => 'undo',
            'nada'      => 'rose',
            'prioritas' => 'tinggi',
            'judul'     => 'Pengajuan refund baru',
            'pesan'     => implode(' · ', array_filter([
                $r->transaksi?->nama_pelanggan,
                $r->transaksi?->kode_transaksi,
                self::rp($r->jumlah),
                Str::limit((string) $r->alasan, 60),
            ])),
            'url'       => route('admin.refund.show', $r->id, false),
            'ref'       => self::ref('refund', $r->id),
        ]);
    }

    private static function refundBerubah(int $id, string $status): void
    {
        $r = Refund::with('transaksi.user')->find($id);
        if (! $r) {
            return;
        }

        $kirim = $status === 'barang_dikirim';

        self::kirim('refund', [
            'kategori'  => 'refund',
            'ikon'      => $kirim ? 'truck' : 'x-circle',
            'nada'      => $kirim ? 'rose' : 'amber',
            'prioritas' => $kirim ? 'tinggi' : 'normal',
            'judul'     => $kirim ? 'Barang refund dikirim pelanggan' : 'Pengajuan refund dibatalkan',
            'pesan'     => implode(' · ', array_filter([
                $r->transaksi?->nama_pelanggan,
                $r->transaksi?->kode_transaksi,
                $kirim ? 'catat saat barang tiba' : null,
            ])),
            'url'       => route('admin.refund.show', $r->id, false),
            'ref'       => self::ref('refund', $r->id),
        ]);
    }

    private static function pelangganBaru(int $id): void
    {
        $u = User::find($id);
        if (! $u) {
            return;
        }

        self::kirim('pengguna', [
            'kategori'  => 'pelanggan',
            'ikon'      => 'user-add',
            'nada'      => 'sky',
            'prioritas' => 'normal',
            'judul'     => 'Pelanggan baru terdaftar',
            'pesan'     => implode(' · ', array_filter([$u->name, $u->email])),
            'url'       => route('admin.pengguna.index', [], false),
            'ref'       => self::ref('pengguna', $u->id),
        ]);
    }

    private static function ratingBaru(int $id): void
    {
        $r = Rating::find($id);
        if (! $r) {
            return;
        }

        $nama   = User::find($r->user_id)?->name ?? 'Pelanggan';
        $produk = Produk::find($r->produk_id)?->nama;
        $rendah = (int) $r->rating <= 2;

        $isi = $nama . ($produk ? ' · ' . $produk : '');
        if (filled($r->ulasan)) {
            $isi .= ' · “' . Str::limit(trim((string) $r->ulasan), 70) . '”';
        }

        self::kirim('rating', [
            'kategori'  => 'pelanggan',
            'ikon'      => 'star',
            'nada'      => $rendah ? 'rose' : 'sky',
            'prioritas' => $rendah ? 'tinggi' : 'normal',
            'judul'     => ($rendah ? 'Ulasan rendah ' : 'Ulasan baru ') . '★' . (int) $r->rating,
            'pesan'     => $isi,
            'url'       => route('admin.rating.index', [], false),
            'ref'       => self::ref('rating', $r->id),
        ]);
    }

    private static function pesananDiterima(int $id): void
    {
        $p = Pengiriman::with('transaksi.user')->find($id);
        $t = $p?->transaksi;
        if (! $p || ! $t) {
            return;
        }

        $bagian = [
            $t->nama_pelanggan,
            $t->kode_transaksi,
            trim(strtoupper((string) $p->kurir) . ' ' . $p->no_resi),
        ];
        if ($t->jenis === 'beli') {
            $bagian[] = 'masa refund ' . Refund::BATAS_HARI . ' hari dimulai';
        }

        self::kirim('pengiriman', [
            'kategori'  => 'pesanan',
            'ikon'      => 'check-circle',
            'nada'      => 'emerald',
            'prioritas' => 'normal',
            'judul'     => 'Pesanan diterima pelanggan',
            'pesan'     => implode(' · ', array_filter($bagian)),
            'url'       => route('admin.pengiriman.index', ['status' => 'diterima', 'search' => $t->kode_transaksi], false),
            'ref'       => self::ref('pengiriman', $p->id),
        ]);
    }

    /* ======================================================================
     |  PENGINGAT SEWA (jatuh tempo & terlambat) — dijalankan harian oleh
     |  command notifikasi:pengingat-sewa
     * ==================================================================== */

    /**
     * Sewa yang barangnya belum kembali. Sewa online yang tidak pernah dibayar (DP maupun lunas)
     * TIDAK dihitung, karena itu hanya pesanan terbengkalai, bukan barang yang dibawa pelanggan.
     * Sewa dari kasir selalu dihitung.
     * Dipakai bersama oleh pengingat harian dan badge sidebar supaya angkanya selalu sama.
     */
    public static function sewaBelumKembali(): Builder
    {
        return Sewa::query()
            ->whereNotIn('status', self::STATUS_SEWA_SELESAI)
            ->whereNull('tanggal_kembali_aktual')
            ->whereHas('transaksi', function ($q) {
                $q->where(function ($w) {
                    $w->where('channel', 'kasir')
                      ->orWhereIn('status_pembayaran', self::STATUS_BAYAR)
                      ->orWhereHas('pembayaran', fn ($p) => $p->whereIn('status_midtrans', self::STATUS_MIDTRANS_OK));
                });
            });
    }

    /** Sewa yang sudah lewat tanggal kembali (untuk badge sidebar). */
    public static function sewaTelatQuery(): Builder
    {
        return self::sewaBelumKembali()
            ->whereDate('tanggal_kembali_rencana', '<', today()->toDateString())
            ->whereDate('tanggal_kembali_rencana', '>=', today()->subDays(self::BATAS_HARI_TELAT)->toDateString());
    }

    /** Selisih hari: positif = telat, 0 = jatuh tempo hari ini, negatif = belum jatuh tempo. */
    public static function hariTelat($tanggalKembali): int
    {
        $rencana = Carbon::parse($tanggalKembali)->startOfDay();

        return (int) round((Carbon::today()->getTimestamp() - $rencana->getTimestamp()) / 86400);
    }

    /**
     * Tahap pengingat untuk selisih hari tertentu (null = belum waktunya).
     * Tiap tahap dikirim SEKALI per sewa; bila command sempat tidak jalan, tahap terkini tetap terkirim.
     */
    public static function tahapSewa(int $hariTelat): ?string
    {
        return match (true) {
            $hariTelat >= 7   => 'telat-7',
            $hariTelat >= 3   => 'telat-3',
            $hariTelat >= 1   => 'telat-1',
            $hariTelat === 0  => 'hari-ini',
            $hariTelat === -1 => 'besok',
            default           => null,
        };
    }

    /**
     * Kirim pengingat sewa. Aman dijalankan berkali-kali: tiap (sewa, tahap) hanya terkirim sekali,
     * dicatat di tabel pengingat_admin (terpisah dari notifikasi, jadi tetap aman walau admin
     * menghapus notifikasi atau cache dibersihkan).
     *
     * @return array<int, array{sewa_id:int, kode:?string, tahap:string, hari:int, dikirim:bool}>
     */
    public static function pengingatSewa(bool $simulasi = false): array
    {
        $hasil = [];

        $daftar = self::sewaBelumKembali()
            ->whereDate('tanggal_kembali_rencana', '<=', today()->addDay()->toDateString())
            ->whereDate('tanggal_kembali_rencana', '>=', today()->subDays(self::BATAS_HARI_TELAT)->toDateString())
            ->with(['transaksi.user', 'transaksi.detail'])
            ->orderBy('tanggal_kembali_rencana')
            ->get();

        foreach ($daftar as $sewa) {
            $hari  = self::hariTelat($sewa->tanggal_kembali_rencana);
            $tahap = self::tahapSewa($hari);
            if ($tahap === null) {
                continue;
            }

            $kunci = "sewa:{$sewa->id}:{$tahap}";
            $baris = [
                'sewa_id' => $sewa->id,
                'kode'    => $sewa->transaksi?->kode_transaksi,
                'tahap'   => $tahap,
                'hari'    => $hari,
                'dikirim' => false,
            ];

            if ($simulasi) {
                $baris['dikirim'] = ! DB::table('pengingat_admin')->where('kunci', $kunci)->exists();
            } elseif (DB::table('pengingat_admin')->insertOrIgnore(['kunci' => $kunci, 'created_at' => now()]) === 1) {
                self::kirim('sewa', self::payloadPengingatSewa($sewa, $tahap, $hari) + ['kunci' => $kunci]);
                $baris['dikirim'] = true;
            }

            $hasil[] = $baris;
        }

        return $hasil;
    }

    private static function payloadPengingatSewa(Sewa $sewa, string $tahap, int $hari): array
    {
        $t = $sewa->transaksi;

        $bagian = [
            $sewa->nama_pelanggan,
            $t?->kode_transaksi,
            'jatuh tempo ' . self::tgl($sewa->tanggal_kembali_rencana),
            ($t?->detail->count() ?? 0) . ' busana',
        ];

        $telat = $hari >= 1;
        if ($telat) {
            $bagian[] = 'denda telat dihitung otomatis';
        }
        if ($hp = $sewa->no_hp_pelanggan) {
            $bagian[] = 'hubungi ' . $hp;
        }

        [$judul, $ikon, $nada, $prioritas] = match (true) {
            $telat           => ['Sewa terlambat ' . $hari . ' hari', 'alert', 'rose', 'tinggi'],
            $tahap === 'hari-ini' => ['Sewa jatuh tempo hari ini', 'clock', 'amber', 'tinggi'],
            default          => ['Sewa jatuh tempo besok', 'clock', 'amber', 'normal'],
        };

        return [
            'kategori'  => 'pesanan',
            'ikon'      => $ikon,
            'nada'      => $nada,
            'prioritas' => $prioritas,
            'judul'     => $judul,
            'pesan'     => implode(' · ', array_filter($bagian)),
            'url'       => route('admin.sewa.show', $sewa->id, false),
            'ref'       => self::ref('sewa', $sewa->id),
        ];
    }

    /* ======================================================================
     |  PENGIRIMAN
     * ==================================================================== */

    /**
     * Kirim ke semua admin yang boleh mengakses menu $menu. Tidak pernah melempar error.
     */
    public static function kirim(string $menu, array $payload): void
    {
        try {
            $penerima = self::penerima($menu);

            if ($penerima->isEmpty()) {
                return;
            }

            Notification::send($penerima, new AdminNotifikasi($payload + ['menu' => $menu]));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Owner selalu menerima; admin biasa hanya bila menu-nya ada di admin_permissions. */
    public static function penerima(string $menu): Collection
    {
        return User::query()
            ->whereIn('role', ['owner', 'admin'])
            ->get()
            ->filter(fn (User $u) => $u->akunAktif()
                && ($u->status_aktif ?? true)
                && $u->canAccessAdminMenu($menu))
            ->values();
    }

    /* ======================================================================
     |  MEMBACA (lonceng & halaman riwayat)
     * ==================================================================== */

    /** Ringkasan untuk admin yang sedang login. Aman dipanggil di setiap halaman admin. */
    public static function ringkasanSaatIni(): array
    {
        if (self::$ringkasanRequest !== null) {
            return self::$ringkasanRequest;
        }

        $kosong = ['unread' => 0, 'items' => []];

        try {
            $user = auth('admin')->user() ?? auth()->user();
            if (! $user || ! method_exists($user, 'isAdminPanelUser') || ! $user->isAdminPanelUser()) {
                return $kosong;
            }

            return self::$ringkasanRequest = self::ringkasan($user);
        } catch (\Throwable $e) {
            // mis. tabel notifications belum dimigrasi: jangan sampai halaman admin ikut error
            report($e);

            return $kosong;
        }
    }

    public static function ringkasan(User $user, int $batas = 15): array
    {
        $notifs = $user->notifications()->latest()->limit($batas)->get();
        $ada    = self::refAda($notifs); // satu query per jenis data, bukan per notifikasi

        return [
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $notifs->map(fn (DatabaseNotification $n) => self::ringkas($n, $ada))->all(),
        ];
    }

    /**
     * Bentuk data satu notifikasi yang dipakai view/JSON.
     *
     * @param array<string,bool>|null $ada hasil refAda() untuk banyak notifikasi sekaligus (hemat query);
     *                                     bila null, keberadaan data diperiksa untuk notifikasi ini saja.
     */
    public static function ringkas(DatabaseNotification $n, ?array $ada = null): array
    {
        $d = $n->data ?? [];

        $ref = self::refDari($n);
        $terhapus = false;
        if ($ref) {
            $ada ??= self::refAda([$n]);
            $terhapus = ($ada[$ref['jenis'] . ':' . $ref['id']] ?? true) === false;
        }

        return [
            // true bila data yang dirujuk (ulasan, transaksi, refund, dst.) sudah dihapus
            'terhapus'      => $terhapus,
            'terhapus_teks' => $terhapus ? self::REF[$ref['jenis']][1] . ' ini sudah dihapus' : null,
            'id'        => $n->id,
            'judul'     => $d['judul'] ?? 'Notifikasi',
            'pesan'     => $d['pesan'] ?? '',
            'kategori'  => $d['kategori'] ?? 'lainnya',
            'ikon'      => isset(self::IKON[$d['ikon'] ?? '']) ? $d['ikon'] : 'bell',
            'nada'      => $d['nada'] ?? 'amber',
            'prioritas' => $d['prioritas'] ?? 'normal',
            'dibaca'    => $n->read_at !== null,
            // Selalu lewat route "buka": menandai dibaca lalu redirect ke halaman tujuan.
            'url'       => route('admin.notifikasi.buka', $n->id, false),
            'waktu'     => $n->created_at?->copy()->locale('id')->diffForHumans(),
            'waktu_lengkap' => $n->created_at?->copy()->locale('id')->translatedFormat('j F Y, H:i'),
        ];
    }

    /* ======================================================================
     |  DATA YANG SUDAH DIHAPUS
     * ==================================================================== */

    /** Referensi ke data asal notifikasi, disimpan di payload agar bisa dicek keberadaannya nanti. */
    private static function ref(string $jenis, $id): array
    {
        return ['jenis' => $jenis, 'id' => (int) $id];
    }

    /**
     * Referensi data dari sebuah notifikasi. Notifikasi lama (sebelum 'ref' ada) tetap bisa dikenali
     * bila URL tujuannya memuat id (transaksi, sewa, refund).
     *
     * @return array{jenis:string,id:int}|null
     */
    private static function refDari(DatabaseNotification $n): ?array
    {
        $d = $n->data ?? [];
        $r = $d['ref'] ?? null;

        if (is_array($r) && isset(self::REF[$r['jenis'] ?? '']) && ! empty($r['id'])) {
            return ['jenis' => $r['jenis'], 'id' => (int) $r['id']];
        }

        if (preg_match('#^/admin/(transaksi|sewa|refund)/(\d+)#', (string) ($d['url'] ?? ''), $m)) {
            return ['jenis' => $m[1], 'id' => (int) $m[2]];
        }

        // Notifikasi ulasan lama (dibuat sebelum 'ref' ada): ulasannya tercatat pada detik yang sama dengan
        // notifikasinya. Tidak ada ulasan di rentang itu berarti ulasannya sudah dihapus (id 0 tidak pernah ada).
        // Hanya berlaku untuk notifikasi lama; setelah semuanya terganti yang baru, bagian ini bisa dibuang.
        if (($d['ikon'] ?? '') === 'star' && $n->created_at) {
            try {
                $waktu = $n->created_at;
                $id = Rating::query()
                    ->whereBetween('created_at', [$waktu->copy()->subSeconds(5), $waktu->copy()->addSeconds(5)])
                    ->get(['id', 'created_at'])
                    ->sortBy(fn ($r) => abs($r->created_at->diffInSeconds($waktu, false)))
                    ->first()?->id;

                return ['jenis' => 'rating', 'id' => (int) ($id ?? 0)];
            } catch (\Throwable $e) {
                report($e);

                return null;
            }
        }

        return null;
    }

    /**
     * Cek sekaligus apakah data yang dirujuk banyak notifikasi masih ada.
     *
     * @param  iterable<DatabaseNotification> $notifs
     * @return array<string,bool> "jenis:id" => masih ada?
     */
    public static function refAda(iterable $notifs): array
    {
        $perJenis = [];
        foreach ($notifs as $n) {
            if ($r = self::refDari($n)) {
                $perJenis[$r['jenis']][] = $r['id'];
            }
        }

        $hasil = [];
        foreach ($perJenis as $jenis => $ids) {
            $ids = array_values(array_unique($ids));

            try {
                $model  = self::REF[$jenis][0];
                $adaIds = $model::query()->whereIn('id', $ids)->pluck('id')->map(fn ($v) => (int) $v)->all();
            } catch (\Throwable $e) {
                report($e);
                $adaIds = $ids; // gagal memeriksa: anggap masih ada, jangan salah menandai terhapus
            }

            foreach ($ids as $id) {
                $hasil["{$jenis}:{$id}"] = in_array($id, $adaIds, true);
            }
        }

        return $hasil;
    }

    /** True bila data yang dirujuk notifikasi ini sudah dihapus. */
    public static function terhapus(DatabaseNotification $n): bool
    {
        $ref = self::refDari($n);

        return $ref !== null && (self::refAda([$n])[$ref['jenis'] . ':' . $ref['id']] ?? true) === false;
    }

    /* ======================================================================
     |  PEMBANTU
     * ==================================================================== */

    /**
     * Jalankan setelah transaksi database yang sedang berjalan selesai (commit).
     * Penting: pesanan dibuat dalam DB::transaction bersama detail/pembayaran/pengiriman,
     * sehingga saat pesan disusun semua datanya sudah lengkap, dan bila transaksi dibatalkan
     * (rollback) tidak ada notifikasi hantu.
     */
    private static function nanti(\Closure $aksi): void
    {
        DB::afterCommit(function () use ($aksi) {
            try {
                $aksi();
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    /**
     * Kejadian yang dipicu admin sendiri (panel admin), command/seeder, tidak perlu memberi tahu admin.
     */
    private static function dariPanelAdmin(): bool
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return true;
        }

        return request()->routeIs('admin.*');
    }

    private static function dariKasir(Transaksi $t): bool
    {
        return ($t->channel ?? $t->kanal ?? null) === 'kasir';
    }

    private static function rp($angka): string
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }

    private static function tgl($tanggal): string
    {
        return Carbon::parse($tanggal)->locale('id')->translatedFormat('j M');
    }

    private static function jadwalFitting(ReservasiFitting $r): ?string
    {
        $slot = $r->slot;
        if (! $slot || ! $slot->tanggal) {
            return null;
        }

        return Carbon::parse($slot->tanggal)->locale('id')->translatedFormat('D, j M')
            . ' · ' . substr((string) $slot->jam_mulai, 0, 5);
    }
}