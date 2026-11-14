<?php

namespace App\Http\View\Composers;

use App\Support\NotifikasiAdmin;
use Illuminate\View\View;

/**
 * Menyuplai data lonceng notifikasi di topbar admin.
 * Nama variabel ($totalNotif, $pesanNotif) sengaja dipertahankan supaya
 * pendaftaran composer yang sudah ada tidak perlu diubah.
 */
class NotifikasiComposer
{
    public function compose(View $view): void
    {
        $data = NotifikasiAdmin::ringkasanSaatIni();

        $view->with('totalNotif', $data['unread'])
             ->with('pesanNotif', $data['items']);
    }
}