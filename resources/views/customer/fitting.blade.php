@extends('layouts.public')

@section('title', 'Booking Fitting — ' . config('alnay.brand.nama'))

@section('content')

@php
    // Tanggal penuh (pink) — hardcode sementara
    $tanggalPenuh = $tanggalPenuh ?? [];
    $slotJam = $slotJam ?? ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:00'];
    $slotTerisi = $slotTerisi ?? [];
@endphp

<div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" x-data="fittingForm(@js([
    'tanggalPenuh' => $tanggalPenuh,
    'slotJam' => $slotJam,
    'slotTerisi' => $slotTerisi,
]))">
    <div class="max-w-[1180px] mx-auto px-4 md:px-6">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="font-display text-2xl md:text-3xl font-semibold text-ink/90 mb-2">Booking Fitting di Studio</h1>
            <p class="text-[12px] text-ink/50 max-w-2xl leading-relaxed">
                Karena Anda memilih ambil sendiri di toko, sesi fitting wajib dilakukan dulu agar ukuran benar-benar pas dan busana tidak rusak saat dipakai. Pilih tanggal & jam sebelum tanggal pengambilan busana Anda.
            </p>
        </div>

        {{-- Info Box --}}
        <div class="bg-emerald-50 border border-emerald-200/50 rounded-lg p-4 mb-6 flex gap-3">
            <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
            <div>
                <p class="text-[11px] font-semibold text-emerald-800 mb-0.5">Sesi fitting</p>
                <p class="text-[11px] text-emerald-700/80 leading-relaxed">Jadwal sewa Anda sudah terkunci lewat DP, tapi busana baru bisa diambil setelah ukuran dikonfirmasi lewat sesi fitting ini.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 lg:gap-8">

            {{-- ==================== KOLOM KIRI ==================== --}}
            <div class="space-y-4">

                {{-- Lokasi Fitting --}}
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <h2 class="font-display text-base font-semibold text-ink/80 mb-1">Lokasi Fitting</h2>
                    <p class="text-[11px] text-ink/50 mb-3">Sesi fitting dilakukan langsung di lokasi kami — bukan mengirim tim ke tempat Anda.</p>

                    <div class="flex items-start justify-between gap-3 p-3 rounded-lg bg-cream/50 border border-line/30">
                        <div>
                            <p class="text-[12px] font-semibold text-ink/80">ALNAY.ID</p>
                            <p class="text-[10px] text-ink/50 mt-0.5">{{ config('alnay.studio.alamat') }}</p>
                            <span class="inline-block mt-2 px-2 py-0.5 rounded bg-gold/10 text-gold text-[9px] font-medium">{{ config('alnay.studio.jam_buka') }}</span>
                        </div>
                        <a href="#" class="shrink-0 px-3 py-1.5 rounded-md border border-line/50 text-[10px] font-medium text-ink/60 hover:border-gold/50 hover:text-gold transition-all">
                            Buka di Google Maps →
                        </a>
                    </div>
                </div>

                {{-- 1. Pilih Tanggal --}}
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <h2 class="font-display text-base font-semibold text-ink/80 mb-1">1. Pilih Tanggal</h2>
                    <p class="text-[11px] text-ink/50 mb-4">Kapasitas tiap hari diatur oleh admin — tanggal penuh atau tutup tidak bisa dipilih.</p>

                    {{-- Header Kalender --}}
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold text-ink/80 capitalize" x-text="namaBulan"></span>
                        <div class="flex gap-1">
                            <button type="button" @click="bulanSebelumnya()" :disabled="!bisaMundur"
                                    class="w-6 h-6 rounded-md border border-line/50 bg-white flex items-center justify-center text-ink/40 hover:border-gold/60 hover:text-gold/80 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-300">
                                <x-icon name="chevron-right" :size="10" class="rotate-180" />
                            </button>
                            <button type="button" @click="bulanBerikutnya()"
                                    class="w-6 h-6 rounded-md border border-line/50 bg-white flex items-center justify-center text-ink/40 hover:border-gold/60 hover:text-gold/80 transition-all duration-300">
                                <x-icon name="chevron-right" :size="10" />
                            </button>
                        </div>
                    </div>

                    {{-- Grid Tanggal --}}
                    <div class="grid grid-cols-7 gap-0.5 text-center">
                        <template x-for="(h, idx) in namaHari" :key="idx">
                            <div class="text-[9px] text-ink/40 py-1 font-medium" x-text="h"></div>
                        </template>

                        <template x-for="c in sel" :key="c.t">
                            <button type="button" @click="pilihTanggal(c.t)" :disabled="c.luar || nonaktif(c.t)"
                                    :class="kelasTanggal(c)"
                                    class="h-7 sm:h-8 mx-0.5 rounded-md text-[10px] font-medium transition-all duration-300 ease-out">
                                <span x-text="c.hari"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Legend --}}
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-3 text-[9px] text-ink/40">
                        <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-ink/80"></span> Dipilih</div>
                        <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-200/80"></span> Penuh</div>
                        <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-ink/10"></span> Studio tutup</div>
                        <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full border border-line/50"></span> Tersedia</div>
                    </div>
                </div>

                {{-- 2. Pilih Jam Kedatangan --}}
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <h2 class="font-display text-base font-semibold text-ink/80 mb-1">2. Pilih Jam Kedatangan</h2>
                    <p class="text-[11px] text-ink/50 mb-4" x-text="tanggalDipilih ? 'Tanggal: ' + formatTanggal(tanggalDipilih) + ' — ALNAY.ID Studio' : 'Pilih tanggal terlebih dahulu.'"></p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <template x-for="jam in slotJam" :key="jam">
                            <button type="button" 
                                    @click="pilihJam(jam)"
                                    :disabled="jamTerisi(jam)"
                                    :class="kelasJam(jam)"
                                    class="h-9 rounded-md border text-[11px] font-medium transition-all duration-300">
                                <span x-text="jam"></span>
                            </button>
                        </template>
                    </div>

                    <p x-show="!tanggalDipilih" class="text-[10px] text-ink/40 mt-3">Pilih tanggal dulu untuk melihat slot yang tersedia.</p>
                </div>

                {{-- 3. Data Pemesan --}}
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <h2 class="font-display text-base font-semibold text-ink/80 mb-1">3. Data Pemesan</h2>
                    <p class="text-[11px] text-ink/50 mb-4">Isi data untuk konfirmasi via WhatsApp.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-[10px] font-medium text-ink/60 mb-1">Nama Lengkap</label>
                            <input type="text" x-model="nama" value="{{ auth()->user()->name ?? '' }}" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-ink/60 mb-1">Nomor WhatsApp Aktif</label>
                            <input type="text" x-model="noWa" value="{{ auth()->user()->no_hp ?? '' }}" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 focus:outline-none focus:border-gold/60">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="block text-[10px] font-medium text-ink/60 mb-1">Produk yang Ingin Dicoba (opsional)</label>
                        <input type="text" x-model="produk" placeholder="Contoh: Kebaya Pengantin" class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 placeholder:text-ink/30 focus:outline-none focus:border-gold/60">
                    </div>

                    <div>
                        <label class="block text-[10px] font-medium text-ink/60 mb-1">Catatan Tambahan (opsional)</label>
                        <textarea x-model="catatan" rows="2" placeholder="Contoh: ingin konsultasi warna aksen juga." class="w-full px-3 py-2 rounded-md border border-line/50 bg-white text-[12px] text-ink/80 placeholder:text-ink/30 focus:outline-none focus:border-gold/60 resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- ==================== KOLOM KANAN — RINGKASAN ==================== --}}
            <div class="lg:sticky lg:top-24 h-fit">
                <div class="bg-panel border border-line/40 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full" :class="tanggalDipilih && jamDipilih ? 'bg-emerald-500' : 'bg-gold animate-pulse'"></span>
                        <span class="text-[10px] font-semibold" :class="tanggalDipilih && jamDipilih ? 'text-emerald-700' : 'text-gold'" x-text="tanggalDipilih && jamDipilih ? 'Jadwal siap dikonfirmasi' : 'Jadwal fitting belum dikonfirmasi'"></span>
                    </div>

                    <div class="space-y-2 text-[11px] mb-4 border-b border-line/30 pb-4">
                        <div class="flex justify-between">
                            <span class="text-ink/50">Tanggal</span>
                            <span class="font-semibold text-ink/80" x-text="tanggalDipilih ? formatTanggal(tanggalDipilih) : 'Belum dipilih'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Jam</span>
                            <span class="font-semibold text-ink/80" x-text="jamDipilih || 'Belum dipilih'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Produk</span>
                            <span class="font-semibold text-ink/80 text-right truncate max-w-[180px]" x-text="produk || '—'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink/50">Biaya Fitting</span>
                            <span class="font-semibold text-emerald-600">Gratis</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-md bg-cream/60 border border-line/30 mb-4">
                        <p class="text-[10px] text-ink/50 leading-relaxed">Anda akan menerima notifikasi 1 hari sebelum jadwal fitting. Jadwal otomatis tercatat di sistem kami setelah dikonfirmasi.</p>
                    </div>

                    <form method="POST" action="{{ route('fitting.store') }}" x-ref="formFitting">
                        @csrf
                        <input type="hidden" name="tanggal" x-ref="inputTanggal">
                        <input type="hidden" name="jam" x-ref="inputJam">
                        <input type="hidden" name="nama" x-ref="inputNama">
                        <input type="hidden" name="no_wa" x-ref="inputNoWa">
                        <input type="hidden" name="produk" x-ref="inputProduk">
                        <input type="hidden" name="catatan" x-ref="inputCatatan">
                        
                        <button type="submit" 
                                @click.prevent="submitForm()"
                                :disabled="!tanggalDipilih || !jamDipilih"
                                :class="tanggalDipilih && jamDipilih ? 'bg-gold/95 hover:bg-gold' : 'bg-ink/20 cursor-not-allowed'"
                                class="w-full h-[42px] rounded-lg text-white text-[10px] font-semibold tracking-wider transition-all duration-300">
                            Konfirmasi Booking Fitting
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Link kembali ke produk --}}
        <div class="mt-8 text-center">
            <a href="{{ route('koleksi.index') }}" class="inline-flex items-center gap-1.5 text-[11px] text-gold hover:underline">
                <x-icon name="chevron-right" :size="12" class="rotate-180" />
                Kembali ke Katalog Produk
            </a>
        </div>
    </div>
