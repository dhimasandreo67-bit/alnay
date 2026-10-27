<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function create()
    {
        // Cek login menggunakan guard admin
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'no_hp';

        // GUNAKAN GUARD 'admin' UNTUK LOGIN
        if (! Auth::guard('admin')->attempt([
            $field => $credentials['email'], 
            'password' => $credentials['password'] 
        ], $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email/HP atau kata sandi yang kamu masukkan salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Cek role menggunakan guard admin
        $user = Auth::guard('admin')->user();
        if (! in_array($user->role, ['admin', 'owner'])) {
            Auth::guard('admin')->logout();
            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses ke backoffice.',
            ]);
        }

        if (! $user->status_aktif) {
            Auth::guard('admin')->logout();
            return back()->withErrors([
                'email' => 'Akun ini sedang dinonaktifkan. Hubungi owner untuk mengaktifkannya kembali.',
            ]);
        }

        $request->session()->forget('url.intended');

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        // Logout menggunakan guard admin
        Auth::guard('admin')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // GUNAKAN GUARD 'admin'
        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::guard('admin')->user();
        if (! $user->isAdminPanelUser()) {
            Auth::guard('admin')->logout();
            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses ke backoffice.',
            ])->onlyInput('email');
        }

        if (! $user->status_aktif) {
            Auth::guard('admin')->logout();
            return back()->withErrors([
                'email' => 'Akun ini sedang dinonaktifkan. Hubungi owner untuk mengaktifkannya kembali.',
            ])->onlyInput('email');
        }

        $request->session()->forget('url.intended');

        return redirect()->route('admin.dashboard');
    }
}