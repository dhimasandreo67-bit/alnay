@extends('layouts.public')

@section('title', 'Ubah Jadwal Fitting — ' . config('alnay.brand.nama'))

@section('content')

<style>
    [x-cloak] { display: none !important; }
    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }
    @keyframes pd-shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
    @keyframes pd-spin { to { transform: rotate(360deg); } }
    @keyframes pd-pulse { 0%, 100% { opacity: .4; } 50% { opacity: 1; } }

    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }
    .pd-shake { animation: pd-shake .45s ease; }
    .pd-spin  { animation: pd-spin .8s linear infinite; }
    .pd-pulse { animation: pd-pulse 2s ease-in-out infinite; }

    .pd-shine { position: relative; overflow: hidden; }
    .pd-shine::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
        transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
    }
    .pd-shine:hover::after { transform: translateX(120%); }

    @media (prefers-reduced-motion: reduce) {
        .pd-up, .pd-pop, .pd-shake, .pd-spin, .pd-pulse { animation: none !important; }
        .pd-shine::after { transition: none !important; }
    }
</style>

@php
    $tanggalPenuh = $tanggalPenuh ?? [];
    $slotJam = $slotJam ?? ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];
    $slotTerisi = $slotTerisi ?? [];

    $cfg = [
        'tanggalPenuh' => $tanggalPenuh,
        'slotJam' => $slotJam,
        'slotTerisi' => $slotTerisi,
        'nama' => auth()->user()->name ?? '',
        'noWa' => auth()->user()->no_hp ?? '',
        'produkDefault' => $reservasi->produk->nama ?? '',
        'tanggalDefault' => $reservasi->slot->tanggal ?? null,
        'jamDefault' => $reservasi->slot->jam_mulai ?? null,
        'tanggalMaksimal' => $tanggalMaksimal ?? null, // <-- SINKRONISASI DITAMBAHKAN
    ];
