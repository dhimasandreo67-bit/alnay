<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi area /owner hanya untuk akun ber-role owner yang aktif.
 * Dipasang SETELAH auth:web, jadi tamu sudah dialihkan ke halaman masuk sebelum sampai sini.
 */
class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if (! $user || ! $user->isOwner() || $user->status_aktif === false) {
            abort(403, 'Halaman ini khusus owner.');
        }

        return $next($request);
    }
}