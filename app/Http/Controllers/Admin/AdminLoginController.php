<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function create()
    {
        if (Auth::check()) {
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

        // GUNAKAN KEY 'password', BUKAN 'password_hash'
        if (! Auth::attempt([
            $field => $credentials['email'], 
            'password' => $credentials['password'] 
        ], $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email/HP atau kata sandi yang kamu masukkan salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if (! in_array($request->user()->role, ['admin', 'owner'])) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses ke backoffice.',
            ]);
        }

        // Jangan pakai redirect()->intended() di sini. Kalau admin sempat
        // membuka halaman user/customer sebelum login, Laravel akan menyimpan
        // URL tersebut sebagai "intended" dan admin akan diarahkan ke sana
        // (halaman user) setelah login, bukan ke dashboard admin.
        // Untuk login admin, selalu paksa ke admin.dashboard.
        $request->session()->forget('url.intended');

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }
}