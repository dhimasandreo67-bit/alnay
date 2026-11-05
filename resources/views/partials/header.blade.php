<style>
    @keyframes wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-15deg); }
        50% { transform: rotate(10deg); }
        75% { transform: rotate(-5deg); }
    }
</style>

@php
    $navItems = [
        ['label' => 'Beranda', 'route' => 'beranda', 'active' => request()->routeIs('beranda')],
        ['label' => 'Koleksi', 'route' => 'koleksi.index', 'active' => request()->routeIs('koleksi.*')],
        ['label' => 'Cara Sewa', 'route' => 'cara-sewa', 'active' => request()->routeIs('cara-sewa')],
        ['label' => 'Tentang Kami', 'route' => 'tentang', 'active' => request()->routeIs('tentang')],
        ['label' => 'Testimoni', 'route' => 'testimoni', 'active' => request()->routeIs('testimoni')],
    ];

    // Total keranjang = keranjang beli + keranjang sewa
    $totalKeranjang = 0;
    if (isset($cartCount) && $cartCount > 0) {
        $totalKeranjang += $cartCount;
    }
    if (session()->has('keranjang_sewa')) {
        $totalKeranjang += count(session('keranjang_sewa'));
    }

    if (auth()->check()) {
        $role = auth()->user()->role;

        if ($role === 'owner') {
            $profileRoute = route('owner.dashboard');
            $profileText = 'Owner';
            $profileLabel = 'Dashboard Owner';
        } elseif ($role === 'admin') {
            $profileRoute = route('admin.dashboard');
            $profileText = 'Admin';
            $profileLabel = 'Dashboard Admin';
        } else {
            $profileRoute = route('akun.dashboard');
            $profileText = 'Profil';
            $profileLabel = 'Akun Saya';
        }
    }
@endphp

