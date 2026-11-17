<?php

namespace App\Providers;

use App\Support\Audit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\AlamatPengiriman;
use App\Services\SinkronAlamatUtama;
use App\Http\View\Composers\NotifikasiComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Share data jumlah keranjang ke SEMUA view (termasuk header)
        View::composer('*', function ($view) {
            $cartItems = session('cart', []);
            $view->with('cartCount', count($cartItems));
        });

        // 2. Daftarkan Notifikasi hanya untuk Layout Admin
        View::composer('partials.admin-topbar', NotifikasiComposer::class);

        // 3. Pasang perekam audit log (created/updated/deleted + login/logout)
        Audit::daftarkan();
        \App\Support\NotifikasiAdmin::daftarkan(); 
    }
}