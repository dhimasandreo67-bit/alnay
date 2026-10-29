<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Transaksi;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.public', function ($view) {
            if (auth()->check()) {
                $notifikasi = Transaksi::where('user_id', auth()->id())
                    ->whereIn('status_pembayaran', ['pending', 'dp'])
                    ->latest()
                    ->take(5)
                    ->get();
                $jumlahNotifikasi = $notifikasi->count();
            } else {
                $notifikasi = collect();
                $jumlahNotifikasi = 0;
            }

            $view->with('notifikasi', $notifikasi)
                 ->with('jumlahNotifikasi', $jumlahNotifikasi);
        });
    }
}