<header x-data="{ mobileOpen: false, koleksiOpen: false, searchOpen: false, akunOpen: false }"
    @keydown.escape.window="mobileOpen = false; searchOpen = false; akunOpen = false"
    class="bg-panel/95 backdrop-blur border-b border-line/60 sticky top-0 z-50">

    {{-- BARIS UTAMA --}}
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-3">

        {{-- Logo --}}
        <a href="{{ route('beranda') }}" class="flex items-center gap-2.5 shrink-0 min-h-[44px]">
            <img src="{{ asset('images/home/alnaylogo.png') }}" alt="ALNAY.ID"
                class="w-10 h-10 rounded-lg border border-gold/60 bg-[#fbf6e9] object-contain p-1">
            <span class="hidden sm:block leading-tight">
                <span class="block font-display font-bold text-ink text-lg tracking-tight">ALNAY.ID</span>
                <span class="block text-[9px] uppercase tracking-[0.2em] text-muted">Jas & Gown Berbudaya</span>
            </span>
        </a>

        {{-- Navigasi desktop --}}
        <nav class="hidden lg:flex items-center gap-1 h-full ml-6">
            @foreach ($navItems as $item)
                @if ($item['label'] === 'Koleksi')
                    <div class="relative h-full flex items-center" @mouseenter="koleksiOpen = true"
                        @mouseleave="koleksiOpen = false">
                        <a href="{{ route($item['route']) }}"
                            class="inline-flex items-center gap-1 px-3.5 py-2 rounded-full text-[13px] font-medium transition-colors
                              {{ $item['active'] ? 'bg-cream text-ink font-semibold' : 'text-ink/70 hover:text-ink hover:bg-cream/70' }}">
                            {{ $item['label'] }}
                            <x-icon name="chevron-down" :size="14"
                                class="transition-transform duration-200 {{ $item['active'] ? '' : 'opacity-60' }}"
                                x-bind:class="koleksiOpen ? 'rotate-180' : ''" />
                        </a>

                        <div x-show="koleksiOpen" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="absolute top-full left-0 w-60 bg-panel border border-line/60 rounded-2xl shadow-xl z-50 overflow-hidden"
                            style="display: none;">
                            <div class="p-2">
                                @foreach ([['Jas Adat', 'jas-adat'], ['Gown Pengantin', 'gown-pengantin'], ['Siger', 'siger'], ['Beskap', 'beskap']] as [$nama, $slug])
                                    <a href="{{ route('koleksi.index', ['kategori' => $slug]) }}"
                                        class="block px-3.5 py-2.5 rounded-xl text-sm text-ink/80 hover:bg-cream hover:text-ink transition-colors">
                                        {{ $nama }}
                                    </a>
                                @endforeach
                                <div class="border-t border-line/50 my-1.5"></div>
                                <a href="{{ route('koleksi.index') }}"
                                    class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gold hover:bg-cream transition-colors">
                                    Lihat Semua Koleksi →
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route($item['route']) }}"
                        class="inline-flex items-center px-3.5 py-2 rounded-full text-[13px] font-medium transition-colors
                          {{ $item['active'] ? 'bg-cream text-ink font-semibold' : 'text-ink/70 hover:text-ink hover:bg-cream/70' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Aksi kanan --}}
        <div class="flex items-center gap-0.5 sm:gap-1 shrink-0 ml-auto">

            {{-- Cari --}}
            <button type="button"
                @click="searchOpen = !searchOpen; mobileOpen = false; if (searchOpen) $nextTick(() => $refs.q.focus())"
                :class="searchOpen ? 'bg-cream' : ''"
                class="w-10 h-10 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors active:scale-95"
                aria-label="Cari produk">
                <x-icon name="search" :size="20" />
            </button>

            {{-- Keranjang (gabungan beli & sewa) --}}
            <a href="{{ route('keranjang.index') }}"
                class="relative w-10 h-10 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors active:scale-95"
                aria-label="Keranjang">
                <x-icon name="cart" :size="20" />
                @if ($totalKeranjang > 0)
                    <span
                        class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-gold text-white text-[10px] font-bold flex items-center justify-center border-2 border-panel">
                        {{ $totalKeranjang }}
                    </span>
                @endif
            </a>

            {{-- Notifikasi (hanya untuk yang sudah masuk) --}}
            @auth
                <div class="relative" x-data="{ notifOpen: false }">
                    <button type="button" @click="notifOpen = !notifOpen"
                        class="group relative w-10 h-10 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors active:scale-95"
                        aria-label="Notifikasi">
                        <div class="group-active:animate-[wiggle_0.3s_ease-in-out]">
                            <x-icon name="bell" :size="20" />
                        </div>
                        @if (isset($jumlahNotifikasi) && $jumlahNotifikasi > 0)
                            <span
                                class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center border-2 border-panel">
                                {{ $jumlahNotifikasi }}
                            </span>
                        @endif
                    </button>

                    <div x-show="notifOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-95" @click.outside="notifOpen = false"
                        x-cloak
                        class="fixed sm:absolute left-1/2 -translate-x-1/2 sm:left-auto sm:right-0 sm:translate-x-0 top-[72px] sm:top-full mt-0 sm:mt-2 w-[calc(100vw-2rem)] max-w-[340px] bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden origin-top-center sm:origin-top-right ring-1 ring-black/5">

                        <div
                            class="px-5 py-4 border-b border-gray-50 flex items-center justify-between bg-white sticky top-0 z-10">
                            <h3 class="text-[13px] font-bold text-gray-900">Notifikasi</h3>
                            <button type="button" @click="notifOpen = false"
                                class="text-gray-400 hover:text-gray-600 p-1 bg-gray-50 hover:bg-gray-100 rounded-full transition-colors"
                                aria-label="Tutup">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="max-h-[320px] overflow-y-auto custom-scrollbar">
                            @forelse($notifikasi ?? [] as $n)
                                <a href="{{ route('akun.pesanan.detail', $n->id) }}"
                                    class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-gray-50/80 transition-colors group">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center shrink-0
                                        {{ $n->jenis === 'sewa' ? 'bg-amber-50 text-amber-500' : 'bg-sky-50 text-sky-500' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if ($n->jenis === 'sewa')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            @endif
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-[12px] font-bold text-gray-800 truncate">{{ $n->kode_transaksi }}</p>
                                            <span
                                                class="text-[9px] text-gray-400 whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] text-gray-500">Pesanan {{ strtoupper($n->jenis) }}</span>
                                            @if (strtolower($n->status_pembayaran) === 'pending')
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-50 text-amber-600 border border-amber-100">PENDING</span>
                                            @elseif(in_array(strtolower($n->status_pembayaran), ['lunas', 'success']))
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">LUNAS</span>
                                            @else
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-gray-50 text-gray-500 border border-gray-200">{{ strtoupper($n->status_pembayaran) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="py-12 text-center">
                                    <div
                                        class="w-12 h-12 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3">
                                        <x-icon name="bell" :size="24" />
                                    </div>
                                    <p class="text-xs text-gray-400">Belum ada notifikasi baru.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endauth

            {{-- Akun (desktop) --}}
            @guest
                <div class="hidden lg:flex items-center gap-1 ml-2">
                    <a href="{{ route('masuk') }}"
                        class="inline-flex items-center px-4 h-10 rounded-full text-[13px] font-semibold text-ink/80 hover:text-ink hover:bg-cream transition-colors">
                        Masuk
                    </a>
                    <a href="{{ route('daftar') }}"
                        class="inline-flex items-center px-5 h-10 rounded-full bg-gold text-white text-[13px] font-semibold hover:bg-gold/90 transition-colors">
                        Daftar
                    </a>
                </div>
            @endguest

            @auth
                <div class="relative hidden lg:block ml-1" @click.outside="akunOpen = false">
                    <button type="button" @click="akunOpen = !akunOpen" :aria-expanded="akunOpen"
                        class="inline-flex items-center gap-1.5 h-10 pl-3 pr-2.5 rounded-full border border-line/60 text-[13px] font-medium text-ink hover:border-gold/60 transition-colors">
                        <x-icon name="user" :size="18" />
                        {{ $profileText }}
                        <x-icon name="chevron-down" :size="14" class="opacity-60" />
                    </button>

                    <div x-show="akunOpen" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="absolute right-0 top-full mt-2 w-52 bg-panel border border-line/60 rounded-2xl shadow-xl z-50 p-2">
                        <a href="{{ $profileRoute }}"
                            class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm text-ink/80 hover:bg-cream hover:text-ink transition-colors">
                            <x-icon name="user" :size="16" />
                            {{ $profileLabel }}
                        </a>
                        <form method="POST" action="{{ route('keluar') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm text-ink/80 hover:bg-cream hover:text-ink transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            {{-- Hamburger --}}
            <button type="button" @click="mobileOpen = !mobileOpen; searchOpen = false"
                class="lg:hidden w-10 h-10 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors active:scale-95"
                aria-label="Menu">
                <template x-if="!mobileOpen"><x-icon name="menu" :size="22" /></template>
                <template x-if="mobileOpen"><x-icon name="close" :size="22" /></template>
            </button>
        </div>
    </div>

    {{-- PANEL PENCARIAN (semua ukuran layar) --}}
    <div x-show="searchOpen" x-collapse x-cloak class="border-t border-line/50 bg-panel">
        <form action="{{ route('koleksi.index') }}" method="GET" class="max-w-3xl mx-auto px-4 py-3 relative">
            <x-icon name="search" :size="18"
                class="absolute left-8 top-1/2 -translate-y-1/2 text-muted pointer-events-none" />
            <input type="search" name="search" x-ref="q" value="{{ request('search') }}" autocomplete="off"
                enterkeyhint="search" placeholder="Cari kebaya, siger, motif, atau kode produk…"
                class="w-full h-12 pl-11 pr-4 rounded-full border border-line/60 bg-cream text-base placeholder:text-muted focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all">
        </form>
    </div>

    {{-- MENU MOBILE --}}
    <div x-show="mobileOpen" x-collapse x-cloak class="lg:hidden border-t border-line/50 bg-panel">
        <nav class="max-w-7xl mx-auto px-4 py-3 flex flex-col gap-0.5">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                    class="flex items-center justify-between rounded-xl px-4 py-3.5 text-[15px] font-medium transition-colors
                      {{ $item['active'] ? 'bg-cream text-ink font-semibold' : 'text-ink/80 hover:bg-cream/70' }}">
                    {{ $item['label'] }}
                    @if ($item['active'])
                        <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                    @endif
                </a>
            @endforeach

            <div class="border-t border-line/50 mt-2 pt-3">
                @guest
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('masuk') }}"
                            class="flex items-center justify-center h-12 rounded-full border border-line/70 text-sm font-semibold text-ink hover:bg-cream transition-colors">
                            Masuk
                        </a>
                        <a href="{{ route('daftar') }}"
                            class="flex items-center justify-center h-12 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 transition-colors">
                            Daftar
                        </a>
                    </div>
                @endguest

                @auth
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ $profileRoute }}"
                            class="flex items-center justify-center gap-2 h-12 rounded-full bg-cream text-sm font-semibold text-ink hover:bg-cream-dim transition-colors">
                            <x-icon name="user" :size="16" />
                            {{ $profileLabel }}
                        </a>
                        <form method="POST" action="{{ route('keluar') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center justify-center h-12 rounded-full border border-line/70 text-sm font-semibold text-ink hover:bg-cream transition-colors">
                                Keluar
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </nav>
    </div>

</header>