@endphp

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" x-data="fittingEditForm(@js($cfg))">
    <div class="max-w-[1180px] mx-auto px-4 md:px-6">

        @if ($errors->any())
            <div class="pd-up p-4 mb-4 rounded-lg bg-red-50 border border-red-200 text-[13px] text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="pd-up mb-6" style="--d: .05s;">
            <h1 class="font-display text-2xl md:text-3xl font-medium text-ink/85 mb-2">Ubah Jadwal Fitting</h1>
            <p class="text-[12px] text-ink/55 max-w-2xl leading-relaxed">
                Silakan pilih tanggal dan jam baru untuk sesi fitting Anda. Jadwal lama Anda akan digantikan dengan jadwal yang baru.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 lg:gap-8">
            <div class="space-y-4">
                {{-- 1. Pilih Tanggal --}}
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .2s;">
                    <h2 class="font-display text-base font-medium text-ink/80 mb-1">1. Pilih Tanggal Baru</h2>
                    <p class="text-[11px] text-ink/50 mb-4">Tanggal penuh atau tutup tidak bisa dipilih.</p>

                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold text-ink/80 capitalize" x-text="namaBulan"></span>
                        <div class="flex gap-1">
                            <button type="button" @click="bulanSebelumnya()" :disabled="!bisaMundur" class="w-6 h-6 rounded-md border border-line/50 bg-white flex items-center justify-center text-ink/40 hover:border-gold/60 hover:text-gold/80 disabled:opacity-40 disabled:cursor-not-allowed hover:-translate-x-0.5 transition-all duration-300">
                                <x-icon name="chevron-right" :size="10" class="rotate-180" />
                            </button>
                            <button type="button" @click="bulanBerikutnya()" class="w-6 h-6 rounded-md border border-line/50 bg-white flex items-center justify-center text-ink/40 hover:border-gold/60 hover:text-gold/80 hover:translate-x-0.5 transition-all duration-300">
                                <x-icon name="chevron-right" :size="10" />
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-0.5 text-center transition-all duration-150" :class="gantiBulan ? 'opacity-0 translate-y-1' : 'opacity-100'">
                        <template x-for="(h, idx) in namaHari" :key="idx">
                            <div class="text-[9px] text-ink/40 py-1 font-medium" x-text="h"></div>
                        </template>
                        <template x-for="c in sel" :key="c.t">
                            <button type="button" @click="pilihTanggal(c.t)" :disabled="c.luar || nonaktif(c.t)" :class="kelasTanggal(c)" class="h-7 sm:h-8 mx-0.5 rounded-md text-[10px] font-medium transition-all duration-300 ease-out">
                                <span x-text="c.hari"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- 2. Pilih Jam --}}
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .25s;">
                    <h2 class="font-display text-base font-medium text-ink/80 mb-1">2. Pilih Jam Kedatangan</h2>
                    <p class="text-[11px] text-ink/50 mb-4" x-text="tanggalDipilih ? 'Tanggal: ' + formatTanggal(tanggalDipilih) + ' — ALNAY.ID Studio' : 'Pilih tanggal terlebih dahulu.'"></p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <template x-for="jam in slotJam" :key="jam">
                            <button type="button" @click="pilihJam(jam)" :disabled="jamTerisi(jam) || !tanggalDipilih" :class="kelasJam(jam)" class="h-9 rounded-md border text-[11px] font-medium transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                                <span x-text="jam"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="lg:sticky lg:top-24 h-fit">
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .35s;">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-gold pd-pulse"></span>
                        <span class="text-[10px] font-medium text-gold">Form Ubah Jadwal</span>
                    </div>

                    <div class="space-y-2 text-[11px] mb-4 border-b border-line/30 pb-4">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Jadwal Lama</span>
                            <span class="font-medium text-ink/80 text-right">
                                {{ $reservasi->slot ? \Carbon\Carbon::parse($reservasi->slot->tanggal)->format('d M Y') . ' - ' . $reservasi->slot->jam_mulai : '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Jadwal Baru</span>
                            <span class="font-medium text-ink/80 text-right" x-text="tanggalDipilih ? formatTanggal(tanggalDipilih) + ' - ' + (jamDipilih || 'Pilih Jam') : 'Belum dipilih'"></span>
                        </div>
                    </div>

                    <p x-show="errorMetode" x-cloak x-transition x-text="errorMetode" class="mb-3 p-2.5 rounded-md bg-rose-50 border border-rose-200 text-xs text-rose-700"></p>

                    <form method="POST" action="{{ route('fitting.update', $reservasi->id) }}" x-ref="formFitting" @submit.prevent="kirim()">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tanggal" x-ref="inputTanggal">
                        <input type="hidden" name="jam" x-ref="inputJam">

                        <button type="submit" :disabled="sedangKirim" class="pd-shine w-full h-[42px] rounded-lg text-white text-[10px] font-semibold tracking-wider transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait disabled:hover:translate-y-0" :class="tanggalDipilih && jamDipilih ? 'bg-gold hover:bg-gold/90 hover:shadow-gold/30' : 'bg-ink/20 cursor-not-allowed'">
                            <span x-show="!sedangKirim">Simpan Perubahan Jadwal</span>
                            <span x-show="sedangKirim" x-cloak class="inline-flex items-center justify-center gap-2">
                                <svg class="pd-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 3a9 9 0 1 0 9 9" /></svg> Memproses…
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="pd-up mt-8 text-center" style="--d: .4s;">
            <a href="{{ route('fitting.show', $reservasi->id) }}" class="inline-flex items-center gap-1.5 text-[11px] text-gold font-medium hover:underline">
                <x-icon name="chevron-right" :size="12" class="rotate-180" /> Kembali ke Rincian Fitting
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('fittingEditForm', (cfg) => {
            const pad = (n) => String(n).padStart(2, '0');
            const iso = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
            const now = new Date();
            const reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

            let initTgl = cfg.tanggalDefault;
            let initBulan = now.getMonth();
            let initTahun = now.getFullYear();
            
            if (initTgl) {
                const d = new Date(initTgl);
                initBulan = d.getMonth();
                initTahun = d.getFullYear();
            }

            return {
                tanggalPenuh: cfg.tanggalPenuh,
                slotJam: cfg.slotJam,
                slotTerisi: cfg.slotTerisi,
                tanggalMaksimal: cfg.tanggalMaksimal, // <-- SINKRONISASI DITAMBAHKAN

                tanggalDipilih: cfg.tanggalDefault,
                jamDipilih: cfg.jamDefault,

                tahun: initTahun,
                bulan: initBulan,
                hariIni: iso(now.getFullYear(), now.getMonth(), now.getDate()),
                namaHari: ['S', 'S', 'R', 'K', 'J', 'S', 'M'],

                gantiBulan: false,
                errorMetode: '',
                sedangKirim: false,

                get namaBulan() {
                    return new Date(Date.UTC(this.tahun, this.bulan, 1)).toLocaleDateString('id-ID', { month: 'long', year: 'numeric', timeZone: 'UTC' });
                },
                get bisaMundur() {
                    return this.tahun > now.getFullYear() || (this.tahun === now.getFullYear() && this.bulan > now.getMonth());
                },
                get sel() {
                    const pertama = new Date(Date.UTC(this.tahun, this.bulan, 1));
                    const offset = (pertama.getUTCDay() + 6) % 7;
                    const cells = [];
                    for (let i = 0; i < 42; i++) {
                        const d = new Date(Date.UTC(this.tahun, this.bulan, 1 - offset + i));
                        cells.push({ t: iso(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()), hari: d.getUTCDate(), luar: d.getUTCMonth() !== this.bulan });
                    }
                    return cells.slice(35).every(c => c.luar) ? cells.slice(0, 35) : cells;
                },

                transisiBulan(fn) {
                    if (reduce) { fn(); return; }
                    this.gantiBulan = true;
                    setTimeout(() => { fn(); this.gantiBulan = false; }, 150);
                },
                bulanSebelumnya() {
                    if (!this.bisaMundur) return;
                    this.transisiBulan(() => {
                        if (this.bulan === 0) { this.bulan = 11; this.tahun--; } else { this.bulan--; }
                    });
                },
                bulanBerikutnya() {
                    this.transisiBulan(() => {
                        if (this.bulan === 11) { this.bulan = 0; this.tahun++; } else { this.bulan++; }
                    });
                },

                lalu(t) { return t < this.hariIni; },
                penuh(t) { return this.tanggalPenuh.includes(t); },
                nonaktif(t) {
                    // LOGIKA SINKRONISASI: Cek maksimal tanggal
                    if (this.tanggalMaksimal && t > this.tanggalMaksimal) return true;
                    return this.lalu(t) || this.penuh(t);
                },

                kelasTanggal(c) {
                    if (this.tanggalDipilih === c.t) return 'bg-ink/90 text-gold font-semibold scale-105 shadow-sm';
                    // LOGIKA SINKRONISASI: Cek maksimal tanggal
                    if (this.tanggalMaksimal && c.t > this.tanggalMaksimal) return 'bg-rose-50 text-rose-400 cursor-not-allowed';
                    if (c.luar || this.lalu(c.t)) return 'text-ink/25 cursor-default';
                    if (this.penuh(c.t)) return 'bg-rose-50 text-rose-400 cursor-not-allowed';
                    return 'text-ink/60 hover:bg-cream hover:text-ink hover:scale-110 active:scale-95';
                },

                pilihTanggal(t) {
                    if (this.nonaktif(t)) return;
                    this.tanggalDipilih = t;
                    this.jamDipilih = null;
                    this.errorMetode = '';
                },

                jamTerisi(jam) { return this.slotTerisi.includes(jam); },

                kelasJam(jam) {
                    if (this.jamDipilih === jam) return 'bg-ink/90 text-gold border-ink/90 font-semibold';
                    if (this.jamTerisi(jam)) return 'bg-cream/60 text-ink/30 border-line/30 cursor-not-allowed';
                    if (!this.tanggalDipilih) return 'bg-white text-ink/40 border-line/50 cursor-not-allowed';
                    return 'bg-white text-ink/70 border-line/50 hover:border-gold/50 hover:text-ink/90';
                },

                pilihJam(jam) {
                    if (!this.tanggalDipilih || this.jamTerisi(jam)) return;
                    this.jamDipilih = jam;
                    this.errorMetode = '';
                },

                formatTanggal(t) {
                    if (!t) return '—';
                    return new Date(t).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
                },

                kirim() {
                    if (this.sedangKirim) return;
                    if (!this.tanggalDipilih || !this.jamDipilih) {
                        this.errorMetode = 'Pilih tanggal dan jam baru terlebih dahulu.';
                        return;
                    }
                    this.errorMetode = '';
                    this.sedangKirim = true;
                    this.$refs.inputTanggal.value = this.tanggalDipilih;
                    this.$refs.inputJam.value = this.jamDipilih;
                    this.$refs.formFitting.submit();
                },
            };
        });
    });
</script>

@endsection