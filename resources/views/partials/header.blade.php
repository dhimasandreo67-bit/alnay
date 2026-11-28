{{-- ============================================================
     HEADER ALNAY.ID — tema gelap-emas senada footer
     ============================================================ --}}
<style>
    @keyframes wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-15deg); }
        50% { transform: rotate(10deg); }
        75% { transform: rotate(-5deg); }
    }
    @keyframes tapis-geser {
        from { transform: translateX(0); }
        to   { transform: translateX(-307px); }
    }
    @keyframes marquee-mobile {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    
    :root {
        --al-gold: #c99a32;
        --al-gold-soft: #e6c877;
        --al-bg: #ffffff;
        --al-cream: #f5efe0;
        --al-muted: #b9ae96;
        --al-line: rgba(201, 154, 50, .28);

        /* Kembalikan variabel opacity tapis agar tengahnya transparan */
        --motif-opacity: .70;   
        --motif-tepi: .55;      
        --motif-tengah: .10;    /* Dibuat 0.15 agar teks menu sangat jelas */
        --motif-kiri: 25%;      
        --motif-kanan: 75%;     
    }

    /* --- PROMO BAR --- */
    .animate-marquee-mobile { animation: marquee-mobile 20s linear infinite; }
    .animate-marquee-mobile:hover { animation-play-state: paused; }

    /* --- HEADER UTAMA --- */
    .al-header { border-bottom: 1px solid var(--al-gold); background: rgba(255, 255, 255, .98); transition: background-color .3s ease, box-shadow .3s ease; }
    .al-header.is-scrolled {
        background: rgba(255, 255, 255, .85);
        -webkit-backdrop-filter: blur(22px) saturate(180%);
        backdrop-filter: blur(22px) saturate(180%);
        box-shadow: 0 12px 32px -14px rgba(201, 154, 50, .40), inset 0 1px 0 rgba(255, 255, 255, .7);
    }
    .al-main { position: relative; z-index: 2; }
    
    .al-icon-btn {
        background: #fbf6e9;
        border: 1px solid rgba(201, 154, 50, .28);
        color: #15233d;
        transition: all .25s ease;
    }
    .al-icon-btn:hover { border-color: var(--al-gold); color: #9a6f12; background: #fff; transform: translateY(-2px); box-shadow: 0 8px 18px -8px rgba(201, 154, 50, .55); }

    /* Badge jumlah notifikasi: bulat untuk 1 digit, melebar rapi untuk 2-3 digit */
    .al-badge {
        position: absolute; top: -4px; right: -4px; z-index: 2;
        box-sizing: border-box; min-width: 18px; height: 18px; padding: 0 4px;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #fff; border-radius: 9999px;
        background: #f43f5e; color: #fff;
        font-size: 9px; font-weight: 700; line-height: 1; font-variant-numeric: tabular-nums;
        pointer-events: none;
    }
    @media (min-width: 640px) {
        .al-badge { top: -2px; right: -2px; min-width: 20px; height: 20px; padding: 0 5px; font-size: 10px; }
    }

    /* --- ANIMASI TAPIS (BISA HILANG KE ATAS) --- */
    .al-tapis {
        position: absolute; left: 0; right: 0; top: calc(100% + 1px);
        height: 49px; z-index: 1;
        background: #fdf8ea;
        border-bottom: 1px solid var(--al-gold);
        box-shadow: 0 14px 24px -18px rgba(201, 154, 50, .45);
        opacity: 1;
        clip-path: inset(0 -60px -500px -60px);
        transition: transform .5s cubic-bezier(0.4, 0, 0.2, 1), clip-path .5s cubic-bezier(0.4, 0, 0.2, 1), opacity .4s ease, visibility 0s linear 0s;
        will-change: transform, clip-path, opacity;
    }
    /* Class ini yang membuat menu meluncur ke atas dan memudar */
    .al-tapis.is-hidden {
        transform: translateY(-100%);
        opacity: 0;
        clip-path: inset(100% -60px -500px -60px);
        visibility: hidden;
        pointer-events: none;
        transition: transform .5s cubic-bezier(0.4, 0, 0.2, 1), clip-path .5s cubic-bezier(0.4, 0, 0.2, 1), opacity .38s ease, visibility 0s linear .5s;
    }

    .al-tapis-motif {
        position: absolute; inset: 0; display: block; overflow: hidden;
        opacity: var(--motif-opacity);
        mix-blend-mode: multiply;
        pointer-events: none;
    }
    .al-tapis-track {
        display: flex; width: max-content; height: 100%;
        animation: tapis-geser 11s linear infinite; will-change: transform;
    }
    .al-tapis-track i {
        display: block; flex: none; width: 307px; height: 100%;
        background: url("{{ asset('images/home/tapis.png') }}") -323.16px bottom / 2111.1px 63.68px no-repeat;
    }
    
    /* Masking tengah tapis agar transparan */
    .al-tapis::after {
        content: ''; position: absolute; inset: 0; z-index: 0;
        background: linear-gradient(90deg,
            rgb(253 248 234 / calc(1 - var(--motif-tepi))) 0%,
            rgb(253 248 234 / calc(1 - var(--motif-tepi))) 12%,
            rgb(253 248 234 / calc(1 - var(--motif-tengah))) var(--motif-kiri),
            rgb(253 248 234 / calc(1 - var(--motif-tengah))) var(--motif-kanan),
            rgb(253 248 234 / calc(1 - var(--motif-tepi))) 88%,
            rgb(253 248 234 / calc(1 - var(--motif-tepi))) 100%);
        pointer-events: none;
    }
    .al-tapis > div { position: relative; z-index: 1; transition: opacity .4s ease; }
    .al-tapis.is-hidden > div { opacity: 0; transition: opacity .25s ease; }

    /* --- IKON (komponen x-icon) --- */
    .al-ikon { flex: none; transition: transform .25s ease; }
    .al-icon-btn:hover .al-ikon { transform: scale(1.08); }
    .al-icon-btn:hover .al-ikon-bell { animation: wiggle .6s ease-in-out; transform-origin: 50% 10%; }
    .al-nav .al-ikon { color: #a97c1a; transition: transform .3s cubic-bezier(.22, .8, .3, 1), color .25s ease; }
    .al-nav:hover .al-ikon { transform: translateY(-1px) scale(1.12); }

    /* --- NAVIGASI DESKTOP --- */
    .al-nav {
        position: relative; color: #0a0a0a; font-weight: 800; border-radius: 999px;
        text-shadow: 0 0 6px rgba(253, 248, 234, .95);
        background-image: linear-gradient(var(--al-gold), var(--al-gold));
        background-repeat: no-repeat; background-position: 1rem calc(100% - 7px); background-size: 0 2px;
        transition: color .25s ease, background-color .25s ease, background-size .4s cubic-bezier(.22, .8, .3, 1);
    }
    .al-nav:hover { color: #000; background-color: rgba(255, 255, 255, .92); background-size: calc(100% - 2rem) 2px; }
    .al-nav.is-active { --al-active-text: #8a6410; color: var(--al-active-text); background-color: #f7e9bd; background-size: 0 2px; box-shadow: inset 0 0 0 1px var(--al-gold); text-shadow: none; }
    .al-nav.is-active, .al-nav.is-active * { color: var(--al-active-text) !important; text-shadow: none !important; }
    
    .al-dd-panel { background: #fff; border: 1px solid var(--al-line); box-shadow: 0 24px 48px -18px rgba(21, 35, 61, .22), 0 0 0 1px rgba(201, 154, 50, .06); }
    .al-dd-link { color: #15233d; transition: background-color .2s ease, color .2s ease, padding-left .2s ease; }
    .al-dd-link:hover { background: rgba(201, 154, 50, .10); color: #8a6410; padding-left: 1.25rem; }
    .al-search:focus { box-shadow: 0 0 0 3px rgba(201, 154, 50, .18); }

    @media (prefers-reduced-motion: reduce) {
        .al-header, .al-main, .al-nav, .al-icon-btn, .al-tapis, .al-tapis.is-hidden, .al-tapis > div { transition: none !important; }
        .al-tapis-track, .animate-marquee-mobile { animation: none; }
        .al-ikon { transition: none !important; animation: none !important; }
    }
</style>

@php
    use Illuminate\Support\Facades\Route;
    // Nama pengguna yang sedang login (untuk sapaan di promo bar); null jika belum login
    $penggunaLogin = auth()->user();
    $namaUser = null;
    if ($penggunaLogin) {
        $atribut = $penggunaLogin->getAttributes();
        $namaMentah = trim((string) ($atribut['nama'] ?? $atribut['name'] ?? $atribut['username'] ?? ''));
        $namaUser = $namaMentah !== '' ? \Illuminate\Support\Str::limit($namaMentah, 24) : null;
    }

    $navItems = [
        ['label' => 'Beranda', 'route' => 'beranda', 'ikon' => 'home', 'active' => request()->routeIs('beranda')],
        ['label' => 'Koleksi', 'route' => 'koleksi.index', 'ikon' => 'dress', 'active' => request()->routeIs('koleksi.*')],
        ['label' => 'Cara Sewa', 'route' => 'cara-sewa', 'ikon' => 'calendar-check', 'active' => request()->routeIs('cara-sewa')],
        ['label' => 'Tentang Kami', 'route' => 'tentang', 'ikon' => 'info', 'active' => request()->routeIs('tentang')],
        ['label' => 'Testimoni', 'route' => 'testimoni', 'ikon' => 'testimoni', 'active' => request()->routeIs('testimoni')],
    ];
@endphp

{{-- ============================================================
     PROMO BAR
     ============================================================ --}}
<div class="bg-[#fbf6e9] text-[#15233d] text-xs border-b border-[#c99a32]/25 overflow-hidden">
    
    {{-- Tampilan MOBILE --}}
    <div class="md:hidden py-1.5 relative flex">
        <div class="animate-marquee-mobile flex items-center whitespace-nowrap text-[10px]">
            <div class="flex items-center gap-4 px-2">
                <span class="text-[#8a6410] flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#c99a32] inline-block"></span>
                    @if ($namaUser)Selamat datang <strong class="font-semibold">{{ $namaUser }}</strong> di {{ config('alnay.brand.nama') }}@else{{ config('alnay.promo_bar.teks') }}@endif
                </span>
                <span class="text-[#a97c1a] flex items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    WA CS: {{ config('alnay.kontak.wa_cs_display') }}
                </span>
                <span class="text-[#6b7280]">Toko: {{ config('alnay.toko.jam_buka') }}</span>
                <span class="text-gray-300 mx-1">•</span>
            </div>
            <div class="flex items-center gap-4 px-2">
                <span class="text-[#8a6410] flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#c99a32] inline-block"></span>
                    @if ($namaUser)Selamat datang <strong class="font-semibold">{{ $namaUser }}</strong> di {{ config('alnay.brand.nama') }}@else{{ config('alnay.promo_bar.teks') }}@endif
                </span>
                <span class="text-[#a97c1a] flex items-center gap-1">
                    <x-icon name="whatsapp" :size="12" /> WA CS: {{ config('alnay.kontak.wa_cs_display') }}
                </span>
                <span class="text-[#6b7280]">Toko: {{ config('alnay.toko.jam_buka') }}</span>
                <span class="text-gray-300 mx-1">•</span>
            </div>
        </div>
    </div>

    {{-- Tampilan DESKTOP --}}
    <div class="hidden md:flex max-w-7xl mx-auto px-4 py-2 items-center justify-between">
        <p class="flex items-center gap-2 text-[#8a6410]">
            <span class="w-2 h-2 rounded-full bg-[#c99a32] inline-block shadow-[0_0_8px_rgba(201,154,50,.9)]"></span>
            @if ($namaUser)Selamat datang <strong class="font-semibold">{{ $namaUser }}</strong> di {{ config('alnay.brand.nama') }}@else{{ config('alnay.promo_bar.teks') }}@endif
        </p>
        <div class="flex items-center gap-4 text-[#6b7280]">
            <span class="inline-flex items-center gap-1.5"><span class="text-[#a97c1a]">Toko:</span> {{ config('alnay.toko.jam_buka') }}</span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#a97c1a]" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                WA CS: {{ config('alnay.kontak.wa_cs_display') }}
            </span>
        </div>
    </div>
</div>

{{-- ============================================================
     HEADER UTAMA
     ============================================================ --}}
<header x-data="alnayHeader()" @scroll.window.passive="onScroll()" :class="{ 'is-scrolled': scrolled }" class="al-header sticky top-0 z-50">
        <div class="al-main max-w-7xl mx-auto px-3 sm:px-4 py-2 sm:py-3 flex items-center gap-2 sm:gap-4">

        {{-- Logo --}}
        <a href="{{ route('beranda') }}" class="flex items-center gap-2 shrink-0 min-h-[40px] sm:min-h-[44px]">
            <img src="{{ asset('images/home/alnaylogo.png') }}" alt="{{ config('alnay.brand.nama') }}" class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl border border-[#c99a32] bg-[#fbf6e9] object-contain p-1 shadow-[0_6px_20px_-6px_rgba(201,154,50,.65)]">
            <span class="hidden sm:block leading-tight">
                <span class="block font-display font-bold text-[#15233d] text-lg tracking-tight">{{ config('alnay.brand.nama') }}</span>
                <span class="block text-[9px] uppercase tracking-[0.24em] text-[#a97c1a]">{{ config('alnay.brand.tagline') }}</span>
            </span>
        </a>

        {{-- Search Bar (Tampil di HP & Desktop) --}}
        <form action="{{ route('koleksi.index') }}" method="GET" class="relative flex-1 min-w-0 mx-1 sm:mx-0">
            <x-icon name="search" :size="16" class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[#a97c1a] pointer-events-none sm:w-[18px] sm:h-[18px]" />
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari busana..."
                class="al-search w-full pl-8 sm:pl-10 pr-2 sm:pr-4 py-1.5 sm:py-2.5 rounded-full border border-[#c99a32]/25 bg-[#fbf6e9] text-[#15233d] text-xs sm:text-sm placeholder:text-[#9a9384] focus:outline-none focus:border-[#c99a32] transition-shadow">
        </form>

        {{-- Aksi Kanan --}}
        <div class="flex items-center gap-1 sm:gap-2 shrink-0 ml-auto">
            
            {{-- Keranjang (Hanya Ikon di HP) --}}
            <a href="{{ route('keranjang.index') }}" class="al-icon-btn relative inline-flex items-center justify-center w-8 h-8 sm:w-auto sm:h-11 sm:px-4 sm:gap-2 rounded-full" aria-label="Keranjang">
                <x-icon name="bag" :size="18" class="sm:w-[22px] sm:h-[22px]" />
                <span class="hidden sm:inline text-sm font-medium">Keranjang</span>
                @php
                    $totalKeranjang = 0;
                    if (isset($cartCount) && $cartCount > 0) $totalKeranjang += $cartCount;
                    if (session()->has('keranjang_sewa')) $totalKeranjang += count(session('keranjang_sewa'));
                @endphp
                @if ($totalKeranjang > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-[#c99a32] text-[#1a1408] text-[9px] sm:text-[10px] font-bold flex items-center justify-center border-2 border-white">
                        {{ $totalKeranjang }}
                    </span>
                @endif
            </a>

            {{-- Notifikasi --}}
            @php
                $notifData = [
                    'unread'        => (int) ($jumlahNotifikasi ?? 0),
                    'total'         => (int) ($totalNotifikasi ?? collect($notifikasi ?? [])->count()),
                    'csrf'          => csrf_token(),
                    'urlBacaSemua'  => route('akun.notifikasi.baca-semua'),
                    'urlHapusSemua' => route('akun.notifikasi.hapus-semua'),
                    'items'         => collect($notifikasi ?? [])->map(fn ($n) => [
                        'id'       => $n->id,
                        'kode'     => $n->kode_transaksi,
                        'jenis'    => $n->jenis,
                        'waktu'    => $n->created_at?->diffForHumans(),
                        'dibaca'   => ! empty($n->dibaca_at),
                        'urlBuka'  => route('akun.notifikasi.buka', $n->id),
                        'urlHapus' => route('akun.notifikasi.hapus', $n->id),
                    ])->values()->all(),
                ];
            @endphp
            <div class="relative" x-data="notifLonceng(@js($notifData))">
                <button type="button" @click="notifOpen = !notifOpen" class="al-icon-btn group relative w-8 h-8 sm:w-11 sm:h-11 rounded-full flex items-center justify-center active:scale-95" aria-label="Notifikasi" :aria-expanded="notifOpen">
                    <div class="group-active:animate-[wiggle_0.3s_ease-in-out]"><x-icon name="bell" :size="18" class="sm:w-[22px] sm:h-[22px]" /></div>
                    {{-- Badge: hanya jumlah notifikasi yang BELUM dibuka --}}
                    <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread" class="al-badge"></span>
                </button>

                {{-- Dropdown Notif --}}
                <div x-show="notifOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-90" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-90" @click.outside="notifOpen = false" @keydown.escape.window="notifOpen = false" x-cloak class="al-dd-panel fixed sm:absolute left-1/2 -translate-x-1/2 sm:left-auto sm:right-0 sm:translate-x-0 top-[60px] sm:top-full mt-0 sm:mt-2 w-[calc(100vw-2rem)] max-w-[320px] rounded-2xl z-50 overflow-hidden origin-top-center sm:origin-top-right">
                    <div class="px-5 py-4 border-b border-[#c99a32]/15 flex items-center justify-between bg-white sticky top-0 z-10">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[13px] font-bold text-[#15233d]">Notifikasi</h3>
                            <span x-show="unread > 0" x-cloak x-text="unread + ' baru'" class="px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-bold"></span>
                        </div>
                        <button type="button" @click="notifOpen = false" class="text-[#6b7280] hover:text-[#8a6410] p-1 bg-gray-50 hover:bg-gray-100 rounded-full transition-colors" aria-label="Tutup">
                            <x-icon name="close" :size="16" />
                        </button>
                    </div>

                    {{-- Aksi massal --}}
                    <div x-show="items.length > 0" x-cloak class="px-5 py-2 flex items-center justify-between gap-3 bg-[#faf7f0] border-b border-[#c99a32]/15 text-[11px]">
                        <button type="button" @click="bacaSemua()" :disabled="unread === 0 || busy"
                                class="font-semibold text-[#15233d] transition-colors disabled:opacity-40 disabled:cursor-default enabled:hover:text-[#8a6410]">
                            Tandai semua dibaca
                        </button>
                        <button type="button" @click="hapusSemua()" :disabled="busy"
                                class="font-semibold transition-colors disabled:opacity-40"
                                :class="konfirmasi ? 'text-rose-600' : 'text-[#6b7280] hover:text-rose-600'"
                                x-text="konfirmasi ? 'Yakin hapus semua?' : 'Hapus semua'"></button>
                    </div>

                    <p x-show="galat" x-cloak x-text="galat" class="px-5 py-2 text-[11px] text-rose-600 bg-rose-50 border-b border-rose-100"></p>

                    <div class="max-h-[320px] overflow-y-auto custom-scrollbar">
                        <template x-for="n in items" :key="n.id">
                            <div class="flex items-center transition-colors" :class="n.dibaca ? '' : 'bg-[#c99a32]/[0.07]'">
                                <a :href="n.urlBuka" class="flex flex-1 min-w-0 items-center gap-3.5 pl-5 pr-2 py-3.5 hover:bg-[#c99a32]/10 transition-colors">
                                    <div class="relative w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="n.jenis === 'sewa' ? 'bg-amber-50 text-amber-500' : 'bg-sky-50 text-sky-500'">
                                        <span x-show="!n.dibaca" class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                                        <x-icon x-show="n.jenis === 'sewa'" name="calendar-check" :size="16" />
                                        <x-icon x-show="n.jenis !== 'sewa'" name="bag" :size="16" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-[12px] text-[#15233d] truncate" :class="n.dibaca ? 'font-medium' : 'font-bold'" x-text="n.kode"></p>
                                            <span class="text-[9px] text-[#9a9384] whitespace-nowrap" x-text="n.waktu"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] text-[#6b7280]" x-text="'Pesanan ' + String(n.jenis).toUpperCase()"></span>
                                        </div>
                                    </div>
                                </a>
                                <button type="button" @click.stop="hapus(n)" :disabled="busy"
                                        class="mr-3 w-7 h-7 shrink-0 rounded-full flex items-center justify-center text-[#9a9384] hover:text-rose-600 hover:bg-rose-50 transition-colors disabled:opacity-40"
                                        aria-label="Hapus notifikasi" title="Hapus notifikasi">
                                    <x-icon name="close" :size="14" />
                                </button>
                            </div>
                        </template>

                        <div x-show="items.length === 0" x-cloak class="py-12 text-center">
                            <div class="w-12 h-12 rounded-full bg-gray-50 text-[#9a9384] flex items-center justify-center mx-auto mb-3"><x-icon name="bell" :size="24" /></div>
                            <p class="text-xs text-[#9a9384]">Belum ada notifikasi baru.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Profil & Daftar / Login --}}
            @guest
                <a href="{{ route('masuk') }}" class="hidden md:inline-flex items-center px-4 py-2 text-sm font-semibold text-[#15233d] hover:text-[#8a6410] transition-colors">Masuk</a>
                <a href="{{ route('daftar') }}" class="hidden sm:inline-flex items-center justify-center px-4 sm:px-5 py-1.5 sm:py-2 rounded-full bg-gradient-to-b from-[#e2b93e] to-[#b8860b] text-[#1a1408] text-[11px] sm:text-sm font-bold shadow-[0_8px_20px_-8px_rgba(201,154,50,.8)] hover:brightness-110 transition">Daftar</a>
            @endguest

            @auth
                @php
                    $role = auth()->user()->role;
                    if ($role === 'owner') { $profileRoute = route('owner.dashboard'); $profileText = 'Owner'; }
                    elseif ($role === 'admin') { $profileRoute = route('admin.dashboard'); $profileText = 'Admin'; }
                    else { $profileRoute = route('akun.dashboard'); $profileText = 'Profil'; }
                @endphp

                {{-- Profil (Desktop) --}}
                <a href="{{ $profileRoute }}" class="hidden md:inline-flex items-center justify-center w-9 h-9 sm:w-auto sm:h-11 sm:px-4 rounded-full border border-[#c99a32]/40 text-sm font-semibold text-[#8a6410] hover:bg-[#c99a32]/15 transition-colors">
                    <x-icon name="user" :size="20" />
                    <span class="hidden sm:inline ml-1.5">{{ $profileText }}</span>
                </a>

                {{-- Keluar (Desktop) --}}
                <form method="POST" action="{{ route('keluar') }}" class="hidden md:inline-block">
                    @csrf
                    <button type="submit" class="al-icon-btn inline-flex items-center justify-center w-9 h-9 sm:w-auto sm:h-11 sm:px-4 rounded-full text-sm font-medium">
                        <x-icon name="logout" :size="18" />
                        <span class="hidden sm:inline ml-1.5">Keluar</span>
                    </button>
                </form>
            @endauth

            {{-- Hamburger Menu Mobile --}}
            <button type="button" @click="mobileOpen = !mobileOpen" class="al-icon-btn md:hidden w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center" aria-label="Menu">
                <template x-if="!mobileOpen"><x-icon name="menu" :size="20" class="sm:w-[20px] sm:h-[20px]" /></template>
                <template x-if="mobileOpen"><x-icon name="close" :size="20" class="sm:w-[20px] sm:h-[20px]" /></template>
            </button>
        </div>
    </div>

    {{-- ============================================================
         NAVIGASI DESKTOP (ANIMASI MEMUDAR KE ATAS)
         ============================================================ --}}
    {{-- PERHATIKAN BAGIAN :class="{ 'is-hidden': hideMenu }" --}}
    <nav class="al-tapis hidden md:block" :class="{ 'is-hidden': hideMenu }">
        <span class="al-tapis-motif" aria-hidden="true">
            <span class="al-tapis-track" id="alTapisTrack">@for ($t = 0; $t < 14; $t++)<i></i>@endfor</span>
        </span>
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-center gap-2">
            @foreach ($navItems as $item)
                @if ($item['label'] === 'Koleksi')
                    <div class="relative" @mouseenter="koleksiOpen = true" @mouseleave="koleksiOpen = false">
                        <a href="{{ route($item['route']) }}" class="al-nav px-4 py-2.5 text-xs font-semibold uppercase tracking-wide inline-flex items-center gap-1.5 {{ $item['active'] ? 'is-active' : '' }}">
                            <x-icon name="dress" :size="16" />
                            {{ $item['label'] }}
                            <x-icon name="chevron-down" :size="14" />
                        </a>
                        <div x-show="koleksiOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="al-dd-panel absolute top-full left-0 mt-0 w-64 rounded-xl z-50 overflow-hidden" style="display: none;" x-cloak>
                            <div class="py-2">
                                @foreach (($kategoriPopuler ?? []) as $kat)
                                    <a href="{{ route('koleksi.index', ['kategori' => $kat['slug']]) }}" class="al-dd-link block px-4 py-2.5 text-sm">{{ $kat['nama'] }}</a>
                                @endforeach
                                <div class="border-t border-[#c99a32]/20 my-2"></div>
                                <a href="{{ route('koleksi.index') }}" class="al-dd-link block px-4 py-2.5 text-sm font-semibold !text-[#a97c1a] hover:!text-[#8a6410]">Lihat Semua Koleksi →</a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route($item['route']) }}" class="al-nav px-4 py-2.5 text-xs font-semibold uppercase tracking-wide inline-flex items-center gap-1.5 {{ $item['active'] ? 'is-active' : '' }}">
                        <x-icon :name="$item['ikon']" :size="16" />
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </nav>

    {{-- ============================================================
         NAVIGASI MOBILE
         ============================================================ --}}
    <div x-show="mobileOpen" x-collapse x-cloak class="md:hidden border-t border-[#c99a32]/20 bg-white">
        <nav class="max-w-7xl mx-auto px-4 py-3 flex flex-col">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors {{ $item['active'] ? 'text-[#8a6410] bg-[#c99a32]/14 ring-1 ring-[#c99a32]/30' : 'text-[#3b4358] hover:bg-[#c99a32]/10 hover:text-[#8a6410]' }}">
                    <x-icon :name="$item['ikon']" :size="18" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

            @auth
            <div class="mt-4 border-t border-[#c99a32]/15 pt-4 flex flex-col gap-1">
                <a href="{{ $profileRoute }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-[#3b4358] hover:bg-[#c99a32]/10 hover:text-[#8a6410] transition-colors">
                    <x-icon name="user" :size="20" class="text-[#a97c1a]" />
                    <span>Akun Saya ({{ $profileText }})</span>
                </a>
                <form method="POST" action="{{ route('keluar') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 transition-colors">
                        <x-icon name="logout" :size="20" />
                        Keluar Akun
                    </button>
                </form>
            </div>
            @endauth
        </nav>
    </div>
</header>

{{-- Spacer untuk nav desktop --}}
<div class="hidden md:block h-[49px]" aria-hidden="true"></div>

<script>
    // Animasi Tapis Continuity
    (function () {
        try {
            var D = 11, k = 'alnayTapisStart', s = sessionStorage.getItem(k);
            if (!s) { s = String(Date.now()); sessionStorage.setItem(k, s); }
            var t = document.getElementById('alTapisTrack');
            if (t) t.style.animationDelay = (-(((Date.now() - Number(s)) / 1000) % D)) + 's';
        } catch (e) {}
    })();

    // Alpine.js Data & Scroll Logic
    function alnayHeader() {
        return {
            mobileOpen: false, 
            koleksiOpen: false, 
            scrolled: false, 
            hideMenu: false, // State untuk menyembunyikan menu bawah
            last: 0,
            init() { 
                this.last = window.scrollY; 
                this.scrolled = this.last > 30; 
            },
            onScroll() {
                const y = window.scrollY;
                this.scrolled = y > 30;
                
                // Logika slide-up (menyembunyikan menu tapis)
                if (this.koleksiOpen || y < 80) { 
                    this.hideMenu = false; 
                } else {
                    const d = y - this.last;
                    if (d > 6) this.hideMenu = true; // Scroll turun -> Sembunyikan
                    else if (d < -6) this.hideMenu = false; // Scroll naik -> Munculkan
                }
                this.last = y;
            },
        };
    }

    // Lonceng notifikasi pelanggan: badge = jumlah yang belum dibuka, notifikasi bisa dihapus.
    function notifLonceng(cfg) {
        return {
            notifOpen: false,
            items: cfg.items,
            unread: cfg.unread,
            total: cfg.total,
            busy: false,
            galat: '',
            konfirmasi: false,
            _timer: null,

            async kirim(url, method) {
                this.busy = true;
                this.galat = '';
                try {
                    const r = await fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': cfg.csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                    if (!r.ok) throw new Error('gagal');
                    return await r.json();
                } catch (e) {
                    this.galat = 'Gagal memproses. Coba lagi.';
                    return null;
                } finally {
                    this.busy = false;
                }
            },

            terapkan(d) {
                this.unread = d.unread;
                this.total = d.total;
            },

            async hapus(n) {
                const d = await this.kirim(n.urlHapus, 'DELETE');
                if (!d) return;
                this.items = this.items.filter(x => x.id !== n.id);
                this.terapkan(d);
                // Masih ada notifikasi lain di server yang belum tampil -> muat ulang daftar
                if (this.items.length === 0 && d.total > 0) location.reload();
            },

            async bacaSemua() {
                if (this.unread === 0) return;
                const d = await this.kirim(cfg.urlBacaSemua, 'POST');
                if (!d) return;
                this.items = this.items.map(x => ({ ...x, dibaca: true }));
                this.terapkan(d);
            },

            // Dua langkah supaya tidak terhapus tanpa sengaja
            async hapusSemua() {
                if (!this.konfirmasi) {
                    this.konfirmasi = true;
                    clearTimeout(this._timer);
                    this._timer = setTimeout(() => this.konfirmasi = false, 3000);
                    return;
                }
                clearTimeout(this._timer);
                this.konfirmasi = false;
                const d = await this.kirim(cfg.urlHapusSemua, 'DELETE');
                if (!d) return;
                this.items = [];
                this.terapkan(d);
            },
        };
    }
</script>