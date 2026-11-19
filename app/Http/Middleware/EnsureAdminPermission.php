<?php

namespace App\Http\Middleware;

use App\Support\AdminMenu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * Key route admin yang tidak butuh hak menu khusus.
     * - login        : harus bisa dibuka sebelum login
     * - logout       : semua admin yang sudah login
     * - notifikasi   : isinya sudah disaring per hak akses
     * - dashboard    : selalu boleh untuk semua admin
     */
    private const TERBUKA = ['login', 'logout', 'notifikasi', 'dashboard'];

    /**
     * Cek apakah akun yang login boleh mengakses halaman panel admin ini.
     *
     * - Route di luar prefix nama 'admin.' dilewatkan (bukan urusan middleware ini).
     * - Selalu membaca guard 'admin' secara eksplisit, jadi hasilnya sama
     *   di mana pun middleware ini ditempatkan dalam urutan middleware.
     * - Fail-closed: route 'admin.*' yang key-nya tidak dikenal DITOLAK,
     *   bukan dilewatkan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (! $routeName || ! str_starts_with($routeName, 'admin.')) {
            return $next($request);
        }

        $menuKey = AdminMenu::keyFromRouteName($routeName);

        // Halaman login admin (admin.login, admin.login.store)
        if ($menuKey === 'login') {
            return $next($request);
        }

        $user = $request->user('admin');

        if (! $user) {
            return $request->expectsJson()
                ? abort(401, 'Silakan login.')
                : redirect()->route('admin.login');
        }

        if (! $user->isAdminPanelUser() || $user->status_aktif === false) {
            abort(403, 'Akun ini tidak punya akses ke panel admin.');
        }

        if ($user->isOwner()) {
            return $next($request);
        }

        if (in_array($menuKey, self::TERBUKA, true)) {
            return $next($request);
        }

        if (! $menuKey || ! array_key_exists($menuKey, AdminMenu::all())) {
            abort(403, 'Halaman admin ini tidak dikenali.');
        }

        if (! $user->canAccessAdminMenu($menuKey)) {
            abort(403, 'Kamu tidak memiliki akses ke halaman "' . AdminMenu::label($menuKey) . '".');
        }

        return $next($request);
    }
}