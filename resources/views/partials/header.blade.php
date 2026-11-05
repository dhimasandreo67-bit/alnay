<style>
    @keyframes wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-15deg); }
        50% { transform: rotate(10deg); }
        75% { transform: rotate(-5deg); }
    }
</style>

@php
    use Illuminate\Support\Facades\Route;

    $navItems = [
        ['label' => 'Beranda', 'route' => 'beranda', 'active' => request()->routeIs('beranda')],
        ['label' => 'Koleksi', 'route' => 'koleksi.index', 'active' => request()->routeIs('koleksi.*')],
        ['label' => 'Cara Sewa', 'route' => 'cara-sewa', 'active' => request()->routeIs('cara-sewa')],
        ['label' => 'Tentang Kami', 'route' => 'tentang', 'active' => request()->routeIs('tentang')],
        ['label' => 'Testimoni', 'route' => 'testimoni', 'active' => request()->routeIs('testimoni')],
    ];
@endphp

{{-- PROMO BAR --}}
<div class="bg-[#17140f] text-white text-xs">
    <div class="max-w-7xl mx-auto px-4 py-2 flex flex-col sm:flex-row items-center justify-between gap-1">
        <p class="text-center sm:text-left flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-gold inline-block"></span>
            {{ config('alnay.promo_bar.teks') }}
        </p>
        <div class="flex items-center gap-4 text-cream/70">
                <span class="hidden sm:inline-flex items-center gap-1.5">
                    Toko: {{ config('alnay.toko.jam_buka') }}
                </span>
            <span class="inline-flex items-center gap-1.5">
                <x-icon name="whatsapp" :size="14" />
                WA CS: {{ config('alnay.kontak.wa_cs_display') }}
            </span>
        </div>
    </div>
</div>

