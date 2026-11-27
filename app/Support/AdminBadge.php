<?php

namespace App\Support;

use App\Models\JaminanSewa;
use App\Models\Pengiriman;
use App\Models\Rating;
use App\Models\Refund;
use App\Models\ReservasiFitting;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Hitungan badge di sidebar admin.
 *
 * Dua jenis:
 *  1. Berbasis status  -> "perlu tindakan" (jaminan, refund, fitting, pengiriman). Hilang setelah ditangani.
 *  2. Berbasis "baru"  -> data masuk sejak admin terakhir membuka menunya (transaksi, rating, pengguna).
 *     Badge reset otomatis saat menu tersebut dibuka (lihat tandaiDilihat()).
 *
 * Hasil di-cache singkat agar sidebar tidak menjalankan query berulang di setiap halaman,
 * dan tidak pernah membuat halaman error bila satu query gagal.
 */
class AdminBadge
{
    private const TTL = 20; // detik

    /* ---------- Perlu tindakan (berbasis status) ---------- */

    public static function jaminan(): int
    {
        return self::ingat('jaminan', fn () => JaminanSewa::where('status_verifikasi', 'pending')->count());
    }

    /** Pengajuan baru, barang dalam perjalanan (perlu dicatat diterima), dan barang diterima (perlu transfer dana). */
    public static function refund(): int
    {
        return self::ingat('refund', fn () => Refund::whereIn('status', ['diajukan', 'barang_dikirim', 'barang_diterima'])->count());
    }

    /** Sewa yang sudah lewat tanggal kembali tapi barangnya belum dikembalikan. Hilang setelah sewa diselesaikan. */
    public static function sewaTelat(): int
    {
        return self::ingat('sewa_telat', fn () => NotifikasiAdmin::sewaTelatQuery()->count());
    }

    /** Badge menu "Data Sewa": jaminan menunggu verifikasi + sewa terlambat. */
    public static function sewa(): int
    {
        return self::jaminan() + self::sewaTelat();
    }

    public static function fitting(): int
    {
        return self::ingat('fitting', fn () => ReservasiFitting::where('status', 'pending')->count());
    }

    /**
     * Pengiriman yang belum selesai: semua status KECUALI "diterima" (menunggu, dikemas, dikirim, dst).
     * Badge hilang hanya setelah pengiriman ditandai diterima. Tidak di-cache supaya langsung turun
     * begitu admin menekan "Tandai Diterima".
     */
    public static function pengiriman(): int
    {
        return self::langsung(fn () => Pengiriman::where(function ($q) {
            $q->whereNull('status_kirim')->orWhere('status_kirim', '!=', self::STATUS_PENGIRIMAN_SELESAI);
        })->count());
    }

    /* ---------- Data masuk sejak terakhir dibuka ---------- */

    public static function transaksiBaru(): int
    {
        return self::baru('transaksi', Transaksi::class);
    }

    public static function ratingBaru(): int
    {
        return self::baru('rating', Rating::class);
    }

    public static function penggunaBaru(): int
    {
        return self::baru('pengguna', User::class);
    }

    /** Dipanggil saat admin membuka halaman menu terkait: badge "baru" mulai dari nol lagi. */
    public static function tandaiDilihat(string $kunci): void
    {
        try {
            $uid = self::uid();

            Cache::forever("admin_seen:{$uid}:{$kunci}", now()->toIso8601String());
            Cache::forget("admin_badge:{$uid}:{$kunci}");
        } catch (\Throwable $e) {
            // cache bermasalah: jangan sampai halaman admin ikut error
            report($e);
        }
    }

    public const STATUS_TRANSAKSI_BELUM_LUNAS = ['pending', 'dp', 'dp_dibayar'];

    /** Status pengiriman yang dianggap selesai: setelah ini badge Pengiriman tidak lagi menghitungnya. */
    public const STATUS_PENGIRIMAN_SELESAI = 'diterima';
 
    /** Jumlah transaksi yang statusnya belum lunas. Tetap tampil sampai transaksi lunas. */
    public static function transaksiBelumLunas(): int
    {
        return self::langsung(fn () => Transaksi::whereIn('status_pembayaran', self::STATUS_TRANSAKSI_BELUM_LUNAS)->count());
    }

    /* ---------- Internal ---------- */

    private static function baru(string $kunci, string $model): int
    {
        $uid = self::uid();

        return self::ingat("{$uid}:{$kunci}", function () use ($uid, $kunci, $model) {
            $terakhir = Cache::get("admin_seen:{$uid}:{$kunci}");
            $sejak = $terakhir ? Carbon::parse($terakhir) : now()->subDays(7); // pertama kali: tampilkan 7 hari terakhir

            return $model::where('created_at', '>', $sejak)->count();
        });
    }

    /** Hitung langsung tanpa cache (badge yang harus langsung akurat); error tidak merusak halaman. */
    private static function langsung(\Closure $hitung): int
    {
        try {
            return (int) $hitung();
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }

    private static function ingat(string $kunci, \Closure $hitung): int
    {
        try {
            $kunciCache = "admin_badge:{$kunci}";

            // Request penyegar badge dari sidebar (window.refreshSidebarBadges) mengirim header ini:
            // lewati cache, hitung ulang, lalu simpan agar halaman berikutnya ikut akurat.
            if (request()->hasHeader('X-Refresh-Badge')) {
                $nilai = (int) $hitung();
                Cache::put($kunciCache, $nilai, self::TTL);

                return $nilai;
            }

            return (int) Cache::remember($kunciCache, self::TTL, $hitung);
        } catch (\Throwable $e) {
            report($e);

            // Cache bermasalah (mis. tabel cache belum ada / folder tidak bisa ditulis di server):
            // hitung langsung dari database, jangan diam di angka 0.
            return self::langsung($hitung);
        }
    }

    private static function uid(): int
    {
        return (int) (auth()->id() ?? auth('admin')->id() ?? 0);
    }
}