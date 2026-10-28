<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * 1. Mengeluarkan pelanggan yang akunnya sudah dinonaktifkan admin (berlaku untuk semua cara login:
 *    OTP, Google, "ingat saya"), tanpa perlu mengubah controller login.
 * 2. Mencatat waktu aktivitas terakhir (maks. sekali per 10 menit) untuk halaman Kelola Pengguna.
 *
 * Hanya memeriksa guard `web` (pelanggan); guard `admin` tidak tersentuh.
 */
class EnsureUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');

        if ($guard->check()) {
            $user = $guard->user();
            $atribut = $user->getAttributes();

            if (array_key_exists('is_active', $atribut) && ! $user->is_active) {
                $guard->logout();

                return redirect()
                    ->to($this->urlMasuk())
                    ->with('error', 'Akun kamu sedang dinonaktifkan. Hubungi admin ALNAY.ID untuk mengaktifkannya kembali.');
            }

            if (array_key_exists('last_active_at', $atribut)) {
                $terakhir = $atribut['last_active_at'] ? Carbon::parse($atribut['last_active_at']) : null;

                if (! $terakhir || $terakhir->lt(now()->subMinutes(10))) {
                    DB::table('users')->where('id', $user->getKey())->update(['last_active_at' => now()]);
                }
            }
        }

        return $next($request);
    }

    protected function urlMasuk(): string
    {
        foreach (['masuk', 'login'] as $nama) {
            if (\Illuminate\Support\Facades\Route::has($nama)) {
                return route($nama);
            }
        }

        return url('/');
    }
}