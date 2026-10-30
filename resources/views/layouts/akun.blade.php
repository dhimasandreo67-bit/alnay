<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Akun') — {{ config('alnay.brand.nama') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-ink font-sans antialiased min-h-screen">

        {{-- Header Minimalis --}}
    <header class="bg-panel border-b border-line sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            {{-- Tombol Kembali --}}
            <a href="{{ route('beranda') }}" 
                class="inline-flex items-center gap-2 text-sm font-medium text-ink hover:text-gold transition-colors group min-h-[44px] shrink-0">
                <div class="w-8 h-8 rounded-full bg-cream flex items-center justify-center group-hover:bg-gold/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </div>
                <span class="hidden sm:inline">Kembali</span>
            </a>

            {{-- Logo Tengah --}}
            <a href="{{ route('beranda') }}" class="absolute left-1/2 -translate-x-1/2 flex items-center gap-2">
                <span class="font-display font-bold text-base sm:text-lg tracking-[0.1em] text-gold">ALNAY.ID</span>
            </a>

            {{-- Spacer Kanan --}}
            <div class="w-8 shrink-0"></div> 
        </div>
    </header>

    {{-- Konten --}}
    <main>
        @yield('content')
    </main>
    <x-toast />
    @stack('modals')

</body>
</html>