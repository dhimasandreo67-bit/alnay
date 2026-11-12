@extends('layouts.public')

@section('title', 'Ubah Jadwal Fitting — ' . config('alnay.brand.nama'))

@section('content')

    {{-- Gaya + komponen Alpine yang SAMA dengan halaman booking baru --}}
    @include('customer.partials.fitting-assets')

    @php
        $tanggalPenuh = $tanggalPenuh ?? [];
        $slotJam = $slotJam ?? \App\Models\SlotFitting::JAM;
        $slotTerisi = $slotTerisi ?? [];
        $transaksi = $transaksi ?? null;

        // Daftar busana: dari pesanan kalau ada, kalau tidak dari produk reservasi
        $namaProduk = $transaksi
            ? $transaksi->detail->map(fn ($d) => $d->produk->nama ?? null)->filter()->unique()->values()
            : collect([$reservasi->produk->nama ?? null])->filter()->values();

        // Jadwal saat ini, dinormalkan ke format yang dipakai kalender ('Y-m-d' dan 'H:i')
        $tglLama = $reservasi->slot ? \Carbon\Carbon::parse($reservasi->slot->tanggal) : null;
        $jamLama = $reservasi->slot?->jam_mulai ? \Carbon\Carbon::parse($reservasi->slot->jam_mulai)->format('H:i') : null;

        $catatan = collect(preg_split('/\R/', (string) $reservasi->catatan))
            ->reject(fn ($l) => \Illuminate\Support\Str::startsWith(trim($l), 'Busana dalam pesanan:'))
            ->filter()->implode(' ');

        $cfg = [
            'mode' => 'ubah',
            'tanggalPenuh' => $tanggalPenuh,
            'slotJam' => $slotJam,
            'slotTerisi' => $slotTerisi,
            'kapasitas' => $kapasitas ?? 2,
            'slotTutup' => $slotTutup ?? [],
            'tanggalTutup' => $tanggalTutup ?? [],
            'nama' => auth()->user()->name ?? '',
            'noWa' => auth()->user()->no_hp ?? '',
            'produkDefault' => $namaProduk->implode(', '),
            'tanggalMaksimal' => $tanggalMaksimal ?? null,
            'tanggalDefault' => $tglLama?->format('Y-m-d'),
            'jamDefault' => $jamLama,
        ];
    @endphp

    <div class="bg-[#FBFAF7] min-h-screen py-8 md:py-12 font-sans" x-data="fittingForm(@js($cfg))">
        <div class="max-w-[1180px] mx-auto px-4 md:px-6">

            @if ($errors->any() && ! $errors->has('alasan'))
                <div class="pd-up p-4 mb-4 rounded-lg bg-red-50 border border-red-200 text-[13px] text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="pd-up mb-6" style="--d: .05s;">
                <h1 class="font-display text-2xl md:text-3xl font-medium text-ink/85 mb-2">Ubah Jadwal Fitting</h1>
                <p class="text-[12px] text-ink/55 max-w-2xl leading-relaxed">
                    Pilih tanggal dan jam baru. Jadwal lama Anda dilepas dan diganti dengan jadwal baru begitu Anda menyimpan,
                    tanpa perlu menunggu persetujuan.
                    @if ($transaksi)
                        Fitting harus tetap dilakukan sebelum tanggal pengambilan busana.
                    @endif
                </p>
            </div>

            @if ($transaksi)
                @include('customer.partials.alur-sewa', ['transaksi' => $transaksi])
                @include('customer.partials.fitting-pesanan', ['transaksi' => $transaksi])
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 lg:gap-8">
                <div class="space-y-4">
                    @include('customer.partials.fitting-lokasi')

                    {{-- 1 & 2: tanggal + jam (komponen yang sama dengan booking baru) --}}
                    @include('customer.partials.fitting-kalender')

                    {{-- 3. Detail booking (tidak bisa diubah di sini) --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .3s;">
                        <h2 class="font-display text-base font-medium text-ink/80 mb-1">3. Detail Booking</h2>
                        <p class="text-[11px] text-ink/50 mb-4">Data berikut tetap sama. Yang berubah hanya tanggal dan jam.</p>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-[12px]">
                            <div>
                                <dt class="text-[10px] text-ink/50 mb-0.5">Kode Booking</dt>
                                <dd class="font-mono font-semibold text-ink/80">{{ $reservasi->kode_booking }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] text-ink/50 mb-0.5">Status</dt>
                                <dd class="font-medium text-sky-700">Terjadwal</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] text-ink/50 mb-0.5">Nama</dt>
                                <dd class="font-medium text-ink/80">{{ auth()->user()->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] text-ink/50 mb-0.5">WhatsApp</dt>
                                <dd class="font-medium text-ink/80">{{ auth()->user()->no_hp ?: '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-[10px] text-ink/50 mb-0.5">Busana</dt>
                                <dd class="font-medium text-ink/80">{{ $namaProduk->implode(', ') ?: 'Sesi fitting umum' }}</dd>
                            </div>
                            @if ($catatan)
                                <div class="sm:col-span-2">
                                    <dt class="text-[10px] text-ink/50 mb-0.5">Catatan</dt>
                                    <dd class="text-ink/70 italic">“{{ $catatan }}”</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- ==================== KOLOM KANAN: RINGKASAN ==================== --}}
                <div class="lg:sticky lg:top-24 h-fit">
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .35s;" :class="{ 'pd-shake border-rose-300': getar }">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-2 h-2 rounded-full" :class="tanggalDipilih && jamDipilih ? 'bg-emerald-500' : 'bg-gold pd-pulse'"></span>
                            <span class="text-[10px] font-medium" :class="tanggalDipilih && jamDipilih ? 'text-emerald-700' : 'text-gold'" x-text="tanggalDipilih && jamDipilih ? 'Jadwal baru siap disimpan' : 'Silakan pilih jadwal baru'"></span>
                        </div>

                        <div class="space-y-2 text-[11px] mb-4 border-b border-line/30 pb-4">
                            <div class="flex justify-between gap-3">
                                <span class="text-ink/50">Jadwal Saat Ini</span>
                                <span class="font-medium text-ink/60 text-right">
                                    @if ($tglLama)
                                        {{ $tglLama->copy()->locale('id')->translatedFormat('d M Y') }} · {{ $jamLama }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span class="text-ink/50">Tanggal Baru</span>
                                <span class="font-medium text-ink/80 text-right" x-text="tanggalDipilih ? formatTanggal(tanggalDipilih) : 'Belum dipilih'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">Jam Baru</span>
                                <span class="font-medium text-ink/80 tabular-nums" x-text="jamDipilih || 'Belum dipilih'"></span>
                            </div>
                            <div class="flex justify-between" x-show="jamDipilih" x-cloak>
                                <span class="text-ink/50">Kuota jam ini</span>
                                <span class="font-medium text-ink/80" x-text="jamDipilih ? 'Sisa ' + sisa(jamDipilih) + ' dari ' + kapasitas + ' tamu' : ''"></span>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span class="text-ink/50">Produk</span>
                                <span class="font-medium text-ink/80 text-right truncate max-w-[180px]" x-text="produk || '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink/50">Biaya Fitting</span>
                                <span class="font-medium text-emerald-600">Gratis</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-md bg-cream/60 border border-line/30 mb-4">
                            <p class="text-[10px] text-ink/50 leading-relaxed">Perubahan langsung berlaku begitu Anda menyimpan. Pengingat 1 hari sebelum jadwal akan mengikuti jadwal yang baru.</p>
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
                            <p x-show="!tanggalDipilih || !jamDipilih" class="text-[9px] text-center text-ink/40 mt-2">Pilih tanggal & jam terlebih dahulu.</p>
                        </form>

                        <div class="mt-4 pt-4 border-t border-line/30 text-center">
                            <button type="button" onclick="document.getElementById('dlg-batal-fitting').showModal()"
                                    class="text-[11px] font-medium text-rose-600 hover:text-rose-700 hover:underline transition-colors">
                                Batalkan fitting ini
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pd-up mt-8 flex flex-col sm:flex-row items-center justify-center gap-6" style="--d: .4s;">
                <a href="{{ route('fitting.show', $reservasi->id) }}" class="inline-flex items-center gap-1.5 text-[11px] text-ink/60 font-medium hover:text-gold hover:underline transition-colors">
                    <x-icon name="chevron-right" :size="12" class="rotate-180" /> Kembali ke Rincian Fitting
                </a>
                @if ($transaksi)
                    <a href="{{ route('akun.pesanan.detail', $transaksi->id) }}" class="inline-flex items-center gap-1.5 text-[11px] text-gold font-semibold hover:underline">
                        Lihat Pesanan Saya <x-icon name="chevron-right" :size="12" />
                    </a>
                @endif
            </div>
        </div>
    </div>

    @include('customer.partials.modal-batal-fitting', ['reservasi' => $reservasi])
@endsection