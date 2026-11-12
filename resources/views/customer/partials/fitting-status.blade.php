{{--
    Kartu status fitting untuk satu pesanan sewa.
    Taruh di: customer/akun/pesanan-detail.blade.php (dan boleh di daftar pesanan).
        @include('customer.partials.fitting-status', ['transaksi' => $transaksi])
    Tidak menampilkan apa-apa kalau pesanan dikirim / belum bayar DP.
--}}
@php
    $tampil = $transaksi->jenis === 'sewa' && $transaksi->perluFitting() && $transaksi->dpSudahDibayar();
    $fitting = $tampil ? $transaksi->fittingAktif() : null;
    $tglAmbil = $transaksi->sewa?->tanggal_pinjam;
@endphp

@if ($tampil)
    @if ($fitting)
        @php
            $st = strtolower($fitting->status);
            $labelStatus = ['pending' => 'Menunggu konfirmasi', 'disetujui' => 'Disetujui', 'hadir' => 'Selesai'][$st] ?? ucfirst($st);
        @endphp
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-emerald-100 bg-emerald-50/40">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-emerald-700 font-semibold">Fitting · {{ $labelStatus }}</p>
                <p class="text-sm font-bold text-ink/85 mt-0.5">
                    {{ $fitting->slot ? $fitting->slot->tanggal->translatedFormat('l, d F Y') : '—' }}
                    @if ($fitting->slot?->jam_mulai)
                        · {{ \Carbon\Carbon::parse($fitting->slot->jam_mulai)->format('H:i') }} WIB
                    @endif
                </p>
                <p class="text-[11px] text-ink/50 mt-0.5">{{ $fitting->kode_booking }}</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('fitting.show', $fitting->id) }}" class="px-4 py-2 rounded-lg bg-white border border-emerald-200 text-emerald-700 text-xs font-bold hover:bg-emerald-50 transition-colors">Lihat Detail</a>
                @if ($st !== 'hadir')
                    <a href="{{ route('fitting.edit', $fitting->id) }}" class="px-4 py-2 rounded-lg bg-white border border-line/60 text-ink/70 text-xs font-bold hover:bg-cream/50 transition-colors">Ubah Jadwal</a>
                @endif
            </div>
        </div>
    @else
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-amber-200 bg-amber-50/60">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-amber-700 font-semibold">Fitting belum dijadwalkan</p>
                <p class="text-[12px] text-ink/65 mt-0.5 leading-relaxed">
                    Busana baru bisa diambil setelah fitting.
                    @if ($tglAmbil)
                        Jadwalkan paling lambat sebelum <strong>{{ $tglAmbil->translatedFormat('d F Y') }}</strong>.
                    @endif
                </p>
            </div>
            <a href="{{ route('fitting.create', ['transaksi_id' => $transaksi->id]) }}" class="shrink-0 px-5 py-2.5 rounded-lg bg-gold text-white text-xs font-bold hover:bg-gold/90 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 transition-all duration-300 text-center">Jadwalkan Fitting</a>
        </div>
    @endif
@endif