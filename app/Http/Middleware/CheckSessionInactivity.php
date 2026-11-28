<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionInactivity
{
    /**
     * Route yang sifatnya polling otomatis (bukan aksi user), jadi tidak
     * boleh memperpanjang waktu aktivitas. Tambahkan nama route lain bila perlu.
     */
    private const ROUTE_ABAIKAN = [
        'qris.status',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Sisi Admin & Owner (guard: admin)
        $hasil = $this->periksa(
            $request,
            'admin',
            'last_activity_admin',
            (int) config('session.inactivity_timeout.admin', 30),
            'admin.login',
            'Sesi admin Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.'
        );

        if ($hasil) {
            return $hasil;
        }

        // 2. Sisi Pelanggan (guard: web)
        $hasil = $this->periksa(
            $request,
            'web',
            'last_activity_web',
            (int) config('session.inactivity_timeout.user', 60),
            'masuk',
            'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.'
        );

        if ($hasil) {
            return $hasil;
        }

        return $next($request);
    }

    private function periksa(
        Request $request,
        string $guard,
        string $kunci,
        int $menit,
        string $routeLogin,
        string $pesan
    ): ?Response {
        $auth = Auth::guard($guard);

        if (! $auth->check()) {
            return null;
        }

        $sekarang = now()->timestamp;
        $batas = $menit * 60;
        $terakhir = $request->session()->get($kunci);

        if ($terakhir) {
            $kedaluwarsa = ($sekarang - (int) $terakhir) > $batas;
        } else {
            // Tidak ada catatan aktivitas di session, tapi user terautentikasi.
            // Kalau ini terjadi karena dipulihkan dari cookie "ingat saya",
            // berarti session lamanya sudah hilang/habis -> anggap kedaluwarsa.
            $kedaluwarsa = method_exists($auth, 'viaRemember') && $auth->viaRemember();
        }

        if ($kedaluwarsa) {
            $auth->logout(); // juga menghapus cookie remember
            $request->session()->forget($kunci);
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => $pesan], 401);
            }

            return redirect()->route($routeLogin)->with('error', $pesan);
        }

        // Perbarui waktu aktivitas, kecuali request polling otomatis
        // (tetap dicatat bila belum ada nilai sama sekali).
        if (! $terakhir || ! $request->routeIs(...self::ROUTE_ABAIKAN)) {
            $request->session()->put($kunci, $sekarang);
        }

        return null;
    }
}