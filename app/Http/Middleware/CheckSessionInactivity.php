<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionInactivity
{
    /**
     * Handle an incoming request.
     *
     * Memeriksa durasi inaktivitas (idle) pengguna baik dari sisi admin maupun pelanggan.
     * Jika melebihi batas waktu tanpa aktivitas, pengguna otomatis di-logout dengan pesan keterangan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek inaktivitas untuk Sisi Admin & Owner (Guard: admin)
        if (Auth::guard('admin')->check()) {
            $timeoutAdmin = (int) config('session.inactivity_timeout.admin', 30) * 60; // konversi ke detik
            $lastActivityAdmin = session('last_activity_admin');

            if ($lastActivityAdmin && (now()->timestamp - $lastActivityAdmin > $timeoutAdmin)) {
                Auth::guard('admin')->logout();
                session()->forget('last_activity_admin');
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Sesi admin Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.',
                    ], 401);
                }

                return redirect()->route('admin.login')->with('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
            }

            session(['last_activity_admin' => now()->timestamp]);
        }

        // 2. Cek inaktivitas untuk Sisi Pelanggan / User Biasa (Guard: web)
        if (Auth::guard('web')->check()) {
            $timeoutUser = (int) config('session.inactivity_timeout.user', 60) * 60; // konversi ke detik
            $lastActivityUser = session('last_activity_web');

            if ($lastActivityUser && (now()->timestamp - $lastActivityUser > $timeoutUser)) {
                Auth::guard('web')->logout();
                session()->forget('last_activity_web');
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.',
                    ], 401);
                }

                return redirect()->route('masuk')->with('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
            }

            session(['last_activity_web' => now()->timestamp]);
        }

        return $next($request);
    }
}
