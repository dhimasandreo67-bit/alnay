@props(['produk', 'delay' => 0, 'peringkat' => null])

@php
    $statusLabel = \App\Support\ProdukStatus::label($produk->status, $produk->dapat_dijual);

    // Override label jika hanya bisa dijual atau hanya bisa disewa
    if ($produk->dapat_dijual && (!$produk->bisa_disewa || $produk->harga_sewa <= 0)) {
        $statusLabel = 'Bisa Dijual';
    } elseif (!$produk->dapat_dijual && $produk->bisa_disewa && $produk->harga_sewa > 0) {
        $statusLabel = 'Bisa Disewa';
    }
    // Apa saja yang benar-benar bisa dilakukan pada produk ini
    $bisaSewa = $produk->bisa_disewa && $produk->harga_sewa > 0;
    $bisaBeli = $produk->dapat_dijual && $produk->harga_jual > 0;

    $statusVariant = \App\Support\ProdukStatus::variant($produk->status, $produk->dapat_dijual);

    $badgeClass = match ($statusVariant) {
        'success' => 'bg-white/90 text-ink border border-line backdrop-blur-sm',
        'warning' => 'bg-gold-soft/90 text-ink border border-gold/30 backdrop-blur-sm',
        'danger' => 'bg-red-50 text-red-700 border border-red-200',
        'ink' => 'bg-ink/90 text-cream border border-ink backdrop-blur-sm',
        default => 'bg-cream text-muted border border-line',
    };

    // foto_produk di model = array path. sampulUrl() mengambil foto pertama
    // yang valid (sudah berupa URL penuh), atau null kalau tidak ada.
    $fotoUrl = $produk->sampulUrl();
@endphp

{{--
    Struktur: satu <article>; link utama berupa overlay transparan (absolute inset-0)
    dan tombol Sewa/Beli ada di atasnya (z-20). TIDAK BOLEH ada <a> di dalam <a>.
--}}
<article
    class="pd-up group relative flex flex-col h-full bg-panel border border-line rounded-xl overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-500"
    style="--d: {{ $delay }}s;">

    {{-- Link utama: seluruh kartu bisa diklik --}}
    <a href="{{ route('produk.detail', $produk->sku) }}" class="absolute inset-0 z-10"
        aria-label="Lihat {{ $produk->nama }}"></a>

    {{-- Gambar --}}
    <div class="relative aspect-[3/4] bg-cream-dim overflow-hidden shrink-0">
        @if ($fotoUrl)
            <img src="{{ $fotoUrl }}" alt="{{ $produk->nama }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
        @else
            <div
                class="w-full h-full flex items-center justify-center bg-cream-dim text-muted text-[10px] uppercase tracking-widest">
                Foto Produk
            </div>
        @endif

        <span
            class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $badgeClass }}">
            {{ $statusLabel }}
        </span>

        {{-- Badge peringkat (hanya dipakai di section "Produk Terpopuler") --}}
        @if ($peringkat)
            <span
                class="absolute top-3 right-3 px-3 py-1 rounded-full bg-ink text-gold-soft text-[10px] font-bold uppercase tracking-wider">
                Top #{{ $peringkat }}
            </span>
        @endif
    </div>

    {{-- Isi kartu --}}
    <div class="p-2 flex flex-col flex-1">

        {{-- Nama --}}
        <h3
            class="font-sans font-semibold text-ink text-[13px] leading-snug line-clamp-1 mb-1 group-hover:text-gold transition-colors">
            {{ $produk->nama }}
        </h3>

        {{-- Deskripsi disembunyikan agar card lebih ringkas --}}

        {{-- @if (!empty($produk->deskripsi))
            <p class="text-xs text-muted line-clamp-1 mt-0.5 mb-2">{{ $produk->deskripsi }}</p>
        @endif --}}

        {{-- Rating --}}
        <div class="flex items-center gap-1.5 mb-1">
            <div class="flex text-amber-400 gap-0.5">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-3 h-3 {{ $i <= round($produk->rating_rata_rata) ? 'text-amber-400' : 'text-gray-300' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                @endfor
            </div>
            <span
                class="text-[10px] font-semibold text-ink/80">{{ number_format($produk->rating_rata_rata, 1) }}</span>
            <span class="text-[9px] text-ink/50">({{ $produk->jumlah_rating }})</span>
        </div>

        {{-- Harga & tombol --}}
        <div class="mt-auto">
            {{-- Dua baris harga selalu ada supaya posisinya sama di semua kartu --}}
            <div class="space-y-0.5 mb-1.5 border-t border-line/50 pt-1.5">
                <div class="flex items-baseline justify-between min-h-[16px]">
                    @if ($bisaBeli)
                        <span class="text-[9px] uppercase tracking-wider text-muted">Beli</span>
                        <span class="font-sans font-bold text-[11px] text-ink">
                            Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                <div class="flex items-baseline justify-between min-h-[16px]">
                    @if ($bisaSewa)
                        <span class="text-[9px] uppercase tracking-wider text-muted">Sewa</span>
                        <span class="font-sans font-bold text-[11px] text-gold">
                            Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            </div>

            {{--
                Tombol (z-20 supaya di atas link overlay).
                Dua aksi  -> Sewa (outline emas) + Beli (emas penuh).
                Satu aksi -> tombol itu emas penuh selebar kartu.
            --}}
            @php
                $tbl = 'inline-flex items-center justify-center px-3 py-2 rounded-lg text-[11px] font-semibold uppercase tracking-wider transition-colors min-h-[36px]';
                $tblEmas = $tbl . ' bg-gold text-white hover:bg-gold/90';
                $tblOutline = $tbl . ' border border-gold text-gold bg-white hover:bg-gold hover:text-white';
            @endphp

            <div class="relative z-20 grid {{ $bisaSewa && $bisaBeli ? 'grid-cols-2' : 'grid-cols-1' }} gap-1.5">
                @if ($bisaSewa)
                    <a href="{{ route('produk.detail', ['sku' => $produk->sku]) }}"
                        class="{{ $bisaBeli ? $tblOutline : $tblEmas }}">
                        Sewa
                    </a>
                @endif
                @if ($bisaBeli)
                    <a href="{{ route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli']) }}"
                        class="{{ $tblEmas }}">
                        Beli
                    </a>
                @endif
                @unless ($bisaSewa || $bisaBeli)
                    <a href="{{ route('produk.detail', ['sku' => $produk->sku]) }}" class="{{ $tblOutline }}">
                        Lihat Detail
                    </a>
                @endunless
            </div>
        </div>
    </div>
</article>