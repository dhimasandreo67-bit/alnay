<?php

namespace App\Providers;

use App\Support\NotifikasiAdmin;
use Illuminate\Support\ServiceProvider;

class NotifikasiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pasang pendengar event model (pesanan, fitting, refund, pelanggan, rating).
        NotifikasiAdmin::daftarkan();
    }
}