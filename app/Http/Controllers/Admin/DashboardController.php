<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JaminanSewa;
use App\Models\Produk;
use App\Models\Refund;
use App\Models\ReservasiFitting;
use App\Models\Sewa;
use App\Models\Transaksi;
use App\Support\ProdukStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    /** Status pembayaran yang dihitung sebagai pendapatan (sama dengan warna "hijau" di Kelola Transaksi). */
    private const STATUS_PENDAPATAN = ['lunas', 'dp_dibayar'];

    /** Ambang "stok hampir habis" (sama dengan Kelola Produk). */
    private const BATAS_STOK_MENIPIS = 3;

    public function index(Request $request)
    {
        $today = Carbon::today();

        $stats = [
            'transaksi_hari_ini' => Transaksi::whereDate('created_at', $today)->count(),
            'transaksi_trend' => $this->trendLabel($today),
            'sewa_aktif' => Sewa::where('status', 'aktif')->count(),
            'sewa_jatuh_tempo' => Sewa::where('status', 'aktif')
                ->whereDate('tanggal_kembali_rencana', $today->copy()->addDay())
                ->count(),
            // Aktif tapi lewat jadwal kembali: sama dengan tab "Terlambat" di halaman Sewa
            'sewa_terlambat' => Sewa::where('status', 'aktif')
                ->whereDate('tanggal_kembali_rencana', '<', $today)
                ->count(),
            'refund_pending' => Refund::where('status', 'diajukan')->count(),
            'refund_saldo' => Refund::where('status', 'diajukan')->sum('jumlah'),
            // Hanya transaksi yang sudah dibayar (lunas / DP dibayar); pending tidak dihitung
            'pendapatan_bulan_ini' => Transaksi::whereIn('status_pembayaran', self::STATUS_PENDAPATAN)
                ->whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->sum('total'),
        ];

        // Grafik 7 hari: satu query (bukan 7), hari tanpa transaksi bernilai 0
        $perHari = Transaksi::where('created_at', '>=', $today->copy()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as tgl, COUNT(*) as jml')
            ->groupBy('tgl')
            ->pluck('jml', 'tgl');

        $chart = collect(range(6, 0))->map(function ($daysAgo) use ($today, $perHari) {
            $date = $today->copy()->subDays($daysAgo);

            return [
                'label' => $daysAgo === 0 ? 'Hari ini' : $date->translatedFormat('D'),
                'value' => (int) ($perHari[$date->toDateString()] ?? 0),
                'today' => $daysAgo === 0,
            ];
        })->toArray();

        // Transaksi terbaru: model asli + relasi yang sama dengan halaman Kelola Transaksi
        $transaksiTerbaru = Transaksi::with(['detail.produk', 'sewa', 'user'])
            ->latest()
            ->take(6)
            ->get();

        // ======================= PERLU TINDAKAN =======================
        $jaminanPending = JaminanSewa::where('status_verifikasi', 'pending')->count();
        $refundPending = Refund::where('status', 'pending')->count();
        $returPengecekan = Sewa::where('status', 'selesai')->doesntHave('pengecekan')->count();
        $fittingPending = ReservasiFitting::where('status', 'pending')->count();

        $actionQueue = [
            [
                'key' => 'jaminan',
                'title' => 'Verifikasi jaminan sewa',
                'desc' => $jaminanPending.' jaminan KTP / deposit pending',
                'count' => $jaminanPending,
                'route' => Route::has('admin.jaminan.index') ? route('admin.jaminan.index') : '#',
            ],
            [
                'key' => 'refund',
                'title' => 'Refund diproses',
                'desc' => $refundPending.' pengajuan pembatalan',
                'count' => $refundPending,
                'route' => Route::has('admin.refund.index') ? route('admin.refund.index') : '#',
            ],
            [
                'key' => 'retur',
                'title' => 'Return pengecekan',
                'desc' => $returPengecekan.' gaun baru tiba di toko, perlu dicek',
                'count' => $returPengecekan,
                'route' => Route::has('admin.sewa.index') ? route('admin.sewa.index', ['status' => 'dikembalikan']) : '#',
            ],
            [
                'key' => 'fitting',
                'title' => 'Reservasi fitting baru',
                'desc' => $fittingPending.' jadwal butuh konfirmasi',
                'count' => $fittingPending,
                'route' => Route::has('admin.fitting.index') ? route('admin.fitting.index') : '#',
            ],
        ];

        $antreanTotal = collect($actionQueue)->sum('count');

        // ======================= STOK PERLU PERHATIAN =======================
        // Hanya produk PENJUALAN: stok habis + stok hampir habis (+ status rusak / dicuci).
        // Stok sewa tidak ikut dihitung; produk khusus sewa tidak ditampilkan.
        $semuaProduk = Produk::where('dapat_dijual', true)->get();

        $stokTersedia = fn (Produk $p) => max(0, $p->stokUntuk('jual'));

        $semuaAlert = $semuaProduk->map(function (Produk $p) use ($stokTersedia) {
            if (in_array($p->status, ['rusak', 'dicuci'], true)) {
                return [
                    'prioritas' => $p->status === 'rusak' ? 0 : 1,
                    'nama' => $p->nama,
                    'sku' => $p->sku,
                    'status' => $p->status,
                    'label' => ProdukStatus::label($p->status, (bool) $p->dapat_dijual),
                ];
            }

            $sisa = $stokTersedia($p);

            if ($sisa <= 0) {
                return ['prioritas' => 2, 'nama' => $p->nama, 'sku' => $p->sku, 'status' => 'habis', 'label' => 'Stok Habis'];
            }
            if ($sisa <= self::BATAS_STOK_MENIPIS) {
                return ['prioritas' => 3, 'nama' => $p->nama, 'sku' => $p->sku, 'status' => 'menipis', 'label' => 'Hampir Habis ('.$sisa.' unit)'];
            }

            return null;
        })->filter()->sortBy('prioritas')->values();

        $stockAlertTotal = $semuaAlert->count();
        $stockAlerts = $semuaAlert->take(5);

        $notifCount = $jaminanPending + $refundPending;

        return view('admin.dashboard', compact(
            'stats', 'chart', 'transaksiTerbaru', 'notifCount',
            'actionQueue', 'antreanTotal', 'stockAlerts', 'stockAlertTotal'
        ));
    }

    private function trendLabel(Carbon $today): string
    {
        $todayCount = Transaksi::whereDate('created_at', $today)->count();
        $yesterdayCount = Transaksi::whereDate('created_at', $today->copy()->subDay())->count();

        if ($yesterdayCount === 0) {
            return $todayCount > 0 ? '+100%' : '0%';
        }

        $diff = round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100);

        return ($diff >= 0 ? '+' : '').$diff.'%';
    }
}
