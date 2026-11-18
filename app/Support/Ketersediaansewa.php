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
 * disewakan (kapasitas); TIDAK dikurangi oleh transaksi sewa mana pun. Ketersediaan hanya ditentukan per tanggal.
 * Semua halaman (Kelola Produk, kasir, kalender pelanggan, validasi checkout) memakai kelas ini.
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
        return self::pemesananAktifBanyak([$produk->id])[$produk->id] ?? [];
    }

    /**
     * Sama seperti pemesananAktif() tapi untuk banyak produk sekaligus (hemat query).
     *
     * @param  int[]  $produkIds
     * @return array<int, array<int, array{ukuran:?string, warna:?string, mulai:string, selesai:string, jumlah:int}>>
     */
    public static function pemesananAktifBanyak(array $produkIds): array
    {
        if (! $produkIds) {
            return [];
        }

        $produkIds = array_map('intval', $produkIds);

        // Transaksi (online + kasir) yang masih punya minimal satu sewa aktif untuk produk-produk ini.
        $transaksiIds = DetailTransaksi::whereIn('produk_id', $produkIds)
            ->whereHas('transaksi', fn ($q) => $q
                ->whereIn('status_pembayaran', self::STATUS_BAYAR)
                ->whereHas('sewa', fn ($s) => $s->whereIn('status', self::STATUS_SEWA)))
            ->pluck('transaksi_id')
            ->unique()
            ->values();

        if ($transaksiIds->isEmpty()) {
            return [];
        }

        // Transaksi online multi-item punya SATU baris Sewa PER ITEM (status & tanggal kembali aktualnya
        // sendiri-sendiri). Karena itu semua baris sewa diambil, lalu dipasangkan ke tiap baris detail,
        // bukan memakai satu relasi hasOne untuk semua item.
        $detailPerTransaksi = DetailTransaksi::whereIn('transaksi_id', $transaksiIds)->orderBy('id')->get()->groupBy('transaksi_id');
        $sewaPerTransaksi = Sewa::whereIn('transaksi_id', $transaksiIds)->orderBy('id')->get()->groupBy('transaksi_id');

        $hariIni = Carbon::today();
        $hasil = [];

        foreach ($detailPerTransaksi as $transaksiId => $daftarDetail) {
            $sewaMilik = self::pasangkanSewa($daftarDetail, $sewaPerTransaksi->get($transaksiId, collect()));

            foreach ($daftarDetail as $d) {
                if (! in_array((int) $d->produk_id, $produkIds, true)) {
                    continue;
                }

                $sewa = $sewaMilik[$d->id] ?? null;
                if (! $sewa) {
                    continue;
                }

                // Item ini sudah dikembalikan (atau sewanya dibatalkan): tanggalnya bebas di kalender.
                if (! in_array($sewa->status, self::STATUS_SEWA, true) || ! empty($sewa->tanggal_kembali_aktual)) {
                    continue;
                }

                $mulai = Carbon::parse($d->tanggal_pinjam ?? $sewa->tanggal_pinjam)->startOfDay();
                $selesai = Carbon::parse($d->tanggal_kembali_rencana ?? $sewa->tanggal_kembali_rencana)->startOfDay();

                // Belum dikembalikan padahal lewat jadwal: unit masih keluar sampai hari ini
                $rencana = $selesai->toDateString();
                $terlambat = false;
                if ($sewa->status === 'aktif' && $selesai->lt($hariIni)) {
                    $selesai = $hariIni->copy();
                    $terlambat = true;
                }

                $ukuran = trim((string) $d->ukuran) ?: null;
                if ($ukuran !== null && strcasecmp($ukuran, 'All Size') === 0) {
                    $ukuran = null;   // tanpa varian: dihitung untuk semua kombinasi
                }

                $hasil[(int) $d->produk_id][] = [
                    'ukuran'    => $ukuran,
                    'warna'     => trim((string) $d->warna) ?: null,
                    'mulai'     => $mulai->toDateString(),
                    'selesai'   => $selesai->toDateString(),
                    'jumlah'    => max(1, (int) $d->jumlah),
                    'rencana'   => $rencana,
                    'terlambat' => $terlambat,
                ];
            }
        }

        return $hasil;
    }

    /**
     * Pasangkan tiap baris detail ke baris Sewa miliknya dalam satu transaksi.
     * - Satu Sewa (kasir / data lama): dipakai semua item.
     * - Beberapa Sewa (online multi-item): dicocokkan lewat rentang tanggal yang sama, berurutan menurut id.
     *   Sisanya (tanggal kosong / tidak ada pasangan) dipasangkan ke sewa yang belum terpakai.
     *
     * @return array<int, Sewa> detail_id => Sewa
     */
    private static function pasangkanSewa($daftarDetail, $daftarSewa): array
    {
        $hasil = [];

        if ($daftarSewa->isEmpty()) {
            return $hasil;
        }

        if ($daftarSewa->count() === 1) {
            $satu = $daftarSewa->first();
            foreach ($daftarDetail as $d) {
                $hasil[$d->id] = $satu;
            }

            return $hasil;
        }

        $kunci = fn ($pinjam, $kembali) => ($pinjam ? Carbon::parse($pinjam)->toDateString() : '')
            . '|' . ($kembali ? Carbon::parse($kembali)->toDateString() : '');

        $antrean = [];
        foreach ($daftarSewa as $s) {
            $antrean[$kunci($s->tanggal_pinjam, $s->tanggal_kembali_rencana)][] = $s;
        }

        $tanpaPasangan = [];
        foreach ($daftarDetail as $d) {
            $k = $kunci($d->tanggal_pinjam, $d->tanggal_kembali_rencana);
            if (! empty($antrean[$k])) {
                $hasil[$d->id] = array_shift($antrean[$k]);
            } else {
                $tanpaPasangan[] = $d;
            }
        }

        $sisaSewa = collect($antrean)->flatten()->sortBy('id')->values()->all();
        foreach ($tanpaPasangan as $d) {
            $hasil[$d->id] = array_shift($sisaSewa)
                ?? $daftarSewa->first(fn ($s) => in_array($s->status, self::STATUS_SEWA, true));
        }

        return array_filter($hasil);
    }

    /**
     * Jumlah unit yang sedang keluar (disewa) per produk (semua sewa yang belum dikembalikan), untuk tampilan "tersedia / total" di admin.
     *
     * Dihitung dengan aturan yang SAMA dengan kalender pelanggan dan kasir (kombinasi ukuran/warna,
     * dibatasi kapasitasnya), jadi  kapasitas - terpakai  = jumlah sisa unit yang terlihat di kasir untuk hari ini.
     * Kombinasi per warna tidak dijumlah lagi karena sudah termasuk di tingkat ukurannya.
     *
     * @param  iterable<Produk>  $produks
     * @return array<int, int>  produk_id => unit sedang disewa
     */
    public static function terpakaiHariIni(iterable $produks): array
    {
        $daftar = collect($produks)->filter(fn ($p) => $p->bisa_disewa)->values();
        if ($daftar->isEmpty()) {
            return [];
        }

        $hariIni = Carbon::today()->toDateString();
        $semua = self::pemesananAktifBanyak($daftar->pluck('id')->map(fn ($v) => (int) $v)->all());
        $hasil = [];

        foreach ($daftar as $produk) {
            $pesanan = $semua[(int) $produk->id] ?? [];
            // Semua sewa yang belum dikembalikan dihitung sebagai unit keluar, tanpa melihat tanggal mulai.
            // (Yang terlambat sudah dimajukan 'selesai'-nya ke hari ini di pemesananAktifBanyak.)
            $hariIniSaja = array_values(array_filter($pesanan, fn ($p) => $p['selesai'] >= $hariIni));

            $terpakai = 0;
            foreach (self::kombinasi($produk) as $k) {
                if ($k['tingkat'] === 'warna') {
                    continue;
                }

                $dipakai = 0;
                foreach ($hariIniSaja as $p) {
                    if (self::cocok($k, $p)) {
                        $dipakai += $p['jumlah'];
                    }
                }

                $terpakai += min($k['kapasitas'], $dipakai);
            }

            $hasil[(int) $produk->id] = $terpakai;
        }

        return $hasil;
    }

    /**
     * Unit yang sedang keluar HARI INI untuk tiap kombinasi (kunci sama dengan kombinasi()):
     * "ukuran|warna", "ukuran|", atau "|". Dipakai halaman Edit Produk supaya admin melihat sisa unit
     * yang sebenarnya, tanpa mengubah angka stok (kapasitas) yang disimpan.
     *
     * @return array<string, int>
     */
    public static function pemakaianHariIni(Produk $produk): array
    {
        $hariIni = Carbon::today()->toDateString();
        $pesanan = array_values(array_filter(
            self::pemesananAktif($produk),
            fn ($p) => $p['selesai'] >= $hariIni   // belum dikembalikan, tanpa melihat tanggal mulai
        ));

        $hasil = [];
        foreach (self::kombinasi($produk) as $kunci => $k) {
            $hasil[$kunci] = array_sum(array_map(
                fn ($p) => self::cocok($k, $p) ? $p['jumlah'] : 0,
                $pesanan
            ));
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

    /** Apakah pemesanan ini memakai kombinasi $k? Satu aturan untuk kalender, validasi, dan ringkasan admin. */
    private static function cocok(array $k, array $p): bool
    {
        return match ($k['tingkat']) {
            'produk' => true,
            'ukuran' => $p['ukuran'] === null || $p['ukuran'] === $k['ukuran'],
            default  => $p['ukuran'] === null
                || ($p['ukuran'] === $k['ukuran'] && ($p['warna'] === null || $p['warna'] === $k['warna'])),
        };
    }

    /**
     * Daftar rentang booking (ambil, dipakai, kembali) yang memakai kombinasi ini dan belum lewat.
     * Tanpa data pelanggan: hanya tanggal dan jumlah unit.
     *
     * @return array<int, array{mulai:string, selesai:string, jumlah:int, terlambat:bool, rencana:string}>
     */
    private static function rentang(array $k, array $pesanan): array
    {
        $hariIni = Carbon::today()->toDateString();
        $daftar = [];

        foreach ($pesanan as $p) {
            if (! self::cocok($k, $p) || $p['selesai'] < $hariIni) {
                continue;
            }

            $daftar[] = [
                'mulai'     => $p['mulai'],
                'selesai'   => $p['selesai'],
                'jumlah'    => $p['jumlah'],
                'terlambat' => (bool) ($p['terlambat'] ?? false),
                'rencana'   => $p['rencana'] ?? $p['selesai'],
            ];
        }

        usort($daftar, fn ($a, $b) => strcmp($a['mulai'], $b['mulai']));

        return $daftar;
    }

    /** @return array<string, int> tanggal => jumlah unit yang dibooking untuk kombinasi ini */
    private static function hitung(array $k, array $pesanan): array
    {
        $hitung = [];

        foreach ($pesanan as $p) {
            if (! self::cocok($k, $p)) {
                continue;
            }

            for ($t = Carbon::parse($p['mulai'])->startOfDay(), $akhir = Carbon::parse($p['selesai'])->startOfDay(); $t->lte($akhir); $t->addDay()) {
                $tgl = $t->toDateString();
                $hitung[$tgl] = ($hitung[$tgl] ?? 0) + $p['jumlah'];
            }
        }

        return $hitung;
    }

    /** Unit kombinasi ini yang belum dikembalikan (selesai >= hari ini), tanpa melihat tanggal mulai. */
    private static function terpakaiSekarang(array $k, array $pesanan): int
    {
        $hariIni = Carbon::today()->toDateString();
        $jumlah = 0;

        foreach ($pesanan as $p) {
            if (self::cocok($k, $p) && $p['selesai'] >= $hariIni) {
                $jumlah += $p['jumlah'];
            }
        }

        return $jumlah;
    }

    /**
     * Seperti hitung(), tapi tanggal HARI INI minimal sama dengan jumlah unit yang belum dikembalikan.
     * Jadi sewa yang jadwal mulainya besok tetap mengurangi stok hari ini, sama dengan angka di
     * Kelola Produk (terpakaiHariIni). Tanggal setelah hari ini tetap murni per jadwal booking.
     *
     * @return array<string, int>
     */
    private static function hitungSekarang(array $k, array $pesanan): array
    {
        $hitung = self::hitung($k, $pesanan);
        $hariIni = Carbon::today()->toDateString();
        $terpakai = self::terpakaiSekarang($k, $pesanan);

        if ($terpakai > ($hitung[$hariIni] ?? 0)) {
            $hitung[$hariIni] = $terpakai;
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
                foreach (self::hitungSekarang($k, $pesanan) as $tgl => $jml) {
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

    /**
     * Ringkasan untuk kalender: per kombinasi, kapasitas unit dan jumlah unit yang dibooking per tanggal.
     * Tanggal "penuh" bila terpakai >= kapasitas; "sebagian" bila 0 < terpakai < kapasitas.
     *
     * @return array<string, array{kapasitas:int, hitung:array<string,int>}>
     */
    public static function ringkasan(Produk $produk): array
    {
        $pesanan = self::pemesananAktif($produk);
        $hasil = [];

        foreach (self::kombinasi($produk) as $kunci => $k) {
            $hasil[$kunci] = [
                'kapasitas' => $k['kapasitas'],
                'hitung'    => self::hitungSekarang($k, $pesanan),
                'rentang'   => self::rentang($k, $pesanan),
            ];
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

        $hitung = self::hitungSekarang($k, self::pemesananAktif($produk));

        for ($t = Carbon::parse($mulai)->startOfDay(), $akhir = Carbon::parse($selesai)->startOfDay(); $t->lte($akhir); $t->addDay()) {
            if (($hitung[$t->toDateString()] ?? 0) + $jumlah > $k['kapasitas']) {
                return false;
            }
        }

        return true;
    }
}