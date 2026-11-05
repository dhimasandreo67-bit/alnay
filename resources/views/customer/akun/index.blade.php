@extends('layouts.akun')

@section('title', 'Akun Saya — ' . config('alnay.brand.nama'))

@section('content')
@php
    $adaFoto = $user->foto_profil && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->foto_profil);
    $inisial = strtoupper(substr($user->name, 0, 1));
    $statusAktif = request('status', 'semua');
    $tabs = [
        'semua' => 'Semua',
        'belum_bayar' => 'Belum Bayar',
        'dikemas' => 'Sedang Dikemas',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'pengembalian' => 'Pengembalian',
    ];
    $adaAktivitas = $pesananAktif > 0 || $penyewaanAktif > 0 || $fittingAktif;
    $waNomor = config('alnay.brand.whatsapp');

    // Menu sidebar. 'jumlah' null berarti tidak ada badge.
    $menuSidebar = [
        ['panel' => 'profil',    'icon' => 'user',         'label' => 'Edit Profil',    'jumlah' => null],
        ['panel' => 'alamat',    'icon' => 'map-pin',      'label' => 'Alamat Saya',    'jumlah' => null],
        ['panel' => 'ringkasan', 'icon' => 'cart',         'label' => 'Pesanan Saya',   'jumlah' => $pesananAktif],
        ['panel' => 'penyewaan', 'icon' => 'cart',         'label' => 'Penyewaan',      'jumlah' => $penyewaanAktif],
        ['panel' => 'fitting',   'icon' => 'calendar',     'label' => 'Fitting Saya',   'jumlah' => $totalFitting],
        ['panel' => 'refund',    'icon' => 'refresh-cw',   'label' => 'Ajukan Refund',  'jumlah' => $refundAktif],
    ];
@endphp

