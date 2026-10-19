<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP — {{ config('alnay.brand.nama') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body { height: 100%; margin: 0; overflow: hidden; }
        @keyframes slideInLeft { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes floatPattern { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-12px) rotate(2deg); } }
        .anim-left { animation: slideInLeft 0.9s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .anim-right { animation: slideInRight 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both; }
        .anim-float { animation: floatPattern 8s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { .anim-left, .anim-right, .anim-float { animation: none !important; } }
    </style>
</head>
<body class="h-screen bg-cream text-ink font-sans antialiased">

    <div class="h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-[960px] bg-panel rounded-xl shadow-xl shadow-ink/10 overflow-hidden grid grid-cols-1 md:grid-cols-2 border border-line" style="max-height: 92vh;">

            {{-- PANEL KIRI — BIRU GELAP --}}
            <div class="relative bg-[#0A1526] p-8 md:p-10 flex flex-col justify-between overflow-hidden anim-left hidden md:flex">
                <div class="absolute -top-20 -right-20 w-56 h-56 rounded-full border border-gold/20 anim-float"></div>
                <div class="absolute -top-10 -right-10 w-36 h-36 rounded-full border border-gold/30 anim-float" style="animation-delay: -2s;"></div>

                <div class="absolute inset-y-0 left-0 w-3/4 flex items-center justify-start pointer-events-none">
                    <div class="h-full" style="transform: translateX(-30%);">
                        <img src="{{ asset('images/alnaylogo.png') }}" alt=""
                             class="h-[80%] md:h-[85%] w-auto max-w-none object-contain anim-float"
                             style="opacity: 0.10; filter: blur(4px) saturate(1.1);">
                    </div>
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/alnaylogo.png') }}" alt="ALNAY.ID" class="w-10 h-10 object-contain">
                        <div>
                            <p class="font-display font-bold text-base tracking-[0.1em] text-gold">ALNAY.ID</p>
                            <p class="text-[10px] text-cream/40 tracking-wide">Jas & Gown Berbudaya</p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="font-display font-bold text-3xl leading-tight text-cream mb-3">
                        Satu langkah<br>lagi untuk<br><span class="text-gold">masuk.</span>
                    </h2>
                    <p class="text-xs leading-relaxed text-cream/40 max-w-xs">
                        Kami mengirim kode 6 digit ke email Anda. Masukkan untuk melanjutkan.
                    </p>
                </div>
            </div>

            {{-- PANEL KANAN — FORM OTP --}}
            <div class="p-6 md:p-8 flex flex-col justify-center bg-panel overflow-y-auto anim-right">
                <div class="w-full max-w-[320px] mx-auto">

                    <div class="md:hidden flex items-center gap-2 mb-5">
                        <img src="{{ asset('images/alnaylogo.png') }}" alt="ALNAY.ID" class="w-8 h-8 object-contain">
                        <p class="font-display font-bold text-sm tracking-[0.1em] text-gold">ALNAY.ID</p>
                    </div>

                    <div class="mb-6">
                        <p class="text-[9px] uppercase tracking-[0.25em] text-gold mb-2">Verifikasi</p>
                        <h1 class="font-display text-2xl md:text-3xl text-ink leading-tight mb-1">Kode OTP</h1>
                        <p class="text-xs text-muted">
                            Masukkan kode 6 digit yang dikirim ke
                            <strong class="text-ink">{{ $email }}</strong>
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 p-2.5 rounded-md bg-red-50 border border-red-200 text-[11px] text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-4 p-2.5 rounded-md bg-green-50 border border-green-200 text-[11px] text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('masuk.otp.verify') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div>
                            <label for="code" class="block text-[10px] font-semibold uppercase tracking-wider text-ink mb-1.5 text-center">Kode OTP</label>
                            <input type="text" name="code" id="code" required autofocus maxlength="6" inputmode="numeric"
                                   placeholder="000000" pattern="[0-9]{6}"
                                   class="input-elegant w-full px-3.5 py-3 rounded-md border border-line bg-cream text-center text-xl tracking-[0.5em] font-mono text-ink placeholder:text-muted/40 focus:outline-none">
                        </div>

                        <button type="submit"
                                class="btn-gold w-full h-10 rounded-md bg-gold text-white text-xs font-bold uppercase tracking-wider">
                            Verifikasi
                        </button>
                    </form>

                    <div class="mt-4 flex items-center justify-between text-[11px] text-muted">
                        <a href="{{ route('masuk') }}" class="hover:text-gold transition-colors">
                            Kembali
                        </a>
                        <span>Kode berlaku 5 menit</span>
                    </div>

                    <p class="text-center text-[9px] uppercase tracking-[0.2em] text-muted/50 mt-6">
                        &copy; {{ date('Y') }} <span class="font-semibold text-gold">ALNAY.ID</span>
                    </p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>