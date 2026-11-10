<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Pengiriman;

class NotifikasiComposer
{
    public function compose(View $view)
    {
        // 1. Hitung pesanan yang belum dikirim
        $belumDikirim = Pengiriman::where('status_kirim', 'menunggu')->count();
        
        $totalNotif = $belumDikirim;

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