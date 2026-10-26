<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP — {{ config('alnay.brand.nama') }}</title>
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

        /* Menghilangkan spinner panah pada input number */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }

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
                        Verifikasi<br>untuk jadi<br><span class="text-gold">bagian kami.</span>
                    </h2>
                    <p class="text-xs leading-relaxed text-cream/40 max-w-xs">
                        Masukkan kode 6 digit yang dikirim ke email Anda untuk menyelesaikan pendaftaran.
                    </p>
                </div>
            </div>

            {{-- PANEL KANAN — FORM OTP --}}
            <div class="p-6 md:p-8 flex flex-col justify-center bg-panel overflow-y-auto anim-right">
                <div class="w-full max-w-[360px] mx-auto">

                    {{-- Header mobile --}}
                    <div class="md:hidden flex items-center gap-2 mb-5">
                        <img src="{{ asset('images/alnaylogo.png') }}" alt="ALNAY.ID" class="w-8 h-8 object-contain">
                        <p class="font-display font-bold text-sm tracking-[0.1em] text-gold">ALNAY.ID</p>
                    </div>

                    <div class="mb-6 text-center md:text-left">
                        <p class="text-[9px] uppercase tracking-[0.25em] text-gold mb-2">Verifikasi</p>
                        <h1 class="font-display text-2xl md:text-3xl text-ink leading-tight mb-1">Kode OTP</h1>
                        <p class="text-xs text-muted">Masukkan kode 6 digit yang dikirim ke email Anda.</p>
                    </div>

                    {{-- Alert Sukses (Gaya Elegan, bukan Hijau Terang) --}}
                    @if(session('success'))
                        <div class="mb-6 p-3 rounded-md bg-[#faf8f2] border border-[#eee5d2] flex items-start gap-2.5 pd-swap">
                            <svg class="w-4 h-4 text-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <p class="text-[11px] text-[#7F766C] leading-relaxed">{{ session('success') }}</p>
                        </div>
                    @endif

                    {{-- Alert Error --}}
                    @if ($errors->any())
                        <div class="mb-4 p-2.5 rounded-md bg-red-50 border border-red-200 text-[11px] text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{-- Form OTP --}}
                    <form method="POST" action="{{ route('masuk.attempt') }}">
                        @csrf
                        
                        {{-- 6 Kotak Input --}}
                        <div class="mb-6">
                            <div class="flex justify-between gap-1.5 sm:gap-2" id="otp-container">
                                @for ($i = 0; $i < 6; $i++)
                                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" 
                                           class="input-elegant w-full h-12 sm:h-14 text-center text-xl font-bold text-ink bg-cream border border-line rounded-md focus:outline-none" 
                                           autocomplete="one-time-code" {{ $i == 0 ? 'autofocus' : '' }}>
                                @endfor
                            </div>
                            <input type="hidden" name="otp" id="otp-hidden">
                        </div>

                        <button type="submit"
                                class="btn-gold pd-shine w-full h-11 rounded-md bg-gold text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2">
                            Verifikasi & Daftar
                        </button>
                    </form>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between mt-6 text-xs">
                        <a href="{{ route('daftar') }}" class="text-muted hover:text-gold transition-colors flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Kembali
                        </a>
                        <span class="text-muted text-[11px]">Berlaku <strong class="text-ink">5 menit</strong></span>
                    </div>

                    <p class="text-center text-[9px] uppercase tracking-[0.2em] text-muted/50 mt-8">
                        &copy; {{ date('Y') }} <span class="font-semibold text-gold">ALNAY.ID</span>
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPT AUTO-FOCUS OTP --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('#otp-container input');
            const hiddenInput = document.getElementById('otp-hidden');
            const form = document.getElementById('otp-form');

            inputs.forEach((input, index) => {
                // Pindah fokus ke kanan saat mengetik
                input.addEventListener('input', (e) => {
                    if (e.target.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    
                    // Gabungkan semua angka ke input hidden
                    let otpValue = '';
                    inputs.forEach(inp => otpValue += inp.value);
                    hiddenInput.value = otpValue;

                    // Opsional: Auto submit jika 6 digit sudah penuh
                    // if (otpValue.length === 6) form.submit();
                });

                // Pindah fokus ke kiri saat Backspace
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });

                // Handle Paste (Copy-Paste kode OTP)
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasteData = e.clipboardData.getData('text').slice(0, 6).replace(/\D/g, '');
                    if (pasteData) {
                        pasteData.split('').forEach((char, i) => {
                            if (inputs[i]) {
                                inputs[i].value = char;
                                inputs[i].dispatchEvent(new Event('input'));
                            }
                        });
                    }
                });
            });
        });
    </script>

</body>
</html>