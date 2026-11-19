<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('masuk')
                ->withErrors(['email' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            // Buat akun baru otomatis
            $user = new User();
            $user->forceFill([
                'name'         => $googleUser->getName() ?? 'Pengguna Google',
                'email'        => $googleUser->getEmail(),
                'password'     => bcrypt(Str::random(16)),
                'role'         => 'pelanggan',
                'status_aktif' => true,
                'foto_profil'  => $googleUser->getAvatar(),
            ])->save();
        }

        Auth::login($user, true);

        return match ($user->role) {
            'owner' => redirect()->route('owner.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('beranda'),
        };
    }
}