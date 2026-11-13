{{--
    Stepper alur sewa online (ambil di toko).
    Dipakai di: halaman fitting, detail pesanan, halaman konfirmasi.
    Butuh: $transaksi (Transaksi sewa)
--}}
@php
    $fittingTrx = $transaksi->fittingAktif();

    // 0 = bayar DP, 1 = jadwalkan fitting, 2 = fitting & ambil, 3 = pakai & kembali
    if (! $transaksi->dpSudahDibayar()) {
        $langkah = 0;
    } elseif (! $fittingTrx) {
        $langkah = 1;
    } elseif (strtolower($fittingTrx->status) === 'hadir') {
        $langkah = 3;
    } else {
        $langkah = 2;
    }

    $daftarLangkah = ['Bayar DP', 'Jadwalkan fitting', 'Fitting & ambil busana', 'Pakai & kembalikan'];
@endphp

<ol class="pd-up flex items-start gap-1 mb-4 overflow-x-auto" style="--d: .06s;">
    @foreach ($daftarLangkah as $i => $label)
        @php
            $selesai = $i < $langkah;
            $sekarang = $i === $langkah;
        @endphp
        <li class="flex-1 min-w-[84px]">
            <div class="flex items-center">
                <span class="w-6 h-6 shrink-0 rounded-full flex items-center justify-center text-[10px] font-bold border
                    {{ $selesai ? 'bg-emerald-500 border-emerald-500 text-white' : ($sekarang ? 'bg-gold border-gold text-white' : 'bg-white border-line/60 text-ink/40') }}">
                    @if ($selesai)
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                </span>
                @if (! $loop->last)
                    <span class="flex-1 h-px mx-1 {{ $selesai ? 'bg-emerald-400' : 'bg-line/60' }}"></span>
                @endif
            </div>
            <p class="mt-1.5 pr-1 text-[10px] leading-tight {{ $sekarang ? 'font-semibold text-gold' : ($selesai ? 'text-emerald-700' : 'text-ink/40') }}">{{ $label }}</p>
        </li>
    @endforeach
</ol>