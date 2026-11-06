<?php

namespace App\Support;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Sewa;
use Carbon\Carbon;

/**
 * Ketersediaan sewa per tanggal, berdasarkan stok sewa dan pemesanan aktif.
 *
 * Sebuah tanggal "penuh" bila jumlah unit yang sedang dibooking (online + kasir) sudah sama dengan
 * stok sewa untuk kombinasi ukuran/warna yang dipilih. Stok sewa = jumlah unit yang dimiliki untuk
 * disewakan; TIDAK dikurangi oleh transaksi sewa mana pun. Ketersediaan hanya ditentukan per tanggal.
 */
class KetersediaanSewa
{
    private const STATUS_BAYAR = ['pending', 'dp', 'dp_dibayar', 'lunas', 'success', 'dibayar'];
    private const STATUS_SEWA = ['aktif', 'pending'];

    /**
     * Pemesanan sewa aktif untuk produk ini.
     *
     * @return array<int, array{ukuran:?string, warna:?string, mulai:string, selesai:string, jumlah:int}>
     */
    public static function pemesananAktif(Produk $produk): array
    {
        $ids = DetailTransaksi::where('produk_id', $produk->id)
            // Online DAN kasir dihitung bersama, jadi kalender kedua sisi selalu sama.
            ->whereHas('transaksi', fn ($q) => $q->whereIn('status_pembayaran', self::STATUS_BAYAR)->whereHas('sewa'))
            ->pluck('transaksi_id')
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        // Satu transaksi bisa berisi beberapa item; tiap item punya baris Sewa sendiri (urut sama).
        // Transaksi kasir memakai satu baris Sewa untuk semua item.
        $detailPerTransaksi = DetailTransaksi::whereIn('transaksi_id', $ids)->orderBy('id')->get()->groupBy('transaksi_id');
        $sewaPerTransaksi = Sewa::whereIn('transaksi_id', $ids)->orderBy('id')->get()->groupBy('transaksi_id');

        $hasil = [];
        foreach ($detailPerTransaksi as $tid => $baris) {
            $daftarSewa = $sewaPerTransaksi->get($tid, collect())->values();

            foreach ($baris->values() as $i => $d) {
                if ((int) $d->produk_id !== (int) $produk->id) {
                    continue;
                }

                $sewa = $daftarSewa[$i] ?? $daftarSewa->first();
                if (! $sewa || ! in_array($sewa->status, self::STATUS_SEWA, true)) {
                    continue;
                }

                $ukuran = $d->ukuran ?: null;
                if ($ukuran !== null && strcasecmp($ukuran, 'All Size') === 0) {
                    $ukuran = null;   // tanpa varian: dihitung untuk semua kombinasi
                }

                $hasil[] = [
                    'ukuran'  => $ukuran,
                    'warna'   => $d->warna ?: null,
                    'mulai'   => Carbon::parse($sewa->tanggal_pinjam)->toDateString(),
                    'selesai' => Carbon::parse($sewa->tanggal_kembali_rencana)->toDateString(),
                    'jumlah'  => max(1, (int) $d->jumlah),
                ];
            }
        }

        return $hasil;
    }

    /**
     * Semua kombinasi yang punya stok sendiri di set sewa.
     * Kunci: "ukuran|warna" (stok per warna), "ukuran|" (stok per ukuran), "|" (tanpa varian).
     *
     * @return array<string, array{ukuran:?string, warna:?string, kapasitas:int, tingkat:string}>
     */
    public static function kombinasi(Produk $produk): array
    {
        $baris = array_values(array_filter($produk->ukuranUntuk('sewa'), fn ($r) => is_array($r) && trim((string) ($r['ukuran'] ?? '')) !== ''));

        if (! $baris) {
            return ['|' => ['ukuran' => null, 'warna' => null, 'kapasitas' => max(0, $produk->stokUntuk('sewa')), 'tingkat' => 'produk']];
        }

        $hasil = [];
        foreach ($baris as $r) {
            $u = trim((string) $r['ukuran']);
            $perWarna = array_values(array_filter((array) ($r['warna'] ?? []), fn ($w) => trim((string) ($w['nama'] ?? '')) !== ''));

            $hasil[$u . '|'] = [
                'ukuran'    => $u,
                'warna'     => null,
                'kapasitas' => $perWarna
                    ? array_sum(array_map(fn ($w) => max(0, (int) ($w['stok'] ?? 0)), $perWarna))
                    : max(0, (int) ($r['stok'] ?? 0)),
                'tingkat'   => 'ukuran',
            ];

            foreach ($perWarna as $w) {
                $nama = trim((string) $w['nama']);
                $hasil[$u . '|' . $nama] = [
                    'ukuran'    => $u,
                    'warna'     => $nama,
                    'kapasitas' => max(0, (int) ($w['stok'] ?? 0)),
                    'tingkat'   => 'warna',
                ];
            }
        }

        return $hasil;
    }

