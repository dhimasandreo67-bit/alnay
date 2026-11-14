@extends('layouts.akun')

@section('title', 'Refund Saya — ' . config('alnay.brand.nama'))

@section('content')
@php
    $batasHari = \App\Models\Refund::BATAS_HARI;
    $urutan = ['diajukan', 'disetujui', 'barang_dikirim', 'barang_diterima', 'selesai'];

    $chip = fn ($st) => match (true) {
        $st === 'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        in_array($st, ['ditolak', 'dibatalkan'], true) => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-amber-50 text-amber-700 border-amber-200',
    };

    // Petunjuk singkat "apa yang harus saya lakukan" per status
    $petunjuk = [
        'diajukan'        => ['Menunggu tinjauan admin (maks. 1x24 jam).', 'text-ink/55'],
        'disetujui'       => ['Tindakan Anda: kirim barang kembali.', 'text-emerald-700 font-semibold'],
        'barang_dikirim'  => ['Barang dalam perjalanan ke kami.', 'text-sky-700'],
        'barang_diterima' => ['Barang diterima — dana sedang diproses.', 'text-sky-700'],
        'selesai'         => ['Dana sudah ditransfer.', 'text-emerald-700'],
        'ditolak'         => ['Pengajuan ditolak — lihat alasannya.', 'text-rose-700'],
        'dibatalkan'      => ['Dibatalkan oleh Anda.', 'text-ink/50'],
    ];

    $aktif = $refundList->whereIn('status', \App\Models\Refund::AKTIF)->count();
    $perluTindakan = $refundList->where('status', 'disetujui')->count();
@endphp