{{-- HEADER UTAMA --}}
<header x-data="{ mobileOpen: false, koleksiOpen: false }" class="bg-panel border-b border-line sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-3 flex items-center gap-2 sm:gap-4">

        {{-- Logo --}}
        <a href="{{ route('beranda') }}" class="flex items-center gap-2 shrink-0 min-h-[44px]">
            <img src="{{ asset('images/home/alnaylogo.png') }}" alt="ALNAY.ID"
                class="w-10 h-10 sm:w-11 sm:h-11 rounded-md border border-[#c99a32] bg-[#fbf6e9] object-contain p-1">
            <span class="hidden sm:block leading-tight">
                <span class="block font-display font-bold text-ink text-lg tracking-tight">ALNAY.ID</span>
                <span class="block text-[9px] uppercase tracking-[0.2em] text-muted">Jas & Gown Berbudaya</span>
            </span>
        </a>

        {{-- Search --}}
        <form action="{{ route('koleksi.index') }}" method="GET" class="relative flex-1 min-w-0">
            <x-icon name="search" :size="18"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none" />
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 rounded-full border border-line bg-cream text-base placeholder:text-muted focus:outline-none focus:border-gold">
        </form>

                {{-- Aksi kanan --}}
        <div class="flex items-center gap-0.5 sm:gap-2 shrink-0 ml-auto">

            {{-- Keranjang (Gabungan Beli & Sewa) --}}
            <a href="{{ route('keranjang.index') }}"
                class="relative inline-flex items-center justify-center w-9 h-9 sm:w-auto sm:h-auto sm:px-3 sm:py-2 rounded-lg text-ink hover:bg-cream transition-colors"
                aria-label="Keranjang">
                <x-icon name="cart" :size="20" />
                <span class="hidden sm:inline text-sm font-medium">Keranjang</span>
                
                {{-- Hitung Total dari Keranjang Beli + Keranjang Sewa --}}
                @php
                    $totalKeranjang = 0;
                    if (isset($cartCount) && $cartCount > 0) {
                        $totalKeranjang += $cartCount;
                    }
                    if (session()->has('keranjang_sewa')) {
                        $totalKeranjang += count(session('keranjang_sewa'));
                    }
                @endphp

                @if ($totalKeranjang > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-gold text-white text-[10px] font-bold flex items-center justify-center">
                        {{ $totalKeranjang }}
                    </span>
                @endif
            </a>

            {{-- Notifikasi --}}
            <div class="relative" x-data="{ notifOpen: false }">
                <button type="button" @click="notifOpen = !notifOpen"
                    class="group relative w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors active:scale-95"
                    aria-label="Notifikasi">
                    <div class="group-active:animate-[wiggle_0.3s_ease-in-out]">
                        <x-icon name="bell" :size="20" />
                    </div>
                    @if (isset($jumlahNotifikasi) && $jumlahNotifikasi > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-5 h-5 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center border-2 border-panel">
                            {{ $jumlahNotifikasi }}
                        </span>
                    @endif
                </button>

                <div x-show="notifOpen"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-90"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 scale-90"
                    @click.outside="notifOpen = false"
                    x-cloak
                    class="fixed sm:absolute left-1/2 -translate-x-1/2 sm:left-auto sm:right-0 sm:translate-x-0 top-[60px] sm:top-full mt-0 sm:mt-2 w-[calc(100vw-2rem)] max-w-[320px] bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden origin-top-center sm:origin-top-right ring-1 ring-black/5">

                    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between bg-white sticky top-0 z-10">
                        <h3 class="text-[13px] font-bold text-gray-900">Notifikasi</h3>
                        <button type="button" @click="notifOpen = false" class="text-gray-400 hover:text-gray-600 p-1 bg-gray-50 hover:bg-gray-100 rounded-full transition-colors" aria-label="Tutup">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="max-h-[320px] overflow-y-auto custom-scrollbar">
                        @forelse($notifikasi ?? [] as $n)
                            <a href="{{ route('akun.pesanan.detail', $n->id) }}"
                            class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-gray-50/80 transition-colors group">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0
                                    {{ $n->jenis === 'sewa' ? 'bg-amber-50 text-amber-500' : 'bg-sky-50 text-sky-500' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($n->jenis === 'sewa')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        @endif
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[12px] font-bold text-gray-800 truncate">{{ $n->kode_transaksi }}</p>
                                        <span class="text-[9px] text-gray-400 whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] text-gray-500">Pesanan {{ strtoupper($n->jenis) }}</span>
                                        @if(strtolower($n->status_pembayaran) === 'pending')
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-50 text-amber-600 border border-amber-100">PENDING</span>
                                        @elseif(in_array(strtolower($n->status_pembayaran), ['lunas', 'success']))
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">LUNAS</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-gray-50 text-gray-500 border border-gray-200">{{ strtoupper($n->status_pembayaran) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3">
                                    <x-icon name="bell" :size="24" />
                                </div>
                                <p class="text-xs text-gray-400">Belum ada notifikasi baru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            @guest
                <a href="{{ route('masuk') }}"
                    class="hidden sm:inline-flex items-center px-5 py-2.5 text-sm font-semibold uppercase tracking-wide text-ink hover:text-gold transition-colors min-h-[44px]">
                    Masuk
                </a>
                <a href="{{ route('daftar') }}"
                    class="inline-flex items-center justify-center px-2.5 sm:px-5 py-2 rounded-md bg-gold text-white text-[10px] sm:text-sm font-semibold uppercase tracking-normal sm:tracking-wide hover:bg-gold/90 transition-colors min-h-[40px] sm:min-h-[44px]">
                    Daftar
                </a>
            @endguest

            @auth
                @php
                    $role = auth()->user()->role;

                    if ($role === 'owner') {
                        $profileRoute = route('owner.dashboard');
                        $profileText = 'Owner';
                    } elseif ($role === 'admin') {
                        $profileRoute = route('admin.dashboard');
                        $profileText = 'Admin';
                    } else {
                        $profileRoute = route('akun.dashboard');
                        $profileText = 'Profil';
                    }
                @endphp

                <a href="{{ $profileRoute }}"
                    class="inline-flex items-center justify-center w-9 h-9 sm:w-auto sm:h-auto sm:px-4 sm:py-2.5 rounded-full sm:rounded-none text-sm font-medium text-ink hover:text-gold hover:bg-cream sm:hover:bg-transparent transition-colors">
                    <x-icon name="user" :size="18" />
                    <span class="hidden sm:inline ml-1.5">{{ $profileText }}</span>
                </a>

                <form method="POST" action="{{ route('keluar') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 w-9 h-9 sm:w-auto sm:h-auto sm:px-3 sm:py-2.5 rounded-full border border-line text-sm font-medium text-ink hover:bg-cream hover:border-gold transition-colors">
                        <span class="hidden sm:inline">Keluar</span>
                        <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            @endauth

            {{-- Hamburger --}}
            <button type="button" @click="mobileOpen = !mobileOpen"
                class="md:hidden w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors"
                aria-label="Menu">
                <template x-if="!mobileOpen"><x-icon name="menu" :size="22" /></template>
                <template x-if="mobileOpen"><x-icon name="close" :size="22" /></template>
            </button>
        </div>
    </div>

    {{-- NAV DESKTOP --}}
    <nav class="hidden md:block border-t border-line">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-center gap-4">
            @foreach ($navItems as $item)
                @if ($item['label'] === 'Koleksi')
                    <div class="relative" @mouseenter="koleksiOpen = true" @mouseleave="koleksiOpen = false">
                        <a href="{{ route($item['route']) }}"
                            class="px-4 py-3 text-xs font-semibold uppercase tracking-wide transition-colors min-h-[50px] inline-flex items-center gap-1.5
                              {{ $item['active'] ? 'text-gold border-b-2 border-gold' : 'text-ink hover:text-gold' }}">
                            <x-icon name="dress" :size="14" />
                            {{ $item['label'] }}
                            <x-icon name="chevron-down" :size="14" />
                        </a>

                        <div x-show="koleksiOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="absolute top-full left-0 mt-0 w-64 bg-panel border border-line rounded-lg shadow-lg z-50 overflow-hidden"
                            style="display: none;" x-cloak>
                            <div class="py-2">
                                <a href="{{ route('koleksi.index', ['kategori' => 'jas-adat']) }}"
                                    class="block px-4 py-2.5 text-sm text-ink hover:bg-cream hover:text-gold transition-colors">
                                    Jas Adat
                                </a>
                                <a href="{{ route('koleksi.index', ['kategori' => 'gown-pengantin']) }}"
                                    class="block px-4 py-2.5 text-sm text-ink hover:bg-cream hover:text-gold transition-colors">
                                    Gown Pengantin
                                </a>
                                <a href="{{ route('koleksi.index', ['kategori' => 'siger']) }}"
                                    class="block px-4 py-2.5 text-sm text-ink hover:bg-cream hover:text-gold transition-colors">
                                    Siger
                                </a>
                                <a href="{{ route('koleksi.index', ['kategori' => 'beskap']) }}"
                                    class="block px-4 py-2.5 text-sm text-ink hover:bg-cream hover:text-gold transition-colors">
                                    Beskap
                                </a>
                                <div class="border-t border-line my-2"></div>
                                <a href="{{ route('koleksi.index') }}"
                                    class="block px-4 py-2.5 text-sm font-semibold text-gold hover:bg-cream transition-colors">
                                    Lihat Semua Koleksi →
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route($item['route']) }}"
                        class="px-4 py-3 text-xs font-semibold uppercase tracking-wide transition-colors min-h-[50px] inline-flex items-center gap-1.5
                          {{ $item['active'] ? 'text-gold border-b-2 border-gold' : 'text-ink hover:text-gold' }}">
                        @if ($item['label'] === 'Beranda')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                        @elseif($item['label'] === 'Koleksi')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3l-3 4 2 14h8l2-14-3-4M9 3h6M9 3v4M15 3v4M12 7v14" />
                            </svg>
                        @elseif($item['label'] === 'Cara Sewa')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v4m0 0l-6 10a1 1 0 00.87 1.5h10.26a1 1 0 00.87-1.5L12 8zM8 4h8" />
                            </svg>
                        @elseif($item['label'] === 'Tentang Kami')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($item['label'] === 'Testimoni')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endif
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </nav>

    {{-- NAV MOBILE --}}
    <div x-show="mobileOpen" x-collapse x-cloak class="md:hidden border-t border-line bg-panel">
        <nav class="max-w-7xl mx-auto px-4 py-3 flex flex-col">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition-colors
                      {{ $item['active'] ? 'text-gold bg-cream' : 'text-ink hover:bg-cream hover:text-gold' }}">

                    @if ($item['label'] === 'Beranda')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                        </svg>
                    @elseif($item['label'] === 'Koleksi')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3l-3 4 2 14h8l2-14-3-4M9 3h6M9 3v4M15 3v4M12 7v14" />
                        </svg>
                    @elseif($item['label'] === 'Cara Sewa')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v4m0 0l-6 10a1 1 0 00.87 1.5h10.26a1 1 0 00.87-1.5L12 8zM8 4h8" />
                        </svg>
                    @elseif($item['label'] === 'Tentang Kami')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($item['label'] === 'Testimoni')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endif

                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

            <form action="{{ route('koleksi.index') }}" method="GET" class="mt-3 relative">
                <x-icon name="search" :size="18"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none" />

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..."
                    class="w-full pl-8 pr-2 sm:pl-10 sm:pr-4 py-2 rounded-full border border-line bg-cream text-sm sm:text-base placeholder:text-muted focus:outline-none focus:border-gold">
            </form>
        </nav>
    </div>

</header>