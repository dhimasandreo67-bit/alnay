<?php

namespace App\Http\Middleware;

use App\Support\AdminMenu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * Cek apakah user yang login boleh mengakses halaman panel admin ini.
     *
     * - 'owner'   : selalu diizinkan (super-admin).
     * - 'admin'   : diizinkan hanya jika menu-nya ada di $user->admin_permissions,
     *               kecuali 'dashboard' yang selalu boleh diakses semua admin.
     * - role lain : tidak pernah lolos middleware 'auth' admin, jadi tidak sampai sini.
     * - route di luar daftar AdminMenu (login/logout, dsb) dilewatkan begitu saja.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->isOwner()) {
            return $next($request);
        }

        $menuKey = AdminMenu::keyFromRouteName($request->route()?->getName());

        if (!$menuKey || !array_key_exists($menuKey, AdminMenu::all())) {
            return $next($request);
        }

        if ($menuKey === 'dashboard') {
            return $next($request);
        }

        if (!$user->canAccessAdminMenu($menuKey)) {
            abort(403, 'Kamu tidak memiliki akses ke halaman "' . AdminMenu::label($menuKey) . '".');
        }

        return $next($request);
    }
}