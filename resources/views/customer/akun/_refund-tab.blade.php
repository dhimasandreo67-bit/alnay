{{-- Isi tab "Refund" di halaman akun. Variabel: $refundList, $bisaDirefund (opsional) --}}
@php
    $bisaDirefund = $bisaDirefund ?? collect();
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

    $total = $refundList->count();
    $berjalan = $refundList->whereIn('status', \App\Models\Refund::AKTIF)->count();
    $perluTindakan = $refundList->where('status', 'disetujui')->count();
    $selesai = $refundList->where('status', 'selesai')->count();
@endphp

<div class="space-y-6">

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach ([
            ['Bisa direfund', $bisaDirefund->count(), 'text-ink/90'],
            ['Sedang berjalan', $berjalan, 'text-amber-700'],
            ['Perlu tindakan Anda', $perluTindakan, $perluTindakan ? 'text-emerald-700' : 'text-ink/90'],
            ['Selesai', $selesai, 'text-ink/90'],
        ] as [$label, $angka, $warna])
            <div class="bg-panel border border-line/40 rounded-xl p-3 text-center">
                <p class="text-xl font-display font-medium tabular-nums {{ $warna }}">{{ $angka }}</p>
                <p class="text-[10px] text-ink/50 mt-0.5">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- Perlu tindakan --}}
    @foreach ($refundList->where('status', 'disetujui') as $r)
        <a href="{{ route('akun.refund.show', $r->id) }}"
           class="block rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 hover:shadow-sm transition-all">
            <p class="text-[12px] font-bold text-emerald-800">Refund {{ $r->transaksi->kode_transaksi ?? '' }} disetujui</p>
            <p class="text-[11px] text-emerald-900 mt-0.5">
                Kirim barang kembali dan tekan "Saya sudah mengirim barang".
                @if ($r->no_resi_penjemputan) No. resi penjemputan: <span class="font-mono font-bold">{{ $r->no_resi_penjemputan }}</span>@endif
            </p>
            <span class="inline-block mt-2 text-[11px] font-bold text-emerald-700">Lihat langkah selanjutnya →</span>
        </a>
    @endforeach

    {{-- Ketentuan singkat --}}
    <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-xl p-4 text-xs text-amber-900 leading-relaxed">
        <p class="font-bold mb-1">Ketentuan singkat</p>
        Refund hanya untuk <strong>pembelian</strong>, maksimal <strong>{{ $batasHari }} hari</strong> setelah barang diterima,
        dengan foto bukti. Barang dikirim kembali, lalu dana ditransfer setelah barang dicek. Pada <strong>sewa</strong>, DP tidak dapat dikembalikan.
    </div>

    {{-- Pesanan yang bisa direfund --}}
    <div>
        <h3 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Pesanan yang bisa direfund</h3>
        <div class="space-y-3">
            @forelse ($bisaDirefund as $t)
                <div class="bg-panel border border-line/40 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <p class="text-[11px] text-ink/50 font-mono">{{ $t->kode_transaksi }}</p>
                            <p class="text-[11px] text-ink/50">
                                {{ $t->detail->count() }} produk · {{ $t->detail->sum('jumlah') }} item ·
                                <span class="font-semibold text-ink/80 tabular-nums">Rp{{ number_format($t->total, 0, ',', '.') }}</span>
                            </p>
                            <div class="mt-1.5">@include('customer.akun._refund-batas', ['t' => $t, 'kompak' => true])</div>
                        </div>
                        <a href="{{ route('akun.refund.create', $t->id) }}"
                           class="shrink-0 px-4 py-2 rounded-lg border border-rose-200 text-rose-600 text-[12px] font-semibold hover:bg-rose-50 transition-colors">
                            Ajukan Refund
                        </a>
                    </div>
                    <div class="space-y-3 pt-3 border-t border-line/30">
                        @foreach ($t->detail->take(2) as $d)
                            @include('customer.akun._refund-item', ['d' => $d])
                        @endforeach
                        @if ($t->detail->count() > 2)
                            <p class="text-[11px] text-ink/50">+{{ $t->detail->count() - 2 }} produk lainnya dalam pesanan ini</p>
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
    </div>

    {{-- Riwayat pengajuan --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60">Pengajuan saya ({{ $total }})</h3>
            @if ($total > 0)
                <a href="{{ route('akun.refund.index') }}" class="text-[11px] font-bold text-gold hover:underline">Halaman refund lengkap</a>
            @endif
        </div>

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
                            <p class="text-[11px] text-ink/50 font-mono">{{ $r->transaksi->kode_transaksi ?? '-' }}</p>
                            <p class="text-[13px] font-semibold text-ink/85 truncate">
                                {{ $items->first()->produk->nama ?? '-' }}
                                @if ($items->count() > 1)<span class="font-normal text-ink/45">+{{ $items->count() - 1 }} produk lainnya</span>@endif
                            </p>
                            <p class="text-[11px] text-ink/55 mt-0.5 tabular-nums">
                                Rp{{ number_format($r->jumlah, 0, ',', '.') }}
                                @if ($st === 'diajukan')<span class="text-ink/40">(usulan)</span>@endif
                            </p>
                            <p class="text-[10px] text-ink/45 mt-0.5">Diajukan {{ $r->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase shrink-0 {{ $chip($st) }}">{{ $r->label_status }}</span>
                    </div>

                    {{-- Progres mini --}}
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
    </div>

    @include('customer.akun._refund-panduan', ['compact' => true])
</div>