</div>

<script>
function fittingForm(cfg) {
    const pad = (n) => String(n).padStart(2, '0');
    const iso = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
    const now = new Date();

    return {
        tanggalPenuh: cfg.tanggalPenuh,
        slotJam: cfg.slotJam,
        slotTerisi: cfg.slotTerisi,
        
        tanggalDipilih: null,
        jamDipilih: null,
        
        nama: '{{ auth()->user()->name ?? '' }}',
        noWa: '{{ auth()->user()->no_hp ?? '' }}',
        produk: '',
        catatan: '',

        tahun: now.getFullYear(),
        bulan: now.getMonth(),
        hariIni: iso(now.getFullYear(), now.getMonth(), now.getDate()),
        namaHari: ['S', 'S', 'R', 'K', 'J', 'S', 'M'],

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
        bulanSebelumnya() { 
            if (!this.bisaMundur) return; 
            if (this.bulan === 0) { this.bulan = 11; this.tahun--; } else { this.bulan--; } 
        },
        bulanBerikutnya() { 
            if (this.bulan === 11) { this.bulan = 0; this.tahun++; } else { this.bulan++; } 
        },

        lalu(t) { return t < this.hariIni; },
        penuh(t) { return this.tanggalPenuh.includes(t); },
        nonaktif(t) { return this.lalu(t) || this.penuh(t); },

        kelasTanggal(c) {
            if (this.tanggalDipilih === c.t) return 'bg-ink/90 text-gold font-medium';
            if (c.luar || this.lalu(c.t)) return 'text-ink/20 cursor-default';
            if (this.penuh(c.t)) return 'bg-rose-50 text-rose-300/80 cursor-not-allowed';
            return 'text-ink/60 hover:bg-cream/60';
        },

        pilihTanggal(t) {
            if (this.nonaktif(t)) return;
            this.tanggalDipilih = t;
            this.jamDipilih = null;
        },

        jamTerisi(jam) {
            return this.slotTerisi.includes(jam);
        },

        kelasJam(jam) {
            if (this.jamDipilih === jam) return 'bg-ink/90 text-gold border-ink/90 font-medium';
            if (this.jamTerisi(jam)) return 'bg-cream/60 text-ink/30 border-line/30 cursor-not-allowed';
            if (!this.tanggalDipilih) return 'bg-white text-ink/40 border-line/50 cursor-not-allowed';
            return 'bg-white text-ink/70 border-line/50 hover:border-gold/50 hover:text-ink/90';
        },

        pilihJam(jam) {
            if (!this.tanggalDipilih || this.jamTerisi(jam)) return;
            this.jamDipilih = jam;
        },

        formatTanggal(t) {
            if (!t) return '—';
            const d = new Date(t);
            return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
        },

        submitForm() {
            if (!this.tanggalDipilih || !this.jamDipilih) {
                alert('Pilih tanggal dan jam terlebih dahulu.');
                return;
            }
            this.$refs.inputTanggal.value = this.tanggalDipilih;
            this.$refs.inputJam.value = this.jamDipilih;
            this.$refs.inputNama.value = this.nama;
            this.$refs.inputNoWa.value = this.noWa;
            this.$refs.inputProduk.value = this.produk;
            this.$refs.inputCatatan.value = this.catatan;
            this.$refs.formFitting.submit();
        }
    };
}
</script>

@endsection