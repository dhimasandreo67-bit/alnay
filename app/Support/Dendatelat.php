<?php

namespace App\Support;

use App\Models\Denda;
use App\Models\Sewa;
use Carbon\Carbon;

/**
 * Hitung & sinkronkan denda keterlambatan otomatis untuk satu sewa.
 * Simpan di app/Support/DendaTelat.php
 *
 * Aturan:
 * - Tarif per hari = jumlah produk.denda_per_hari_telat dari semua produk di transaksi.
 * - Hari telat = selisih hari antara tanggal_kembali_rencana dan hari ini
 *   (atau $akhir kalau diberikan, mis. saat barang ditandai kembali).
 * - Hanya ada 1 baris denda otomatis per sewa; baris itu di-update, bukan dibuat berulang.
 * - Sewa berstatus 'selesai' dibekukan (tidak dihitung ulang) kecuali $akhir diberikan.
 * - Denda yang sudah 'lunas' tidak diubah lagi.
 */
class DendaTelat
{
    public static function sinkron(Sewa $sewa, ?Carbon $akhir = null): ?Denda
    {
        // Semua perubahan denda di sini dicatat di audit log atas nama "Sistem",
        // bukan atas nama admin yang kebetulan membuka halaman (atau tanpa pelaku saat scheduler).
        return Audit::sebagaiSistem(fn () => self::hitung($sewa, $akhir));
    }

    protected static function hitung(Sewa $sewa, ?Carbon $akhir = null): ?Denda
    {
        if ($sewa->status === 'selesai' && $akhir === null) {
            return null;
        }

        $sewa->loadMissing('transaksi.detail.produk', 'denda');

        $tarif = (float) $sewa->transaksi->detail
            ->map(fn ($d) => $d->produk)
            ->filter()
            ->sum('denda_per_hari_telat');

        $batas = Carbon::parse($sewa->tanggal_kembali_rencana)->startOfDay();
        $akhir = ($akhir ?? now())->copy()->startOfDay();
        $hari  = max(0, (int) $batas->diffInDays($akhir, false));

        $denda = Denda::where('sewa_id', $sewa->id)->where('otomatis', true)->first();

        if ($hari === 0 || $tarif <= 0) {
            return $denda; // belum telat / tarif belum diatur
        }

        if ($denda && $denda->status === 'lunas') {
            return $denda;
        }

        $denda ??= new Denda(['sewa_id' => $sewa->id]);
        $denda->forceFill([
            'sewa_id'        => $sewa->id,
            'jenis'          => 'Keterlambatan',
            'status'         => 'belum_lunas',
            'keterangan'     => "Terlambat {$hari} hari × Rp " . number_format($tarif, 0, ',', '.') . ' per hari',
            'otomatis'       => true,
            'jumlah_hari'    => $hari,
            'tarif_per_hari' => $tarif,
            'jumlah'         => (int) round($hari * $tarif),
        ])->save();

        return $denda;
    }
}