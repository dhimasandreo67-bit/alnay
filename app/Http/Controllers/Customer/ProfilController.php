<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function edit()
    {
        // Diarahkan ke dashboard tab profil
        return redirect(route('akun.dashboard') . '#profil');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:1024'],
        ]);

        if ($request->hasFile('foto')) {
            try {
                $fotoBaru = ImageOptimizer::simpanSebagaiWebp($request->file('foto'), 'foto-profil', 80, 400);
            } catch (\RuntimeException $e) {
                return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
            }

            // Hapus foto lama hanya setelah foto baru berhasil dibuat
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $data['foto_profil'] = $fotoBaru;
        }

        unset($data['foto']);
        $user->update($data);

        return redirect(route('akun.dashboard') . '#profil')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}