<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminLoginController extends Controller
{
    public function create()
    {
        // Cek login menggunakan guard admin
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        // Sisa detik blokir (0 = tidak diblokir). Dipakai view untuk timer hitung mundur,
        // tetap akurat walau halaman di-refresh.
        $sisaDetik = 0;

        if ($key = session('login_throttle_key')) {
            $sisaDetik = $this->sisaDetikBlokir($key);
        }

        return view('admin.auth.login', compact('sisaDetik'));
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $key = $this->throttleKey($request);
        session(['login_throttle_key' => $key]);

        // Cek dulu apakah sedang diblokir (sebelum cek password)
        if ($detik = $this->sisaDetikBlokir($key)) {
            return $this->tolak($request, $this->pesanBlokir($detik), 429);
        }

        $field = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'no_hp';

        // GUNAKAN GUARD 'admin' UNTUK LOGIN
        if (! Auth::guard('admin')->attempt([
            $field => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $attempts = $this->catatGagal($key);

            // Kalau percobaan ini memicu blokir, langsung kasih tahu
            if ($detik = $this->sisaDetikBlokir($key)) {
                return $this->tolak($request, $this->pesanBlokir($detik), 429);
            }

            return $this->tolak(
                $request,
                'Email/HP atau kata sandi yang kamu masukkan salah. ' . $this->infoSisaPercobaan($attempts),
                401
            );
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

        // Login sukses -> reset semua hitungan
        Cache::forget("{$key}:attempts");
        Cache::forget("{$key}:locked_until");
        $request->session()->forget('login_throttle_key');

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

    // Alias lama: diarahkan ke method utama agar rate limiter tidak terlewati
    public function showLoginForm()
    {
        return $this->create();
    }

    public function login(Request $request)
    {
        return $this->store($request);
    }

    // ---------------------------------------------------------------
    // Rate limiter bertingkat
    // 3 gagal -> kunci 5 menit | 6 gagal -> 15 menit | 9 gagal -> sampai besok
    // ---------------------------------------------------------------

    private function throttleKey(Request $request): string
    {
        return 'admin-login:' . sha1(Str::lower($request->input('email')) . '|' . $request->ip());
    }

    private function catatGagal(string $key): int
    {
        $attempts = Cache::get("{$key}:attempts", 0) + 1;

        $kunciSampai = match ($attempts) {
            3 => now()->addMinutes(5),
            6 => now()->addMinutes(15),
            9 => now()->addDay()->startOfDay(),
            default => null,
        };

        $ttlAttempts = $attempts >= 9 ? $kunciSampai : now()->addDay();
        Cache::put("{$key}:attempts", $attempts, $ttlAttempts);

        if ($kunciSampai) {
            Cache::put("{$key}:locked_until", $kunciSampai->timestamp, $kunciSampai);
        }

        return $attempts;
    }

    private function sisaDetikBlokir(string $key): int
    {
        $until = Cache::get("{$key}:locked_until");

        return $until ? max(0, $until - now()->timestamp) : 0;
    }

    private function pesanBlokir(int $detik): string
    {
        if ($detik >= 3600) {
            $jam = intdiv($detik, 3600);
            $menit = intdiv($detik % 3600, 60);
            $waktu = "{$jam} jam {$menit} menit";
        } elseif ($detik >= 60) {
            $waktu = ceil($detik / 60) . ' menit';
        } else {
            $waktu = "{$detik} detik";
        }

        return "Terlalu banyak percobaan gagal. Coba lagi dalam {$waktu}.";
    }

    private function infoSisaPercobaan(int $attempts): string
    {
        [$batas, $durasi] = match (true) {
            $attempts < 3 => [3, 'selama 5 menit'],
            $attempts < 6 => [6, 'selama 15 menit'],
            default => [9, 'sampai besok'],
        };

        $sisa = $batas - $attempts;

        return "Sisa {$sisa} percobaan sebelum akun dikunci {$durasi}.";
    }

    private function tolak(Request $request, string $pesan, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $pesan], $status);
        }

        return back()->withErrors(['email' => $pesan])->onlyInput('email');
    }
}