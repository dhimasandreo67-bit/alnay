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
</head>
<body class="bg-cream font-sans text-ink antialiased">

    <div class="flex min-h-screen">

        {{-- Left brand panel --}}
        <div class="relative hidden w-full max-w-xl flex-col justify-between overflow-hidden bg-ink px-12 py-14 text-cream lg:flex">

            {{-- Decorative kawung-inspired motif, original geometric pattern, low opacity --}}
            <svg class="pointer-events-none absolute -right-24 -top-16 h-[34rem] w-[34rem] text-gold/10" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                <g stroke="currentColor" stroke-width="1.5">
                    @for ($i = 0; $i < 5; $i++)
                        <circle cx="100" cy="100" r="{{ 20 + $i * 20 }}" />
                    @endfor
                    @for ($a = 0; $a < 360; $a += 45)
                        <line x1="100" y1="100"
                              x2="{{ 100 + 100 * cos(deg2rad($a)) }}"
                              y2="{{ 100 + 100 * sin(deg2rad($a)) }}" />
                    @endfor
                </g>
            </svg>

            <div class="relative">
                <p class="font-display text-2xl font-semibold tracking-tight text-gold">ALNAY.ID</p>
                <p class="mt-1 text-sm text-cream/60">Jas &amp; gaun berbudaya</p>
            </div>

            <div class="relative max-w-sm">
                <h1 class="font-display text-4xl font-semibold leading-tight text-cream">
                    Selamat datang kembali di mahakarya nusantara.
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-cream/60">
                    Masuk untuk mengelola transaksi, sewa, dan operasional atelier ALNAY.ID.
                </p>
            </div>
        </div>

        {{-- Right form panel --}}
        <div class="flex w-full flex-1 items-center justify-center px-6 py-14">
            <div class="w-full max-w-sm">

                <p class="mb-10 font-display text-xl font-semibold tracking-tight text-ink lg:hidden">ALNAY.ID</p>

                <h2 class="font-display text-3xl font-semibold text-ink">Masuk</h2>
                <p class="mt-2 text-sm text-ink-muted">Kelola operasional atelier di ALNAY.ID.</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email atau nomor HP</label>
                        <input
                            id="email"
                            type="text"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            required
                            autofocus
                            class="w-full rounded-xl border border-black/10 bg-white px-4 py-3 text-sm text-ink placeholder:text-ink-muted/60 focus:border-gold focus:outline-none focus:ring-2 focus:ring-gold/30"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Kata sandi</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Masukkan kata sandi"
                            required
                            class="w-full rounded-xl border border-black/10 bg-white px-4 py-3 text-sm text-ink placeholder:text-ink-muted/60 focus:border-gold focus:outline-none focus:ring-2 focus:ring-gold/30"
                        >
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-ink-muted">
                            <input type="checkbox" name="remember" checked
                                   class="h-4 w-4 rounded border-black/20 text-gold focus:ring-gold/40">
                            Ingat saya
                        </label>
                        {{-- Tautan lupa password dihilangkan karena rutenya belum ada --}}
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-gold px-4 py-3 text-sm font-semibold text-ink shadow-sm transition hover:bg-gold-dark hover:text-cream">
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
