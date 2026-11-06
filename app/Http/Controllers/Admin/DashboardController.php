<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JaminanSewa;
use App\Models\Produk;
use App\Models\Refund;
use App\Models\ReservasiFitting;
use App\Models\Sewa;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        $stats = [
            'transaksi_hari_ini'   => Transaksi::whereDate('created_at', $today)->count(),
            'transaksi_trend'      => $this->trendLabel($today),
            'sewa_aktif'           => Sewa::where('status', 'aktif')->count(),
            'sewa_jatuh_tempo'     => Sewa::where('status', 'aktif')
                ->whereDate('tanggal_kembali_rencana', $today->copy()->addDay())
                ->count(),
            'refund_pending'       => Refund::where('status', 'pending')->count(),
            'refund_saldo'         => Refund::where('status', 'pending')->sum('jumlah'),
            'pendapatan_bulan_ini' => Transaksi::whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->sum('total'),
        ];

        $chart = collect(range(6, 0))->map(function ($daysAgo) use ($today) {
            $date = $today->copy()->subDays($daysAgo);

            return [
                'label' => $daysAgo === 0 ? 'Hari ini' : $date->translatedFormat('D'),
                'value' => Transaksi::whereDate('created_at', $date)->count(),
                'today' => $daysAgo === 0,
            ];
        })->toArray();

        $transactions = Transaksi::with('user')
            ->latest()
            ->take(6)
            ->get()
            ->map(function (Transaksi $trx) {
                $nama = $trx->user->name ?? $trx->nama_tamu ?? 'Tamu';

                return [
                    'kode'       => $trx->kode_transaksi,
                    'pelanggan'  => $nama,
                    'inisial'    => collect(explode(' ', $nama))->filter()->map(fn ($w) => $w[0])->take(2)->implode(''),
                    'detail'     => $trx->catatan ?? $trx->jenis,
                    'jenis'      => $trx->jenis === 'sewa' ? 'Sewa' : 'Beli',
                    'kanal'      => $trx->kanal === 'online' ? 'Online' : 'Walk In',
                    'status'     => $trx->status_pembayaran === 'lunas' ? 'Lunas' : 'DP 50%',
                    'statusTone' => $trx->status_pembayaran === 'lunas' ? 'green' : 'rose',
                    'total'      => $trx->total,
                ];
            })->toArray();

        // ======================= PERLU TINDAKAN =======================
        $jaminanPending  = JaminanSewa::where('status_verifikasi', 'pending')->count();
        $refundPending   = Refund::where('status', 'pending')->count();
        $returPengecekan = Sewa::where('status', 'selesai')->doesntHave('pengecekan')->count();
        $fittingPending  = ReservasiFitting::where('status', 'pending')->count();

        $actionQueue = [
            [
                'key'   => 'jaminan',
                'title' => 'Verifikasi jaminan sewa',
                'desc'  => $jaminanPending . ' jaminan KTP / deposit pending',
                'count' => $jaminanPending,
                'route' => \Illuminate\Support\Facades\Route::has('admin.jaminan.index') ? route('admin.jaminan.index') : '#',
            ],
            [
                'key'   => 'refund',
                'title' => 'Refund diproses',
                'desc'  => $refundPending . ' pengajuan pembatalan',
                'count' => $refundPending,
                'route' => \Illuminate\Support\Facades\Route::has('admin.refund.index') ? route('admin.refund.index') : '#',
            ],
            [
                'key'   => 'retur',
                'title' => 'Return pengecekan',
                'desc'  => $returPengecekan . ' gaun baru tiba di toko, perlu dicek',
                'count' => $returPengecekan,
                'route' => \Illuminate\Support\Facades\Route::has('admin.sewa.index') ? route('admin.sewa.index', ['status' => 'dikembalikan']) : '#',
            ],
            [
                'key'   => 'fitting',
                'title' => 'Reservasi fitting baru',
                'desc'  => $fittingPending . ' jadwal butuh konfirmasi',
                'count' => $fittingPending,
                'route' => \Illuminate\Support\Facades\Route::has('admin.fitting.index') ? route('admin.fitting.index') : '#',
            ],
        ];

        $antreanTotal = collect($actionQueue)->sum('count');

        // ======================= STOK PERLU PERHATIAN =======================
        $stockAlerts = Produk::whereIn('status', ['dicuci', 'rusak'])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function (Produk $p) {
                return [
                    'nama'   => $p->nama,
                    'sku'    => $p->sku,
                    'status' => $p->status, // 'dicuci' | 'rusak'
                    'label'  => \App\Support\ProdukStatus::label($p->status, (bool) $p->dapat_dijual),
                ];
            });

        $notifCount = $jaminanPending + $refundPending;

        return view('admin.dashboard', compact(
            'stats', 'chart', 'transactions', 'notifCount',
            'actionQueue', 'antreanTotal', 'stockAlerts'
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

        return ($diff >= 0 ? '+' : '') . $diff . '%';
    }
}