<div class="bg-cream min-h-screen py-8 md:py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 grid md:grid-cols-[280px_1fr] gap-6 items-start">

        {{-- SIDEBAR --}}
        <aside class="space-y-5 md:sticky md:top-6">
            <div class="bg-white rounded-2xl border border-line/60 p-5 flex items-center gap-4">
                <div class="shrink-0">
                    @if($adaFoto)
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
                    <p class="font-semibold text-ink/90">Halo, {{ $user->name }}!</p>
                    <p class="text-xs text-ink/60 mt-1">Selamat datang kembali di akun Anda.</p>
                </div>
            </div>

            <nav class="bg-white rounded-2xl border border-line/60 p-2">
                <p class="px-3 pt-3 pb-2 text-xs font-semibold tracking-wider text-ink/50">AKUN SAYA</p>

                @foreach ($menuSidebar as $menu)
                    <button type="button" data-panel="{{ $menu['panel'] }}"
                            class="panel-nav w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm text-ink/80 hover:bg-cream text-left">
                        <span class="flex items-center gap-3"><x-icon name="{{ $menu['icon'] }}" :size="16" /> {{ $menu['label'] }}</span>
                        @if (! is_null($menu['jumlah']) && $menu['jumlah'] > 0)
                            <span class="text-xs font-semibold bg-gold text-white rounded-full px-2 py-0.5">{{ $menu['jumlah'] }}</span>
                        @endif
                    </button>
                @endforeach
            </nav>

            <div class="bg-white rounded-2xl border border-line/60 p-5">
                <p class="text-xs font-semibold tracking-wider text-ink/90">BUTIK CONCIERGE</p>
                <p class="text-sm text-ink/60 mt-2">Butuh asistensi gaun atau fitting privat?</p>
                <a href="{{ $waNomor ? 'https://wa.me/' . $waNomor : '#' }}" target="_blank"
                   class="inline-block text-sm font-semibold text-gold mt-3 hover:underline">
                    Hubungi Konsultan →
                </a>
            </div>
        </aside>

        {{-- KONTEN UTAMA --}}
        <section class="space-y-5 min-w-0">

            {{-- KARTU STATISTIK: selalu tampil di atas --}}
            <div class="grid grid-cols-3 gap-3 md:gap-4">
                <button type="button" data-panel="fitting" class="text-left bg-white rounded-2xl border border-line/60 p-4 md:p-5 flex flex-col hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-[10px] md:text-xs font-semibold uppercase tracking-wide text-ink/50 leading-tight">Total Fitting</p>
                        <span class="w-8 h-8 rounded-lg bg-gold/10 text-gold flex items-center justify-center shrink-0"><x-icon name="calendar" :size="16" /></span>
                    </div>
                    <p class="font-display text-3xl font-bold text-ink/90 mt-3">{{ $totalFitting }}</p>
                    <span class="mt-auto pt-4 text-xs md:text-sm font-semibold text-gold">Lihat semua &rsaquo;</span>
                </button>

                <button type="button" data-panel="ringkasan" class="text-left bg-white rounded-2xl border border-line/60 p-4 md:p-5 flex flex-col hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-[10px] md:text-xs font-semibold uppercase tracking-wide text-ink/50 leading-tight">Pesanan Aktif</p>
                        <span class="w-8 h-8 rounded-lg bg-gold/10 text-gold flex items-center justify-center shrink-0"><x-icon name="cart" :size="16" /></span>
                    </div>
                    <p class="font-display text-3xl font-bold text-ink/90 mt-3">{{ $pesananAktif }}</p>
                    <span class="mt-auto pt-4 text-xs md:text-sm font-semibold text-gold">Lihat semua &rsaquo;</span>
                </button>

                <button type="button" data-panel="penyewaan" class="text-left bg-white rounded-2xl border border-line/60 p-4 md:p-5 flex flex-col hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-[10px] md:text-xs font-semibold uppercase tracking-wide text-ink/50 leading-tight">Penyewaan Aktif</p>
                        <span class="w-8 h-8 rounded-lg bg-gold/10 text-gold flex items-center justify-center shrink-0"><x-icon name="cart" :size="16" /></span>
                    </div>
                    <p class="font-display text-3xl font-bold text-ink/90 mt-3">{{ $penyewaanAktif }}</p>
                    <span class="mt-auto pt-4 text-xs md:text-sm font-semibold text-gold">Lihat semua &rsaquo;</span>
                </button>
            </div>

            {{-- ===== PANEL: RINGKASAN ===== --}}
            <div data-panel-content="ringkasan" class="space-y-5">

                @if ($fittingAktif)
                    <div class="bg-white rounded-2xl border border-line/60 overflow-hidden">
                        <div class="p-5 border-b border-line/60 flex items-center justify-between">
                            <h2 class="font-display text-lg font-bold text-ink/90">Jadwal Fitting Terdekat</h2>
                            <a href="{{ route('akun.fitting.index') }}" class="text-xs font-bold text-gold hover:underline">Lihat semua &rsaquo;</a>
                        </div>
                        <div class="p-5">
                            @include('customer.akun._fitting-terdekat', ['fitting' => $fittingAktif])
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-line/60 overflow-x-auto no-scrollbar">
                    <div class="flex min-w-max px-2">
                        @foreach ($tabs as $key => $label)
                            <a href="{{ route('akun.dashboard', ['status' => $key]) }}"
                               class="px-4 md:px-5 py-4 text-sm whitespace-nowrap border-b-2 {{ $statusAktif === $key ? 'border-gold text-gold font-semibold' : 'border-transparent text-ink/60 hover:text-ink' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <form method="GET" action="{{ route('akun.dashboard') }}" class="bg-white rounded-2xl border border-line/60 p-3">
                    <input type="hidden" name="status" value="{{ $statusAktif }}">
                    <div class="flex items-center gap-3 bg-cream rounded-xl px-4 py-3">
                        <x-icon name="search" :size="16" class="text-ink/50" />
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Cari riwayat pesanan, jadwal fitting, atau refund..."
                               class="bg-transparent outline-none text-sm w-full placeholder:text-ink/50">
                    </div>
                </form>

                <div class="bg-white rounded-2xl border border-line/60 p-6 md:p-8 min-h-[360px] flex flex-col items-center justify-center text-center">
                    @if (! $adaAktivitas)
                        <div class="w-20 h-20 rounded-full bg-cream flex items-center justify-center">
                            <x-icon name="clipboard-list" :size="32" class="text-ink/50" />
                        </div>
                        <p class="font-semibold text-ink/90 mt-5">Belum Ada Pesanan Berlangsung</p>
                        <p class="text-sm text-ink/60 mt-2 max-w-md">
                            Saat ini Anda belum memiliki pesanan aktif. Mulai jelajahi koleksi busana eksklusif dan jadwalkan sesi fitting Anda.
                        </p>
                        <a href="{{ url('/koleksi') }}"
                           class="mt-7 bg-gold text-white px-8 py-3.5 rounded-full text-sm font-semibold uppercase tracking-wide hover:opacity-90">
                            Jelajahi Koleksi Busana
                        </a>
                    @else
                        <p class="font-semibold text-ink/90">Anda memiliki {{ $pesananAktif + $penyewaanAktif }} pesanan aktif</p>
                        <p class="text-sm text-ink/60 mt-2">Lihat detail dan statusnya di halaman Pesanan Saya.</p>
                        <a href="{{ route('akun.pesanan.index') }}"
                           class="mt-6 border border-line/60 px-6 py-3 rounded-full text-sm font-semibold text-ink/80 hover:bg-cream">
                            Buka Pesanan Saya
                        </a>
                    @endif
                </div>
            </div>

            {{-- ===== PANEL: EDIT PROFIL ===== --}}
            <div data-panel-content="profil" class="hidden bg-white rounded-2xl border border-line/60 overflow-hidden">
                <div class="p-5 md:p-6 border-b border-line/60">
                    <h2 class="font-display text-xl font-bold text-ink/90">Profil Saya</h2>
                    <p class="text-sm text-ink/60 mt-1">Kelola informasi profil Anda untuk mengontrol, melindungi dan mengamankan akun.</p>
                </div>

                @if (session('success'))
                    <div class="mx-5 md:mx-6 mt-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mx-5 md:mx-6 mt-5 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('akun.profil.update') }}" enctype="multipart/form-data" class="md:grid md:grid-cols-[1fr_220px]">
                    @csrf
                    @method('PUT')

                    {{-- Kolom kiri: data --}}
                    <div class="p-5 md:p-6 space-y-5">
                        <div class="grid sm:grid-cols-[120px_1fr] gap-2 sm:gap-4 sm:items-center">
                            <label for="profil-nama" class="text-sm text-ink/60 sm:text-right">Nama</label>
                            <input id="profil-nama" type="text" name="name" value="{{ old('name', $user->name) }}"
                                   class="w-full border border-line/60 rounded-md px-4 py-2.5 text-sm bg-white focus:outline-none focus:border-gold">
                        </div>

                        <div class="grid sm:grid-cols-[120px_1fr] gap-2 sm:gap-4 sm:items-center">
                            <span class="text-sm text-ink/60 sm:text-right">Email</span>
                            <p class="text-sm text-ink/90 break-all">{{ $user->email }}</p>
                        </div>

                        <div class="grid sm:grid-cols-[120px_1fr] gap-2 sm:gap-4 sm:items-center">
                            <label for="profil-hp" class="text-sm text-ink/60 sm:text-right">Nomor Telepon</label>
                            <input id="profil-hp" type="tel" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                                   class="w-full border border-line/60 rounded-md px-4 py-2.5 text-sm bg-white focus:outline-none focus:border-gold">
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
                                <button type="submit" class="bg-gold text-white px-8 py-2.5 rounded-md text-sm font-semibold hover:opacity-90">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom kanan: foto --}}
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

                        <label for="foto-input"
                               class="mt-5 cursor-pointer border border-line/60 px-5 py-2 rounded-md text-sm text-ink/80 hover:bg-cream">
                            Pilih Gambar
                        </label>
                        <p id="foto-nama" class="text-xs text-ink/60 mt-2 break-all"></p>
                        <p class="text-xs text-ink/50 mt-3 leading-relaxed">
                            Ukuran gambar: maks. 1 MB<br>Format gambar: .JPEG, .PNG
                        </p>
                    </div>
                </form>
            </div>

            {{-- ===== PANEL: ALAMAT ===== --}}
