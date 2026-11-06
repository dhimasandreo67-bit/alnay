<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Akun Dinonaktifkan - {{ config('alnay.brand.nama', 'ALNAY.ID') }}</title>

    {{-- Halaman mandiri: sengaja TANPA header, footer, dan menu apa pun --}}
    @vite(['resources/css/app.css'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background: #F7F3EA; }
        .font-display { font-family: 'Playfair Display', serif; }
        .an-rise { animation: anRise .55s cubic-bezier(.22, 1, .36, 1) both; }
        @keyframes anRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { .an-rise { animation: none; } }
    </style>
</head>
<body class="min-h-screen antialiased">

@php
    $brand  = config('alnay.brand.nama', 'ALNAY.ID');
    $kontak = config('akun.kontak_admin', []);

    $nomorWa = preg_replace('/\D+/', '', (string) ($kontak['whatsapp'] ?? ''));
    if (str_starts_with($nomorWa, '0')) {
        $nomorWa = '62' . substr($nomorWa, 1);
    }
    $emailAdmin = trim((string) ($kontak['email'] ?? ''));

    $pesan = "Halo Admin {$brand}, akun saya dengan email {$info['email']} sedang dinonaktifkan. Mohon bantuannya untuk mengaktifkan kembali akun saya. Terima kasih.";
    $urlWa    = $nomorWa ? 'https://wa.me/' . $nomorWa . '?text=' . rawurlencode($pesan) : null;
    $urlEmail = $emailAdmin ? 'mailto:' . $emailAdmin . '?subject=' . rawurlencode('Permohonan Aktivasi Akun') . '&body=' . rawurlencode($pesan) : null;

    $urlMasuk = \Illuminate\Support\Facades\Route::has('masuk') ? route('masuk') : url('/');
    $sesiDiakhiri = ! empty($info['sesi_diakhiri']);
@endphp

<main class="mx-auto flex min-h-screen max-w-xl flex-col justify-center px-4 py-10">

    <p class="font-display mb-5 text-center text-lg font-bold tracking-wide" style="color: #15233d;">{{ $brand }}</p>

    <div class="an-rise overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

        <div class="border-b border-gray-100 px-6 pb-6 pt-8 text-center" style="background: #FBF9F5;">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4"></path>
                </svg>
            </div>
            <h1 class="font-display text-2xl font-bold md:text-3xl" style="color: #15233d;">Akun Anda Sedang Dinonaktifkan</h1>
            <p class="mt-2 text-sm text-gray-500">
                Halo, {{ $info['name'] }}.
                {{ $sesiDiakhiri ? 'Anda otomatis keluar karena akun ini dinonaktifkan.' : 'Anda belum bisa masuk dengan akun ini.' }}
            </p>
        </div>

        <div class="space-y-6 p-6 md:p-8">

            {{-- Keterangan alasan --}}
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-relaxed text-amber-900">
                @if ($info['karena_tidak_aktif'])
                    Akun <span class="font-semibold">{{ $info['email'] }}</span> dinonaktifkan karena tidak ada aktivitas selama
                    <span class="font-semibold">{{ number_format($info['hari_tidak_aktif'], 0, ',', '.') }} hari</span>{{ $info['tahun_tidak_aktif'] >= 1 ? ' (lebih dari ' . $info['tahun_tidak_aktif'] . ' tahun)' : '' }}.
                    @if ($info['terakhir_aktif'])
                        <span class="mt-1 block text-xs text-amber-800/80">Aktivitas terakhir: {{ $info['terakhir_aktif'] }}</span>
                    @endif
                @else
                    Akun <span class="font-semibold">{{ $info['email'] }}</span> sedang dinonaktifkan oleh admin.
                    @if ($info['dinonaktifkan_pada'])
                        <span class="mt-1 block text-xs text-amber-800/80">Dinonaktifkan pada: {{ $info['dinonaktifkan_pada'] }}</span>
                    @endif
                @endif
            </div>

            {{-- Kontak admin --}}
            <div>
                <h2 class="text-sm font-bold text-gray-800">Ingin mengaktifkan akun kembali?</h2>
                <p class="mt-1 text-xs leading-relaxed text-gray-500">
                    Hubungi admin {{ $brand }} dan sebutkan email akun Anda. Akun akan diaktifkan kembali setelah dikonfirmasi.
                </p>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                    @if ($urlWa)
                        <a href="{{ $urlWa }}" target="_blank" rel="noopener"
                           class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-lg">
                            Hubungi via WhatsApp
                        </a>
                    @endif
                    @if ($urlEmail)
                        <a href="{{ $urlEmail }}"
                           class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-[#c99a32] bg-white px-5 py-3 text-sm font-bold text-[#c99a32] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#c99a32] hover:text-white hover:shadow-lg">
                            Kirim Email
                        </a>
                    @endif
                </div>

                <dl class="mt-4 space-y-1.5 text-xs text-gray-600">
                    @if ($nomorWa)
                        <div class="flex gap-2"><dt class="w-20 shrink-0 font-semibold text-gray-500">WhatsApp</dt><dd>+{{ $nomorWa }}</dd></div>
                    @endif
                    @if ($emailAdmin)
                        <div class="flex gap-2"><dt class="w-20 shrink-0 font-semibold text-gray-500">Email</dt><dd>{{ $emailAdmin }}</dd></div>
                    @endif
                    @if (! empty($kontak['alamat']))
                        <div class="flex gap-2"><dt class="w-20 shrink-0 font-semibold text-gray-500">Toko</dt><dd>{{ $kontak['alamat'] }}</dd></div>
                    @endif
                    @if (! empty($kontak['jam']))
                        <div class="flex gap-2"><dt class="w-20 shrink-0 font-semibold text-gray-500">Layanan</dt><dd>{{ $kontak['jam'] }}</dd></div>
                    @endif
                </dl>

                @if (! $urlWa && ! $urlEmail)
                    <p class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">
                        Silakan datang langsung ke toko kami untuk meminta aktivasi akun.
                    </p>
                @endif
            </div>

            <div class="border-t border-gray-100 pt-4">
                <a href="{{ $urlMasuk }}" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 px-5 py-2.5 text-xs font-bold text-gray-700 transition-colors hover:border-[#c99a32] hover:text-[#c99a32]">
                    Masuk dengan akun lain
                </a>
            </div>
        </div>
    </div>
</main>

</body>
</html>