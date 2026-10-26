<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // ← PASTIKAN BARIS INI ADA

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
        // === TAMBAHKAN KODE INI ===
        // Share data jumlah keranjang ke SEMUA view (termasuk header)
        View::composer('*', function ($view) {
            $cartItems = session('cart', []);
            $view->with('cartCount', count($cartItems));
        });
        // ==========================
    }
}