<div data-panel-content="alamat" class="hidden space-y-5">

    @if (session('success_alamat'))
        <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
            {{ session('success_alamat') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-line/60 p-6 md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-display text-2xl font-bold text-ink/90">Alamat Saya</h2>
                <p class="text-sm text-ink/60 mt-1">Alamat utama dipakai otomatis saat Anda membeli atau menyewa busana.</p>
            </div>
            <button type="button" data-buka-alamat
                    class="bg-gold text-white px-5 py-2.5 rounded-md text-sm font-semibold hover:opacity-90 shrink-0">
                + Tambahkan Alamat
            </button>
        </div>

        @forelse ($alamatList as $a)
            <div class="mt-6 border border-line/60 rounded-xl p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-semibold text-ink/90">{{ $a->nama_penerima }}</p>
                        <span class="text-sm text-ink/60">{{ $a->no_hp }}</span>
                        <span class="text-xs px-2 py-0.5 rounded border border-line/60 text-ink/60">{{ $a->label }}</span>
                        @if ($a->is_utama)
                            <span class="text-xs px-2 py-0.5 rounded border border-gold text-gold">Utama</span>
                        @endif
                    </div>
                    <p class="text-sm text-ink/70 mt-2">{{ $a->alamat }}</p>
                    <p class="text-sm text-ink/60">
                        {{ $a->kecamatan }}, {{ $a->kota_kabupaten }}, {{ $a->provinsi }} {{ $a->kode_pos }}
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    @unless ($a->is_utama)
                        <form method="POST" action="{{ route('akun.alamat.utama', $a->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="border border-line/60 px-4 py-2 rounded-md text-xs font-semibold text-ink/80 hover:bg-cream">
                                Atur sebagai utama
                            </button>
                        </form>
                    @endunless

                    <form method="POST" action="{{ route('akun.alamat.destroy', $a->id) }}" onsubmit="return confirm('Hapus alamat ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-rose-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="mt-8 text-center py-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-cream flex items-center justify-center">
                    <x-icon name="map-pin" :size="28" class="text-ink/50" />
                </div>
                <p class="font-semibold text-ink/90 mt-4">Belum ada alamat tersimpan</p>
                <p class="text-sm text-ink/60 mt-1">Tambahkan alamat untuk memudahkan pembelian dan penyewaan.</p>
                <button type="button" data-buka-alamat
                        class="mt-6 bg-gold text-white px-6 py-3 rounded-full text-sm font-semibold hover:opacity-90">
                    + Tambahkan Alamat
                </button>
            </div>
        @endforelse
    </div>
</div>

            {{-- ===== PANEL: PENYEWAAN ===== --}}
            <div data-panel-content="penyewaan" class="hidden bg-white rounded-2xl border border-line/60 p-6 md:p-8">
                <h2 class="font-display text-2xl font-bold text-ink/90">Penyewaan Saya</h2>
                <p class="text-sm text-ink/60 mt-1">Ringkasan penyewaan busana Anda.</p>

                <div class="mt-8 flex flex-col items-center text-center py-8">
                    @if ($penyewaanAktif > 0)
                        <p class="font-display text-4xl font-bold text-ink/90">{{ $penyewaanAktif }}</p>
                        <p class="text-sm text-ink/60 mt-2">penyewaan sedang berjalan</p>
                        <a href="{{ route('akun.pesanan.index', ['jenis' => 'sewa']) }}"
                           class="mt-6 bg-gold text-white px-6 py-3 rounded-full text-sm font-semibold hover:opacity-90">
                            Lihat Daftar Penyewaan
                        </a>
                    @else
                        <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center">
                            <x-icon name="cart" :size="28" class="text-ink/50" />
                        </div>
                        <p class="font-semibold text-ink/90 mt-4">Belum ada penyewaan aktif</p>
                        <p class="text-sm text-ink/60 mt-1">Penyewaan yang sedang berjalan akan tampil di sini.</p>
                    @endif
                </div>
            </div>

            {{-- ===== PANEL: FITTING ===== --}}
            <div data-panel-content="fitting" class="hidden bg-white rounded-2xl border border-line/60 p-6 md:p-8">
                <h2 class="font-display text-2xl font-bold text-ink/90">Fitting Saya</h2>
                <p class="text-sm text-ink/60 mt-1">Jadwal fitting busana Anda.</p>

                <div class="mt-6">
                    @if ($fittingAktif)
                        @include('customer.akun._fitting-terdekat', ['fitting' => $fittingAktif])
                    @else
                        <div class="mt-8 flex flex-col items-center text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center">
                                <x-icon name="calendar" :size="28" class="text-ink/50" />
                            </div>
                            <p class="font-semibold text-ink/90 mt-4">Belum ada jadwal fitting</p>
                            <p class="text-sm text-ink/60 mt-1">Jadwalkan fitting agar ukuran busana Anda pas.</p>
                            <a href="{{ route('fitting.create') }}"
                               class="mt-6 bg-gold text-white px-6 py-3 rounded-full text-sm font-semibold hover:opacity-90">
                                Jadwalkan Fitting
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===== PANEL: REFUND ===== --}}
            <div data-panel-content="refund" class="hidden bg-white rounded-2xl border border-line/60 p-6 md:p-8">
                <h2 class="font-display text-2xl font-bold text-ink/90">Ajukan Refund</h2>
                <p class="text-sm text-ink/60 mt-1">Refund diajukan per pesanan.</p>

                <div class="mt-8 flex flex-col items-center text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center">
                        <x-icon name="refresh-cw" :size="28" class="text-ink/50" />
                    </div>
                    <p class="font-semibold text-ink/90 mt-4">Pilih pesanan yang ingin direfund</p>
                    <p class="text-sm text-ink/60 mt-1 max-w-md">Buka pesanan Anda untuk mengajukan refund.</p>
                    <a href="{{ route('akun.refund.index') }}"
                       class="mt-6 border border-line/60 px-6 py-3 rounded-full text-sm font-semibold text-ink/80 hover:bg-cream">
                        Buka Halaman Refund
                    </a>
                </div>
            </div>

        </section>
    </div>
