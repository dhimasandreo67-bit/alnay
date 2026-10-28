@php
    use Illuminate\Support\Facades\Route;

  $navItems = [
    ['label' => 'Beranda',     'route' => 'beranda',      'active' => request()->routeIs('beranda')],
    ['label' => 'Koleksi',     'route' => 'koleksi.index','active' => request()->routeIs('koleksi.*')],
    ['label' => 'Cara Sewa',   'route' => 'cara-sewa',    'active' => request()->routeIs('cara-sewa')],
    ['label' => 'Tentang Kami','route' => 'tentang',      'active' => request()->routeIs('tentang')],
    ['label' => 'Testimoni',   'route' => 'testimoni',    'active' => request()->routeIs('testimoni')],
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
    Studio: {{ config('alnay.studio.jam_buka') }}
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
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">

        {{-- Logo --}}
        <a href="{{ route('beranda') }}" class="flex items-center gap-2 shrink-0 min-h-[44px]">
            <img src="{{ asset('images/home/alnaylogo.png') }}" alt="ALNAY.ID" class="w-11 h-11 rounded-md border border-[#c99a32] bg-[#fbf6e9] object-contain p-1">
            <span class="hidden sm:block leading-tight">
                <span class="block font-display font-bold text-ink text-lg tracking-tight">ALNAY.ID</span>
                <span class="block text-[9px] uppercase tracking-[0.2em] text-muted">Jas & Gown Berbudaya</span>
            </span>
        </a>

        {{-- Search (full panjang sampai area keranjang) --}}
        <form action="{{ route('koleksi.index') }}" method="GET" class="relative flex-1 min-w-0">
            <x-icon name="search" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none" />
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                class="w-full pl-10 pr-4 py-2.5 rounded-full border border-line bg-cream text-base placeholder:text-muted focus:outline-none focus:border-gold">
        </form>

        {{-- Aksi kanan --}}
        <div class="flex items-center gap-2 shrink-0 ml-auto">

            {{-- Keranjang — dinamis dari session --}}
            <a href="{{ route('keranjang.index') }}"
               class="relative inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-ink hover:bg-cream transition-colors"
               aria-label="Keranjang">
                <x-icon name="cart" :size="20" />
                <span class="text-sm font-medium">Keranjang</span>
                @if(isset($cartCount) && $cartCount > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-gold text-white text-[10px] font-bold flex items-center justify-center">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>

            {{-- Tombol Notifikasi --}}
            <a href="#" class="relative w-11 h-11 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors" aria-label="Notifikasi">
                <x-icon name="bell" :size="20" />
                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-red-500 border-2 border-panel"></span>
            </a>

            @guest
                <a href="{{ route('masuk') }}"
   class="hidden sm:inline-flex items-center px-5 py-2.5 text-sm font-semibold uppercase tracking-wide text-ink hover:text-gold transition-colors min-h-[44px]">
    Masuk
</a>
<a href="{{ route('daftar') }}"
   class="inline-flex items-center px-5 py-2.5 rounded-md bg-gold text-white text-sm font-semibold uppercase tracking-wide hover:bg-gold/90 transition-colors min-h-[44px]">
    Registrasi
</a>
            @endguest

            @auth
                @php
                    $role = auth()->user()->role;

                    // Tentukan route profil berdasarkan role
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
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium text-ink hover:text-gold transition-colors min-h-[44px]">
                    <x-icon name="user" :size="18" />
                    {{ $profileText }}
                </a>

                {{-- Tombol Logout --}}
                <form method="POST" action="{{ route('keluar') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-full border border-line text-sm font-medium text-ink hover:bg-cream hover:border-gold transition-colors min-h-[44px]">
                        Keluar
                    </button>
                </form>
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
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-center gap-4">
        @foreach ($navItems as $item)
            @if($item['label'] === 'Koleksi')
                {{-- KOLEKSI DENGAN DROPDOWN --}}
                <div class="relative"
                     @mouseenter="koleksiOpen = true"
                     @mouseleave="koleksiOpen = false">
                    <a href="{{ route($item['route']) }}"
                       class="px-4 py-3 text-xs font-semibold uppercase tracking-wide transition-colors min-h-[50px] inline-flex items-center gap-1.5
                              {{ $item['active'] ? 'text-gold border-b-2 border-gold' : 'text-ink hover:text-gold' }}">
                        <x-icon name="dress" :size="14" />
                        {{ $item['label'] }}
                        <x-icon name="chevron-down" :size="14" />
                    </a>

                    {{-- DROPDOWN MENU --}}
                    <div x-show="koleksiOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="absolute top-full left-0 mt-0 w-64 bg-panel border border-line rounded-lg shadow-lg z-50 overflow-hidden"
                         style="display: none;"
                         x-cloak>
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
                {{-- MENU LAIN (tanpa dropdown) --}}
                <a href="{{ route($item['route']) }}"
                   class="px-4 py-3 text-xs font-semibold uppercase tracking-wide transition-colors min-h-[50px] inline-flex items-center gap-1.5
                          {{ $item['active'] ? 'text-gold border-b-2 border-gold' : 'text-ink hover:text-gold' }}">
                    @if($item['label'] === 'Beranda')
    {{-- Icon rumah solid (terisi) --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
    </svg>
@elseif($item['label'] === 'Koleksi')
    {{-- Icon dress/gaun dengan detail --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3l-3 4 2 14h8l2-14-3-4M9 3h6M9 3v4M15 3v4M12 7v14"/>
    </svg>
@elseif($item['label'] === 'Cara Sewa')
    {{-- Icon hanger/gantungan baju --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v4m0 0l-6 10a1 1 0 00.87 1.5h10.26a1 1 0 00.87-1.5L12 8zM8 4h8"/>
    </svg>
@elseif($item['label'] === 'Tentang Kami')
    {{-- Icon tanda tanya dalam lingkaran --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
@elseif($item['label'] === 'Testimoni')
    {{-- Icon bintang --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
    </svg>
@endif
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</nav>


{{-- NAV MOBILE --}}
<div x-show="mobileOpen" x-collapse x-cloak
     class="md:hidden border-t border-line bg-panel">
    <nav class="max-w-7xl mx-auto px-4 py-3 flex flex-col">
        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition-colors
                      {{ $item['active'] ? 'text-gold bg-cream' : 'text-ink hover:bg-cream hover:text-gold' }}">

                @if($item['label'] === 'Beranda')
    {{-- Icon rumah solid (terisi) --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
    </svg>
@elseif($item['label'] === 'Koleksi')
    {{-- Icon dress/gaun dengan detail --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3l-3 4 2 14h8l2-14-3-4M9 3h6M9 3v4M15 3v4M12 7v14"/>
    </svg>
@elseif($item['label'] === 'Cara Sewa')
    {{-- Icon hanger/gantungan baju --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v4m0 0l-6 10a1 1 0 00.87 1.5h10.26a1 1 0 00.87-1.5L12 8zM8 4h8"/>
    </svg>
@elseif($item['label'] === 'Tentang Kami')
    {{-- Icon tanda tanya dalam lingkaran --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
@elseif($item['label'] === 'Testimoni')
    {{-- Icon bintang --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
    </svg>
@endif

                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <form action="{{ route('koleksi.index') }}" method="GET"
              class="mt-3 relative">
            <x-icon name="search" :size="18"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none" />

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari produk..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-full border border-line bg-cream text-base placeholder:text-muted focus:outline-none focus:border-gold">
        </form>
    </nav>
</div>

</header>