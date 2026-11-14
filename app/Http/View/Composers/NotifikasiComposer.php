<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Pengiriman;
use App\Models\Refund;

class NotifikasiComposer
{
    public function compose(View $view)
    {
        // 1. Hitung pesanan yang belum dikirim
        $belumDikirim = Pengiriman::where('status_kirim', 'menunggu')->count();
        
        // 2. Hitung pengajuan refund yang baru masuk
        $refundBaru = Refund::where('status', 'diajukan')->count();
        
        // 3. Total keseluruhan notifikasi untuk angka di lonceng
        $totalNotif = $belumDikirim + $refundBaru;

        // Buat daftar pesan notifikasi
        $pesanNotif = [];
        
        if ($belumDikirim > 0) {
            $pesanNotif[] = [
                'judul' => 'Pengiriman Tertunda',
                'pesan' => "Ada {$belumDikirim} pesanan yang menunggu untuk dikirim.",
                'url' => route('admin.pengiriman.index', ['status' => 'menunggu']),
                'warna' => 'text-amber-500',
                'icon' => 'truck'
            ];
        }

        if ($refundBaru > 0) {
            $pesanNotif[] = [
                'judul' => 'Pengajuan Refund Baru',
                'pesan' => "Ada {$refundBaru} pengajuan refund yang menunggu tinjauan.",
                'url' => route('admin.refund.index'), // Sesuaikan dengan nama route index refund admin
                'warna' => 'text-rose-500',
                'icon' => 'refund'
            ];
        }

        // Jika kosong, tampilkan pesan aman
        if (empty($pesanNotif)) {
            $pesanNotif[] = [
                'judul' => 'Semua aman',
                'pesan' => 'Tidak ada notifikasi baru.',
                'url' => '#',
                'warna' => 'text-emerald-500',
                'icon' => 'check'
            ];
        }

        // Kirim data ke view
        $view->with('totalNotif', $totalNotif)
             ->with('pesanNotif', $pesanNotif);
    }
}