</div>

{{-- ===== MODAL TAMBAH ALAMAT ===== --}}
@php
    $errAlamat = $errors->getBag('alamat');
    $inputAlamat = 'w-full border border-line/60 rounded-md px-4 py-3 text-sm bg-white placeholder:text-ink/40 focus:outline-none focus:border-gold disabled:bg-cream disabled:text-ink/40';
@endphp

<div id="alamat-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl max-h-[90vh] flex flex-col">

        <div class="px-6 py-5 border-b border-line/60 flex items-center justify-between">
            <h3 class="font-display text-xl font-bold text-ink/90">Alamat Baru</h3>
            <button type="button" data-tutup-alamat aria-label="Tutup"
                    class="w-9 h-9 rounded-full hover:bg-cream text-2xl leading-none text-ink/60">&times;</button>
        </div>

        <form method="POST" action="{{ route('akun.alamat.store') }}" class="flex flex-col overflow-hidden">
            @csrf

            <div class="px-6 py-5 overflow-y-auto space-y-4">

                @if ($errAlamat->any())
                    <div class="px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-700 space-y-1">
                        @foreach ($errAlamat->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <input type="text" name="nama_penerima" value="{{ old('nama_penerima') }}"
                           placeholder="Nama Lengkap" class="{{ $inputAlamat }}">
                    <input type="tel" name="no_hp" value="{{ old('no_hp') }}"
                           placeholder="Nomor Telepon" class="{{ $inputAlamat }}">
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <select id="alamat-provinsi" name="provinsi" class="{{ $inputAlamat }}">
                        <option value="">Memuat provinsi...</option>
                    </select>

                    <select id="alamat-kota" name="kota_kabupaten" class="{{ $inputAlamat }}" disabled>
                        <option value="">Pilih Kota/Kabupaten</option>
                    </select>

                    <select id="alamat-kecamatan" name="kecamatan" class="{{ $inputAlamat }}" disabled>
                        <option value="">Pilih kota terlebih dahulu</option>
                    </select>

                    <input type="text" name="kode_pos" id="alamat-kodepos" value="{{ old('kode_pos') }}"
                           placeholder="Kode Pos" class="{{ $inputAlamat }}">
                </div>

                {{-- Pencarian tujuan ongkir --}}
                <div class="relative">
                    <input type="text" id="ongkir-cari" autocomplete="off"
                           value="{{ old('ongkir_tujuan_label') }}"
                           placeholder="Cari kelurahan untuk ongkir (min. 3 huruf)"
                           class="{{ $inputAlamat }}">
                    <input type="hidden" name="ongkir_tujuan_id" id="ongkir-id" value="{{ old('ongkir_tujuan_id') }}">
                    <input type="hidden" name="ongkir_tujuan_label" id="ongkir-label" value="{{ old('ongkir_tujuan_label') }}">
                    <ul id="ongkir-hasil" class="hidden absolute z-20 left-0 right-0 mt-1 bg-white border border-line/60 rounded-md shadow-lg max-h-60 overflow-y-auto"></ul>
                    <p id="ongkir-terpilih" class="text-xs text-emerald-700 mt-2">
                        {{ old('ongkir_tujuan_label') ? 'Dipilih: ' . old('ongkir_tujuan_label') : '' }}
                    </p>
                </div>

                <textarea name="alamat" rows="3" placeholder="Nama jalan, gedung, No. rumah"
                          class="{{ $inputAlamat }}">{{ old('alamat') }}</textarea>

                <div>
                    <p class="text-xs font-semibold text-ink/60 mb-2">Tandai sebagai</p>
                    <div class="flex gap-3">
                        @foreach (['Rumah', 'Kantor'] as $lbl)
                            <label class="cursor-pointer">
                                <input type="radio" name="label" value="{{ $lbl }}" class="peer sr-only"
                                       @checked(old('label', 'Rumah') === $lbl)>
                                <span class="inline-block px-4 py-1.5 rounded-md border border-line/60 text-sm text-ink/70
                                             peer-checked:border-gold peer-checked:text-gold peer-checked:bg-gold/10">
                                    {{ $lbl }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-ink/80 cursor-pointer">
                    <input type="checkbox" name="is_utama" value="1" @checked(old('is_utama')) class="accent-[#C27C3E]">
                    Jadikan alamat utama
                </label>
            </div>

            <div class="px-6 py-4 border-t border-line/60 flex items-center justify-end gap-3">
                <button type="button" data-tutup-alamat
                        class="px-5 py-2.5 rounded-md text-sm font-semibold text-ink/70 hover:bg-cream">
                    Nanti Saja
                </button>
                <button type="submit"
                        class="px-8 py-2.5 rounded-md bg-gold text-white text-sm font-semibold hover:opacity-90">
                    OK
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('alamat-modal');
        if (!modal) return;

        const URL_PROVINSI = '{{ url('/ongkir/provinsi') }}';
        const URL_KOTA = '{{ url('/ongkir/kota') }}';
        const URL_KECAMATAN = '{{ url('/ongkir/kecamatan') }}';

        const selProv = document.getElementById('alamat-provinsi');
        const selKota = document.getElementById('alamat-kota');
        const selKec = document.getElementById('alamat-kecamatan');
        const kodePos = document.getElementById('alamat-kodepos');
        const cari = document.getElementById('ongkir-cari');
        const hasil = document.getElementById('ongkir-hasil');
        const idInput = document.getElementById('ongkir-id');
        const labelInput = document.getElementById('ongkir-label');
        const terpilih = document.getElementById('ongkir-terpilih');

        let sudahMuat = false;

        function bukaModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (!sudahMuat) {
                sudahMuat = true;
                muatProvinsi(
                    @json(old('provinsi', '')),
                    @json(old('kota_kabupaten', '')),
                    @json(old('kecamatan', ''))
                );
            }
        }

        function tutupModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

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
            sel.innerHTML = `<option value="">${pesan}</option>`;
            sel.disabled = true;
        }

        function resetOngkir() {
            cari.value = '';
            idInput.value = '';
            labelInput.value = '';
            terpilih.textContent = '';
            hasil.classList.add('hidden');
        }

        async function muatProvinsi(pilihProv = '', pilihKota = '', pilihKec = '') {
            try {
                isiPilihan(selProv, await ambilData(URL_PROVINSI), 'Pilih Provinsi', pilihProv);
            } catch (e) {
                gagal(selProv, 'Gagal memuat provinsi');
                return;
            }
            if (selProv.value) await muatKota(pilihKota, pilihKec);
        }

        async function muatKota(pilihKota = '', pilihKec = '') {
            const provOpt = selProv.selectedOptions[0];
            selKec.innerHTML = '<option value="">Pilih kota terlebih dahulu</option>';
            selKec.disabled = true;

            if (!provOpt || !provOpt.dataset.id) {
                gagal(selKota, 'Pilih provinsi terlebih dahulu');
                return;
            }

            try {
                isiPilihan(selKota, await ambilData(`${URL_KOTA}/${provOpt.dataset.id}`), 'Pilih Kota/Kabupaten', pilihKota);
            } catch (e) {
                gagal(selKota, 'Gagal memuat kota');
                return;
            }
            if (selKota.value) await muatKecamatan(pilihKec);
        }

        async function muatKecamatan(pilihKec = '') {
            const kotaOpt = selKota.selectedOptions[0];
            if (!kotaOpt || !kotaOpt.dataset.id) {
                gagal(selKec, 'Pilih kota terlebih dahulu');
                return;
            }

            try {
                isiPilihan(selKec, await ambilData(`${URL_KECAMATAN}/${kotaOpt.dataset.id}`), 'Pilih Kecamatan', pilihKec);
            } catch (e) {
                gagal(selKec, 'Gagal memuat kecamatan');
            }
        }

        // Perubahan pilihan dari user
        selProv.addEventListener('change', () => { resetOngkir(); muatKota(); });
        selKota.addEventListener('change', () => { resetOngkir(); muatKecamatan(); });
        selKec.addEventListener('change', () => {
            if (selKec.value && !cari.value.trim()) {
                cari.value = selKec.value + ', ' + selKota.value;
                cari.dispatchEvent(new Event('input'));
            }
        });

        // Pencarian tujuan ongkir
        let timer;
        cari.addEventListener('input', () => {
            clearTimeout(timer);
            idInput.value = '';
            labelInput.value = '';
            const q = cari.value.trim();
            if (q.length < 3) { hasil.classList.add('hidden'); return; }

            timer = setTimeout(async () => {
                hasil.innerHTML = '';
                try {
                    const data = await ambilData('{{ route('ongkir.tujuan') }}?q=' + encodeURIComponent(q));

                    if (!data.length) {
                        hasil.innerHTML = '<li class="px-4 py-2 text-sm text-ink/60">Tidak ditemukan</li>';
                    }
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
                } catch (e) {
                    hasil.innerHTML = '<li class="px-4 py-2 text-sm text-rose-600">Layanan ongkir sedang tidak tersedia</li>';
                }
                hasil.classList.remove('hidden');
            }, 400);
        });

        modal.querySelectorAll('[data-tutup-alamat]').forEach(el => el.addEventListener('click', tutupModal));
        modal.addEventListener('click', e => { if (e.target === modal) tutupModal(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupModal(); });
        document.querySelectorAll('[data-buka-alamat]').forEach(el => el.addEventListener('click', bukaModal));

        // Jika validasi gagal, popup langsung terbuka lagi dengan pilihan sebelumnya
        @if ($errAlamat->any())
            bukaModal();
        @endif
    })();
