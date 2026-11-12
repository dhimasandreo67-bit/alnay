@php
    $status = strtolower($fitting->status);

    // Tidak ada tahap "menunggu persetujuan": booking langsung Terjadwal. 'disetujui' (data lama) dianggap sama.
    if ($status === 'batal') {
        $tema = [
            'bg' => 'bg-rose-50/40', 'border' => 'border-rose-100',
            'iconBg' => 'bg-rose-100', 'iconBorder' => 'border-rose-500', 'iconColor' => 'text-rose-600',
            'btn' => 'border-rose-200 text-rose-700 hover:bg-rose-50',
            'badge' => 'bg-rose-100 text-rose-700', 'label' => 'Dibatalkan',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>',
        ];
    } elseif ($status === 'hadir') {
        $tema = [
            'bg' => 'bg-emerald-50/40', 'border' => 'border-emerald-100',
            'iconBg' => 'bg-emerald-100', 'iconBorder' => 'border-emerald-500', 'iconColor' => 'text-emerald-600',
            'btn' => 'border-emerald-200 text-emerald-700 hover:bg-emerald-50',
            'badge' => 'bg-emerald-100 text-emerald-700', 'label' => 'Selesai',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>',
        ];
    } else {
        $tema = [
            'bg' => 'bg-sky-50/40', 'border' => 'border-sky-100',
            'iconBg' => 'bg-sky-100', 'iconBorder' => 'border-sky-500', 'iconColor' => 'text-sky-600',
            'btn' => 'border-sky-200 text-sky-700 hover:bg-sky-50',
            'badge' => 'bg-sky-100 text-sky-700', 'label' => 'Terjadwal',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        ];
    }

    $tgl = $fitting->slot ? \Carbon\Carbon::parse($fitting->slot->tanggal) : null;
    $jam = $fitting->slot?->jam_mulai ? \Carbon\Carbon::parse($fitting->slot->jam_mulai)->format('H:i') : null;

    /* ---------- Produk (busana) pada reservasi ---------- */
    // URL foto tahan berbagai format penyimpanan (array / JSON / path relatif / URL penuh)
    $urlFoto = $urlFoto ?? function ($produk) {
        $foto = $produk->foto_produk ?? null;
        if (is_string($foto)) {
            $dec = json_decode($foto, true);
            if (is_array($dec)) { $foto = $dec; }
        }
        if (is_array($foto)) { $foto = $foto[0] ?? null; }
        if (! $foto) { return null; }

        $foto = (string) $foto;
        if (\Illuminate\Support\Str::startsWith($foto, ['http://', 'https://', 'data:'])) { return $foto; }

        return asset('storage/' . ltrim(preg_replace('#^(public/|storage/)#', '', ltrim($foto, '/')), '/'));
    };

    $trx = method_exists($fitting, 'transaksi') ? $fitting->transaksi : null;
    $busana = [];
    if ($trx && $trx->detail->isNotEmpty()) {
        foreach ($trx->detail as $d) {
            $busana[] = [
                'nama'   => $d->produk->nama ?? 'Busana',
                'foto'   => $d->produk ? $urlFoto($d->produk) : null,
                'ukuran' => $d->ukuran,
                'warna'  => $d->warna,
            ];
        }
    } elseif ($fitting->produk) {
        $busana[] = ['nama' => $fitting->produk->nama, 'foto' => $urlFoto($fitting->produk), 'ukuran' => null, 'warna' => null];
    }
    $sewa = $trx?->sewa;
    $fmt  = fn ($t) => \Carbon\Carbon::parse($t)->locale('id')->translatedFormat('d M Y');
@endphp

