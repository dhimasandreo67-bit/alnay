{{--
    Admin login page.
    Route suggestion: GET /admin/login -> name('admin.login')
    No <x-layouts.admin> wrapper here — this is its own full-bleed screen.
--}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — ALNAY.ID</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ---------- Keyframes ---------- */
        @keyframes rise {
            from { opacity: 0; transform: translateY(26px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes floatOrb {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            33% { transform: translate3d(34px, -46px, 0) scale(1.09); }
            66% { transform: translate3d(-24px, 28px, 0) scale(0.94); }
        }

        @keyframes spinSlow {
            to { transform: rotate(360deg); }
        }

        @keyframes sheen {
            0%, 55% { transform: translateX(-160%) skewX(-18deg); }
            100% { transform: translateX(430%) skewX(-18deg); }
        }

        @keyframes shake {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-6px); }
            40%, 60% { transform: translateX(6px); }
        }

        /* ---------- Utilities ---------- */
        .anim-rise { animation: rise .85s cubic-bezier(.22, 1, .36, 1) both; }
        .anim-fade { animation: fadeIn 1s ease both; }
        .anim-float { animation: floatOrb 18s ease-in-out infinite; }
        .anim-float-slow { animation: floatOrb 28s ease-in-out infinite reverse; }
        .anim-spin-slow { animation: spinSlow 70s linear infinite; }
        .anim-spin-rev { animation: spinSlow 95s linear infinite reverse; }
        .anim-shake { animation: shake .55s cubic-bezier(.36, .07, .19, .97) both; }

        .d-1 { animation-delay: .08s; }
        .d-2 { animation-delay: .16s; }
        .d-3 { animation-delay: .24s; }
        .d-4 { animation-delay: .32s; }
        .d-5 { animation-delay: .42s; }
        .d-6 { animation-delay: .52s; }

        .btn-sheen {
            position: absolute;
            inset-block: 0;
            left: 0;
            width: 38%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .7), transparent);
            transform: translateX(-160%) skewX(-18deg);
            animation: sheen 4.5s ease-in-out infinite;
            pointer-events: none;
        }

        /* ---------- Accessibility ---------- */
        @media (prefers-reduced-motion: reduce) {
            .anim-rise, .anim-fade, .anim-float, .anim-float-slow,
            .anim-spin-slow, .anim-spin-rev, .anim-shake, .btn-sheen,
            .mascot-float, .mascot-tracker, .pupil-group {
                animation: none !important;
                transition: none !important;
            }
            .anim-rise, .anim-fade { opacity: 1 !important; transform: none !important; }
            .btn-sheen { display: none; }
        }

        /* ---------- Mascot Blink ---------- */
        .pupil-group {
            transform-box: fill-box;
            transform-origin: center;
            animation: mascotBlink 5.2s cubic-bezier(.3, .7, .4, 1) infinite;
            will-change: transform;
        }

        @keyframes mascotBlink {
            0%, 92%, 100% { transform: scaleY(1); }
            94%, 96% { transform: scaleY(0.1); }
        }

        .eyes-closed .pupil-group {
            animation: none;
            transform: scaleY(0.1);
        }

        /* ---------- Mascot Advanced Animation ---------- */
        .mascot-wrapper {
            position: absolute;
            bottom: 5rem;
            right: 1.5rem;
            z-index: 10;
            cursor: grab;
            user-select: none;
            -webkit-user-select: none;
            touch-action: none; /* Mencegah scroll saat di-drag di mobile */
        }

        .mascot-wrapper:active {
            cursor: grabbing;
        }

        /* Pergerakan halus mengikuti kursor via JS */
        .mascot-tracker {
            transition: transform 0.15s ease-out;
            will-change: transform;
        }

        /* Animasi bernapas / jelly */
        .mascot-float {
            animation: mascotBreathe 5s ease-in-out infinite;
            will-change: transform;
            /* Transisi memantul saat dilepas */
            transition: transform 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Matikan napas saat sedang ditarik */
        .is-dragging .mascot-float {
            animation: none !important;
        }

        /* Transisi memantul untuk bentuk SVG saat dilepas */
        #mascotSvg {
            transform-origin: center center;
            transition: transform 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes mascotBreathe {
            0%, 100% { transform: translateY(0) scale(1, 1) rotate(-1deg); }
            30% { transform: translateY(-6px) scale(1.02, 0.98) rotate(2deg); }
            60% { transform: translateY(-14px) scale(0.97, 1.03) rotate(3deg); }
            85% { transform: translateY(-4px) scale(1.01, 0.99) rotate(0deg); }
        }

        /* Animasi saat diklik (Squish) */
        .is-squished {
            animation: mascotSquish 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) both !important;
        }

        @keyframes mascotSquish {
            0% { transform: scale(1, 1); }
            30% { transform: scale(1.15, 0.8) translateY(15px); }
            50% { transform: scale(0.9, 1.1) translateY(-15px); }
            75% { transform: scale(1.05, 0.95) translateY(5px); }
            100% { transform: scale(1, 1) translateY(0); }
        }
    </style>
