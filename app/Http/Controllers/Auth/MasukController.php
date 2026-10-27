<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MasukController extends Controller
{
    public function create()
    {
        return view('auth.masuk');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        // 1. Coba login sebagai Admin/Owner dulu
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $role = Auth::guard('admin')->user()->role;
            
            if ($role === 'owner') {
                return redirect()->intended(route('owner.dashboard'));
            }
            return redirect()->intended(route('admin.dashboard'));
        }

        // 2. Jika gagal, coba login sebagai User biasa
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('beranda'));
        }

        // 3. Jika keduanya gagal
        throw ValidationException::withMessages([
            'email' => 'Email atau kata sandi salah.',
        ]);
    }

    public function destroy(Request $request)
    {
        // Logout dari kedua guard
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }
}