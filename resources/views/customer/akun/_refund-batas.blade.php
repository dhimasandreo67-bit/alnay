{{-- Batas waktu pengajuan refund. Variabel: $t (Transaksi), $kompak (bool, opsional) = tampil sebagai pill kecil --}}
@php
    $kompak = $kompak ?? false;
    $batasHari = \App\Models\Refund::BATAS_HARI;
    $tglTerima = $t->pengiriman?->tanggal_diterima;
    $info = null;

    if ($tglTerima instanceof \Carbon\CarbonInterface) {
        $batasTgl = $tglTerima->copy()->startOfDay()->addDays($batasHari);
        $sisa = (int) floor(now()->startOfDay()->diffInDays($batasTgl, false));
        $warna = $sisa < 0
            ? 'bg-rose-50 text-rose-700 border-rose-200'
            : ($sisa <= 2 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200');
        $teks = $sisa < 0
            ? 'Batas pengajuan sudah lewat'
            : ($sisa === 0 ? 'Hari terakhir pengajuan' : "Sisa {$sisa} hari untuk mengajukan");
        $info = compact('batasTgl', 'sisa', 'warna', 'teks');
    }
@endphp

@if ($kompak)
    @if ($info)
        <span class="inline-block px-2 py-0.5 rounded-md border text-[10px] font-bold {{ $info['warna'] }}">{{ $info['teks'] }}</span>
    @endif
@elseif ($info)
    <div class="rounded-xl border px-4 py-3 text-[12px] leading-relaxed {{ $info['warna'] }}">
        <p class="font-bold">{{ $info['teks'] }}</p>
        <p class="opacity-80">
            Barang diterima {{ $tglTerima->format('d M Y') }} · batas pengajuan
            {{ $info['batasTgl']->format('d M Y') }} ({{ $batasHari }} hari).
        </p>
    </div>
@else
    <div class="rounded-xl border border-line/40 bg-cream/70 px-4 py-3 text-[12px] text-ink/65 leading-relaxed">
        Pengajuan maksimal {{ $batasHari }} hari setelah barang diterima. Tanggal terima pesanan ini belum tercatat.
    </div>
@endif