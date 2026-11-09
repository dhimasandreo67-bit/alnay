{{-- ============================================================
     HEADER ALNAY.ID — tema gelap-emas senada footer
     (Hanya tampilan berubah; route, data, & logika tetap sama)
     ============================================================ --}}
<style>
    @keyframes wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-15deg); }
        50% { transform: rotate(10deg); }
        75% { transform: rotate(-5deg); }
    }

    /* Satu putaran = tepat 1 petak motif (307px), jalan konstan tanpa jeda */
    @keyframes tapis-geser {
        from { transform: translateX(0); }
        to   { transform: translateX(-307px); }
    }

    :root {
        --al-gold: #c99a32;
        --al-gold-soft: #e6c877;
        --al-bg: #ffffff;
        --al-cream: #f5efe0;
        --al-muted: #b9ae96;
        --al-line: rgba(201, 154, 50, .28);
    }

    /* Header */
    .al-header { border-bottom: 1px solid var(--al-gold); background: rgba(255, 255, 255, .98); transition: background-color .3s ease, box-shadow .3s ease; }
    .al-header.is-scrolled {
        background: rgba(255, 255, 255, .62);
        -webkit-backdrop-filter: blur(22px) saturate(180%);
        backdrop-filter: blur(22px) saturate(180%);
        box-shadow: 0 12px 32px -14px rgba(201, 154, 50, .40), inset 0 1px 0 rgba(255, 255, 255, .7);
    }
    .al-main { position: relative; z-index: 2; }

    /* Tombol ikon bulat */
    .al-icon-btn {
        background: #fbf6e9;
        border: 1px solid rgba(201, 154, 50, .28);
        color: #15233d;
        transition: border-color .25s ease, color .25s ease, transform .25s ease, background-color .25s ease;
    }
    .al-icon-btn:hover { border-color: var(--al-gold); color: #9a6f12; background: #fff; transform: translateY(-2px); box-shadow: 0 8px 18px -8px rgba(201, 154, 50, .55); }

    /* Baris menu desktop */
    .al-tapis {
        position: absolute; left: 0; right: 0; top: calc(100% + 1px);
        height: 49px; z-index: 1;
        background: #fdf8ea;
        border-bottom: 1px solid var(--al-gold);
        box-shadow: 0 14px 24px -18px rgba(201, 154, 50, .45);
        opacity: 1;
        clip-path: inset(0 -60px -500px -60px);
        transition: transform .6s cubic-bezier(.22, .8, .3, 1), clip-path .6s cubic-bezier(.22, .8, .3, 1), opacity .5s ease, visibility 0s linear 0s;
        will-change: transform, clip-path, opacity;
    }
    /* Sembunyi: memudar sambil naik, masuk ke belakang header atas */
    .al-tapis.is-hidden {
        transform: translateY(-100%);
        opacity: 0;
        clip-path: inset(100% -60px -500px -60px);
        visibility: hidden;
        pointer-events: none;
        transition: transform .5s cubic-bezier(.4, 0, .2, 1), clip-path .5s cubic-bezier(.4, 0, .2, 1), opacity .38s ease, visibility 0s linear .5s;
    }

    /* Motif tapis (gambar asli tapis.png). Pola gambar ini berulang setiap 323px (ukuran asli);
       satu potongan sepanjang itu diambil lewat CSS (tanpa gambar baru) lalu diulang berderet.
       Karena potongannya satu periode penuh, sambungan antar petak menyatu, jadi loop mulus.
       Geser konstan (linear) dan pelan; fasenya disimpan agar tidak mulai ulang saat pindah halaman. */
    .al-tapis-motif {
        position: absolute; inset: 0; display: block; overflow: hidden;
        opacity: .42;
        mix-blend-mode: multiply;
        -webkit-mask-image: linear-gradient(90deg, #000 0%, rgba(0,0,0,.55) 12%, transparent 30%, transparent 70%, rgba(0,0,0,.55) 88%, #000 100%);
        mask-image: linear-gradient(90deg, #000 0%, rgba(0,0,0,.55) 12%, transparent 30%, transparent 70%, rgba(0,0,0,.55) 88%, #000 100%);
        pointer-events: none;
    }
    .al-tapis-track {
        display: flex; width: max-content; height: 100%;
        animation: tapis-geser 11s linear infinite;
        will-change: transform;
    }
    .al-tapis-track i {
        display: block; flex: none; width: 307px; height: 100%;
        background: url("{{ asset('images/home/tapis.png') }}") -323.16px bottom / 2111.1px 63.68px no-repeat;
    }

    /* Lapisan krem tipis agar tulisan menu tetap jelas */
    .al-tapis::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(90deg,
            rgba(253, 248, 234, 0) 0%,
            rgba(253, 248, 234, .55) 25%,
            rgba(253, 248, 234, .85) 50%,
            rgba(253, 248, 234, .55) 75%,
            rgba(253, 248, 234, 0) 100%);
        pointer-events: none;
    }

    /* Isi menu di atas motif dan lapisan krem */
    .al-tapis > div { position: relative; z-index: 1; transition: opacity .4s ease; }
    .al-tapis.is-hidden > div { opacity: 0; transition: opacity .25s ease; }

       /* Item menu desktop */
    .al-nav {
        position: relative;
        color: #0a0a0a;
        font-weight: 800;
        border-radius: 999px;
        text-shadow: 0 0 6px rgba(253, 248, 234, .95);
        /* Garis bawah hover digambar lewat background (bukan ::after) supaya tidak bisa
           tertimpa/terpotong aturan lain, dan melebar halus dari kiri ke kanan. */
        background-image: linear-gradient(var(--al-gold), var(--al-gold));
        background-repeat: no-repeat;
        background-position: 1rem calc(100% - 7px);
        background-size: 0 2px;
        transition: color .25s ease, background-color .25s ease, background-size .4s cubic-bezier(.22, .8, .3, 1);
    }
    .al-nav:hover { color: #000; background-color: rgba(255, 255, 255, .92); background-size: calc(100% - 2rem) 2px; }
    /* Tab terpilih: latar sama seperti semula, hanya warna teks yang mengikuti tema.
       Ganti --al-active-text kalau mau warna lain. */
    .al-nav.is-active {
        --al-active-text: #8a6410;
        color: var(--al-active-text);
        background-color: #f7e9bd;
        background-size: 0 2px;
        box-shadow: inset 0 0 0 1px var(--al-gold);
        text-shadow: none;
    }
    .al-nav.is-active, .al-nav.is-active * { color: var(--al-active-text) !important; text-shadow: none !important; }
    .al-nav.is-active:hover { background-color: #f7e9bd; background-size: 0 2px; }

    /* Dropdown */
    .al-dd-panel { background: #fff; border: 1px solid var(--al-line); box-shadow: 0 24px 48px -18px rgba(21, 35, 61, .22), 0 0 0 1px rgba(201, 154, 50, .06); }
    .al-dd-link { color: #15233d; transition: background-color .2s ease, color .2s ease, padding-left .2s ease; }
    .al-dd-link:hover { background: rgba(201, 154, 50, .10); color: #8a6410; padding-left: 1.25rem; }

    .al-search:focus { box-shadow: 0 0 0 3px rgba(201, 154, 50, .18); }

    @media (prefers-reduced-motion: reduce) {
        .al-header, .al-main, .al-nav, .al-icon-btn, .al-tapis, .al-tapis.is-hidden, .al-tapis > div { transition: none !important; }
        .al-tapis-track { animation: none; }
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
<div class="bg-[#fbf6e9] text-[#15233d] text-xs border-b border-[#c99a32]/25">
    <div class="max-w-7xl mx-auto px-4 py-2 flex flex-col sm:flex-row items-center justify-between gap-1">
        <p class="text-center sm:text-left flex items-center gap-2 text-[#8a6410]">
            <span class="w-2 h-2 rounded-full bg-gold inline-block shadow-[0_0_8px_rgba(201,154,50,.9)]"></span>
            {{ config('alnay.promo_bar.teks') }}
        </p>
        <div class="flex items-center gap-4 text-[#6b7280]">
            <span class="hidden sm:inline-flex items-center gap-1.5">
                <span class="text-[#a97c1a]">Toko:</span> {{ config('toko.alnay.jam_buka') }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="text-[#a97c1a]"><x-icon name="whatsapp" :size="14" /></span>
                WA CS: {{ config('alnay.kontak.wa_cs_display') }}
            </span>
        </div>
    </div>
</div>

{{-- HEADER UTAMA --}}
<header x-data="alnayHeader()"
    @scroll.window.passive="onScroll()"
    :class="{ 'is-scrolled': scrolled }"
    class="al-header sticky top-0 z-50">

    <div class="al-main max-w-7xl mx-auto px-3 sm:px-4 py-3 flex items-center gap-2 sm:gap-4">

        {{-- Logo --}}
        <a href="{{ route('beranda') }}" class="flex items-center gap-2.5 shrink-0 min-h-[44px]">
            <img src="{{ asset('images/home/alnaylogo.png') }}" alt="ALNAY.ID"
                class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl border border-[#c99a32] bg-[#fbf6e9] object-contain p-1 shadow-[0_6px_20px_-6px_rgba(201,154,50,.65)]">
            <span class="hidden sm:block leading-tight">
                <span class="block font-display font-bold text-[#15233d] text-lg tracking-tight">ALNAY.ID</span>
                <span class="block text-[9px] uppercase tracking-[0.24em] text-[#a97c1a]">Jas & Gown Berbudaya</span>
            </span>
        </a>

        {{-- Search --}}
        <form action="{{ route('koleksi.index') }}" method="GET" class="relative flex-1 min-w-0">
            <x-icon name="search" :size="18"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-[#a97c1a] pointer-events-none" />
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                class="al-search w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 rounded-full border border-[#c99a32]/25 bg-[#fbf6e9] text-[#15233d] text-base placeholder:text-[#9a9384] focus:outline-none focus:border-[#c99a32] transition-shadow">
        </form>

        {{-- Aksi kanan --}}
        <div class="flex items-center gap-1 sm:gap-2 shrink-0 ml-auto">

            {{-- Keranjang (Gabungan Beli & Sewa) --}}
            <a href="{{ route('keranjang.index') }}"
                class="al-icon-btn relative inline-flex items-center justify-center w-9 h-9 sm:w-auto sm:h-11 sm:px-4 sm:gap-2 rounded-full"
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
                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-gold text-[#1a1408] text-[10px] font-bold flex items-center justify-center border-2 border-white">
                        {{ $totalKeranjang }}
                    </span>
                @endif
            </a>

            {{-- Notifikasi --}}
            <div class="relative" x-data="{ notifOpen: false }">
                <button type="button" @click="notifOpen = !notifOpen"
                    class="al-icon-btn group relative w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center active:scale-95"
                    aria-label="Notifikasi">
                    <div class="group-active:animate-[wiggle_0.3s_ease-in-out]">
                        <x-icon name="bell" :size="20" />
                    </div>
                    @if (isset($jumlahNotifikasi) && $jumlahNotifikasi > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-5 h-5 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center border-2 border-white">
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
                    class="al-dd-panel fixed sm:absolute left-1/2 -translate-x-1/2 sm:left-auto sm:right-0 sm:translate-x-0 top-[60px] sm:top-full mt-0 sm:mt-2 w-[calc(100vw-2rem)] max-w-[320px] rounded-2xl z-50 overflow-hidden origin-top-center sm:origin-top-right">

                    <div class="px-5 py-4 border-b border-[#c99a32]/15 flex items-center justify-between bg-white sticky top-0 z-10">
                        <h3 class="text-[13px] font-bold text-[#15233d]">Notifikasi</h3>
                        <button type="button" @click="notifOpen = false" class="text-[#6b7280] hover:text-[#8a6410] p-1 bg-gray-50 hover:bg-gray-100 rounded-full transition-colors" aria-label="Tutup">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="max-h-[320px] overflow-y-auto custom-scrollbar">
                        @forelse($notifikasi ?? [] as $n)
                            <a href="{{ route('akun.pesanan.detail', $n->id) }}"
                            class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-[#c99a32]/10 transition-colors group">
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
                                        <p class="text-[12px] font-bold text-[#15233d] truncate">{{ $n->kode_transaksi }}</p>
                                        <span class="text-[9px] text-[#9a9384] whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] text-[#6b7280]">Pesanan {{ strtoupper($n->jenis) }}</span>
                                        @if(strtolower($n->status_pembayaran) === 'pending')
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-50 text-amber-600 border border-amber-100">PENDING</span>
                                        @elseif(in_array(strtolower($n->status_pembayaran), ['lunas', 'success']))
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">LUNAS</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-gray-50 text-[#6b7280] border border-gray-200">{{ strtoupper($n->status_pembayaran) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-50 text-[#9a9384] flex items-center justify-center mx-auto mb-3">
                                    <x-icon name="bell" :size="24" />
                                </div>
                                <p class="text-xs text-[#9a9384]">Belum ada notifikasi baru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            @guest
                <a href="{{ route('masuk') }}"
                    class="hidden sm:inline-flex items-center px-5 py-2.5 text-sm font-semibold uppercase tracking-wide text-[#15233d] hover:text-[#8a6410] transition-colors min-h-[44px]">
                    Masuk
                </a>
                <a href="{{ route('daftar') }}"
                    class="inline-flex items-center justify-center px-2.5 sm:px-5 py-2 rounded-full bg-gradient-to-b from-[#e2b93e] to-[#b8860b] text-[#1a1408] text-[10px] sm:text-sm font-bold uppercase tracking-normal sm:tracking-wide shadow-[0_8px_20px_-8px_rgba(201,154,50,.8)] hover:brightness-110 transition min-h-[40px] sm:min-h-[44px]">
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
                    class="inline-flex items-center justify-center w-9 h-9 sm:w-auto sm:h-11 sm:px-4 rounded-full border border-[#c99a32]/40 text-sm font-semibold text-[#8a6410] hover:bg-[#c99a32]/15 transition-colors">
                    <x-icon name="user" :size="18" />
                    <span class="hidden sm:inline ml-1.5">{{ $profileText }}</span>
                </a>

                <form method="POST" action="{{ route('keluar') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="al-icon-btn inline-flex items-center justify-center gap-1.5 w-9 h-9 sm:w-auto sm:h-11 sm:px-4 rounded-full text-sm font-medium">
                        <span class="hidden sm:inline">Keluar</span>
                        <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            @endauth

            {{-- Hamburger --}}
            <button type="button" @click="mobileOpen = !mobileOpen"
                class="al-icon-btn md:hidden w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center"
                aria-label="Menu">
                <template x-if="!mobileOpen"><x-icon name="menu" :size="22" /></template>
                <template x-if="mobileOpen"><x-icon name="close" :size="22" /></template>
            </button>
        </div>
    </div>

    {{-- NAV DESKTOP --}}
    <nav class="al-tapis hidden md:block" :class="{ 'is-hidden': hideMenu }">
        <span class="al-tapis-motif" aria-hidden="true">
            <span class="al-tapis-track" id="alTapisTrack">@for ($t = 0; $t < 14; $t++)<i></i>@endfor</span>
        </span>
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-center gap-2">
            @foreach ($navItems as $item)
                @if ($item['label'] === 'Koleksi')
                    <div class="relative" @mouseenter="koleksiOpen = true" @mouseleave="koleksiOpen = false">
                        <a href="{{ route($item['route']) }}"
                            class="al-nav px-4 py-2.5 text-xs font-semibold uppercase tracking-wide inline-flex items-center gap-1.5 {{ $item['active'] ? 'is-active' : '' }}">
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
                            class="al-dd-panel absolute top-full left-0 mt-0 w-64 rounded-xl z-50 overflow-hidden"
                            style="display: none;" x-cloak>
                            <div class="py-2">
                                <a href="{{ route('koleksi.index', ['kategori' => 'jas-adat']) }}"
                                    class="al-dd-link block px-4 py-2.5 text-sm">
                                    Jas Adat
                                </a>
                                <a href="{{ route('koleksi.index', ['kategori' => 'gown-pengantin']) }}"
                                    class="al-dd-link block px-4 py-2.5 text-sm">
                                    Gown Pengantin
                                </a>
                                <a href="{{ route('koleksi.index', ['kategori' => 'siger']) }}"
                                    class="al-dd-link block px-4 py-2.5 text-sm">
                                    Siger
                                </a>
                                <a href="{{ route('koleksi.index', ['kategori' => 'beskap']) }}"
                                    class="al-dd-link block px-4 py-2.5 text-sm">
                                    Beskap
                                </a>
                                <div class="border-t border-[#c99a32]/20 my-2"></div>
                                <a href="{{ route('koleksi.index') }}"
                                    class="al-dd-link block px-4 py-2.5 text-sm font-semibold !text-[#a97c1a] hover:!text-[#8a6410]">
                                    Lihat Semua Koleksi →
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route($item['route']) }}"
                        class="al-nav px-4 py-2.5 text-xs font-semibold uppercase tracking-wide inline-flex items-center gap-1.5 {{ $item['active'] ? 'is-active' : '' }}">
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
    <div x-show="mobileOpen" x-collapse x-cloak class="md:hidden border-t border-[#c99a32]/20 bg-white">
        <nav class="max-w-7xl mx-auto px-4 py-3 flex flex-col">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors
                      {{ $item['active'] ? 'text-[#8a6410] bg-[#c99a32]/14 ring-1 ring-[#c99a32]/30' : 'text-[#3b4358] hover:bg-[#c99a32]/10 hover:text-[#8a6410]' }}">

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
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-[#a97c1a] pointer-events-none" />

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..."
                    class="al-search w-full pl-8 pr-2 sm:pl-10 sm:pr-4 py-2 rounded-full border border-[#c99a32]/25 bg-[#fbf6e9] text-[#15233d] text-sm sm:text-base placeholder:text-[#9a9384] focus:outline-none focus:border-[#c99a32]">
            </form>
        </nav>
    </div>

</header>

<script>
    // Jaga fase animasi tapis agar tidak mulai ulang (berhenti sebentar) tiap pindah halaman.
    // Angka 11 harus sama dengan durasi (detik) .al-tapis-track di CSS.
    (function () {
        try {
            var D = 11, k = 'alnayTapisStart', s = sessionStorage.getItem(k);
            if (!s) { s = String(Date.now()); sessionStorage.setItem(k, s); }
            var t = document.getElementById('alTapisTrack');
            if (t) t.style.animationDelay = (-(((Date.now() - Number(s)) / 1000) % D)) + 's';
        } catch (e) {}
    })();
</script>

{{-- Pemesan ruang baris menu (49px = tinggi .al-tapis) supaya halaman tidak melompat saat menu disembunyikan --}}
<div class="hidden md:block h-[49px]" aria-hidden="true"></div>

<script>
    function alnayHeader() {
        return {
            mobileOpen: false,
            koleksiOpen: false,
            scrolled: false,
            hideMenu: false,
            last: 0,

            init() {
                this.last = window.scrollY;
                this.scrolled = this.last > 30;
            },

            // Kaca saat di-scroll; baris menu sembunyi saat turun, muncul lagi saat naik.
            onScroll() {
                const y = window.scrollY;
                this.scrolled = y > 30;

                if (this.koleksiOpen || y < 80) {
                    this.hideMenu = false;
                } else {
                    const d = y - this.last;
                    if (d > 6) this.hideMenu = true;
                    else if (d < -6) this.hideMenu = false;
                }
                this.last = y;
            },
        };
    }
</script>