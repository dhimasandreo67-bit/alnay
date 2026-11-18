<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Kategori;
use App\Models\NotifikasiPelanggan;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Lonceng notifikasi pelanggan: yang tampil = pesanan menunggu pembayaran yang belum dihapus.
        // Badge hanya menghitung yang BELUM dibuka.
        View::composer('layouts.public', function ($view) {
            if (auth()->check()) {
                $ringkasan = NotifikasiPelanggan::ringkasan(auth()->id());

                $notifikasi       = $ringkasan['items'];
                $jumlahNotifikasi = $ringkasan['belum_dibaca'];
                $totalNotifikasi  = $ringkasan['total'];
            } else {
                $notifikasi       = collect();
                $jumlahNotifikasi = 0;
                $totalNotifikasi  = 0;
            }

            $view->with('notifikasi', $notifikasi)
                 ->with('jumlahNotifikasi', $jumlahNotifikasi)
                 ->with('totalNotifikasi', $totalNotifikasi);
        });

        // Dropdown Koleksi di header: 4 kategori terpopuler (berdasarkan jumlah produk terpesan)
        View::composer('partials.header', function ($view) {
            $kategoriPopuler = Cache::remember('header.kategori_populer', 600, function () {
                return Kategori::query()
                    ->where('is_active', true)
                    ->whereNotNull('slug')
                    ->select('kategori.id', 'kategori.nama_kategori', 'kategori.slug')
                    ->selectSub(function ($q) {
                        $q->from('detail_transaksi as dt')
                            ->join('transaksi as t', 't.id', '=', 'dt.transaksi_id')
                            ->join('produk as p', 'p.id', '=', 'dt.produk_id')
                            ->whereColumn('p.kategori_id', 'kategori.id')
                            ->whereIn('t.status_pembayaran', ['lunas', 'dp', 'settlement', 'success'])
                            ->selectRaw('COALESCE(SUM(dt.jumlah), 0)');
                    }, 'terpesan')
                    ->withCount('produks')
                    ->orderByDesc('terpesan')
                    ->orderByDesc('produks_count')
                    ->orderBy('nama_kategori')
                    ->limit(4)
                    ->get()
                    ->map(fn ($k) => ['nama' => $k->nama_kategori, 'slug' => $k->slug])
                    ->all();
            });

            $view->with('kategoriPopuler', $kategoriPopuler);
        });
    }
}