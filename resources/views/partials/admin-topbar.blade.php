<header class="sticky top-0 z-40 border-b border-amber-100/60 bg-white/85 backdrop-blur-xl">
    {{-- Style untuk Animasi Lonceng --}}
    <style>
        @keyframes bell-ring {
            0%, 100% { transform: rotate(0); }
            10%, 30%, 50%, 70% { transform: rotate(-12deg); }
            20%, 40%, 60% { transform: rotate(12deg); }
            80% { transform: rotate(-8deg); }
            90% { transform: rotate(8deg); }
        }
        .animate-bell {
            animation: bell-ring 1.2s ease-in-out infinite;
            transform-origin: top center;
        }

        @keyframes bell-click {
            0% { transform: scale(1) rotate(0); }
            25% { transform: scale(0.9) rotate(-20deg); }
            50% { transform: scale(1.1) rotate(15deg); }
            75% { transform: scale(1) rotate(-10deg); }
            100% { transform: scale(1) rotate(0); }
        }
        .animate-bell-click {
            animation: bell-click 0.6s cubic-bezier(.36,.07,.19,.97) both;
            transform-origin: top center;
        }

        @keyframes toast-in {
            from { opacity: 0; transform: translateX(16px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .toast-in { animation: toast-in .3s ease-out both; }
    </style>

    {{-- Logika lonceng notifikasi --}}
    <script>
        function notifBell(awal, feedUrl, bacaSemuaUrl, csrf) {
            // Warna ikon per "nada" (ditulis lengkap agar terbaca Tailwind)
            const NADA = {
                amber:   'bg-amber-50 text-amber-600',
                sky:     'bg-sky-50 text-sky-600',
                rose:    'bg-rose-50 text-rose-600',
                emerald: 'bg-emerald-50 text-emerald-600',
                violet:  'bg-violet-50 text-violet-600',
            };

            return {
                open: false,
                ring: false,
                klik: false,
                unread: awal.unread || 0,
                items: awal.items || [],
                ikon: awal.ikon || {},
                known: new Set((awal.items || []).map(i => i.id)),
                toasts: [],
                suara: localStorage.getItem('admin_notif_suara') === '1',
                judulAsli: document.title,
                timer: null,
                berhenti: false,

                init() {
                    this.perbaruiJudul();
                    this.jadwalkan();
                    document.addEventListener('visibilitychange', () => { if (!document.hidden) this.muat(); });
                },

                nadaKelas(n) { return NADA[n] || NADA.amber; },

                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.ring = false;
                        this.klik = true;
                        setTimeout(() => this.klik = false, 600);
                        this.muat();
                    }
                },

                // Polling: 20 dtk saat tab aktif, 60 dtk saat tab di latar belakang
                jadwalkan() {
                    clearTimeout(this.timer);
                    if (this.berhenti) return;
                    this.timer = setTimeout(async () => { await this.muat(); this.jadwalkan(); }, document.hidden ? 60000 : 20000);
                },

                async muat() {
                    try {
                        const r = await fetch(feedUrl, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        });
                        if (r.status === 401 || r.status === 419) { this.berhenti = true; return; } // sesi habis
                        if (!r.ok) return;

                        const d = await r.json();
                        const baru = d.items.filter(i => !i.dibaca && !this.known.has(i.id));
                        d.items.forEach(i => this.known.add(i.id));
                        this.items = d.items;
                        this.unread = d.unread;
                        this.perbaruiJudul();

                        if (baru.length) this.beritahu(baru);
                    } catch (e) { /* jaringan putus: coba lagi di putaran berikutnya */ }
                },

                beritahu(baru) {
                    if (!this.open) {
                        this.ring = true;
                        setTimeout(() => this.ring = false, 6000);
                    }

                    // Maksimal 3 toast; sisanya diringkas
                    const tampil = baru.slice(0, 3).map(n => ({ ...n, key: n.id }));
                    if (baru.length > 3) {
                        tampil.push({ key: 'ringkas-' + Date.now(), judul: (baru.length - 3) + ' notifikasi lainnya', pesan: 'Buka lonceng untuk melihat semuanya.', url: '/admin/notifikasi', ikon: 'bell', nada: 'amber' });
                    }
                    tampil.forEach(t => {
                        this.toasts.push(t);
                        setTimeout(() => { this.toasts = this.toasts.filter(x => x.key !== t.key); }, 8000);
                    });

                    this.bunyi();
                },

                async bacaSemua() {
                    try {
                        const r = await fetch(bacaSemuaUrl, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        });
                        if (!r.ok) return;
                        this.items = this.items.map(i => ({ ...i, dibaca: true }));
                        this.unread = 0;
                        this.perbaruiJudul();
                    } catch (e) {}
                },

                ubahSuara() {
                    this.suara = !this.suara;
                    localStorage.setItem('admin_notif_suara', this.suara ? '1' : '0');
                    if (this.suara) this.bunyi(true); // contoh bunyi sekaligus "membuka kunci" audio browser
                },

                // Bunyi dua nada pendek (tanpa file audio)
                bunyi(paksa = false) {
                    if (!this.suara && !paksa) return;
                    try {
                        const Ctx = window.AudioContext || window.webkitAudioContext;
                        const ctx = new Ctx();
                        [[880, 0], [1175, 0.16]].forEach(([f, t]) => {
                            const o = ctx.createOscillator(), g = ctx.createGain();
                            o.type = 'sine'; o.frequency.value = f;
                            g.gain.setValueAtTime(0.0001, ctx.currentTime + t);
                            g.gain.exponentialRampToValueAtTime(0.15, ctx.currentTime + t + 0.02);
                            g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + t + 0.3);
                            o.connect(g); g.connect(ctx.destination);
                            o.start(ctx.currentTime + t); o.stop(ctx.currentTime + t + 0.32);
                        });
                        setTimeout(() => ctx.close(), 900);
                    } catch (e) {}
                },

                // Tampilkan jumlah belum dibaca di judul tab, mis. "(3) Dashboard — ALNAY.ID"
                perbaruiJudul() {
                    document.title = this.unread > 0 ? '(' + this.unread + ') ' + this.judulAsli : this.judulAsli;
                },
            };
        }
    </script>


    {{-- Accent bar --}}
    <div class="h-0.5 w-full bg-gradient-to-r from-amber-600 via-amber-500 to-amber-400"></div>

    <div class="flex items-center justify-between gap-4 px-6 lg:px-8 py-4">

        {{-- Judul halaman --}}
        <div class="min-w-0">
            <h1 class="truncate text-xl lg:text-2xl font-bold tracking-tight text-gray-900">
                @yield('title', 'Dashboard')
            </h1>
            <p class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="truncate">@yield('subtitle', \Illuminate\Support\Carbon::now()->translatedFormat('l, j F Y'))</span>
            </p>
        </div>

        {{-- Aksi --}}
        <div class="flex shrink-0 items-center gap-2 lg:gap-3">

            {{-- ============ NOTIFIKASI ============ --}}
            {{-- Data awal dari NotifikasiComposer ($totalNotif, $pesanNotif); selanjutnya diperbarui otomatis (polling). --}}
            @php
                $notifAwal = [
                    'unread' => (int) ($totalNotif ?? 0),
                    'items'  => $pesanNotif ?? [],
                    'ikon'   => \App\Support\NotifikasiAdmin::IKON,
                ];
            @endphp

            <div class="relative"
                 x-data="notifBell(@js($notifAwal), @js(route('admin.notifikasi.feed', [], false)), @js(route('admin.notifikasi.baca-semua', [], false)), @js(csrf_token()))"
                 @keydown.escape.window="open = false">

                {{-- Tombol lonceng --}}
                <button type="button" @click="toggle()" class="relative rounded-full p-2 transition hover:bg-gray-100 group"
                        aria-label="Notifikasi" :aria-expanded="open">
                    <div :class="{ 'animate-bell': ring, 'animate-bell-click': klik }"
                         class="transition-transform duration-200 group-hover:scale-110">
                        <svg class="h-6 w-6 text-gray-500 transition-colors group-hover:text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="ikon.bell"></path>
                        </svg>
                    </div>

                    {{-- Angka belum dibaca --}}
                    <span x-show="unread > 0" x-cloak
                          class="absolute -right-0.5 -top-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white"
                          x-text="unread > 99 ? '99+' : unread"></span>
                </button>

                {{-- Panel --}}
                <div x-show="open" x-cloak @click.outside="open = false"
                     x-transition.opacity.duration.150ms
                     class="absolute right-0 z-50 mt-2 w-96 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl">

                    <div class="flex items-center justify-between gap-2 border-b border-gray-100 bg-gray-50/60 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-800">Notifikasi</h3>
                            <span x-show="unread > 0" x-cloak class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-600" x-text="unread + ' baru'"></span>
                        </div>
                        <div class="flex items-center gap-1">
                            {{-- Suara --}}
                            <button type="button" @click="ubahSuara()"
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-amber-600"
                                    :title="suara ? 'Suara notifikasi: aktif' : 'Suara notifikasi: mati'">
                                <svg x-show="suara" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                                <svg x-show="!suara" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l4-4m0 4l-4-4"/></svg>
                            </button>
                            <button type="button" @click="bacaSemua()" x-show="unread > 0" x-cloak
                                    class="rounded-lg px-2 py-1 text-[11px] font-semibold text-amber-700 transition hover:bg-amber-50">
                                Tandai semua dibaca
                            </button>
                        </div>
                    </div>

                    <div class="max-h-[26rem] overflow-y-auto">
                        <template x-for="n in items" :key="n.id">
                            <a :href="n.url"
                               class="flex items-start gap-3 border-b border-gray-50 px-4 py-3 transition hover:bg-amber-50/50"
                               :class="n.dibaca ? 'bg-white' : 'bg-amber-50/30'">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full" :class="nadaKelas(n.nada)">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="ikon[n.ikon] || ikon.bell"></path>
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-start justify-between gap-2">
                                        <span class="text-sm leading-snug text-gray-800" :class="n.dibaca ? 'font-semibold' : 'font-bold'" x-text="n.judul"></span>
                                        <span x-show="!n.dibaca" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                                    </span>
                                    <span class="mt-0.5 block break-words text-xs text-gray-500" x-text="n.pesan"></span>
                                    <span class="mt-1 block text-[10px] font-medium text-gray-400" x-text="n.waktu"></span>
                                </span>
                            </a>
                        </template>

                        {{-- Kosong --}}
                        <div x-show="items.length === 0" x-cloak class="px-4 py-10 text-center">
                            <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">Semua aman</p>
                            <p class="mt-0.5 text-xs text-gray-400">Belum ada notifikasi.</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.notifikasi.index') }}"
                       class="block border-t border-gray-100 bg-gray-50/60 px-4 py-2.5 text-center text-xs font-bold text-amber-700 transition hover:bg-amber-50">
                        Lihat semua notifikasi
                    </a>
                </div>

                {{-- Toast notifikasi baru (muncul sebentar di pojok kanan atas) --}}
                <div class="pointer-events-none fixed right-4 top-24 z-[70] flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-2">
                    <template x-for="t in toasts" :key="t.key">
                        <a :href="t.url" class="toast-in pointer-events-auto flex items-start gap-3 rounded-2xl border border-amber-100 bg-white p-3 shadow-xl">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" :class="nadaKelas(t.nada)">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="ikon[t.ikon] || ikon.bell"></path>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold leading-snug text-gray-800" x-text="t.judul"></span>
                                <span class="mt-0.5 block break-words text-xs text-gray-500" x-text="t.pesan"></span>
                            </span>
                        </a>
                    </template>
                </div>
            </div>

            {{-- ============ USER INFO ============ --}}
            @auth
                <div class="hidden md:flex items-center gap-2 rounded-xl border border-gray-200 bg-white py-1 pl-1 pr-3 shadow-sm">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-tr from-amber-500 to-amber-400 text-xs font-black text-white shadow-sm shadow-amber-500/20">
                        {{ strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate text-xs font-bold text-gray-900">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] capitalize text-gray-400">{{ auth()->user()->role ?? 'admin' }}</p>
                    </div>
                </div>
            @endauth

            {{-- ============ LOGOUT ============ --}}
            <form action="{{ route('admin.logout') }}" method="POST"
                  onsubmit="return confirm('Yakin ingin keluar dari backoffice?')">
                @csrf
                <button type="submit"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 shadow-sm transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400"
                        title="Keluar">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>