<div class="rounded-xl border {{ $tema['border'] }} {{ $tema['bg'] }}">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full {{ $tema['iconBg'] }} border-2 {{ $tema['iconBorder'] }} flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 {{ $tema['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $tema['icon'] !!}
            </svg>
        </div>

        <div>
            <div class="flex items-center">
                <p class="text-sm font-bold text-ink/90">{{ $fitting->kode_booking }}</p>
                <span class="px-2 py-0.5 rounded {{ $tema['badge'] }} text-[10px] font-bold uppercase ml-2">{{ $tema['label'] }}</span>
            </div>
            <p class="text-xs text-ink/60 mt-0.5">
                {{ $tgl ? $tgl->translatedFormat('l, d M Y') : '—' }}
                • {{ $jam ? $jam . ' WIB' : '—' }}
            </p>
        </div>
    </div>

    {{-- BAGIAN TOMBOL YANG DIGABUNGKAN --}}
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('fitting.show', $fitting->id) }}"
           class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-white border {{ $tema['btn'] }} text-xs font-bold rounded-xl transition-colors">
            Lihat Detail <x-icon name="chevron-right" :size="14" />
        </a>

        @if (in_array($fitting->status, ['pending', 'batal']))
            {{-- Konfirmasi lewat modal: customer/partials/modal-hapus-fitting.blade.php --}}
            <button type="button"
                    data-hapus-fitting
                    data-action="{{ route('fitting.destroy', $fitting->id) }}"
                    data-kode="{{ $fitting->kode_booking }}"
                    data-jadwal="{{ $tgl ? $tgl->locale('id')->translatedFormat('d M Y') . ($jam ? ' pukul ' . $jam . ' WIB' : '') : '' }}"
                    data-status="{{ $fitting->status }}"
                    data-edit-url="{{ route('fitting.edit', $fitting->id) }}"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-rose-200 text-rose-500 hover:bg-rose-50 transition-colors"
                    title="Hapus Reservasi" aria-label="Hapus reservasi {{ $fitting->kode_booking }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        @endif
    </div>
</div>

    {{-- ============ PRODUK FITTING ============ --}}
    @if (count($busana))
        <div class="border-t border-black/5 bg-white/60 rounded-b-xl px-4 py-3.5 space-y-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-ink/40">Busana yang Difitting</p>

            <div class="space-y-2.5">
                @foreach (array_slice($busana, 0, 2) as $b)
                    <div class="flex items-center gap-3">
                        <div class="relative w-14 h-14 shrink-0 rounded-lg overflow-hidden bg-gray-100 border border-line/40">
                            @if ($b['foto'])
                                <img src="{{ $b['foto'] }}" alt="{{ $b['nama'] }}" loading="lazy"
                                     class="relative z-10 w-full h-full object-cover bg-white" onerror="this.remove()">
                            @endif
                            <svg class="w-5 h-5 text-gray-300 absolute inset-0 m-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4a3 3 0 014 0l4 4m-2-2l1-1a3 3 0 014 0l2 2M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] font-semibold text-ink/90 truncate" title="{{ $b['nama'] }}">{{ $b['nama'] }}</p>
                            @if ($b['ukuran'] || $b['warna'])
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @if ($b['ukuran'])
                                        <span class="px-2 py-0.5 rounded-md bg-white border border-line/40 text-[10px] font-semibold text-ink/65">Ukuran {{ $b['ukuran'] }}</span>
                                    @endif
                                    @if ($b['warna'])
                                        <span class="px-2 py-0.5 rounded-md bg-white border border-line/40 text-[10px] font-semibold text-ink/65">{{ $b['warna'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if (count($busana) > 2)
                    <p class="text-[11px] text-ink/45 pl-[68px]">+{{ count($busana) - 2 }} busana lainnya</p>
                @endif
            </div>

            @if ($sewa || $trx)
                <dl class="flex flex-wrap gap-x-5 gap-y-1 pt-2.5 border-t border-black/5 text-[11px] text-ink/55">
                    @if ($sewa)
                        <div class="flex gap-1.5"><dt>Ambil</dt><dd class="font-semibold text-ink/75">{{ $fmt($sewa->tanggal_pinjam) }}</dd></div>
                        <div class="flex gap-1.5"><dt>Kembali</dt><dd class="font-semibold text-ink/75">{{ $fmt($sewa->tanggal_kembali_rencana) }}</dd></div>
                    @endif
                    @if ($trx)
                        <div class="flex gap-1.5"><dt>Pesanan</dt><dd class="font-mono font-semibold text-ink/75">{{ $trx->kode_transaksi }}</dd></div>
                    @endif
                </dl>
            @endif
        </div>
    @endif
</div>