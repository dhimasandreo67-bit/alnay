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

    /** Pengiriman yang belum dikirim: status "menunggu" atau "dikemas" (lihat Pengiriman::STATUS). */
    public static function pengiriman(): int
    {
        return self::ingat('pengiriman', fn () => Pengiriman::whereIn('status_kirim', ['menunggu', 'dikemas'])->count());
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
        $uid = self::uid();

        Cache::forever("admin_seen:{$uid}:{$kunci}", now()->toIso8601String());
        Cache::forget("admin_badge:{$uid}:{$kunci}");
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

    private static function ingat(string $kunci, \Closure $hitung): int
    {
        try {
            return (int) Cache::remember("admin_badge:{$kunci}", self::TTL, $hitung);
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }

    private static function uid(): int
    {
        return (int) (auth()->id() ?? auth('admin')->id() ?? 0);
    }
}