    /** Kunci kombinasi untuk pilihan pelanggan (mengikuti aturan yang sama dengan halaman detail). */
    public static function kunci(Produk $produk, ?string $ukuran, ?string $warna): string
    {
        $kombinasi = self::kombinasi($produk);

        if (isset($kombinasi['|'])) {
            return '|';
        }

        $ukuran = trim((string) $ukuran);
        if ($warna !== null && $warna !== '' && isset($kombinasi[$ukuran . '|' . $warna])) {
            return $ukuran . '|' . $warna;
        }

        return $ukuran . '|';
    }

    /** @return array<string, int> tanggal => jumlah unit yang dibooking untuk kombinasi ini */
    private static function hitung(array $k, array $pesanan): array
    {
        $hitung = [];

        foreach ($pesanan as $p) {
            $cocok = match ($k['tingkat']) {
                'produk' => true,
                'ukuran' => $p['ukuran'] === null || $p['ukuran'] === $k['ukuran'],
                default  => $p['ukuran'] === null
                    || ($p['ukuran'] === $k['ukuran'] && ($p['warna'] === null || $p['warna'] === $k['warna'])),
            };
            if (! $cocok) {
                continue;
            }

            for ($t = Carbon::parse($p['mulai'])->startOfDay(), $akhir = Carbon::parse($p['selesai'])->startOfDay(); $t->lte($akhir); $t->addDay()) {
                $tgl = $t->toDateString();
                $hitung[$tgl] = ($hitung[$tgl] ?? 0) + $p['jumlah'];
            }
        }

        return $hitung;
    }

    /**
     * Tanggal yang sudah penuh untuk setiap kombinasi (dipakai kalender di halaman detail).
     *
     * @return array<string, string[]>
     */
    public static function tanggalPenuh(Produk $produk): array
    {
        $pesanan = self::pemesananAktif($produk);
        $hasil = [];

        foreach (self::kombinasi($produk) as $kunci => $k) {
            $penuh = [];
            if ($k['kapasitas'] > 0) {
                foreach (self::hitung($k, $pesanan) as $tgl => $jml) {
                    if ($jml >= $k['kapasitas']) {
                        $penuh[] = $tgl;
                    }
                }
            }
            sort($penuh);
            $hasil[$kunci] = $penuh;
        }

        return $hasil;
    }

    /** Apakah seluruh tanggal pada rentang masih punya $jumlah unit kosong? (validasi di server) */
    public static function tersedia(Produk $produk, ?string $ukuran, ?string $warna, string $mulai, string $selesai, int $jumlah = 1): bool
    {
        $kombinasi = self::kombinasi($produk);
        $k = $kombinasi[self::kunci($produk, $ukuran, $warna)] ?? null;

        if (! $k || $k['kapasitas'] <= 0) {
            return false;
        }

        $hitung = self::hitung($k, self::pemesananAktif($produk));

        for ($t = Carbon::parse($mulai)->startOfDay(), $akhir = Carbon::parse($selesai)->startOfDay(); $t->lte($akhir); $t->addDay()) {
            if (($hitung[$t->toDateString()] ?? 0) + $jumlah > $k['kapasitas']) {
                return false;
            }
        }

        return true;
    }
}