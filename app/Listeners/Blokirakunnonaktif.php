<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

/**
 * Memastikan akun pelanggan yang dinonaktifkan (Kelola Pengguna) tidak bisa memakai
 * fitur apa pun. Listener ini otomatis ditemukan Laravel 11+ (app/Listeners, method
 * berawalan "handle" dengan type-hint event).
 *
 *  - handleLogin         : saat proses login (OTP, Google, password) -> login ditolak.
 *  - handleAuthenticated : setiap request dari pelanggan yang SUDAH login -> langsung
 *                          di-logout. Event ini dipicu saat user pertama kali dibaca di
 *                          request tersebut (middleware auth, header, footer, dll), jadi
 *                          berlaku untuk semua halaman dan URL tanpa perlu daftar rute.
 *
 * Keduanya mengarahkan ke halaman /akun-nonaktif (tanpa header & footer).
 */
class BlokirAkunNonaktif
{
    public function handleLogin(Login $event): void
    {
        if ($event->guard !== 'web') {
            return;
        }

        if ($this->aktif($event->user)) {
            // Login berhasil dengan akun lain: buang sisa info akun nonaktif sebelumnya
            session()->forget('akun_nonaktif');

            return;
        }

        $this->blokir($event->guard, $event->user, false);
    }

    public function handleAuthenticated(Authenticated $event): void
    {
        // Hanya pelanggan (guard web). Staf memakai guard admin.
        if ($event->guard !== 'web' || $this->aktif($event->user)) {
            return;
        }

        $this->blokir($event->guard, $event->user, true);
    }

    private function aktif($user): bool
    {
        return ! method_exists($user, 'akunAktif') || $user->akunAktif();
    }

    private function blokir(string $guard, $user, bool $sesiDiakhiri): never
    {
        Auth::guard($guard)->logout();

        $info = $user->infoNonaktif();
        $info['sesi_diakhiri'] = $sesiDiakhiri;
        session()->put('akun_nonaktif', $info);

        // Permintaan AJAX/fetch (mis. hitung ongkir) tidak diarahkan ke halaman HTML
        if (request()->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Akun Anda sedang dinonaktifkan.',
                'redirect' => route('akun.nonaktif'),
            ], 403));
        }

        throw new HttpResponseException(redirect()->route('akun.nonaktif'));
    }
}