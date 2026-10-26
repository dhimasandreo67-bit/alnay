@php
    use Illuminate\Support\Facades\Route;

    $navItems = [
        ['label' => 'Beranda',     'route' => 'beranda',      'active' => request()->routeIs('beranda')],
        ['label' => 'Koleksi',     'route' => 'koleksi.index','active' => request()->routeIs('koleksi.*')],
        ['label' => 'Cara Sewa',   'route' => 'cara-sewa',    'active' => request()->routeIs('cara-sewa')],
        ['label' => 'Tentang Kami','route' => 'tentang',      'active' => request()->routeIs('tentang')],
        ['label' => 'Testimoni',   'route' => 'testimoni',    'active' => request()->routeIs('testimoni')],
        ['label' => 'Event',       'route' => 'event',        'active' => false],
    ];
@endphp

{{-- PROMO BAR --}}
<div class="bg-ink text-cream text-xs">
    <div class="max-w-7xl mx-auto px-4 py-2 flex flex-col sm:flex-row items-center justify-between gap-1">
        <p class="text-center sm:text-left">{{ config('alnay.promo_bar.teks') }}</p>
        <div class="flex items-center gap-4 text-cream/70">
            <span class="hidden sm:inline-flex items-center gap-1.5">
                <x-icon name="clock" :size="14" />
                {{ config('alnay.studio.jam_buka') }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <x-icon name="whatsapp" :size="14" />
                CS {{ config('alnay.kontak.wa_cs_display') }}
            </span>
        </div>
    </div>
</div>

{{-- HEADER UTAMA --}}
<header x-data="{ mobileOpen: false }" class="bg-panel border-b border-line sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">

        {{-- Logo --}}
        <a href="{{ route('beranda') }}" class="flex items-center gap-2 shrink-0 min-h-[44px]">
            <span class="w-9 h-9 rounded-md bg-ink text-gold flex items-center justify-center font-display font-bold text-lg">A</span>
            <span class="hidden sm:block leading-tight">
                <span class="block font-display font-bold text-ink text-lg tracking-tight">ALNAY.ID</span>
                <span class="block text-[9px] uppercase tracking-[0.2em] text-muted">Atelier Backoffice</span>
            </span>
        </a>

        {{-- Search (desktop) --}}
        <form action="#" class="hidden md:flex flex-1 max-w-md relative">
            {{-- TODO: route asli → koleksi.index --}}
            <x-icon name="search" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none" />
            <input type="text" placeholder="Cari produk..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-full border border-line bg-cream text-base placeholder:text-muted focus:outline-none focus:border-gold transition-colors">
        </form>

        {{-- Aksi kanan --}}
        <div class="flex items-center gap-2 ml-auto">

            {{-- Keranjang (tampilan saja — prompt: fitur keranjang belum ada) --}}
            <a href="{{ route('keranjang.index') }}" {{-- TODO: route asli --}}
               class="relative w-11 h-11 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors"
               aria-label="Keranjang">
                <x-icon name="cart" :size="20" />
                <span class="absolute -top-0.5 -right-0.5 w-5 h-5 rounded-full bg-gold text-white text-[10px] font-bold flex items-center justify-center">2</span>
                {{-- Badge angka dummy — nanti dari state keranjang --}}
            </a>

            @guest
                <a href="{{ route('masuk') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium text-ink hover:text-gold transition-colors min-h-[44px]">
                    <x-icon name="user" :size="18" />
                    Masuk
                </a>
                <a href="{{ route('daftar') }}"
                   class="inline-flex items-center px-5 py-2.5 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 transition-colors min-h-[44px]">
                    Daftar
                </a>
            @endguest

            @auth
                <a href="{{ route('admin.partials.dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium text-ink hover:text-gold transition-colors min-h-[44px]">
                    <x-icon name="user" :size="18" />
                    Akun
                </a>
            @endauth

            {{-- Hamburger (mobile) --}}
            <button type="button" @click="mobileOpen = !mobileOpen"
                    class="md:hidden w-11 h-11 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors"
                    aria-label="Menu">
                <template x-if="!mobileOpen"><x-icon name="menu" :size="22" /></template>
                <template x-if="mobileOpen"><x-icon name="close" :size="22" /></template>
            </button>
        </div>
    </div>

    {{-- NAV DESKTOP --}}
    <nav class="hidden md:block border-t border-line">
        <div class="max-w-7xl mx-auto px-4 flex items-center gap-1">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="px-4 py-3 text-sm font-medium transition-colors min-h-[44px] inline-flex items-center
                          {{ $item['active'] ? 'text-gold border-b-2 border-gold' : 'text-ink hover:text-gold' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    {{-- NAV MOBILE --}}
    <div x-show="mobileOpen" x-collapse x-cloak class="md:hidden border-t border-line bg-panel">
        <nav class="max-w-7xl mx-auto px-4 py-3 flex flex-col">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="px-3 py-3 rounded-lg text-sm font-medium min-h-[44px] flex items-center
                          {{ $item['active'] ? 'bg-cream text-gold' : 'text-ink hover:bg-cream' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach

            <form action="#" class="md:hidden mt-3 relative">
                <x-icon name="search" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none" />
                <input type="text" placeholder="Cari produk..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-full border border-line bg-cream text-base placeholder:text-muted focus:outline-none focus:border-gold">
            </form>
        </nav>
    </div>
</header>