@extends('layouts.public')

@section('title', 'Rincian Booking Fitting — ' . config('alnay.brand.nama'))

@section('content')

<style>
    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }

    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }

    .pd-shine { position: relative; overflow: hidden; }
    .pd-shine::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
        transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
    }
    .pd-shine:hover::after { transform: translateX(120%); }

    #dlg-batal-fitting::backdrop { background: rgba(0, 0, 0, .5); backdrop-filter: blur(3px); }

    @media (prefers-reduced-motion: reduce) {
        .pd-up, .pd-pop { animation: none !important; }
        .pd-shine::after { transition: none !important; }
    }
</style>

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    /* ---------- Status ---------- */
    $status    = strtolower($reservasi->status);
    // Booking langsung terjadwal begitu dibuat; admin hanya menandai Hadir / Batal.
    $terjadwal = ! in_array($status, ['hadir', 'batal'], true);

    /* ---------- Jadwal ---------- */
    $tgl   = $reservasi->slot ? Carbon::parse($reservasi->slot->tanggal) : null;
    $mulai = ($tgl && ! empty($reservasi->slot->jam_mulai))
        ? Carbon::parse($tgl->format('Y-m-d') . ' ' . $reservasi->slot->jam_mulai)
        : null;
    $jam       = $mulai ? $mulai->format('H:i') : null;
    $tglPanjang = $tgl ? $tgl->translatedFormat('l, d F Y') : null;
    $lewat     = $terjadwal && $mulai && $mulai->isPast();

    // Penanda waktu (countdown)
    $countdown = null;
    if ($terjadwal && $mulai) {
        if ($lewat)                       { $countdown = ['Jadwal terlewat', 'bg-amber-100 text-amber-700 border-amber-200']; }
        elseif ($mulai->isToday())        { $countdown = ['Hari ini', 'bg-gold text-white border-gold']; }
        elseif ($mulai->isTomorrow())     { $countdown = ['Besok', 'bg-gold/15 text-gold border-gold/40']; }
        else {
            $selisih   = (int) now()->startOfDay()->diffInDays($mulai->copy()->startOfDay());
            $countdown = [$selisih . ' hari lagi', 'bg-white text-ink/60 border-line/60'];
        }
    }

    $bisaUbah = $terjadwal && ! $lewat;

    /* ---------- Tema banner ---------- */
    if ($status === 'batal') {
        $themeBg = 'bg-rose-50 border-rose-200';
        $iconBg  = 'bg-rose-100 border-rose-500 text-rose-600';
        $title   = 'Booking Dibatalkan';
        $desc    = 'Jadwal fitting ini sudah dibatalkan.';
        if ($reservasi->dibatalkan_oleh === 'pelanggan') {
            $desc = 'Anda membatalkan jadwal fitting ini.';
        } elseif (! empty($reservasi->alasan_batal)) {
            $desc = 'Dibatalkan oleh toko. Alasan: ' . $reservasi->alasan_batal;
        }
        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>';
    } elseif ($status === 'hadir') {
        $themeBg = 'bg-emerald-50 border-emerald-200';
        $iconBg  = 'bg-emerald-100 border-emerald-500 text-emerald-600';
        $title   = 'Fitting Selesai';
        $desc    = 'Terima kasih, Anda sudah hadir pada sesi fitting ini.';
        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>';
    } elseif ($lewat) {
        $themeBg = 'bg-amber-50 border-amber-200';
        $iconBg  = 'bg-amber-100 border-amber-500 text-amber-600';
        $title   = 'Jadwal Fitting Terlewat';
        $desc    = 'Waktu fitting sudah lewat. Hubungi Customer Service jika Anda belum sempat hadir.';
        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
    } else {
        $themeBg = 'bg-emerald-50 border-emerald-200';
        $iconBg  = 'bg-emerald-100 border-emerald-500 text-emerald-600';
        $title   = 'Jadwal Fitting Terjadwal';
        $desc    = 'Booking Anda sudah terdaftar. Silakan datang ke toko sesuai jadwal di bawah.';
        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>';
    }

    /* ---------- Lokasi ---------- */
    $alamat    = (string) config('alnay.toko.alamat');
    $alamatAda = filled($alamat) && ! Str::contains(Str::lower($alamat), 'belum diatur');
    $jamBuka   = config('alnay.toko.jam_buka');
    $urlPeta   = config('alnay.toko.maps')
        ?: ($alamatAda ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($alamat) : null);

    /* ---------- WhatsApp CS (pesan sudah terisi) ---------- */
    $noCs   = preg_replace('/\D/', '', (string) config('alnay.kontak.wa_cs'));
    $pesanCs = "Halo ALNAY, saya ingin bertanya terkait booking fitting {$reservasi->kode_booking}"
             . ($tglPanjang ? " ({$tglPanjang}" . ($jam ? ", pukul {$jam} WIB" : '') . ')' : '') . '.';
    $urlCs  = $noCs ? 'https://wa.me/' . $noCs . '?text=' . rawurlencode($pesanCs) : null;

    /* ---------- Tambah ke Google Calendar (durasi diasumsikan 1 jam) ---------- */
    $urlKalender = null;
    if ($terjadwal && $mulai) {
        $urlKalender = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . rawurlencode('Fitting busana di ALNAY')
            . '&dates=' . $mulai->format('Ymd\THis') . '/' . $mulai->copy()->addHour()->format('Ymd\THis')
            . '&ctz=' . rawurlencode(config('app.timezone', 'Asia/Jakarta'))
            . '&details=' . rawurlencode('Kode booking: ' . $reservasi->kode_booking)
            . ($alamatAda ? '&location=' . rawurlencode($alamat) : '');
    }

    /* ---------- Menu cepat ---------- */
    $ikon = [
        'ubah'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        'kalender'=> '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'arah'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'chat'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
        'batal'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        'tas'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
        'ulang'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
    ];

    $menu = [];
    if ($bisaUbah) {
        $menu[] = ['label' => 'Ubah Jadwal', 'sub' => 'Pilih tanggal / jam lain', 'ikon' => 'ubah', 'tone' => 'primary', 'href' => route('fitting.edit', $reservasi->id)];
    }
    if ($urlKalender && ! $lewat) {
        $menu[] = ['label' => 'Tambah ke Kalender', 'sub' => 'Pengingat di Google Calendar', 'ikon' => 'kalender', 'tone' => 'default', 'href' => $urlKalender, 'blank' => true];
    }
    if ($terjadwal && $urlPeta) {
        $menu[] = ['label' => 'Petunjuk Arah', 'sub' => 'Buka lokasi di Google Maps', 'ikon' => 'arah', 'tone' => 'default', 'href' => $urlPeta, 'blank' => true];
    }
    if ($status === 'batal') {
        $menu[] = ['label' => 'Booking Fitting Lagi', 'sub' => 'Buat jadwal baru', 'ikon' => 'ulang', 'tone' => 'primary', 'href' => url('/fitting')];
    }
    if ($urlCs) {
        $menu[] = ['label' => 'Hubungi CS', 'sub' => 'Tanya lewat WhatsApp', 'ikon' => 'chat', 'tone' => 'wa', 'href' => $urlCs, 'blank' => true];
    }
    $menu[] = ['label' => 'Lihat Pesanan Sewa', 'sub' => 'Cek status busana Anda', 'ikon' => 'tas', 'tone' => 'default', 'href' => route('akun.dashboard') . '#penyewaan'];
    if ($bisaUbah) {
        $menu[] = ['label' => 'Batalkan Booking', 'sub' => 'Jadwal akan dilepas', 'ikon' => 'batal', 'tone' => 'danger', 'dialog' => true];
    }

    $tone = [
        'primary' => ['wrap' => 'bg-gold border-gold text-white hover:bg-gold/90 pd-shine', 'icon' => 'bg-white/20 text-white', 'sub' => 'text-white/80'],
        'default' => ['wrap' => 'bg-white border-line/60 text-ink/85 hover:border-gold/60 hover:bg-gold/5', 'icon' => 'bg-gold/10 text-gold', 'sub' => 'text-ink/50'],
        'wa'      => ['wrap' => 'bg-white border-emerald-200 text-ink/85 hover:border-emerald-400 hover:bg-emerald-50', 'icon' => 'bg-emerald-100 text-emerald-600', 'sub' => 'text-ink/50'],
        'danger'  => ['wrap' => 'bg-white border-rose-200 text-rose-600 hover:border-rose-400 hover:bg-rose-50', 'icon' => 'bg-rose-100 text-rose-600', 'sub' => 'text-rose-400'],
    ];

    /* ---------- Linimasa ---------- */
    $langkah = [
        ['Booking dibuat', $reservasi->created_at->translatedFormat('d M Y, H:i'), 'selesai'],
        ['Terjadwal', $tgl ? ($tgl->translatedFormat('d M Y') . ($jam ? ", {$jam} WIB" : '')) : '—', 'selesai'],
    ];
    if ($status === 'hadir') {
        $langkah[] = ['Hadir fitting', 'Sesi selesai', 'selesai'];
    } elseif ($status === 'batal') {
        $langkah[] = ['Dibatalkan', $reservasi->dibatalkan_oleh === 'pelanggan' ? 'Oleh Anda' : 'Oleh toko', 'batal'];
    } else {
        $langkah[] = ['Hadir fitting', $lewat ? 'Jadwal terlewat' : 'Menunggu kedatangan Anda', $lewat ? 'telat' : 'berjalan'];
    }
