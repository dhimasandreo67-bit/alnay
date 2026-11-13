@extends('layouts.akun')

@section('title', 'Akun Saya — ' . config('alnay.brand.nama'))

@section('content')
@php
    $adaFoto = $user->foto_profil && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->foto_profil);
    $inisial = strtoupper(mb_substr($user->name, 0, 1));
    $statusAktif = request('status', 'semua');
    $waNomor = config('alnay.brand.whatsapp');
    $rp = fn ($n) => 'Rp' . number_format((int) $n, 0, ',', '.');

    $menuSidebar = [
        ['panel' => 'profil',    'icon' => 'user',       'label' => 'Edit Profil',   'jumlah' => null],
        ['panel' => 'alamat',    'icon' => 'map-pin',    'label' => 'Alamat Saya',   'jumlah' => null],
        ['panel' => 'ringkasan', 'icon' => 'cart',       'label' => 'Pesanan Saya',  'jumlah' => $pesananAktif],
        ['panel' => 'penyewaan', 'icon' => 'cart',       'label' => 'Penyewaan',     'jumlah' => $penyewaanAktif],
        ['panel' => 'fitting',   'icon' => 'calendar',   'label' => 'Fitting Saya',  'jumlah' => $totalFitting],
        ['panel' => 'refund',    'icon' => 'refresh-cw', 'label' => 'Ajukan Refund', 'jumlah' => $refundAktif],
    ];

    $statKartu = [
        ['panel' => 'fitting',   'label' => 'Total Fitting',    'nilai' => $totalFitting,   'icon' => 'calendar'],
        ['panel' => 'ringkasan', 'label' => 'Pesanan Aktif',    'nilai' => $pesananAktif,   'icon' => 'cart'],
        ['panel' => 'penyewaan', 'label' => 'Penyewaan Aktif',  'nilai' => $penyewaanAktif, 'icon' => 'cart'],
    ];

    $kartu    = 'bg-white rounded-2xl border border-line/60 shadow-sm';
    $btnEmas  = 'inline-flex items-center justify-center rounded-lg bg-gold text-white text-sm font-semibold px-5 py-2.5 transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 active:translate-y-0 active:scale-[.98]';
    $btnTepi  = 'inline-flex items-center justify-center rounded-lg border border-line/60 bg-white text-ink/80 text-sm font-semibold px-5 py-2.5 transition duration-300 hover:bg-cream hover:-translate-y-0.5 active:translate-y-0 active:scale-[.98]';
    $input    = 'w-full border border-line/60 rounded-lg px-4 py-2.5 text-sm bg-white placeholder:text-ink/40 transition focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 disabled:bg-cream disabled:text-ink/40 disabled:cursor-not-allowed';
    $inputAlamat = $input;
    $errAlamat = $errors->getBag('alamat');

    $chip = [
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'rose'    => 'bg-rose-50 text-rose-700 ring-rose-200',
        'sky'     => 'bg-sky-50 text-sky-700 ring-sky-200',
        'amber'   => 'bg-amber-50 text-amber-700 ring-amber-200',
    ];

    $statusPesanan = function ($t) {
        $bayar = strtolower((string) $t->status_pembayaran);
        $kirim = $t->pengiriman?->status_kirim;
        return match (true) {
            in_array($bayar, ['gagal', 'batal'], true) => ['Dibatalkan', 'rose'],
            $bayar === 'pending'                       => ['Belum Bayar', 'amber'],
            $kirim === 'diterima'                      => ['Selesai', 'emerald'],
            $kirim === 'dikirim'                       => ['Dikirim', 'sky'],
            $kirim !== null                            => ['Sedang Dikemas', 'sky'],
            default                                    => ['Selesai', 'emerald'],
        };
    };

    $statusSewa = function ($s) {
        $bayar = strtolower((string) ($s->transaksi->status_pembayaran ?? ''));
        $st    = strtolower((string) $s->status);
        return match (true) {
            in_array($bayar, ['gagal', 'batal'], true) => ['Dibatalkan', 'rose'],
            $bayar === 'dp'                            => ['Belum Bayar DP', 'amber'],
            $st === 'selesai'                          => ['Selesai', 'emerald'],
            default                                    => ['Berlangsung', 'sky'],
        };
    };

    $fotoProduk = function ($produk) {
        $f = $produk->foto_produk ?? null;
        if (is_string($f)) {
            $dec = json_decode($f, true);
            $f = is_array($dec) ? $dec : [$f];
        }
        $f = is_array($f) ? collect($f)->filter()->first() : null;
        return $f ? (preg_match('#^https?://#', $f) ? $f : asset('storage/' . ltrim($f, '/'))) : null;
    };
@endphp

