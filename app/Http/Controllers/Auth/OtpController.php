<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    /**
     * Kirim OTP untuk login.
     */
    public function sendLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Email belum terdaftar. Silakan daftar dulu.',
        ]);

        $this->kirimOtp($request->email, 'login');

        return redirect()->route('masuk.otp.form', ['email' => $request->email])
                         ->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    /**
     * Verifikasi OTP untuk login.
     */
    public function verifyLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $otp = OtpCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('purpose', 'login')
            ->latest()
            ->first();

        if (! $otp || ! $otp->isValid()) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        $otp->update(['used_at' => now()]);

        $user = User::where('email', $request->email)->firstOrFail();
        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    /**
     * Kirim OTP untuk daftar.
     */
    public function sendDaftar(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
        ], [
            'email.unique' => 'Email sudah terdaftar. Silakan masuk.',
        ]);

        // Simpan sementara di session
        $request->session()->put('daftar_name', $request->name);
        $request->session()->put('daftar_email', $request->email);

        $this->kirimOtp($request->email, 'daftar');

        return redirect()->route('daftar.otp.form')
                         ->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    /**
     * Verifikasi OTP untuk daftar (sekaligus buat akun).
     */
    public function verifyDaftar(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $email = $request->session()->get('daftar_email');
        $name = $request->session()->get('daftar_name');

        if (! $email || ! $name) {
            return redirect()->route('daftar')
                ->withErrors(['code' => 'Sesi habis. Silakan daftar ulang.']);
        }

        $otp = OtpCode::where('email', $email)
            ->where('code', $request->code)
            ->where('purpose', 'daftar')
            ->latest()
            ->first();

        if (! $otp || ! $otp->isValid()) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        $otp->update(['used_at' => now()]);

        // Buat user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(str()->random(16)), // password sementara, user login via OTP
            'role' => 'pelanggan',
            'status_aktif' => true,
        ]);

        $request->session()->forget(['daftar_name', 'daftar_email']);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('beranda')
                         ->with('success', 'Akun berhasil dibuat. Selamat datang!');
    }

    /**
     * Helper — kirim OTP.
     */
    private function kirimOtp(string $email, string $purpose): void
    {
        // Hapus OTP lama untuk email ini & purpose ini
        OtpCode::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'email' => $email,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($email)->send(new OtpMail($code, $purpose));
    }

    private function redirectByRole($user)
    {
        return match ($user->role) {
            'owner' => redirect()->route('owner.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('beranda'),
        };
    }
}
