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
@endphp

<div class="bg-cream min-h-screen py-8 md:py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 grid md:grid-cols-[280px_1fr] gap-6 items-start">

        {{-- SIDEBAR --}}
        <aside class="space-y-5 md:sticky md:top-6">
            <div class="bg-white rounded-2xl border border-line/60 p-5 flex items-center gap-4">
                <div class="relative shrink-0">
                    @if($adaFoto)
                        <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}"
                             class="w-14 h-14 rounded-full object-cover border-2 border-white shadow">
                    @else
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-gold to-[#8f5a2c] text-white font-display text-xl flex items-center justify-center">
                            {{ $inisial }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-ink/90">Halo, {{ $user->name }}!</p>
                    <p class="text-xs text-ink/60 mt-1">Selamat datang kembali di akun Anda.</p>
                </div>
            </div>

            <nav class="bg-white rounded-2xl border border-line/60 p-2">
                <p class="px-3 pt-3 pb-2 text-xs font-semibold tracking-wider text-ink/50">AKUN SAYA</p>

                <button type="button" data-panel="profil" class="panel-nav w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm text-ink/80 hover:bg-cream text-left">
                    <span class="flex items-center gap-3"><x-icon name="user" :size="16" /> Edit Profil</span>
                </button>

                <button type="button" data-panel="alamat" class="panel-nav w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm text-ink/80 hover:bg-cream text-left">
                    <span class="flex items-center gap-3"><x-icon name="map-pin" :size="16" /> Alamat Saya</span>
                </button>

                <button type="button" data-panel="ringkasan" class="panel-nav w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm text-ink/80 hover:bg-cream text-left">
                    <span class="flex items-center gap-3"><x-icon name="cart" :size="16" /> Pesanan Saya</span>
                    <span class="text-xs font-semibold bg-gold text-white rounded-full px-2 py-0.5">{{ $pesananAktif }}</span>
                </button>

                <button type="button" data-panel="penyewaan" class="panel-nav w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm text-ink/80 hover:bg-cream text-left">
                    <span class="flex items-center gap-3"><x-icon name="cart" :size="16" /> Penyewaan</span>
                    <span class="text-xs text-ink/60">{{ $penyewaanAktif }}</span>
                </button>

                <button type="button" data-panel="fitting" class="panel-nav w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm text-ink/80 hover:bg-cream text-left">
                    <span class="flex items-center gap-3"><x-icon name="calendar" :size="16" /> Fitting Saya</span>
                    <span class="text-xs text-ink/60">{{ $totalFitting }}</span>
                </button>

                <button type="button" data-panel="refund" class="panel-nav w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm text-ink/80 hover:bg-cream text-left">
                    <span class="flex items-center gap-3"><x-icon name="refresh-cw" :size="16" /> Ajukan Refund</span>
                </button>
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
                        <a href="{{ route('akun.pesanan.index') }}"
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

                <form method="POST" action="{{ route('akun.profil.update') }}" class="md:grid md:grid-cols-[1fr_220px]">
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
                            <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}"
                                 class="w-32 h-32 rounded-full object-cover">
                        @else
                            <div class="w-32 h-32 rounded-full bg-cream flex items-center justify-center font-display text-4xl text-ink/60">
                                {{ $inisial }}
                            </div>
                        @endif

                        <button type="button" disabled
                                class="mt-5 border border-line/60 px-5 py-2 rounded-md text-sm text-ink/40 cursor-not-allowed">
                            Pilih Gambar
                        </button>
                        <p class="text-xs text-ink/50 mt-3 leading-relaxed">
                            Ukuran gambar: maks. 1 MB<br>Format gambar: .JPEG, .PNG
                        </p>
                    </div>
                </form>
            </div>

            {{-- ===== PANEL: ALAMAT ===== --}}
            <div data-panel-content="alamat" class="hidden bg-white rounded-2xl border border-line/60 p-6 md:p-8">
                <h2 class="font-display text-2xl font-bold text-ink/90">Alamat Saya</h2>
                <p class="text-sm text-ink/60 mt-1">Alamat pengiriman dan pengembalian busana.</p>

                <div class="mt-8 flex flex-col items-center text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-cream flex items-center justify-center">
                        <x-icon name="map-pin" :size="28" class="text-ink/50" />
                    </div>
                    <p class="font-semibold text-ink/90 mt-4">Belum ada alamat tersimpan</p>
                    <p class="text-sm text-ink/60 mt-1">Fitur tambah alamat akan segera tersedia.</p>
                    <button type="button" disabled class="mt-6 border border-line/60 px-6 py-3 rounded-full text-sm font-semibold text-ink/40 cursor-not-allowed">
                        Tambah Alamat
                    </button>
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

<script>
    (function () {
        const DEFAULT = 'profil';
        const panels = document.querySelectorAll('[data-panel-content]');
        const navItems = document.querySelectorAll('aside .panel-nav');

        function tampilkan(nama) {
            if (!document.querySelector(`[data-panel-content="${nama}"]`)) nama = DEFAULT;

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

        window.addEventListener('hashchange', () => tampilkan(location.hash.slice(1) || DEFAULT));
        tampilkan(location.hash.slice(1) || DEFAULT);
    })();
</script>
@endsection