</head>

<body class="min-h-screen overflow-x-hidden bg-cream font-sans text-ink antialiased selection:bg-gold selection:text-ink">

    <div class="flex min-h-screen">

        {{-- ================= LEFT BRAND PANEL ================= --}}
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-ink px-12 py-12 text-cream lg:flex xl:w-[52%] xl:px-16 xl:py-14">

            {{-- Layered background --}}
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-ink via-ink to-black/90" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -left-32 top-1/4 h-[26rem] w-[26rem] rounded-full bg-gold/20 blur-3xl anim-float" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-amber-500/15 blur-3xl anim-float-slow" aria-hidden="true"></div>

            {{-- Decorative kawung-inspired motif, original geometric pattern, low opacity --}}
            <svg class="pointer-events-none absolute -right-40 -top-40 h-[42rem] w-[42rem] text-gold/10 anim-spin-slow" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                <g stroke="currentColor" stroke-width="1.5">
                    @for ($i = 0; $i < 5; $i++)
                        <circle cx="100" cy="100" r="{{ 20 + $i * 20 }}" />
                    @endfor
                    @for ($a = 0; $a < 360; $a += 45)
                        <line x1="100" y1="100" x2="{{ 100 + 100 * cos(deg2rad($a)) }}" y2="{{ 100 + 100 * sin(deg2rad($a)) }}" />
                    @endfor
                </g>
            </svg>

            <svg class="pointer-events-none absolute -bottom-32 -left-24 h-80 w-80 text-gold/[0.08] anim-spin-rev" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                <g stroke="currentColor" stroke-width="2" stroke-dasharray="4 8">
                    <circle cx="100" cy="100" r="88" />
                    <circle cx="100" cy="100" r="60" />
                </g>
            </svg>

            {{-- Brand --}}
            <div class="anim-rise relative z-10 flex items-center gap-3.5">
                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-gold to-gold-dark shadow-lg shadow-gold/25 ring-1 ring-white/10">
                    <span class="font-display text-lg font-bold text-ink">A</span>
                </div>
                <div>
                    <p class="font-display text-xl font-semibold leading-none tracking-tight text-gold">ALNAY.ID</p>
                    <p class="mt-1.5 text-[10px] uppercase tracking-[0.24em] text-cream/40">Jas &amp; gaun berbudaya</p>
                </div>
            </div>

            {{-- Hero copy --}}
            <div class="relative z-10 max-w-md">
                <span class="anim-rise d-1 inline-flex items-center gap-2 rounded-full border border-gold/25 bg-gold/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-gold">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-gold opacity-75"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-gold"></span>
                    </span>
                    Admin Panel
                </span>

                <h1 class="anim-rise d-2 mt-6 font-display text-4xl font-semibold leading-[1.12] tracking-tight text-cream xl:text-[2.65rem]">
                    Selamat datang kembali di
                    <span class="bg-gradient-to-r text-bold from-gold via-amber-200 to-gold bg-clip-text text-transparent">
                        Panel Admin, Min!
                    </span>
                </h1>

                <p class="anim-rise d-3 mt-4 max-w-sm text-sm leading-relaxed text-cream/55">
                    Selamat Bekerja!
                </p>

                <ul class="anim-rise d-4 mt-8 space-y-3.5">
                    <li class="flex items-center gap-3 text-sm text-cream/70">
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-gold/10 text-gold ring-1 ring-gold/20">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 12.5 5 5L20 6.5" /></svg>
                        </span>
                        Kelola transaksi &amp; jadwal sewa
                    </li>
                    <li class="flex items-center gap-3 text-sm text-cream/70">
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-gold/10 text-gold ring-1 ring-gold/20">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 12.5 5 5L20 6.5" /></svg>
                        </span>
                        Pantau koleksi &amp; jadwal fitting
                    </li>
                    <li class="flex items-center gap-3 text-sm text-cream/70">
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-gold/10 text-gold ring-1 ring-gold/20">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 12.5 5 5L20 6.5" /></svg>
                        </span>
                        Laporan atelier serba real-time
                    </li>
                </ul>
            </div>

            {{-- ✨ Maskot Emas Interaktif --}}
            <div class="anim-rise d-6 mascot-wrapper hidden xl:block" id="mascotWrapper" aria-hidden="true">
                <div class="mascot-tracker" id="mascotTracker">
                    <div class="mascot-float" id="mascotFloat">
                        <svg id="mascotSvg" class="h-44 w-auto drop-shadow-[0_15px_25px_rgba(212,175,55,0.3)]" viewBox="0 0 200 200" fill="none">
                            <defs>
                                <!-- Gradient Emas -->
                                <linearGradient id="ghostGold" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#FFF3CD" />
                                    <stop offset="40%" stop-color="#D4AF37" />
                                    <stop offset="100%" stop-color="#997A00" />
                                </linearGradient>
                            </defs>

                            {{-- Badan Hantu Emas --}}
                            <path d="M 40 180 C 15 140, 15 40, 100 40 C 185 40, 185 140, 160 180 C 155 190, 140 185, 130 175 C 115 160, 85 160, 70 175 C 60 185, 45 190, 40 180 Z" fill="url(#ghostGold)" />

                            {{-- Mata Kiri --}}
                            <g class="eye">
                                <g class="eye-track">
                                    <g class="pupil-group">
                                        <ellipse cx="80" cy="90" rx="6.5" ry="13" fill="#1A1A1A" />
                                        <circle cx="78" cy="86" r="2.5" fill="#FFFFFF" opacity="0.8" />
                                    </g>
                                </g>
                            </g>

                            {{-- Mata Kanan --}}
                            <g class="eye">
                                <g class="eye-track">
                                    <g class="pupil-group">
                                        <ellipse cx="120" cy="90" rx="6.5" ry="13" fill="#1A1A1A" />
                                        <circle cx="118" cy="86" r="2.5" fill="#FFFFFF" opacity="0.8" />
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="anim-rise d-5 relative z-10 flex items-center text-[11px] uppercase tracking-[0.18em] text-cream/35 mb-8">
                <span class="h-px w-8 bg-cream/20"></span>
                ALNAY.ID · Est. 2019
            </div>
        </div>

        {{-- ================= RIGHT FORM PANEL ================= --}}
        <div class="relative flex w-full flex-1 items-center justify-center overflow-hidden px-6 py-12 sm:px-10">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(65%_50%_at_50%_0%,rgba(212,175,55,0.12),transparent_70%)]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-24 right-0 h-64 w-64 rounded-full bg-gold/10 blur-3xl anim-float-slow" aria-hidden="true"></div>

            <div class="relative w-full max-w-sm sm:max-w-md">

                {{-- Mobile brand --}}
                <div class="anim-rise mb-10 flex items-center gap-3 lg:hidden">
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-gold to-gold-dark shadow-md shadow-gold/25">
                        <span class="font-display text-base font-bold text-ink">A</span>
                    </div>
                    <div>
                        <p class="font-display text-lg font-semibold leading-none tracking-tight text-ink">ALNAY.ID</p>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.2em] text-ink-muted/70">Muda, Gaya, dan Berbudaya</p>
                    </div>
                </div>

                {{-- Card --}}
                <div class="anim-rise d-1 rounded-3xl border border-black/[0.06] bg-white/80 p-7 shadow-[0_24px_70px_-24px_rgba(0,0,0,0.28)] backdrop-blur-xl sm:p-9">

                    <h2 class="font-display text-3xl font-semibold tracking-tight text-ink">Masuk</h2>
                    <p class="mt-2 text-sm text-ink-muted">Kelola operasional ALNAY.ID.</p>

                    @if ($errors->any())
                        <div class="anim-shake mt-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-700">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v4" />
                                <path d="M12 16h.01" />
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="anim-shake mt-6 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/90 px-4 py-3 text-sm text-amber-800">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v4" />
                                <path d="M12 16h.01" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <form id="loginForm" method="POST" action="{{ route('admin.login.store') }}" class="mt-7 space-y-5">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-ink">Email atau nomor HP</label>
                            <div class="group relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 z-10 grid w-11 place-items-center text-ink-muted/60 transition-colors duration-200 group-focus-within:text-gold">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="3" />
                                        <path d="m3.6 7.2 7.3 5.1a2 2 0 0 0 2.2 0l7.3-5.1" />
                                    </svg>
                                </span>
                                <input id="email" type="text" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="username" required autofocus class="w-full rounded-2xl border border-black/10 bg-white/90 py-3.5 pl-11 pr-4 text-sm text-ink shadow-sm transition-all duration-200 placeholder:text-ink-muted/50 hover:border-black/20 focus:border-gold focus:bg-white focus:shadow-lg focus:shadow-gold/10 focus:outline-none focus:ring-4 focus:ring-gold/15">
                            </div>
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-ink">Kata sandi</label>
                            <div class="group relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 z-10 grid w-11 place-items-center text-ink-muted/60 transition-colors duration-200 group-focus-within:text-gold">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="4" y="10" width="16" height="10" rx="3" />
                                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                    </svg>
                                </span>
                                <input id="password" type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required class="w-full rounded-2xl border border-black/10 bg-white/90 py-3.5 pl-11 pr-12 text-sm text-ink shadow-sm transition-all duration-200 placeholder:text-ink-muted/50 hover:border-black/20 focus:border-gold focus:bg-white focus:shadow-lg focus:shadow-gold/10 focus:outline-none focus:ring-4 focus:ring-gold/15">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 grid w-11 place-items-center rounded-r-2xl text-ink-muted/60 transition-colors duration-200 hover:text-gold focus:outline-none focus-visible:text-gold" aria-label="Tampilkan kata sandi" aria-pressed="false">
                                    <svg id="eyeOpen" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg id="eyeClosed" class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 3l18 18" />
                                        <path d="M10.6 6.1A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.6 3.4" />
                                        <path d="M6.4 7.9A16.6 16.6 0 0 0 2.5 12S6 18 12 18a9.9 9.9 0 0 0 3.6-.7" />
                                        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-sm">
                            <label class="group flex cursor-pointer select-none items-center gap-2.5 text-ink-muted transition-colors hover:text-ink">
                                <input type="checkbox" name="remember" checked class="h-4 w-4 cursor-pointer rounded border-black/20 text-gold focus:ring-gold/40">
                                <span>Ingat saya</span>
                            </label>
                        </div>

                        <button type="submit" id="submitBtn" class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-gold via-amber-300 to-gold-dark bg-[length:200%_100%] bg-left px-4 py-3.5 text-sm font-semibold text-ink shadow-lg shadow-gold/25 transition-all duration-500 hover:-translate-y-0.5 hover:bg-right hover:shadow-xl hover:shadow-gold/40 focus:outline-none focus-visible:ring-4 focus-visible:ring-gold/30 active:translate-y-0 disabled:cursor-wait disabled:opacity-80">
                            <span class="btn-sheen"></span>
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                <svg id="btnSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25" />
                                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                                </svg>
                                <span id="btnLabel">Masuk</span>
                                <svg id="btnArrow" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14" />
                                    <path d="m13 6 6 6-6 6" />
                                </svg>
                            </span>
                        </button>
                    </form>
                </div>

                <p class="anim-rise d-2 mt-7 text-center text-xs text-ink-muted/70">
                    &copy; {{ date('Y') }} ALNAY.ID
                </p>
            </div>
        </div>
    </div>

    <script>
        (function() {
            // Toggle visibilitas kata sandi
            var password = document.getElementById('password');
            var toggle = document.getElementById('togglePassword');
            var eyeOpen = document.getElementById('eyeOpen');
            var eyeClosed = document.getElementById('eyeClosed');

            if (toggle && password) {
                toggle.addEventListener('click', function() {
                    var show = password.type === 'password';
                    password.type = show ? 'text' : 'password';
                    eyeOpen.classList.toggle('hidden', show);
                    eyeClosed.classList.toggle('hidden', !show);
                    toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                    toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    password.focus({ preventScroll: true });
                });
            }

            // Loading state saat submit
            var form = document.getElementById('loginForm');
            var btn = document.getElementById('submitBtn');
            var label = document.getElementById('btnLabel');
            var spinner = document.getElementById('btnSpinner');
            var arrow = document.getElementById('btnArrow');

            if (form && btn) {
                form.addEventListener('submit', function() {
                    if (!form.checkValidity()) return;
                    btn.disabled = true;
                    if (label) label.textContent = 'Memproses…';
                    if (spinner) spinner.classList.remove('hidden');
                    if (arrow) arrow.classList.add('hidden');
                });
            }
        })();

        // ✨ Maskot Interaktif (Mata, Parallax, Drag Elastic, Squish)
        (function() {
            var svg = document.getElementById('mascotSvg');
            var password = document.getElementById('password');
            var tracker = document.getElementById('mascotTracker');
            var floatWrap = document.getElementById('mascotFloat');
            var wrapper = document.getElementById('mascotWrapper');

            if (!svg) return;

            var eyes = svg.querySelectorAll('.eye');
            var maxOffset = 10;
            var maxScreenDist = 400;
            var mouseX = window.innerWidth / 2;
            var mouseY = window.innerHeight / 2;

            // State untuk Drag & Pluffy effect
            var isDragging = false;
            var dragStartX = 0, dragStartY = 0;
            var dragDistance = 0;

            // 1. Pergerakan Mata
            function updateEyes() {
                var rect = svg.getBoundingClientRect();
                if (rect.width === 0) return;

                var scale = rect.width / 200;
                var eyeCenters = [
                    { x: rect.left + 80 * scale, y: rect.top + 90 * scale },
                    { x: rect.left + 120 * scale, y: rect.top + 90 * scale }
                ];

                eyes.forEach(function(eye, index) {
                    var track = eye.querySelector('.eye-track');
                    if (!track) return;

                    var cx = eyeCenters[index].x;
                    var cy = eyeCenters[index].y;
                    var dx = mouseX - cx;
                    var dy = mouseY - cy;
                    var dist = Math.sqrt(dx * dx + dy * dy) || 1;
                    var strength = Math.min(dist / maxScreenDist, 1);

                    var ox = (dx / dist) * maxOffset * strength;
                    var oy = (dy / dist) * maxOffset * strength;

                    track.setAttribute('transform', 'translate(' + ox.toFixed(2) + ' ' + oy.toFixed(2) + ')');
                });
            }

            // 2. Pergerakan Badan (Parallax 3D)
            function updateBody() {
                if (!tracker) return;
                var xOffset = (mouseX / window.innerWidth - 0.5) * 2;
                var yOffset = (mouseY / window.innerHeight - 0.5) * 2;
                
                var moveX = xOffset * 15;
                var moveY = yOffset * 15;

                tracker.style.transform = 'translate3d(' + moveX + 'px, ' + moveY + 'px, 0)';
            }

            // Event Listener Mousemove
            document.addEventListener('mousemove', function(e) {
                mouseX = e.clientX;
                mouseY = e.clientY;

                if (isDragging) {
                    // --- LOGIKA TARIK KENYAL (PLUFFY) ---
                    var dx = mouseX - dragStartX;
                    var dy = mouseY - dragStartY;
                    dragDistance = Math.sqrt(dx * dx + dy * dy);

                    var maxDrag = 120; // Batas maksimal tarikan
                    var dist = Math.min(dragDistance, maxDrag);
                    
                    // Hitung sudut tarikan (dalam radian/derajat)
                    var angle = Math.atan2(dy, dx) * 180 / Math.PI;

                    // Geser bodi sedikit mengikuti kursor (0.4 = 40% ikut bergeser)
                    floatWrap.style.transform = 'translate(' + (dx * 0.4) + 'px, ' + (dy * 0.4) + 'px)';

                    // Hitung efek melar (Stretch)
                    var stretch = dist / maxDrag; // nilai 0 sampai 1
                    var scaleX = 1 + stretch * 0.4; // Memanjang searah tarikan
                    var scaleY = 1 - stretch * 0.3; // Mengecil di sisi tegak lurusnya

                    // Terapkan transformasi pada SVG
                    svg.style.transform = 'rotate(' + angle + 'deg) scale(' + scaleX + ', ' + scaleY + ') rotate(' + (-angle) + 'deg)';

                } else {
                    // Mode normal: ikuti parallax & pergerakan mata
                    updateBody();
                }

                updateEyes(); // Mata selalu mengikuti kursor
            });

            // Kembali ke tengah saat kursor keluar dokumen
            document.addEventListener('mouseleave', function() {
                eyes.forEach(function(eye) {
                    var track = eye.querySelector('.eye-track');
                    if (track) track.setAttribute('transform', 'translate(0 0)');
                });
                if (tracker) tracker.style.transform = 'translate3d(0, 0, 0)';
            });

            // 3. Tutup Mata saat Password Diisi
            if (password) {
                function syncEyes() {
                    var filled = password.value.length > 0;
                    svg.classList.toggle('eyes-closed', filled);
                }
                password.addEventListener('input', syncEyes);
                password.addEventListener('change', syncEyes);
                syncEyes();
            }

            // 4. Event Drag & Drop / Click
            if (wrapper) {
                // Mulai Drag (Hold)
                wrapper.addEventListener('mousedown', function(e) {
                    isDragging = true;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;
                    dragDistance = 0;

                    floatWrap.classList.add('is-dragging');
                    floatWrap.style.transition = 'none';
                    svg.style.transition = 'none';
                    
                    e.preventDefault(); // Mencegah text selection
                });
            }

            // Lepas Drag / Klik
            document.addEventListener('mouseup', function() {
                if (!isDragging) return;
                isDragging = false;

                floatWrap.classList.remove('is-dragging');

                // Efek memantul kembali (Spring/Bouncy)
                floatWrap.style.transition = 'transform 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                svg.style.transition = 'transform 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                
                // Reset transform ke bentuk awal
                floatWrap.style.transform = 'translate(0px, 0px)';
                svg.style.transform = 'rotate(0deg) scale(1, 1) rotate(0deg)';

                // Jika hanya diklik tanpa ditarik jauh, mainkan animasi Squish
                if (dragDistance < 10) {
                    floatWrap.classList.remove('is-squished');
                    void floatWrap.offsetWidth; // Trigger reflow
                    floatWrap.classList.add('is-squished');
                    setTimeout(function() {
                        floatWrap.classList.remove('is-squished');
                    }, 400);
                }
            });

            window.addEventListener('resize', updateEyes);
            updateEyes();
        })();
    </script>

</body>

</html>