@endphp

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans">
    <div class="max-w-[900px] mx-auto px-4 md:px-6">

        {{-- Breadcrumb --}}
        <nav class="pd-up text-[11px] text-ink/50 mb-3" style="--d: .05s;" aria-label="Breadcrumb">
            <a href="{{ route('akun.dashboard') }}#fitting" class="hover:text-gold transition-colors">Riwayat Pemesanan</a>
            <span class="mx-1">/</span>
            <span class="text-ink/70">{{ $reservasi->kode_booking }}</span>
        </nav>

        {{-- Header --}}
        <div class="pd-up flex items-center gap-3 mb-6" style="--d: .1s;">
            <a href="{{ route('akun.dashboard') }}#fitting" aria-label="Kembali ke riwayat pemesanan"
               class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 hover:-translate-x-0.5 transition-all duration-300">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <h1 class="font-display text-2xl md:text-3xl font-medium text-ink/85">Rincian Booking Fitting</h1>
        </div>

        @if (session('success'))
            <div class="pd-up p-3 mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 flex items-center gap-2" style="--d: .15s;" role="status">
                <x-icon name="check" :size="16" />
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="pd-up p-3 mb-4 rounded-md bg-rose-50 border border-rose-200 text-xs text-rose-700" style="--d: .15s;" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-4">

            {{-- ============ KARTU UTAMA: status + jadwal + kode ============ --}}
            <div class="pd-up {{ $themeBg }} border rounded-xl overflow-hidden" style="--d: .2s;">
                <div class="p-5 flex items-center gap-4">
                    <div class="pd-pop w-10 h-10 rounded-full {{ $iconBg }} border-2 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $iconSvg !!}</svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-ink/85">{{ $title }}</p>
                        <p class="text-[11px] text-ink/60 mt-0.5 leading-relaxed">{{ $desc }}</p>
                    </div>
                    @if ($countdown)
                        <span class="hidden sm:inline-flex shrink-0 px-3 py-1 rounded-full border text-[11px] font-bold {{ $countdown[1] }}">{{ $countdown[0] }}</span>
                    @endif
                </div>

                <div class="bg-white/80 border-t border-black/5 p-5 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink/40">Tanggal</p>
                        <p class="mt-1 text-[13px] font-semibold text-ink/85">{{ $tglPanjang ?? '—' }}</p>
                        @if ($countdown)
                            <span class="sm:hidden inline-flex mt-1.5 px-2.5 py-0.5 rounded-full border text-[10px] font-bold {{ $countdown[1] }}">{{ $countdown[0] }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink/40">Jam</p>
                        <p class="mt-1 text-[13px] font-semibold text-ink/85">{{ $jam ? $jam . ' WIB' : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink/40">Kode Booking</p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-[13px] font-semibold text-ink/85 break-all">{{ $reservasi->kode_booking }}</span>
                            <button type="button" onclick="salinKode(this, @js($reservasi->kode_booking))"
                                    class="px-2 py-0.5 rounded border border-line/60 text-[10px] font-semibold text-ink/50 hover:border-gold/60 hover:text-gold transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold">Salin</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ MENU CEPAT (tombol aksi) ============ --}}
            <div class="pd-up" style="--d: .25s;">
                <h2 class="font-display text-base font-medium text-ink/80 mb-3">Menu Cepat</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($menu as $m)
                        @php $t = $tone[$m['tone']]; $tag = isset($m['dialog']) ? 'button' : 'a'; @endphp
                        <{{ $tag }}
                            @if ($tag === 'a')
                                href="{{ $m['href'] }}" @if (! empty($m['blank'])) target="_blank" rel="noopener" @endif
                            @else
                                type="button" onclick="document.getElementById('dlg-batal-fitting').showModal()"
                            @endif
                            class="group flex items-center gap-3 p-3.5 rounded-xl border text-left transition-all duration-300 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold cursor-pointer {{ $t['wrap'] }}">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 {{ $t['icon'] }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $ikon[$m['ikon']] !!}</svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[12px] font-bold leading-tight">{{ $m['label'] }}</span>
                                <span class="block mt-0.5 text-[10px] leading-snug {{ $t['sub'] }}">{{ $m['sub'] }}</span>
                            </span>
                        </{{ $tag }}>
                    @endforeach
                </div>
            </div>

            {{-- ============ LOKASI ============ --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .3s;">
                <h2 class="font-display text-base font-medium text-ink/80 mb-3">Lokasi Fitting</h2>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                        <x-icon name="location" :size="16" class="text-rose-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-ink/85">Toko ALNAY</p>
                        <p class="text-[11px] text-ink/50 mt-0.5">{{ $alamatAda ? $alamat : 'Alamat toko belum diatur, hubungi Customer Service untuk detail lokasi.' }}</p>
                        @if (filled($jamBuka))
                            <p class="text-[10px] text-ink/40 mt-1">Jam buka: {{ $jamBuka }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============ DETAIL SESI ============ --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .35s;">
                <h2 class="font-display text-base font-medium text-ink/80 mb-4">Detail Sesi Fitting</h2>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 overflow-hidden border border-line/50">
                        @if ($fotoProduk)
                            <img src="{{ asset('storage/' . $fotoProduk) }}" alt="{{ $namaProduk }}" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gold/10 text-gold font-bold text-lg">
                                {{ Str::upper(Str::substr($namaProduk ?? 'F', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-ink/85">{{ $namaProduk ?? 'Sesi Fitting' }}</p>
                        <p class="text-[10px] text-ink/50 mt-0.5">Fitting busana sewa di toko</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-600">Gratis</span>
                </div>
                <dl class="mt-4 pt-3 border-t border-line/30 space-y-2 text-[12px]">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Jadwal</dt>
                        <dd class="font-medium text-ink/85 text-right">{{ $tglPanjang ?? '—' }}{{ $jam ? ' · ' . $jam . ' WIB' : '' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Biaya Fitting</dt>
                        <dd class="font-medium text-emerald-600">Gratis</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Booking Dibuat</dt>
                        <dd class="font-medium text-ink/80">{{ $reservasi->created_at->translatedFormat('d F Y, H:i') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- ============ PERSIAPAN (hanya saat terjadwal) ============ --}}
            @if ($bisaUbah)
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .4s;">
                    <h2 class="font-display text-base font-medium text-ink/80 mb-3">Sebelum Datang</h2>
                    <ul class="space-y-2.5 text-[12px] text-ink/70">
                        @foreach ([
                            'Datang 5–10 menit sebelum jam fitting agar sesi berjalan tepat waktu.',
                            'Tunjukkan kode booking ' . $reservasi->kode_booking . ' kepada staf di toko.',
                            'Anda akan menerima pengingat 1 hari sebelum jadwal fitting.',
                            'Berhalangan hadir? Ubah jadwal atau batalkan lewat Menu Cepat di atas.',
                        ] as $poin)
                            <li class="flex items-start gap-2.5">
                                <span class="mt-0.5 w-4 h-4 rounded-full bg-gold/15 text-gold flex items-center justify-center shrink-0">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span>{{ $poin }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ============ LINIMASA ============ --}}
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .45s;">
                <h2 class="font-display text-base font-medium text-ink/80 mb-4">Perjalanan Booking</h2>
                <ol class="relative space-y-4">
                    @foreach ($langkah as $i => [$judul, $ket, $kondisi])
                        @php
                            $titik = [
                                'selesai'  => 'bg-emerald-500 border-emerald-500',
                                'berjalan' => 'bg-white border-gold ring-4 ring-gold/15',
                                'telat'    => 'bg-white border-amber-500 ring-4 ring-amber-100',
                                'batal'    => 'bg-rose-500 border-rose-500',
                            ][$kondisi];
                        @endphp
                        <li class="flex gap-3 relative">
                            @if (! $loop->last)
                                <span class="absolute left-[7px] top-4 -bottom-4 w-px bg-line/60" aria-hidden="true"></span>
                            @endif
                            <span class="relative mt-0.5 w-[15px] h-[15px] rounded-full border-2 shrink-0 {{ $titik }}" aria-hidden="true"></span>
                            <div>
                                <p class="text-[12px] font-semibold text-ink/85 leading-tight">{{ $judul }}</p>
                                <p class="text-[10px] text-ink/50 mt-0.5">{{ $ket }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

        </div>
    </div>
</div>

{{-- ============ DIALOG BATALKAN BOOKING ============ --}}
@if ($bisaUbah)
    <dialog id="dlg-batal-fitting" class="m-auto rounded-2xl p-0 w-[92vw] max-w-sm max-h-[90vh] overflow-y-auto bg-white text-ink shadow-2xl">
        <form method="POST" action="{{ route('fitting.cancel', $reservasi->id) }}" class="p-6 space-y-4" onsubmit="this.querySelector('[type=submit]').disabled = true;">
            @csrf
            @method('PATCH')
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-ink/90">Batalkan booking fitting?</h3>
                    <p class="text-xs text-ink/60 mt-1 leading-relaxed">
                        Jadwal {{ $tgl ? $tgl->translatedFormat('d M Y') : '' }}{{ $jam ? ', ' . $jam . ' WIB' : '' }} ({{ $reservasi->kode_booking }}) akan dilepas dan tidak bisa dikembalikan. Anda tetap bisa membuat booking fitting baru.
                    </p>
                </div>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 rounded-xl border border-line/60 text-xs font-bold text-ink/60 hover:bg-gray-50 cursor-pointer">Kembali</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white text-xs font-bold cursor-pointer">Ya, Batalkan</button>
            </div>
        </form>
    </dialog>
@endif

<script>
    // Salin kode booking (dengan cadangan untuk browser tanpa clipboard API)
    function salinKode(btn, kode) {
        const selesai = function () {
            const awal = btn.textContent;
            btn.textContent = 'Tersalin';
            setTimeout(function () { btn.textContent = awal; }, 1800);
        };
        const cadangan = function () {
            const t = document.createElement('textarea');
            t.value = kode; t.style.position = 'fixed'; t.style.opacity = '0';
            document.body.appendChild(t); t.select();
            try { document.execCommand('copy'); selesai(); } catch (e) {}
            document.body.removeChild(t);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(kode).then(selesai).catch(cadangan);
        } else {
            cadangan();
        }
    }

    // Klik area gelap menutup dialog
    (function () {
        const dlg = document.getElementById('dlg-batal-fitting');
        if (!dlg) return;
        dlg.addEventListener('click', function (e) {
            const r = dlg.getBoundingClientRect();
            if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) dlg.close();
        });
    })();
</script>

@endsection