<style>
    @keyframes ak-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    .ak-rise { animation: ak-rise .5s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .panel-nav { transition: background-color .25s ease, color .25s ease, transform .25s ease; }
    .panel-nav:hover { transform: translateX(2px); }
    @media (prefers-reduced-motion: reduce) {
        .ak-rise { animation: none !important; }
        .panel-nav:hover { transform: none; }
    }
</style>

<div class="bg-cream min-h-screen py-8 md:py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 grid md:grid-cols-[280px_1fr] gap-6 items-start">

        <aside class="space-y-5 md:sticky md:top-6">
            <div class="ak-rise {{ $kartu }} p-5 flex items-center gap-4 border-l-4 border-l-gold">
                <div class="shrink-0">
                    @if ($adaFoto)
                        <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}" data-lihat-foto
                             class="w-14 h-14 rounded-full object-cover border-2 border-white shadow cursor-zoom-in hover:opacity-90 transition">
                    @else
                        <div data-lihat-foto
                             class="w-14 h-14 rounded-full bg-gradient-to-br from-gold to-[#8f5a2c] text-white font-display text-xl flex items-center justify-center cursor-zoom-in hover:opacity-90 transition">
                            {{ $inisial }}
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-ink/90 truncate">Halo, {{ $user->name }}!</p>
                    <p class="text-xs text-ink/60 mt-1">Selamat datang kembali di akun Anda.</p>
                </div>
            </div>

            <nav class="ak-rise {{ $kartu }} p-2" style="--d: .05s;">
                <p class="px-3 pt-3 pb-2 text-xs font-semibold tracking-wider text-ink/50">AKUN SAYA</p>
                @foreach ($menuSidebar as $menu)
                    <button type="button" data-panel="{{ $menu['panel'] }}"
                            class="panel-nav w-full flex items-center justify-between px-3 py-3.5 rounded-xl text-sm text-ink/80 hover:bg-cream hover:text-gold text-left group transition-colors">
                        <span class="flex items-center gap-3">
                            <x-icon name="{{ $menu['icon'] }}" :size="18" class="text-ink/50 group-hover:text-gold transition-colors" /> 
                            {{ $menu['label'] }}
                        </span>
                        <div class="flex items-center gap-2">
                            @if (! is_null($menu['jumlah']) && $menu['jumlah'] > 0)
                                <span class="text-xs font-semibold bg-gold text-white rounded-full px-2 py-0.5">{{ $menu['jumlah'] }}</span>
                            @endif
                            {{-- Tambahan Ikon Chevron --}}
                            <svg class="w-4 h-4 text-ink/30 group-hover:text-gold group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </button>
                @endforeach
            </nav>

            {{-- Tombol Keluar (Logout) --}}
            <div class="ak-rise {{ $kartu }} p-2 mt-4" style="--d: .1s;">
                <form method="POST" action="{{ route('keluar') }}"> 
                {{-- Sesuaikan 'keluar' dengan nama rute yang muncul di terminal --}}
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left group">
                        <span class="flex items-center gap-3">
                            {{-- Ikon Logout --}}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Keluar Akun
                        </span>
                        {{-- Panah kecil saat hover --}}
                        <svg class="w-4 h-4 text-rose-300 group-hover:text-rose-500 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <section class="space-y-5 min-w-0">

            @if (session('success'))
                <div role="status" class="ak-rise px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div role="alert" class="ak-rise px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-3 gap-3 md:gap-4">
                @foreach ($statKartu as $i => $k)
                    <button type="button" data-panel="{{ $k['panel'] }}"
                            class="ak-rise text-left {{ $kartu }} p-4 md:p-5 flex flex-col transition duration-300 hover:-translate-y-1 hover:shadow-md"
                            style="--d: {{ $i * 0.06 }}s;">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-[10px] md:text-xs font-semibold uppercase tracking-wide text-ink/50 leading-tight">{{ $k['label'] }}</p>
                            <span class="w-8 h-8 rounded-lg bg-gold/10 text-gold flex items-center justify-center shrink-0"><x-icon name="{{ $k['icon'] }}" :size="16" /></span>
                        </div>
                        <p class="font-display text-3xl font-bold text-ink/90 mt-3">{{ $k['nilai'] }}</p>
                        <span class="mt-auto pt-4 text-xs md:text-sm font-semibold text-gold">Lihat semua &rsaquo;</span>
                    </button>
                @endforeach
            </div>

            {{-- ===== PANEL: PESANAN BELI ===== --}}
            <div data-panel-content="ringkasan" class="space-y-4">
                <h2 class="font-display text-2xl font-bold text-ink/90 mb-2">Pesanan Pembelian</h2>
                @forelse ($pesananList as $i => $t)
                    @php
                        [$lbl, $nd] = $statusPesanan($t);
                        $item1 = $t->detail->first();
                        $lain  = max(0, $t->detail->count() - 1);
                        $foto  = $fotoProduk($item1?->produk);
                        $kirim = $t->pengiriman;
                    @endphp
                    <article class="ak-rise {{ $kartu }} p-4 md:p-5 transition duration-300 hover:shadow-md" style="--d: {{ min($i, 6) * 0.05 }}s;">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-sm font-bold text-ink/90 break-all">{{ $t->kode_transaksi }}</p>
                                <p class="text-xs text-ink/50 mt-0.5">{{ $t->tanggal?->locale('id')->translatedFormat('d F Y') }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide ring-1 shrink-0 {{ $chip[$nd] }}">{{ $lbl }}</span>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl overflow-hidden border border-line/60 bg-cream shrink-0 flex items-center justify-center">
                                @if ($foto)
                                    <img src="{{ $foto }}" alt="{{ $item1?->produk?->nama }}" loading="lazy" class="w-full h-full object-cover">
                                @else
                                    <x-icon name="cart" :size="20" class="text-ink/30" />
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-ink/90 truncate">{{ $item1?->produk?->nama ?? 'Produk' }}</p>
                                <p class="text-xs text-ink/55 mt-0.5">{{ $item1?->jumlah ?? 0 }} barang @if ($lain > 0) &middot; +{{ $lain }} produk lain @endif</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] uppercase tracking-wider text-ink/45">Total</p>
                                <p class="text-sm font-bold text-ink/90 tabular-nums">{{ $rp($t->total) }}</p>
                            </div>
                        </div>
                        @if ($kirim && $kirim->no_resi)
                            <div class="mt-4 rounded-xl bg-sky-50 ring-1 ring-sky-100 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-sky-700/70">Nomor Resi</p>
                                <p class="mt-0.5 text-sm text-ink/85"><span class="font-semibold">{{ strtoupper((string) $kirim->kurir) }} {{ $kirim->layanan_ongkir }}</span> &middot; <span class="font-mono">{{ $kirim->no_resi }}</span></p>
                            </div>
                        @endif
                        <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                            @if ($t->status_pembayaran === 'pending') <a href="{{ route('beli.konfirmasi', $t->id) }}" class="{{ $btnEmas }}">Bayar Sekarang</a> @endif
                            @if ($kirim && $kirim->status_kirim === 'dikirim')
                                <form method="POST" action="{{ route('akun.pengiriman.terima', $kirim->id) }}" id="form-terima-{{ $kirim->id }}">
                                    @csrf @method('PATCH')
                                    <button type="button" onclick="konfirmasiTerima('{{ $kirim->id }}')" class="{{ $btnEmas }}">Pesanan Diterima</button>
                                </form>
                            @endif
                            <a href="{{ route('akun.pesanan.detail', $t->id) }}" class="{{ $btnTepi }}">Detail</a>
                        </div>
                    </article>
                @empty
                    <div class="ak-rise {{ $kartu }} p-6 md:p-8 min-h-[320px] flex flex-col items-center justify-center text-center">
                        <div class="w-20 h-20 rounded-full bg-cream flex items-center justify-center"><x-icon name="clipboard-list" :size="32" class="text-ink/50" /></div>
                        <p class="font-semibold text-ink/90 mt-5">Belum Ada Pesanan</p>
                        <p class="text-sm text-ink/60 mt-2 max-w-md">Mulai jelajahi koleksi busana eksklusif dan jadwalkan sesi fitting Anda.</p>
                        <a href="{{ url('/koleksi') }}" class="{{ $btnEmas }} mt-6 px-8 py-3 uppercase tracking-wide">Jelajahi Koleksi Busana</a>
                    </div>
                @endforelse
            </div>

            {{-- ===== PANEL: PENYEWAAN ===== --}}
            <div data-panel-content="penyewaan" class="hidden space-y-4">
                <h2 class="font-display text-2xl font-bold text-ink/90 mb-2">Penyewaan Busana</h2>
                @forelse ($penyewaanList as $i => $s)
                    @php
                        $tr = $s->transaksi;
                        [$lbl, $nd] = $statusSewa($s);
                        $p1 = $tr?->detail->first()?->produk;
                        $foto = $fotoProduk($p1);
                        $tglA = \Carbon\Carbon::parse($s->tanggal_pinjam)->locale('id')->translatedFormat('d M Y');
                        $tglK = \Carbon\Carbon::parse($s->tanggal_kembali_rencana)->locale('id')->translatedFormat('d M Y');
                    @endphp
                    <article class="ak-rise {{ $kartu }} p-4 md:p-5 transition duration-300 hover:shadow-md" style="--d: {{ min($i, 6) * 0.05 }}s;">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-sm font-bold text-ink/90 break-all">{{ $tr?->kode_transaksi }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded inline-block mt-1">Penyewaan</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide ring-1 shrink-0 {{ $chip[$nd] }}">{{ $lbl }}</span>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl overflow-hidden border border-line/60 bg-cream shrink-0 flex items-center justify-center">
                                @if ($foto) <img src="{{ $foto }}" alt="{{ $p1?->nama }}" loading="lazy" class="w-full h-full object-cover"> @else <x-icon name="cart" :size="20" class="text-ink/30" /> @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-ink/90 truncate">{{ $p1?->nama ?? 'Produk' }}</p>
                                <div class="mt-1 flex items-center gap-1.5 text-xs text-ink/60"><x-icon name="calendar" :size="12" class="text-gold" /><span>{{ $tglA }} &ndash; {{ $tglK }}</span></div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] uppercase tracking-wider text-ink/45">Total</p>
                                <p class="text-sm font-bold text-ink/90 tabular-nums">{{ $rp($tr?->total ?? 0) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                            @if ($tr && $tr->status_pembayaran === 'dp') <a href="{{ route('sewa.konfirmasi', $tr->id) }}" class="{{ $btnEmas }}">Bayar DP</a> @endif
                            @if ($tr) <a href="{{ route('akun.pesanan.detail', $tr->id) }}" class="{{ $btnTepi }}">Detail</a> @endif
                        </div>
                    </article>
                @empty
                    <div class="ak-rise {{ $kartu }} p-8 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center"><x-icon name="cart" :size="28" class="text-ink/50" /></div>
                        <p class="font-semibold text-ink/90 mt-4">Belum ada penyewaan</p>
                        <p class="text-sm text-ink/60 mt-1">Penyewaan Anda akan tampil di sini.</p>
                    </div>
                @endforelse
            </div>

            {{-- ===== PANEL: FITTING ===== --}}
            <div data-panel-content="fitting" class="hidden space-y-4">
                <h2 class="font-display text-2xl font-bold text-ink/90 mb-2">Jadwal Fitting Saya</h2>
                @forelse ($fittingList as $i => $f)
                    <div class="ak-rise {{ $kartu }} p-5" style="--d: {{ min($i, 6) * 0.05 }}s;">
                        @include('customer.akun._fitting-terdekat', ['fitting' => $f])
                    </div>
                @empty
                    <div class="ak-rise {{ $kartu }} p-8 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center"><x-icon name="calendar" :size="28" class="text-ink/50" /></div>
                        <p class="font-semibold text-ink/90 mt-4">Belum ada jadwal fitting</p>
                        <p class="text-sm text-ink/60 mt-1">Jadwalkan fitting agar ukuran busana Anda pas.</p>
                        <a href="{{ route('fitting.create') }}" class="{{ $btnEmas }} mt-6 px-6 py-3">Jadwalkan Fitting</a>
                    </div>
                @endforelse
            </div>

            {{-- ===== PANEL: REFUND ===== --}}
            <div data-panel-content="refund" class="hidden space-y-4">
                <div class="{{ $kartu }} p-6 md:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl font-bold text-ink/90">Ajukan Refund</h2>
                        <p class="text-sm text-ink/60 mt-1">Refund diajukan per pesanan. Buka pesanan Anda lalu pilih ajukan refund.</p>
                    </div>
                    <a href="{{ route('akun.refund.index') }}" class="{{ $btnTepi }} shrink-0">Buka Halaman Refund</a>
                </div>
                @forelse ($refundList as $i => $r)
                    @php
                        $stRefund = strtolower((string) $r->status);
                        $ndRefund = match (true) {
                            str_contains($stRefund, 'setuju') || str_contains($stRefund, 'selesai') || str_contains($stRefund, 'berhasil') => 'emerald',
                            str_contains($stRefund, 'tolak') || str_contains($stRefund, 'batal') => 'rose',
                            default => 'amber',
                        };
                    @endphp
                    <a href="{{ route('akun.refund.show', $r->id) }}" class="ak-rise {{ $kartu }} p-5 block hover:border-gold/50 transition-colors" style="--d: {{ min($i, 6) * 0.05 }}s;">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-mono text-sm font-bold text-ink/90 break-all">{{ $r->transaksi?->kode_transaksi ?? 'Pesanan' }}</p>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide ring-1 shrink-0 {{ $chip[$ndRefund] }}">{{ $r->label_status }}</span>
                        </div>
                        <p class="mt-2 text-sm text-ink/70">{{ $r->alasan }}</p>
                        <p class="mt-1 text-xs text-ink/45">{{ $r->created_at?->locale('id')->translatedFormat('d F Y') }}</p>
                    </a>
                @empty
                    <div class="ak-rise {{ $kartu }} p-8 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center"><x-icon name="refresh-cw" :size="28" class="text-ink/50" /></div>
                        <p class="font-semibold text-ink/90 mt-4">Belum ada pengajuan refund</p>
                        <p class="text-sm text-ink/60 mt-1 max-w-md">Pengajuan refund Anda akan tampil di sini.</p>
                    </div>
                @endforelse
            </div>

                        {{-- ===== PANEL: EDIT PROFIL ===== --}}
            <div data-panel-content="profil" class="hidden tab-panel {{ $kartu }} overflow-hidden">
                <div class="p-5 md:p-6 border-b border-line/60">
                    <h2 class="font-display text-xl font-bold text-ink/90">Profil Saya</h2>
                    <p class="text-sm text-ink/60 mt-1">Kelola informasi profil Anda untuk mengontrol, melindungi dan mengamankan akun.</p>
                </div>

                {{-- Notifikasi Error/Success --}}
                @if (session('success'))
                    <div class="mx-5 md:mx-6 mt-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any() && ! $errAlamat->any())
                    <div class="mx-5 md:mx-6 mt-5 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('akun.profil.update') }}" enctype="multipart/form-data" class="md:grid md:grid-cols-[1fr_220px]">
                    @csrf
                    @method('PUT')

                    {{-- Kolom Kiri: Form Data --}}
                    <div class="p-5 md:p-6 space-y-5">
                        <div class="grid sm:grid-cols-[120px_1fr] gap-2 sm:gap-4 sm:items-center">
                            <label for="profil-nama" class="text-sm text-ink/60 sm:text-right">Nama</label>
                            <input id="profil-nama" type="text" name="name" value="{{ old('name', $user->name) }}"
                                   class="{{ $input }}">
                        </div>

                        <div class="grid sm:grid-cols-[120px_1fr] gap-2 sm:gap-4 sm:items-center">
                            <span class="text-sm text-ink/60 sm:text-right">Email</span>
                            <p class="text-sm text-ink/90 break-all px-4 py-2.5">{{ $user->email }}</p>
                        </div>

                        <div class="grid sm:grid-cols-[120px_1fr] gap-2 sm:gap-4 sm:items-center">
                            <label for="profil-hp" class="text-sm text-ink/60 sm:text-right">Nomor Telepon</label>
                            <input id="profil-hp" type="tel" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                                   class="{{ $input }}">
                        </div>

                        <div class="grid sm:grid-cols-[120px_1fr] gap-2 sm:gap-4 sm:items-center">
                            <span class="text-sm text-ink/60 sm:text-right">Jenis Kelamin</span>
                            <div class="flex flex-wrap gap-5 text-sm text-ink/80">
                                @foreach (['L' => 'Laki-laki', 'P' => 'Perempuan'] as $nilai => $label)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="{{ $nilai }}"
                                               @checked(old('jenis_kelamin', $user->jenis_kelamin) === $nilai)
                                               class="accent-[#C27C3E]">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-[120px_1fr] gap-2 sm:gap-4 pt-2">
                            <span class="hidden sm:block"></span>
                            <div>
                                <button type="submit" class="{{ $btnEmas }} px-8">Simpan Perubahan</button>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Foto Profil --}}
                    <div class="p-5 md:p-6 border-t md:border-t-0 md:border-l border-line/60 flex flex-col items-center text-center">
                        @if ($adaFoto)
                            <img id="foto-preview" src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}" data-lihat-foto
                                 class="w-32 h-32 rounded-full object-cover cursor-zoom-in hover:opacity-90 transition">
                        @else
                            <img id="foto-preview" src="" alt="" class="hidden w-32 h-32 rounded-full object-cover cursor-zoom-in" data-lihat-foto>
                            <div id="foto-inisial" data-lihat-foto
                                 class="w-32 h-32 rounded-full bg-cream flex items-center justify-center font-display text-4xl text-ink/60 cursor-zoom-in hover:opacity-90 transition">
                                {{ $inisial }}
                            </div>
                        @endif

                        <input id="foto-input" type="file" name="foto" accept="image/jpeg,image/png" class="hidden">

                        <label for="foto-input" class="{{ $btnTepi }} mt-5 cursor-pointer">Pilih Gambar</label>
                        <p id="foto-nama" class="text-xs text-ink/60 mt-2 break-all"></p>
                        <p class="text-xs text-ink/50 mt-3 leading-relaxed">
                            Ukuran gambar: maks. 1 MB<br>Format gambar: .JPEG, .PNG
                        </p>
                    </div>
                </form>
            </div>

            {{-- ===== PANEL: ALAMAT (DITAMBAHKAN) ===== --}}
            <div data-panel-content="alamat" class="hidden tab-panel bg-white rounded-2xl border border-line/60 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-display text-2xl font-bold text-ink/90">Alamat Saya</h2>
                    <button type="button" data-buka-alamat class="{{ $btnEmas }}">+ Tambah Alamat</button>
                </div>
                
                @if (session('success_alamat'))
                    <div data-flash-alamat class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
                        {{ session('success_alamat') }}
                    </div>
                @endif

                {{-- List Alamat --}}
                <div id="alamat-list">
                @forelse ($alamatList as $i => $a)
                    <div data-alamat-card="{{ $a->id }}" class="ak-rise relative overflow-hidden mt-4 border border-line/60 rounded-xl p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-4 transition hover:border-gold/40 hover:shadow-sm">
                        {{-- Penanda alamat utama: garis emas di sisi kiri + tint tipis (di-toggle lewat JS) --}}
                        <span data-penanda-utama aria-hidden="true" class="absolute left-0 inset-y-0 w-1.5 bg-gold {{ $a->is_utama ? '' : 'hidden' }}" @unless ($a->is_utama) style="display:none" @endunless></span>
                        <span data-penanda-utama aria-hidden="true" class="absolute inset-0 bg-gold {{ $a->is_utama ? '' : 'hidden' }}" style="opacity:.06;pointer-events:none;{{ $a->is_utama ? '' : 'display:none' }}"></span>
                        <div class="min-w-0 pl-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-ink/90">{{ $a->nama_penerima }}</p>
                                <span class="text-sm text-ink/60">{{ $a->no_hp }}</span>
                                <span class="text-xs px-2 py-0.5 rounded border border-line/60 text-ink/60">{{ $a->label }}</span>
                                <span data-badge-utama class="{{ $a->is_utama ? '' : 'hidden' }}" @unless ($a->is_utama) style="display:none" @endunless>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded bg-gold text-white">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        Alamat Utama
                                    </span>
                                </span>
                            </div>
                            <p class="text-sm text-ink/70 mt-2">{{ $a->alamat }}</p>
                            <p class="text-sm text-ink/60">{{ $a->kecamatan }}, {{ $a->kota_kabupaten }}, {{ $a->provinsi }} {{ $a->kode_pos }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 shrink-0">
                            <form method="POST" action="{{ route('akun.alamat.utama', $a->id) }}" data-form-utama class="{{ $a->is_utama ? 'hidden' : '' }}" @if ($a->is_utama) style="display:none" @endif>
                                @csrf @method('PATCH')
                                <button type="submit" class="{{ $btnTepi }} text-xs px-3 py-1.5">Atur Utama</button>
                            </form>
                            <button type="button" disabled data-tombol-utama style="opacity:.6;cursor:not-allowed;{{ $a->is_utama ? '' : 'display:none' }}"
                                    class="{{ $btnTepi }} text-xs px-3 py-1.5 {{ $a->is_utama ? '' : 'hidden' }}">
                                Alamat Utama
                            </button>
                            <button type="button" data-edit-alamat="{{ json_encode($a->only(['id','nama_penerima','no_hp','alamat','provinsi','kota_kabupaten','kecamatan','kode_pos','label','is_utama','ongkir_tujuan_id','ongkir_tujuan_label'])) }}" class="text-sm font-semibold text-gold hover:underline">Edit</button>
                            <form method="POST" action="{{ route('akun.alamat.destroy', $a->id) }}" onsubmit="return confirm('Hapus alamat ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-rose-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="ak-rise text-center py-8">
                        <div class="w-16 h-16 mx-auto rounded-full bg-cream flex items-center justify-center"><x-icon name="map-pin" :size="28" class="text-ink/50" /></div>
                        <p class="font-semibold text-ink/90 mt-4">Belum ada alamat tersimpan</p>
                        <p class="text-sm text-ink/60 mt-1">Tambahkan alamat untuk memudahkan pembelian dan penyewaan.</p>
                    </div>
                @endforelse
                </div>
            </div>

        </section>
    </div>
</div>

{{-- ===== MODAL TAMBAH / EDIT ALAMAT ===== --}}
<div id="alamat-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-5 border-b border-line/60 flex items-center justify-between">
            <h3 id="alamat-modal-title" class="font-display text-xl font-bold text-ink/90">Alamat Baru</h3>
            <button type="button" data-tutup-alamat aria-label="Tutup" class="w-9 h-9 rounded-full hover:bg-cream text-2xl leading-none text-ink/60 transition">&times;</button>
        </div>
        <form id="form-alamat" method="POST" action="{{ route('akun.alamat.store') }}" class="flex flex-col overflow-hidden">
            @csrf
            <input type="hidden" name="_method" id="form-alamat-method" value="{{ old('_method', 'POST') }}">
            <input type="hidden" name="_edit_id" id="form-alamat-edit-id" value="{{ old('_edit_id') }}">
            <div class="px-6 py-5 overflow-y-auto space-y-4">
                @if ($errAlamat->any())
                    <div id="alamat-error" class="px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-700 space-y-1">
                        @foreach ($errAlamat->all() as $error) <p>{{ $error }}</p> @endforeach
                    </div>
                @endif
                <div class="grid sm:grid-cols-2 gap-4">
                    <input type="text" name="nama_penerima" id="input-nama-penerima" value="{{ old('nama_penerima') }}" placeholder="Nama Lengkap" class="{{ $inputAlamat }}">
                    <input type="tel" name="no_hp" id="input-no-hp" value="{{ old('no_hp') }}" placeholder="Nomor Telepon" class="{{ $inputAlamat }}">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <select id="alamat-provinsi" name="provinsi" class="{{ $inputAlamat }}"><option value="">Memuat provinsi...</option></select>
                    <select id="alamat-kota" name="kota_kabupaten" class="{{ $inputAlamat }}" disabled><option value="">Pilih Kota/Kabupaten</option></select>
                    <select id="alamat-kecamatan" name="kecamatan" class="{{ $inputAlamat }}" disabled><option value="">Pilih kota terlebih dahulu</option></select>
                    <input type="text" name="kode_pos" id="alamat-kodepos" value="{{ old('kode_pos') }}" placeholder="Otomatis terisi" class="{{ $inputAlamat }} bg-gray-100 cursor-not-allowed" readonly>
                </div>
                <div class="relative">
                    <input type="text" id="ongkir-cari" autocomplete="off" value="{{ old('ongkir_tujuan_label') }}" placeholder="Cari kelurahan untuk ongkir (min. 3 huruf)" class="{{ $inputAlamat }}">
                    <input type="hidden" name="ongkir_tujuan_id" id="ongkir-id" value="{{ old('ongkir_tujuan_id') }}">
                    <input type="hidden" name="ongkir_tujuan_label" id="ongkir-label" value="{{ old('ongkir_tujuan_label') }}">
                    <ul id="ongkir-hasil" class="hidden absolute z-20 left-0 right-0 mt-1 bg-white border border-line/60 rounded-lg shadow-lg max-h-60 overflow-y-auto"></ul>
                    <p class="text-xs text-ink/50 mt-1.5">Pilih kelurahan dari daftar agar ongkir bisa dihitung otomatis.</p>
                    <p id="ongkir-terpilih" class="text-xs text-emerald-700 mt-1">{{ old('ongkir_tujuan_label') ? 'Dipilih: ' . old('ongkir_tujuan_label') : '' }}</p>
                </div>
                <textarea name="alamat" id="input-alamat" rows="3" placeholder="Nama jalan, gedung, No. rumah" class="{{ $inputAlamat }}">{{ old('alamat') }}</textarea>
                <div>
                    <p class="text-xs font-semibold text-ink/60 mb-2">Tandai sebagai</p>
                    <div class="flex gap-3">
                        @foreach (['Rumah', 'Kantor'] as $lbl)
                            <label class="cursor-pointer">
                                <input type="radio" name="label" value="{{ $lbl }}" class="peer sr-only label-radio" @checked(old('label', 'Rumah') === $lbl)>
                                <span class="inline-block px-4 py-1.5 rounded-lg border border-line/60 text-sm text-ink/70 transition peer-checked:border-gold peer-checked:text-gold peer-checked:bg-gold/10">{{ $lbl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-ink/80 cursor-pointer">
                    <input type="checkbox" name="is_utama" id="input-is-utama" value="1" @checked(old('is_utama')) class="accent-[#C27C3E]"> Jadikan alamat utama
                </label>
            </div>
            <div class="px-6 py-4 border-t border-line/60 flex items-center justify-end gap-3">
                <button type="button" data-tutup-alamat class="px-5 py-2.5 rounded-lg text-sm font-semibold text-ink/70 hover:bg-cream transition">Nanti Saja</button>
                <button type="submit" class="{{ $btnEmas }} px-8">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL LIHAT FOTO PROFIL ===== --}}
<div id="foto-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-md p-4">
    <button type="button" id="foto-modal-tutup" aria-label="Tutup" class="absolute top-6 right-6 md:top-8 md:right-10 w-10 h-10 rounded-full bg-white text-ink flex items-center justify-center shadow-lg hover:scale-105 transition"><span class="text-2xl leading-none">&times;</span></button>
    <img id="foto-modal-img" src="" alt="Foto profil" class="hidden w-64 h-64 md:w-96 md:h-96 rounded-full object-cover border-4 border-white/40 shadow-2xl">
    <div id="foto-modal-inisial" class="hidden w-64 h-64 md:w-96 md:h-96 rounded-full bg-gradient-to-br from-gold to-[#8f5a2c] text-white font-display text-8xl items-center justify-center border-4 border-white/40 shadow-2xl"></div>
</div>

<script>
    (function () {
        const modal = document.getElementById('alamat-modal');
        if (!modal) return;

        const URL_PROVINSI = '{{ url('/ongkir/provinsi') }}';
        const URL_KOTA = '{{ url('/ongkir/kota') }}';
        const URL_KECAMATAN = '{{ url('/ongkir/kecamatan') }}';
        const URL_TUJUAN = '{{ route('ongkir.tujuan') }}';
        const URL_STORE = "{{ route('akun.alamat.store') }}";
        const URL_BASE = "{{ url('/akun/alamat') }}";

        const selProv = document.getElementById('alamat-provinsi');
        const selKota = document.getElementById('alamat-kota');
        const selKec = document.getElementById('alamat-kecamatan');
        const kodePos = document.getElementById('alamat-kodepos');
        const cari = document.getElementById('ongkir-cari');
        const hasil = document.getElementById('ongkir-hasil');
        const idInput = document.getElementById('ongkir-id');
        const labelInput = document.getElementById('ongkir-label');
        const terpilih = document.getElementById('ongkir-terpilih');

        const formAlamat = document.getElementById('form-alamat');
        const modalTitle = document.getElementById('alamat-modal-title');
        const methodInput = document.getElementById('form-alamat-method');
        const editIdInput = document.getElementById('form-alamat-edit-id');
        const inNama = document.getElementById('input-nama-penerima');
        const inHp = document.getElementById('input-no-hp');
        const inAlamat = document.getElementById('input-alamat');
        const inUtama = document.getElementById('input-is-utama');

        // Token supaya respons API lama (modal sudah ditutup / dibuka ulang) tidak menimpa pilihan terbaru
        let muatToken = 0;
        let timer;

        /* ---------- Mode form ---------- */
        function modeTambah() {
            formAlamat.action = URL_STORE;
            methodInput.value = 'POST';
            editIdInput.value = '';
            modalTitle.textContent = 'Alamat Baru';
        }

        function modeEdit(id) {
            formAlamat.action = `${URL_BASE}/${id}`;
            methodInput.value = 'PUT';
            editIdInput.value = id;
            modalTitle.textContent = 'Edit Alamat';
        }

        // NB: formAlamat.reset() TIDAK dipakai karena akan mengembalikan nilai old() dari validasi sebelumnya
        function kosongkanForm() {
            inNama.value = '';
            inHp.value = '';
            inAlamat.value = '';
            kodePos.value = '';
            inUtama.checked = false;
            document.querySelectorAll('.label-radio').forEach(r => { r.checked = r.value === 'Rumah'; });
            resetOngkir();
            const err = document.getElementById('alamat-error');
            if (err) err.remove();
        }

        function resetSelect() {
            gagal(selProv, 'Memuat provinsi...');
            gagal(selKota, 'Pilih provinsi terlebih dahulu');
            gagal(selKec, 'Pilih kota terlebih dahulu');
        }

        function tampilModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function bukaTambah() {
            muatToken++;
            modeTambah();
            kosongkanForm();
            resetSelect();
            tampilModal();
            muatProvinsi();
        }

        function bukaEdit(data) {
            muatToken++;
            modeEdit(data.id);
            const err = document.getElementById('alamat-error');
            if (err) err.remove();
            inNama.value = data.nama_penerima || '';
            inHp.value = data.no_hp || '';
            inAlamat.value = data.alamat || '';
            kodePos.value = data.kode_pos || '';
            cari.value = data.ongkir_tujuan_label || '';
            idInput.value = data.ongkir_tujuan_id || '';
            labelInput.value = data.ongkir_tujuan_label || '';
            terpilih.textContent = data.ongkir_tujuan_label ? 'Dipilih: ' + data.ongkir_tujuan_label : '';
            hasil.classList.add('hidden');
            document.querySelectorAll('.label-radio').forEach(r => { r.checked = r.value === data.label; });
            inUtama.checked = (data.is_utama == 1 || data.is_utama === true || data.is_utama === '1');
            resetSelect();
            tampilModal();
            muatProvinsi(data.provinsi || '', data.kota_kabupaten || '', data.kecamatan || '');
        }

        // Dipakai saat halaman dimuat ulang karena validasi gagal: pertahankan input & mode (tambah/edit)
        function bukaDariError() {
            muatToken++;
            const editId = editIdInput.value;
            if (editId) modeEdit(editId); else modeTambah();
            resetSelect();
            tampilModal();
            muatProvinsi(@json(old('provinsi', '')), @json(old('kota_kabupaten', '')), @json(old('kecamatan', '')));
        }

        function tutupModal() {
            muatToken++;
            clearTimeout(timer);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modeTambah();
            kosongkanForm();
        }

        /* ---------- Data wilayah ---------- */
        async function ambilData(url) {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error();
            return res.json();
        }

        function isiPilihan(sel, items, placeholder, pilih) {
            sel.innerHTML = '';
            sel.add(new Option(placeholder, ''));
            items.forEach(item => {
                const opt = new Option(item.name, item.name, false, item.name === pilih);
                opt.dataset.id = item.id;
                sel.add(opt);
            });
            sel.disabled = false;
        }

        function gagal(sel, pesan) {
            sel.innerHTML = '';
            sel.add(new Option(pesan, ''));
            sel.disabled = true;
        }

        function resetOngkir() {
            clearTimeout(timer);
            cari.value = '';
            idInput.value = '';
            labelInput.value = '';
            terpilih.textContent = '';
            hasil.classList.add('hidden');
        }

        async function muatProvinsi(pilihProv = '', pilihKota = '', pilihKec = '') {
            const t = ++muatToken;
            let data;
            try {
                data = await ambilData(URL_PROVINSI);
            } catch (e) {
                if (t === muatToken) gagal(selProv, 'Gagal memuat provinsi');
                return;
            }
            if (t !== muatToken) return;
            isiPilihan(selProv, data, 'Pilih Provinsi', pilihProv);
            if (selProv.value) await muatKota(pilihKota, pilihKec, t);
        }

        async function muatKota(pilihKota = '', pilihKec = '', t = ++muatToken) {
            const provOpt = selProv.selectedOptions[0];
            gagal(selKec, 'Pilih kota terlebih dahulu');
            if (!provOpt || !provOpt.dataset.id) {
                gagal(selKota, 'Pilih provinsi terlebih dahulu');
                return;
            }
            gagal(selKota, 'Memuat kota...');
            let data;
            try {
                data = await ambilData(`${URL_KOTA}/${provOpt.dataset.id}`);
            } catch (e) {
                if (t === muatToken) gagal(selKota, 'Gagal memuat kota');
                return;
            }
            if (t !== muatToken) return;
            isiPilihan(selKota, data, 'Pilih Kota/Kabupaten', pilihKota);
            if (selKota.value) await muatKecamatan(pilihKec, t);
        }

        async function muatKecamatan(pilihKec = '', t = ++muatToken) {
            const kotaOpt = selKota.selectedOptions[0];
            if (!kotaOpt || !kotaOpt.dataset.id) {
                gagal(selKec, 'Pilih kota terlebih dahulu');
                return;
            }
            gagal(selKec, 'Memuat kecamatan...');
            let data;
            try {
                data = await ambilData(`${URL_KECAMATAN}/${kotaOpt.dataset.id}`);
            } catch (e) {
                if (t === muatToken) gagal(selKec, 'Gagal memuat kecamatan');
                return;
            }
            if (t !== muatToken) return;
            isiPilihan(selKec, data, 'Pilih Kecamatan', pilihKec);
        }

        selProv.addEventListener('change', () => { resetOngkir(); kodePos.value = ''; muatKota(); });
        selKota.addEventListener('change', () => { resetOngkir(); kodePos.value = ''; muatKecamatan(); });
        selKec.addEventListener('change', function () {
            idInput.value = '';
            labelInput.value = '';
            terpilih.textContent = '';
            kodePos.value = '';
            if (this.value) {
                cari.value = this.value;
                cari.dispatchEvent(new Event('input'));
            } else {
                cari.value = '';
                hasil.classList.add('hidden');
            }
        });

        /* ---------- Pencarian kelurahan (ongkir) ---------- */
        cari.addEventListener('input', () => {
            clearTimeout(timer);
            idInput.value = '';
            labelInput.value = '';
            terpilih.textContent = '';
            const q = cari.value.trim();
            if (q.length < 3) { hasil.classList.add('hidden'); return; }
            timer = setTimeout(async () => {
                let data = null;
                try {
                    data = await ambilData(URL_TUJUAN + '?q=' + encodeURIComponent(q));
                } catch (e) { /* ditangani di bawah */ }
                if (cari.value.trim() !== q) return; // input sudah berubah / form direset
                hasil.innerHTML = '';
                if (data === null) {
                    const li = document.createElement('li');
                    li.className = 'px-4 py-2 text-sm text-rose-600';
                    li.textContent = 'Layanan ongkir sedang tidak tersedia';
                    hasil.appendChild(li);
                } else if (!data.length) {
                    const li = document.createElement('li');
                    li.className = 'px-4 py-2 text-sm text-ink/60';
                    li.textContent = 'Tidak ditemukan';
                    hasil.appendChild(li);
                } else {
                    data.forEach(d => {
                        const li = document.createElement('li');
                        li.textContent = d.label;
                        li.className = 'px-4 py-2 text-sm cursor-pointer hover:bg-cream';
                        li.addEventListener('click', () => {
                            idInput.value = d.id;
                            labelInput.value = d.label;
                            terpilih.textContent = 'Dipilih: ' + d.label;
                            cari.value = d.label;
                            if (d.zip_code) kodePos.value = d.zip_code;
                            hasil.classList.add('hidden');
                        });
                        hasil.appendChild(li);
                    });
                }
                hasil.classList.remove('hidden');
            }, 400);
        });

        document.addEventListener('click', e => {
            if (!hasil.contains(e.target) && e.target !== cari) hasil.classList.add('hidden');
        });

        /* ---------- Tombol-tombol ---------- */
        document.querySelectorAll('[data-edit-alamat]').forEach(btn => {
            btn.addEventListener('click', function () {
                bukaEdit(JSON.parse(this.dataset.editAlamat));
            });
        });
        document.querySelectorAll('[data-buka-alamat]').forEach(el => el.addEventListener('click', bukaTambah));
        modal.querySelectorAll('[data-tutup-alamat]').forEach(el => el.addEventListener('click', tutupModal));

        // Tutup hanya jika mouse-down DAN mouse-up sama-sama di area gelap (hindari tertutup saat drag teks)
        let downDiOverlay = false;
        modal.addEventListener('mousedown', e => { downDiOverlay = e.target === modal; });
        modal.addEventListener('click', e => { if (e.target === modal && downDiOverlay) tutupModal(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) tutupModal(); });

        /* ---------- Atur alamat utama tanpa reload ---------- */
        const daftar = document.getElementById('alamat-list');

        function tampilFlash(pesan, error = false) {
            const panel = document.querySelector('[data-panel-content="alamat"]');
            if (!panel || !daftar) return;
            panel.querySelectorAll('[data-flash-alamat]').forEach(el => el.remove());
            const el = document.createElement('div');
            el.setAttribute('data-flash-alamat', '');
            el.className = 'mb-4 px-4 py-3 rounded-xl border text-sm ' + (error
                ? 'bg-rose-50 border-rose-200 text-rose-700'
                : 'bg-emerald-50 border-emerald-200 text-emerald-700');
            el.textContent = pesan;
            daftar.before(el);
            setTimeout(() => el.remove(), 3500);
        }

        // tampil/sembunyi yang tahan terhadap bentrok class display (inline-flex vs hidden)
        function tampil(el, ya) {
            if (!el) return;
            el.classList.toggle('hidden', !ya);
            el.style.display = ya ? '' : 'none';
        }

        function tandaiUtama(idUtama) {
            if (!daftar) return;
            let kartuBaru = null;
            daftar.querySelectorAll('[data-alamat-card]').forEach(card => {
                const utama = card.dataset.alamatCard === String(idUtama);
                tampil(card.querySelector('[data-badge-utama]'), utama);
                tampil(card.querySelector('[data-form-utama]'), !utama);
                tampil(card.querySelector('[data-tombol-utama]'), utama);
                card.querySelectorAll('[data-penanda-utama]').forEach(el => tampil(el, utama));

                // sinkronkan data untuk modal Edit supaya checkbox "alamat utama" tidak basi
                const edit = card.querySelector('[data-edit-alamat]');
                if (edit) {
                    try {
                        const d = JSON.parse(edit.dataset.editAlamat);
                        d.is_utama = utama ? 1 : 0;
                        edit.dataset.editAlamat = JSON.stringify(d);
                    } catch (e) {}
                }
                if (utama) kartuBaru = card;
            });
            if (kartuBaru) daftar.prepend(kartuBaru); // alamat utama selalu di paling atas
        }

        document.querySelectorAll('[data-form-utama]').forEach(f => {
            f.addEventListener('submit', async e => {
                e.preventDefault();
                const btn = f.querySelector('button[type="submit"]');
                if (btn.disabled) return;
                const teksAsal = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Memproses...';
                const id = f.closest('[data-alamat-card]').dataset.alamatCard;
                try {
                    const res = await fetch(f.action, {
                        method: 'POST',
                        body: new FormData(f),
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) throw new Error();
                    const json = await res.json();
                    tandaiUtama(json.id ?? id);
                    // Nomor telepon di tab Profil mengikuti nomor alamat utama
                    const hpProfil = document.getElementById('profil-hp');
                    if (hpProfil && json.no_hp) hpProfil.value = json.no_hp;
                    tampilFlash(json.message || 'Alamat utama diperbarui.');
                } catch (err) {
                    // Gagal (sesi habis, jaringan, dll) -> kembali ke cara biasa dengan reload halaman
                    btn.textContent = teksAsal;
                    f.submit();
                }
                btn.disabled = false;
                btn.textContent = teksAsal;
            });
        });

        @if ($errAlamat->any())
            bukaDariError();
        @endif
    })();
</script>

<script>
    (function () {
        const PANEL_DEFAULT = 'ringkasan';
        const panels = document.querySelectorAll('[data-panel-content]');
        const navItems = document.querySelectorAll('aside .panel-nav');

        function panelAwal() {
            if (location.hash.length > 1) return location.hash.slice(1);
            @if ($errAlamat->any())
                return 'alamat';
            @endif
            return PANEL_DEFAULT;
        }

                function tampilkan(nama) {
            if (!document.querySelector(`[data-panel-content="${nama}"]`)) nama = PANEL_DEFAULT;

            panels.forEach(p => {
                const tampil = p.dataset.panelContent === nama;
                p.classList.toggle('hidden', !tampil);
                if (tampil) {
                    p.classList.remove('ak-rise');
                    void p.offsetWidth;
                    p.classList.add('ak-rise');
                }
            });

            navItems.forEach(n => {
                const aktif = n.dataset.panel === nama;
                n.classList.toggle('bg-gold/10', aktif);
                n.classList.toggle('text-gold', aktif);
                n.classList.toggle('font-semibold', aktif);
                n.classList.toggle('text-ink/80', !aktif);
            });

            history.replaceState(null, '', '#' + nama);

            // TAMBAHKAN INI: Geser layar ke area konten secara halus
            setTimeout(() => {
                const target = document.querySelector(`[data-panel-content="${nama}"]`);
                if (target) {
                    const y = target.getBoundingClientRect().top + window.scrollY - 80; // 80px offset untuk header
                    window.scrollTo({ top: y, behavior: 'smooth' });
                }
            }, 50);
        }

        document.querySelectorAll('[data-panel]').forEach(el => {
            el.addEventListener('click', e => {
                e.preventDefault();
                tampilkan(el.dataset.panel);
            });
        });

        window.addEventListener('hashchange', () => tampilkan(location.hash.slice(1) || PANEL_DEFAULT));
        tampilkan(panelAwal());

        // Pratinjau Foto & Modal
        const fotoInput = document.getElementById('foto-input');
        const fotoPreview = document.getElementById('foto-preview');
        const fotoInisial = document.getElementById('foto-inisial');
        const fotoNama = document.getElementById('foto-nama');

        if (fotoInput) {
            fotoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                if (!['image/jpeg', 'image/png'].includes(file.type)) {
                    alert('Format gambar harus JPEG atau PNG.');
                    this.value = '';
                    return;
                }
                if (file.size > 1024 * 1024) {
                    alert('Ukuran gambar maksimal 1 MB.');
                    this.value = '';
                    return;
                }
                fotoPreview.src = URL.createObjectURL(file);
                fotoPreview.classList.remove('hidden');
                if (fotoInisial) fotoInisial.classList.add('hidden');
                fotoNama.textContent = file.name;
            });
        }

        const modalFoto = document.getElementById('foto-modal');
        const modalImg = document.getElementById('foto-modal-img');
        const modalInisial = document.getElementById('foto-modal-inisial');

        function bukaFoto(el) {
            if (el.tagName === 'IMG' && el.getAttribute('src')) {
                modalImg.src = el.src;
                modalImg.classList.remove('hidden');
                modalInisial.classList.add('hidden');
                modalInisial.classList.remove('flex');
            } else {
                modalInisial.textContent = el.textContent.trim();
                modalInisial.classList.remove('hidden');
                modalInisial.classList.add('flex');
                modalImg.classList.add('hidden');
            }
            modalFoto.classList.remove('hidden');
            modalFoto.classList.add('flex');
        }

        function tutupFoto() {
            modalFoto.classList.add('hidden');
            modalFoto.classList.remove('flex');
        }

        document.querySelectorAll('[data-lihat-foto]').forEach(el => {
            el.addEventListener('click', () => bukaFoto(el));
        });

        document.getElementById('foto-modal-tutup').addEventListener('click', tutupFoto);
        modalFoto.addEventListener('click', e => { if (e.target === modalFoto) tutupFoto(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupFoto(); });
    })();
</script>
{{-- MODAL KONFIRMASI TERIMA BARANG (KUSTOM) --}}
<div id="modalTerimaBarang" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all scale-100">
        
        <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-4 border border-emerald-100">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="font-display text-xl font-bold text-gray-900 mb-2">Konfirmasi Terima Barang</h3>
            <p class="text-sm text-gray-500 leading-relaxed">Apakah Anda yakin pesanan sudah diterima dengan baik? Status pesanan akan berubah menjadi selesai.</p>
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-3">
            <button type="button" onclick="tutupModalTerima()"
                class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-100 transition-colors">
                Batal
            </button>
            <button type="button" id="btn-submit-terima"
                class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition-colors shadow-md shadow-emerald-500/20">
                Ya, Terima
            </button>
        </div>
    </div>
</div>

<script>
    function konfirmasiTerima(id) {
        const modal = document.getElementById('modalTerimaBarang');
        const form = document.getElementById('form-terima-' + id);
        const btnSubmit = document.getElementById('btn-submit-terima');

        // Saat tombol "Ya, Terima" di modal diklik, submit form yang sesuai
        btnSubmit.onclick = function() {
            form.submit();
        };

        // Tampilkan modal
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }

    function tutupModalTerima() {
        const modal = document.getElementById('modalTerimaBarang');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    // Tutup modal jika user klik area gelap di luar kotak
    document.getElementById('modalTerimaBarang').addEventListener('click', function(e) {
        if (e.target === this) tutupModalTerima();
    });
</script>

{{-- Modal konfirmasi hapus fitting (dipakai semua kartu fitting) --}}
@include('customer.partials.modal-hapus-fitting')
@endsection