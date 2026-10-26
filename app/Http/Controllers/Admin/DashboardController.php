<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JaminanSewa;
use App\Models\Refund;
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
            // TODO: filter ->where('status_pembayaran', 'lunas') dihapus sementara.
            // Tabel `transaksi` tidak punya kolom status_pembayaran — status lunas/DP
            // sepertinya disimpan di tabel `pembayaran` (lihat relasi Transaksi::pembayaran()).
            // Kalau mau menghitung hanya transaksi yang lunas, query ini perlu join/whereHas
            // ke tabel pembayaran, bukan where langsung di transaksi.
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
            ->map(fn (Transaksi $trx) => [
                'kode'       => $trx->kode_transaksi,
                'pelanggan'  => $trx->user->name,
                'inisial'    => collect(explode(' ', $trx->user->name))->map(fn ($w) => $w[0])->take(2)->implode(''),
                'detail'     => $trx->catatan ?? $trx->jenis,
                'jenis'      => $trx->jenis === 'sewa' ? 'Sewa' : 'Beli',
                'kanal'      => $trx->kanal === 'online' ? 'Online' : 'Walk In',
                'status'     => $trx->status_pembayaran === 'lunas' ? 'Lunas' : 'DP 50%',
                'statusTone' => $trx->status_pembayaran === 'lunas' ? 'green' : 'rose',
                'total'      => $trx->total,
            ])->toArray();

        $notifCount = JaminanSewa::where('status_verifikasi', 'pending')->count()
            + Refund::where('status', 'pending')->count();

        return view('admin.dashboard', compact('stats', 'chart', 'transactions', 'notifCount'));
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