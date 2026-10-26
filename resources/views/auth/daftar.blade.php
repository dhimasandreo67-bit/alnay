<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — {{ config('alnay.brand.nama') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes floatPattern {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(2deg); }
        }

        .anim-left { animation: slideInLeft 0.9s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .anim-right { animation: slideInRight 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both; }
        .anim-float { animation: floatPattern 8s ease-in-out infinite; }

        @media (prefers-reduced-motion: reduce) {
            .anim-left, .anim-right, .anim-float { animation: none !important; }
        }
    </style>
</head>
<body class="h-screen bg-cream text-ink font-sans antialiased">

    <div class="h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-[960px] bg-panel rounded-xl shadow-xl shadow-ink/10 overflow-hidden grid grid-cols-1 md:grid-cols-2 border border-line" style="max-height: 92vh;">

            {{-- PANEL KIRI — BIRU GELAP --}}
            <div class="relative bg-[#0A1526] p-8 md:p-10 flex flex-col justify-between overflow-hidden anim-left hidden md:flex">

                <div class="absolute -top-20 -right-20 w-56 h-56 rounded-full border border-gold/20 anim-float"></div>
                <div class="absolute -top-10 -right-10 w-36 h-36 rounded-full border border-gold/30 anim-float" style="animation-delay: -2s;"></div>

                {{-- Logo background blur --}}
                <div class="absolute inset-y-0 left-0 w-3/4 flex items-center justify-start pointer-events-none">
                    <div class="h-full" style="transform: translateX(-30%);">
                        <img src="{{ asset('images/alnaylogo.png') }}" alt=""
                             class="h-[80%] md:h-[85%] w-auto max-w-none object-contain anim-float"
                             style="opacity: 0.10; filter: blur(4px) saturate(1.1);">
                    </div>
                </div>

                {{-- Logo atas --}}
                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/alnaylogo.png') }}" alt="ALNAY.ID" class="w-10 h-10 object-contain">
                        <div>
                            <p class="font-display font-bold text-base tracking-[0.1em] text-gold">ALNAY.ID</p>
                            <p class="text-[10px] text-cream/40 tracking-wide">Jas & Gown Berbudaya</p>
                        </div>
                    </div>
                </div>

                {{-- Teks bawah --}}
                <div class="relative z-10">
                    <h2 class="font-display font-bold text-3xl leading-tight text-cream mb-3">
                        Jadi bagian<br>dari<br><span class="text-gold">mahakarya.</span>
                    </h2>
                    <p class="text-xs leading-relaxed text-cream/40 max-w-xs">
                        Daftar untuk mulai menyewa, membeli, dan mengikuti event busana adat.
                    </p>
                </div>
            </div>

            {{-- PANEL KANAN — FORM COMPACT --}}
            <div class="p-6 md:p-8 flex flex-col justify-center bg-panel overflow-y-auto anim-right">
                <div class="w-full max-w-[320px] mx-auto">

                    {{-- Header mobile --}}
                    <div class="md:hidden flex items-center gap-2 mb-5">
                        <img src="{{ asset('images/alnaylogo.png') }}" alt="ALNAY.ID" class="w-8 h-8 object-contain">
                        <p class="font-display font-bold text-sm tracking-[0.1em] text-gold">ALNAY.ID</p>
                    </div>

                    <div class="mb-4">
                        <p class="text-[9px] uppercase tracking-[0.25em] text-gold mb-1.5">Akun Baru</p>
                        <h1 class="font-display text-2xl md:text-3xl text-ink leading-tight mb-1">Daftar</h1>
                        <p class="text-xs text-muted">Buat akun untuk mulai bertransaksi.</p>
                    </div>

                    {{-- Error --}}
                    @if ($errors->any())
                        <div class="mb-4 p-2.5 rounded-md bg-red-50 border border-red-200 text-[11px] text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{-- Form --}}
                    <form method="POST" action="{{ route('daftar.store') }}" class="space-y-2.5">
                    @csrf

                    <div>
                        <label for="name" class="block text-[10px] font-semibold uppercase tracking-wider text-ink mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                            placeholder="Nama lengkap"
                            class="input-elegant w-full px-3.5 py-2.5 rounded-md border border-line bg-cream text-sm text-ink placeholder:text-muted/50 focus:outline-none">
                    </div>

                    <div>
                        <label for="email" class="block text-[10px] font-semibold uppercase tracking-wider text-ink mb-1.5">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            placeholder="nama@email.com"
                            class="input-elegant w-full px-3.5 py-2.5 rounded-md border border-line bg-cream text-sm text-ink placeholder:text-muted/50 focus:outline-none">
                    </div>

                    <label class="flex items-start gap-2 cursor-pointer group pt-1">
                        <input type="checkbox" name="terms" value="1" required
                            class="w-3.5 h-3.5 mt-0.5 rounded border-line text-gold focus:ring-gold focus:ring-offset-0 cursor-pointer">
                        <span class="text-[11px] text-ink leading-relaxed group-hover:text-gold transition-colors">
                            Saya menyetujui <a href="#" class="text-gold font-semibold hover:underline">Syarat & Ketentuan</a>
                        </span>
                    </label>

                    <div class="rounded-md bg-[#faf8f2] border border-[#eee5d2] p-3">
                        <p class="text-[11px] text-[#7F766C] leading-relaxed">
                            Kami akan mengirim <strong class="text-gold">kode OTP 6 digit</strong> ke email Anda untuk verifikasi.
                        </p>
                    </div>

                    <button type="submit"
                            class="btn-gold w-full h-10 rounded-md bg-gold text-white text-xs font-bold uppercase tracking-wider mt-2">
                        Kirim Kode OTP
                    </button>
                    <div class="flex items-center gap-2.5 my-3">
    <div class="flex-1 h-px bg-line"></div>
        <span class="text-[9px] uppercase tracking-wider text-muted">atau</span>
        <div class="flex-1 h-px bg-line"></div>
    </div>

    <a href="{{ route('auth.google') }}"
    class="w-full h-10 rounded-md border border-line bg-panel flex items-center justify-center gap-2 text-xs font-semibold text-ink hover:bg-cream hover:border-gold transition-colors">
        <svg class="w-4 h-4" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58v3h3.88c2.26-2.09 3.54-5.17 3.54-8.82z"/>
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.94-2.91l-3.88-3c-1.08.72-2.45 1.16-4.06 1.16-3.13 0-5.78-2.11-6.73-4.96H1.27v3.09C3.25 21.3 7.31 24 12 24z"/>
            <path fill="#FBBC05" d="M5.27 14.29c-.25-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29V6.62H1.27A11.98 11.98 0 0 0 0 12c0 1.93.46 3.76 1.27 5.38l4-3.09z"/>
            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.31 0 3.25 2.7 1.27 6.62l4 3.09C6.22 6.86 8.87 4.75 12 4.75z"/>
        </svg>
        Daftar dengan Google
    </a>
                </form>

                    <p class="text-center text-xs text-muted mt-3">
                        Sudah punya akun?
                        <a href="{{ route('masuk') }}" class="text-ink font-bold hover:text-gold transition-colors">Masuk</a>
                    </p>

                    <p class="text-center text-[9px] uppercase tracking-[0.2em] text-muted/50 mt-3">
                        &copy; {{ date('Y') }} <span class="font-semibold text-gold">ALNAY.ID</span>
                    </p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>