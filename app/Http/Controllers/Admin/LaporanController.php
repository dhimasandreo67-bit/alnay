<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Denda;
use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\Refund;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    /*
     * ===== PENGATURAN NILAI STATUS =====
     * Kalau di database kamu nilainya beda, cukup ubah daftar di bawah ini.
     * Cek nilai yang dipakai dengan SQL:
     *   SELECT DISTINCT status_midtrans FROM pembayaran;
     *   SELECT DISTINCT status FROM refund;
     *   SELECT DISTINCT status_bayar FROM denda;
     */

    /** Status transaksi yang dianggap sudah menghasilkan pemasukan (sama dengan halaman Transaksi). */
    private const STATUS_TRANSAKSI = ['dp', 'dp_dibayar', 'lunas'];

    /** Status baris pembayaran yang dianggap uang sudah masuk. */
    private const STATUS_BAYAR_SAH = ['success', 'settlement', 'capture', 'paid', 'lunas'];

    /** Refund dengan status ini TIDAK mengurangi pendapatan. */
    private const STATUS_REFUND_TIDAK_DIHITUNG = ['pending', 'ditolak', 'dibatalkan'];

    /** Denda dengan status ini dianggap sudah dibayar. */
    private const STATUS_DENDA_LUNAS = ['lunas', 'dibayar', 'sudah_bayar', 'paid'];

    private const PERIODE = ['hari_ini', '7_hari', '30_hari', 'bulan_ini', 'bulan_lalu', 'tahun_ini', 'kustom'];

    public function index(Request $request)
    {
        $f = $this->filter($request);

        $baris = $this->ambilBaris($f);
        $ringkas = $this->ringkasan($f, $baris);
        $ringkasSebelumnya = $this->ringkasan($this->filterSebelumnya($f));

        $banding = [
            'bersih'   => $this->delta($ringkas['bersih'], $ringkasSebelumnya['bersih']),
            'diterima' => $this->delta($ringkas['diterima'], $ringkasSebelumnya['diterima']),
            'jumlah'   => $this->delta($ringkas['jumlah'], $ringkasSebelumnya['jumlah']),
        ];

        $grafik = $this->bangunGrafik($f, $baris);
        $grafik['metode'] = $this->ambilMetode($f, $ringkas['diterima']);

        [$produkTerlaris, $kategoriTerlaris, $totalOmzet] = $this->ambilProduk($f);

        $rincian = $this->queryDenganDibayar($f)
            ->with('user:id,name')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
        $rincian->getCollection()->each(fn ($t) => $t->diterima = $this->hitungDiterima($t));

        $opsiMetode = DB::table((new Pembayaran())->getTable())
            ->selectRaw("DISTINCT COALESCE(NULLIF(metode, ''), NULLIF(payment_type, '')) as m")
            ->pluck('m')
            ->filter()
            ->values();

        $opsiKategori = Kategori::orderBy('nama_kategori')->get(['id', 'nama_kategori']);

        $queryExport = array_filter([
            'periode'     => $f['periode'],
            'dari'        => $f['periode'] === 'kustom' ? $f['dari']->toDateString() : null,
            'sampai'      => $f['periode'] === 'kustom' ? $f['sampai']->toDateString() : null,
            'channel'     => $f['channel'],
            'jenis'       => $f['jenis'],
            'metode'      => $f['metode'],
            'kategori_id' => $f['kategori_id'],
        ], fn ($v) => $v !== null && $v !== '');

        return view('admin.laporan.index', compact(
            'f', 'ringkas', 'banding', 'grafik', 'produkTerlaris', 'kategoriTerlaris',
            'totalOmzet', 'rincian', 'opsiMetode', 'opsiKategori', 'queryExport'
        ));
    }

    /**
     * Unduh rincian transaksi (mengikuti filter aktif) sebagai CSV.
     * Pemisah titik koma + BOM UTF-8 supaya langsung rapi di Excel versi Indonesia.
     */
    public function export(Request $request)
    {
        $f = $this->filter($request);
        $namaFile = 'laporan-pendapatan-' . $f['dari']->format('Ymd') . '-' . $f['sampai']->format('Ymd') . '.csv';

        $transaksis = $this->queryDenganDibayar($f)
            ->with('user:id,name')
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        return response()->streamDownload(function () use ($transaksis) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Kode Transaksi', 'Tanggal', 'Pelanggan', 'Kanal', 'Jenis', 'Status', 'Total', 'Diterima', 'Sisa Tagihan'], ';');

            $sumTotal = $sumDiterima = 0;
            foreach ($transaksis as $t) {
                $total = (float) $t->total;
                $diterima = $this->hitungDiterima($t);
                $sumTotal += $total;
                $sumDiterima += $diterima;

                fputcsv($out, [
                    $this->amanCsv($t->kode_transaksi),
                    $t->tanggal->format('Y-m-d'),
                    $this->amanCsv($t->user->name ?? $t->nama_tamu ?? 'Tamu'),
                    $t->channel === 'kasir' ? 'Kasir' : 'Online',
                    $t->jenis === 'sewa' ? 'Sewa' : 'Beli',
                    $t->status_pembayaran === 'lunas' ? 'Lunas' : 'DP',
                    round($total),
                    round($diterima),
                    round(max(0, $total - $diterima)),
                ], ';');
            }

            fputcsv($out, ['TOTAL', '', '', '', '', '', round($sumTotal), round($sumDiterima), round(max(0, $sumTotal - $sumDiterima))], ';');
            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // =====================================================================
    //  FILTER
    // =====================================================================

    private function filter(Request $request): array
    {
        $hari = Carbon::today();
        $periode = in_array($request->input('periode'), self::PERIODE, true) ? $request->input('periode') : 'bulan_ini';

        [$dari, $sampai] = match ($periode) {
            'hari_ini'   => [$hari->copy(), $hari->copy()],
            '7_hari'     => [$hari->copy()->subDays(6), $hari->copy()],
            '30_hari'    => [$hari->copy()->subDays(29), $hari->copy()],
            'bulan_lalu' => [
                $hari->copy()->subMonthNoOverflow()->startOfMonth(),
                $hari->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            'tahun_ini'  => [$hari->copy()->startOfYear(), $hari->copy()],
            'kustom'     => $this->rentangKustom($request, $hari),
            default      => [$hari->copy()->startOfMonth(), $hari->copy()],
        };

        $dari = $dari->copy()->startOfDay();
        $sampai = $sampai->copy()->startOfDay();

        if ($dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }
        // Batasi rentang maksimal ±3 tahun supaya query & grafik tetap ringan
        if ($dari->diffInDays($sampai) > 1096) {
            $dari = $sampai->copy()->subDays(1096);
        }

        $metode = mb_substr(trim((string) $request->input('metode', '')), 0, 50);
        $kategoriId = $request->filled('kategori_id') ? (int) $request->input('kategori_id') : null;

        return [
            'periode'     => $periode,
            'dari'        => $dari,
            'sampai'      => $sampai,
            'channel'     => in_array($request->input('channel'), ['kasir', 'online'], true) ? $request->input('channel') : '',
            'jenis'       => in_array($request->input('jenis'), ['beli', 'sewa'], true) ? $request->input('jenis') : '',
            'metode'      => $metode,
            'kategori_id' => $kategoriId ?: null,
            // Refund & denda tidak punya relasi ke metode/kategori, jadi disembunyikan saat dua filter itu aktif
            'hitung_refund_denda' => $metode === '' && ! $kategoriId,
        ];
    }

    private function rentangKustom(Request $request, Carbon $hari): array
    {
        $parse = function (?string $nilai, Carbon $default) {
            if (! $nilai) {
                return $default;
            }
            try {
                return Carbon::parse($nilai);
            } catch (\Throwable) {
                return $default;
            }
        };

        return [
            $parse($request->input('dari'), $hari->copy()->startOfMonth()),
            $parse($request->input('sampai'), $hari->copy()),
        ];
    }

    private function filterSebelumnya(array $f): array
    {
        $lama = (int) $f['dari']->diffInDays($f['sampai']) + 1;

        $sebelumnya = $f;
        $sebelumnya['sampai'] = $f['dari']->copy()->subDay();
        $sebelumnya['dari'] = $sebelumnya['sampai']->copy()->subDays($lama - 1);

        return $sebelumnya;
    }

    // =====================================================================
    //  QUERY
    // =====================================================================

    /** Transaksi dalam periode + semua filter aktif. */
    private function queryDasar(array $f): Builder
    {
        $q = Transaksi::query()
            ->whereBetween('tanggal', [$f['dari']->toDateString(), $f['sampai']->toDateString()])
            ->whereIn('status_pembayaran', self::STATUS_TRANSAKSI);

        $this->saringKanalJenis($q, $f);

        if ($f['metode'] !== '') {
            $q->whereHas('pembayaran', function ($p) use ($f) {
                $p->whereRaw("COALESCE(NULLIF(metode, ''), NULLIF(payment_type, '')) = ?", [$f['metode']])
                  ->whereIn('status_midtrans', self::STATUS_BAYAR_SAH);
            });
        }

        if ($f['kategori_id']) {
            $q->whereHas('detail.produk', fn ($p) => $p->where('kategori_id', $f['kategori_id']));
        }

        return $q;
    }

    /** Filter kanal & jenis. Transaksi lama dengan channel NULL dianggap online (sama dengan halaman Transaksi). */
    private function saringKanalJenis($q, array $f): void
    {
        $tabel = (new Transaksi())->getTable();

        if ($f['channel'] === 'kasir') {
            $q->where("$tabel.channel", 'kasir');
        } elseif ($f['channel'] === 'online') {
            $q->where(function ($w) use ($tabel) {
                $w->whereNull("$tabel.channel")->orWhere("$tabel.channel", 'online');
            });
        }

        if ($f['jenis'] !== '') {
            $q->where("$tabel.jenis", $f['jenis']);
        }
    }

    /** queryDasar + kolom `dibayar` (jumlah pembayaran yang sudah masuk per transaksi). */
    private function queryDenganDibayar(array $f, array $kolom = ['transaksi.*']): Builder
    {
        $tabel = (new Transaksi())->getTable();

        $dibayar = Pembayaran::query()
            ->selectRaw('COALESCE(SUM(jumlah), 0)')
            ->whereColumn('transaksi_id', "$tabel.id")
            ->whereIn('status_midtrans', self::STATUS_BAYAR_SAH);

        return $this->queryDasar($f)->select($kolom)->selectSub($dibayar, 'dibayar');
    }

    private function ambilBaris(array $f): Collection
    {
        return $this->queryDenganDibayar($f, [
            'transaksi.id', 'transaksi.tanggal', 'transaksi.total',
            'transaksi.channel', 'transaksi.jenis', 'transaksi.status_pembayaran',
        ])->get()->each(fn ($t) => $t->diterima = $this->hitungDiterima($t));
    }

    /**
     * Uang yang benar-benar diterima dari satu transaksi:
     * - Lunas  : dihitung penuh (total). Pelunasan manual lewat tombol "Lunasi" tidak membuat baris pembayaran baru.
     * - DP     : sebesar pembayaran yang sudah masuk (maksimal total).
     */
    private function hitungDiterima($t): float
    {
        $total = (float) $t->total;

        if ($t->status_pembayaran === 'lunas') {
            return $total;
        }

        return min((float) ($t->dibayar ?? 0), $total);
    }

    // =====================================================================
    //  RINGKASAN
    // =====================================================================

    private function ringkasan(array $f, ?Collection $baris = null): array
    {
        $baris ??= $this->ambilBaris($f);

        $jumlah = $baris->count();
        $nilai = (float) $baris->sum(fn ($t) => (float) $t->total);
        $diterima = (float) $baris->sum('diterima');
        $belumLunas = $baris->filter(fn ($t) => $t->diterima < (float) $t->total - 0.5);
        $sisa = (float) $belumLunas->sum(fn ($t) => (float) $t->total - $t->diterima);

        $refund = $f['hitung_refund_denda'] ? $this->totalRefund($f) : null;
        $denda = $f['hitung_refund_denda'] ? $this->totalDenda($f) : null;

        return [
            'jumlah'      => $jumlah,
            'jumlah_sewa' => $baris->where('jenis', 'sewa')->count(),
            'jumlah_beli' => $baris->where('jenis', '!=', 'sewa')->count(),
            'nilai'       => $nilai,
            'diterima'    => $diterima,
            'sisa'        => $sisa,
            'belum_lunas' => $belumLunas->count(),
            'rata_rata'   => $jumlah > 0 ? $nilai / $jumlah : 0,
            'refund'      => $refund,
            'denda'       => $denda,
            'bersih'      => $diterima + ($denda ?? 0) - ($refund ?? 0),
        ];
    }

    private function totalRefund(array $f): float
    {
        return (float) Refund::query()
            ->whereBetween('created_at', [$f['dari']->copy()->startOfDay(), $f['sampai']->copy()->endOfDay()])
            ->whereNotIn('status', self::STATUS_REFUND_TIDAK_DIHITUNG)
            ->whereHas('transaksi', fn ($q) => $this->saringKanalJenis($q, $f))
            ->sum('jumlah');
    }

    private function totalDenda(array $f): float
    {
        // Tabel denda bisa saja belum dimigrasi; jangan bikin halaman laporan error.
        $tabel = (new Denda())->getTable();
        if (! Schema::hasTable($tabel) || ! Schema::hasColumns($tabel, ['sewa_id', 'jumlah', 'status_bayar'])) {
            return 0.0;
        }

        return (float) Denda::query()
            ->whereBetween('created_at', [$f['dari']->copy()->startOfDay(), $f['sampai']->copy()->endOfDay()])
            ->whereIn('status_bayar', self::STATUS_DENDA_LUNAS)
            ->whereHas('sewa.transaksi', fn ($q) => $this->saringKanalJenis($q, $f))
            ->sum('jumlah');
    }

    private function delta(float $sekarang, float $sebelumnya): ?float
    {
        if ($sebelumnya == 0.0) {
            return null;
        }

        return round((($sekarang - $sebelumnya) / abs($sebelumnya)) * 100, 1);
    }

    // =====================================================================
    //  GRAFIK
    // =====================================================================

    private function bangunGrafik(array $f, Collection $baris): array
    {
        $bulanan = (int) $f['dari']->diffInDays($f['sampai']) > 62;
        $format = $bulanan ? 'Y-m' : 'Y-m-d';

        $slot = [];
        if ($bulanan) {
            for ($d = $f['dari']->copy()->startOfMonth(); $d->lte($f['sampai']); $d->addMonthNoOverflow()) {
                $slot[$d->format('Y-m')] = $d->translatedFormat('M Y');
            }
        } else {
            for ($d = $f['dari']->copy(); $d->lte($f['sampai']); $d->addDay()) {
                $slot[$d->format('Y-m-d')] = $d->translatedFormat('j M');
            }
        }

        $nol = array_fill_keys(array_keys($slot), 0);
        $perKanal = ['kasir' => $nol, 'online' => $nol];
        $jumlah = $nol;
        $totalKanal = ['kasir' => 0, 'online' => 0];
        $totalJenis = ['beli' => 0, 'sewa' => 0];

        foreach ($baris as $t) {
            $kanal = $t->channel === 'kasir' ? 'kasir' : 'online';
            $jenis = $t->jenis === 'sewa' ? 'sewa' : 'beli';
            $kunci = $t->tanggal->format($format);

            $totalKanal[$kanal] += $t->diterima;
            $totalJenis[$jenis] += $t->diterima;

            if (isset($slot[$kunci])) {
                $perKanal[$kanal][$kunci] += $t->diterima;
                $jumlah[$kunci]++;
            }
        }

        $bulat = fn (array $a) => array_map(fn ($v) => (int) round($v), array_values($a));

        return [
            'tren' => [
                'mode'   => $bulanan ? 'bulanan' : 'harian',
                'labels' => array_values($slot),
                'kasir'  => $bulat($perKanal['kasir']),
                'online' => $bulat($perKanal['online']),
                'jumlah' => array_values($jumlah),
            ],
            'kanal' => [
                'labels' => ['Kasir (Offline)', 'Online'],
                'values' => $bulat($totalKanal),
            ],
            'jenis' => [
                'labels' => ['Penjualan', 'Sewa'],
                'values' => $bulat($totalJenis),
            ],
        ];
    }

    private function ambilMetode(array $f, float $diterimaTotal): array
    {
        // Kelompokkan per kolom biasa (aman untuk ONLY_FULL_GROUP_BY), lalu digabung di PHP.
        $rows = Pembayaran::query()
            ->whereIn('transaksi_id', $this->queryDasar($f)->select('transaksi.id'))
            ->whereIn('status_midtrans', self::STATUS_BAYAR_SAH)
            ->selectRaw('metode, payment_type, SUM(jumlah) as total')
            ->groupBy('metode', 'payment_type')
            ->get();

        $gabung = [];
        foreach ($rows as $r) {
            $nama = trim((string) $r->metode) !== ''
                ? $r->metode
                : (trim((string) $r->payment_type) !== '' ? $r->payment_type : 'lainnya');

            $kunci = strtolower(trim($nama));
            $gabung[$kunci] = ($gabung[$kunci] ?? 0) + (float) $r->total;
        }
        arsort($gabung);

        $labels = [];
        $values = [];
        foreach ($gabung as $nama => $total) {
            $labels[] = match ($nama) {
                'qris'    => 'QRIS',
                'lainnya' => 'Lainnya',
                default   => Str::headline($nama),
            };
            $values[] = (int) round($total);
        }

        // Pelunasan manual (tombol "Lunasi") tidak punya baris pembayaran, jadi ditampilkan terpisah
        $manual = $diterimaTotal - array_sum($values);
        if ($manual > 0.5) {
            $labels[] = 'Pelunasan manual';
            $values[] = (int) round($manual);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    // =====================================================================
    //  PRODUK & KATEGORI
    // =====================================================================

    /** Omzet per produk/kategori = harga satuan x jumlah (nilai transaksi, bukan uang diterima). */
    private function ambilProduk(array $f): array
    {
        $tProduk   = $this->namaTabel((new Produk())->getTable());
        $tKategori = $this->namaTabel((new Kategori())->getTable());
        $tDetail   = $this->namaTabel((new DetailTransaksi())->getTable());

        $baris = DB::table($tDetail . ' as d')                                    // ← dari: "$tDetail as d"
            ->join($tProduk . ' as p', 'p.id', '=', 'd.produk_id')                // ← dari: "$tProduk as p"
            ->leftJoin($tKategori . ' as k', 'k.id', '=', 'p.kategori_id')        // ← dari: "$tKategori as k"
            ->whereIn('d.transaksi_id', $this->queryDasar($f)->select('transaksi.id'))
            ->when($f['kategori_id'], fn ($q, $id) => $q->where('p.kategori_id', $id))
            ->selectRaw('p.id as produk_id, p.nama, p.sku, k.nama_kategori, SUM(d.jumlah) as qty, SUM(d.jumlah * d.harga_satuan) as omzet')
            ->groupBy('p.id', 'p.nama', 'p.sku', 'k.nama_kategori')
            ->orderByDesc('omzet')
            ->get();

        $kategori = $baris
            ->groupBy(fn ($r) => $r->nama_kategori ?? 'Tanpa kategori')
            ->map(fn ($g, $nama) => [
                'nama'  => $nama,
                'qty'   => (int) $g->sum('qty'),
                'omzet' => (float) $g->sum('omzet'),
            ])
            ->sortByDesc('omzet')
            ->values();

        return [$baris->take(10), $kategori, (float) $kategori->sum('omzet')];
    }

    /** Cegah formula injection saat CSV dibuka di Excel. */
    private function amanCsv(?string $nilai): string
    {
        $nilai = (string) $nilai;

        return $nilai !== '' && in_array($nilai[0], ['=', '+', '-', '@'], true) ? "'" . $nilai : $nilai;
    }

    /**
     * Nama tabel berasal dari metadata model Eloquent (developer-controlled),
     * tetapi tetap divalidasi sebagai identifier SQL — defense-in-depth sekaligus
     * memenuhi analisis keamanan statis.
     */
    private function namaTabel(string $tabel): string
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $tabel)) {
            throw new \RuntimeException("Nama tabel tidak valid: {$tabel}");
        }

        return $tabel;
    }
}