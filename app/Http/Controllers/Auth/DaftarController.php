<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class DaftarController extends Controller
{
    public function create()
    {
        return view('auth.daftar');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_hp.required' => 'No. HP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'password' => Hash::make($validated['password']),
            'role' => 'pelanggan',
            'status_aktif' => true,
        ]);

        // Jika user-nya admin/owner, login ke guard admin
        if (in_array($user->role, ['admin', 'owner'])) {
            Auth::guard('admin')->login($user);
        } else {
            Auth::guard('web')->login($user);
        }

        return redirect()->route('beranda')->with('success', 'Selamat datang di ALNAY.ID!');
    }
}