</script>

{{-- ===== MODAL LIHAT FOTO PROFIL ===== --}}
<div id="foto-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-md p-4">
    <button type="button" id="foto-modal-tutup" aria-label="Tutup"
            class="absolute top-6 right-6 md:top-8 md:right-10 w-10 h-10 rounded-full bg-white text-ink flex items-center justify-center shadow-lg hover:scale-105 transition">
        <span class="text-2xl leading-none">&times;</span>
    </button>
    <img id="foto-modal-img" src="" alt="Foto profil"
         class="hidden w-64 h-64 md:w-96 md:h-96 rounded-full object-cover border-4 border-white/40 shadow-2xl">
    <div id="foto-modal-inisial"
         class="hidden w-64 h-64 md:w-96 md:h-96 rounded-full bg-gradient-to-br from-gold to-[#8f5a2c] text-white font-display text-8xl flex items-center justify-center border-4 border-white/40 shadow-2xl"></div>
</div>

<script>
    (function () {
        const PANEL_DEFAULT = 'profil';
        const panels = document.querySelectorAll('[data-panel-content]');
        const navItems = document.querySelectorAll('aside .panel-nav');

        // Panel awal: hash di URL dulu, lalu jika dibuka dari tab/pencarian tampilkan Ringkasan
        function panelAwal() {
            if (location.hash.length > 1) return location.hash.slice(1);
            const params = new URLSearchParams(location.search);
            return (params.has('status') || params.has('q')) ? 'ringkasan' : PANEL_DEFAULT;
        }

        function tampilkan(nama) {
            if (!document.querySelector(`[data-panel-content="${nama}"]`)) nama = PANEL_DEFAULT;

            panels.forEach(p => p.classList.toggle('hidden', p.dataset.panelContent !== nama));

            navItems.forEach(n => {
                const aktif = n.dataset.panel === nama;
                n.classList.toggle('bg-gold/10', aktif);
                n.classList.toggle('text-gold', aktif);
                n.classList.toggle('font-semibold', aktif);
                n.classList.toggle('text-ink/80', !aktif);
            });

            history.replaceState(null, '', '#' + nama);
        }

        document.querySelectorAll('[data-panel]').forEach(el => {
            el.addEventListener('click', e => {
                e.preventDefault();
                tampilkan(el.dataset.panel);
            });
        });

        window.addEventListener('hashchange', () => tampilkan(location.hash.slice(1) || PANEL_DEFAULT));
        tampilkan(panelAwal());

        // Pratinjau foto sebelum disimpan
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

        // Lihat foto profil dalam ukuran besar
        const modal = document.getElementById('foto-modal');
        const modalImg = document.getElementById('foto-modal-img');
        const modalInisial = document.getElementById('foto-modal-inisial');

        function bukaFoto(el) {
            if (el.tagName === 'IMG' && el.getAttribute('src')) {
                modalImg.src = el.src;
                modalImg.classList.remove('hidden');
                modalInisial.classList.add('hidden');
            } else {
                modalInisial.textContent = el.textContent.trim();
                modalInisial.classList.remove('hidden');
                modalImg.classList.add('hidden');
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function tutupFoto() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.querySelectorAll('[data-lihat-foto]').forEach(el => {
            el.addEventListener('click', () => bukaFoto(el));
        });

        document.getElementById('foto-modal-tutup').addEventListener('click', tutupFoto);
        modal.addEventListener('click', e => { if (e.target === modal) tutupFoto(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupFoto(); });
    })();
</script>
@endsection