<div class="bg-cream min-h-screen py-6 md:py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-5 md:mb-6">
            <a href="{{ route('akun.dashboard') }}#refund" aria-label="Kembali"
               class="w-10 h-10 shrink-0 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <div class="min-w-0">
                <h1 class="font-display text-xl sm:text-2xl font-medium text-ink/90 leading-tight">Refund Saya</h1>
                <p class="text-[11px] sm:text-xs text-ink/50">Ajukan pengembalian barang dan pantau prosesnya.</p>
            </div>
        </div>

        @foreach (['success' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'error' => 'bg-rose-50 border-rose-200 text-rose-700'] as $tipe => $kelas)
            @if (session($tipe))
                <div class="mb-4 px-4 py-3 rounded-xl border text-sm {{ $kelas }}">{{ session($tipe) }}</div>
            @endif
        @endforeach

        {{-- Baris atas: ringkasan + ketentuan (2 kolom di desktop) --}}
        <div class="grid gap-3 lg:grid-cols-2 lg:gap-6 mb-6 lg:mb-8">
            <div class="grid grid-cols-3 gap-2 sm:gap-3 content-start">
                @foreach ([
                    ['Bisa direfund', $bisaDirefund->count(), 'text-ink/90'],
                    ['Sedang diproses', $aktif, 'text-amber-700'],
                    ['Perlu tindakan', $perluTindakan, $perluTindakan ? 'text-emerald-700' : 'text-ink/90'],
                ] as [$label, $angka, $warna])
                    <div class="bg-panel border border-line/40 rounded-xl px-2 py-3 sm:p-4 text-center">
                        <p class="text-xl sm:text-2xl font-display font-medium tabular-nums {{ $warna }}">{{ $angka }}</p>
                        <p class="text-[10px] sm:text-[11px] text-ink/50 mt-0.5 leading-tight">{{ $label }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-xl p-4 text-xs text-amber-900 leading-relaxed">
                <p class="font-bold mb-1">Ketentuan singkat</p>
                Refund hanya untuk <strong>pembelian</strong>, diajukan maksimal <strong>{{ $batasHari }} hari</strong>
                setelah barang diterima, dengan foto bukti. Barang perlu dikirim kembali, lalu dana ditransfer setelah barang dicek.
                Untuk <strong>sewa</strong>, DP tidak dapat dikembalikan.
            </div>
        </div>

        {{-- Perlu tindakan (refund disetujui, menunggu barang dikirim) --}}
        @foreach ($refundList->where('status', 'disetujui') as $r)
            <a href="{{ route('akun.refund.show', $r->id) }}"
               class="block mb-4 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 hover:shadow-sm transition-all">
                <p class="text-[12px] font-bold text-emerald-800">Refund {{ $r->transaksi->kode_transaksi ?? '' }} disetujui — kirim barang kembali</p>
                @if ($r->no_resi_penjemputan)
                    <p class="text-[11px] text-emerald-900 mt-0.5">No. resi penjemputan: <span class="font-mono font-bold">{{ $r->no_resi_penjemputan }}</span></p>
                @endif
                <span class="inline-block mt-1.5 text-[11px] font-bold text-emerald-700">Lihat langkah selanjutnya →</span>
            </a>
        @endforeach

        {{-- Konten utama: 2 kolom di desktop --}}
        <div class="grid gap-8 lg:grid-cols-2 lg:gap-8 lg:items-start mb-8">

            {{-- Kolom kiri: pesanan yang bisa direfund --}}
            <section>
                <h2 class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">
                    Pesanan yang bisa direfund
                    <span class="px-1.5 py-0.5 rounded-md bg-white border border-line/50 text-[10px] text-ink/60 tabular-nums">{{ $bisaDirefund->count() }}</span>
                </h2>
                <div class="space-y-3">
                    @forelse ($bisaDirefund as $t)
                        <div class="bg-panel border border-line/40 rounded-xl p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] text-ink/50 font-mono break-all">{{ $t->kode_transaksi }}</p>
                                    <p class="text-[11px] text-ink/50">
                                        {{ $t->detail->count() }} produk · {{ $t->detail->sum('jumlah') }} item ·
                                        <span class="font-semibold text-ink/80 tabular-nums">Rp{{ number_format($t->total, 0, ',', '.') }}</span>
                                    </p>
                                    <div class="mt-1.5">@include('customer.akun._refund-batas', ['t' => $t, 'kompak' => true])</div>
                                </div>
                                <a href="{{ route('akun.refund.create', $t->id) }}"
                                   class="shrink-0 w-full sm:w-auto text-center px-4 py-2.5 rounded-lg border border-rose-200 text-rose-600 text-[12px] font-semibold hover:bg-rose-50 transition-colors">
                                    Ajukan Refund
                                </a>
                            </div>
                            <div class="space-y-3 pt-3 border-t border-line/30">
                                @foreach ($t->detail->take(2) as $d)
                                    @include('customer.akun._refund-item', ['d' => $d])
                                @endforeach

                                @if ($t->detail->count() > 2)
                                    <details class="group/lain">
                                        <summary class="cursor-pointer list-none text-[11px] font-semibold text-gold hover:underline py-1">
                                            <span class="group-open/lain:hidden">Lihat {{ $t->detail->count() - 2 }} produk lainnya</span>
                                            <span class="hidden group-open/lain:inline">Sembunyikan</span>
                                        </summary>
                                        <div class="space-y-3 mt-3">
                                            @foreach ($t->detail->slice(2) as $d)
                                                @include('customer.akun._refund-item', ['d' => $d])
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-[12px] text-ink/55 bg-panel border border-line/40 rounded-xl p-4 leading-relaxed">
                            <p class="font-semibold text-ink/70 mb-1">Belum ada pesanan yang memenuhi syarat refund.</p>
                            Pesanan pembelian yang sudah diterima dan masih dalam batas {{ $batasHari }} hari akan muncul di sini.
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Kolom kanan: riwayat pengajuan --}}
            <section>
                <h2 class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">
                    Pengajuan saya
                    <span class="px-1.5 py-0.5 rounded-md bg-white border border-line/50 text-[10px] text-ink/60 tabular-nums">{{ $refundList->count() }}</span>
                </h2>
                <div class="space-y-3">
                    @forelse ($refundList as $r)
                        @php
                            $items = $r->transaksi->detail ?? collect();
                            $st = $r->status;
                            $gagal = in_array($st, ['ditolak', 'dibatalkan'], true);
                            $posisi = array_search($st, $urutan, true);
                            [$teksPetunjuk, $kelasPetunjuk] = $petunjuk[$st] ?? ['', 'text-ink/50'];
                        @endphp
                        <a href="{{ route('akun.refund.show', $r->id) }}"
                           class="block bg-panel border border-line/40 rounded-xl p-4 hover:border-gold/50 hover:shadow-sm transition-all">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] text-ink/50 font-mono break-all">{{ $r->transaksi->kode_transaksi ?? '-' }}</p>
                                    <p class="text-[13px] font-semibold text-ink/85 sm:truncate">
                                        {{ $items->first()->produk->nama ?? '-' }}
                                        @if ($items->count() > 1)<span class="font-normal text-ink/45">+{{ $items->count() - 1 }} produk lainnya</span>@endif
                                    </p>
                                    <p class="text-[11px] text-ink/55 mt-0.5 tabular-nums">
                                        Rp{{ number_format($r->jumlah, 0, ',', '.') }}
                                        @if ($st === 'diajukan')<span class="text-ink/40">(usulan)</span>@endif
                                    </p>
                                    <p class="text-[10px] text-ink/45 mt-0.5">Diajukan {{ $r->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase text-center shrink-0 max-w-[45%] {{ $chip($st) }}">{{ $r->label_status }}</span>
                            </div>

                            @unless ($gagal)
                                <div class="mt-3 flex items-center gap-1" aria-hidden="true">
                                    @foreach ($urutan as $k => $kunci)
                                        <span class="h-1 flex-1 rounded-full {{ $posisi !== false && $k <= $posisi ? 'bg-gold' : 'bg-line/60' }}"></span>
                                    @endforeach
                                </div>
                            @endunless

                            <p class="mt-2 text-[11px] {{ $kelasPetunjuk }}">{{ $teksPetunjuk }}</p>

                            @if ($st === 'ditolak' && $r->alasan_penolakan)
                                <p class="mt-1 text-[11px] text-rose-800/80 line-clamp-2">Alasan: {{ $r->alasan_penolakan }}</p>
                            @endif
                            @if ($st === 'selesai' && $r->bukti_transfer)
                                <p class="mt-1 text-[11px] font-semibold text-emerald-700">Bukti transfer tersedia — buka untuk melihat</p>
                            @endif
                        </a>
                    @empty
                        <p class="text-[12px] text-ink/50 bg-panel border border-line/40 rounded-xl p-4">Belum ada pengajuan refund.</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Panduan: akordeon (otomatis terbuka di desktop), 2 kolom di dalamnya --}}
        @include('customer.akun._refund-panduan', ['compact' => true, 'grid' => true])
    </div>
</div>

<script>
    (function () {
        var panduan = document.getElementById('panduan-refund');
        if (panduan && window.matchMedia('(min-width: 1024px)').matches) { panduan.open = true; }
    })();
</script>
@endsection