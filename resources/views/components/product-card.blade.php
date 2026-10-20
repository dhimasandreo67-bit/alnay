@props(['produk'])

@php
    $statusLabel = \App\Support\ProdukStatus::label($produk->status, $produk->dapat_dijual);
    $statusVariant = \App\Support\ProdukStatus::variant($produk->status, $produk->dapat_dijual);

    $badgeClass = match ($statusVariant) {
        'success' => 'bg-white/90 text-ink border border-line backdrop-blur-sm',
        'warning' => 'bg-gold-soft/90 text-ink border border-gold/30 backdrop-blur-sm',
        'danger'  => 'bg-red-50 text-red-700 border border-red-200',
        'ink'     => 'bg-ink/90 text-cream border border-ink backdrop-blur-sm',
        default   => 'bg-cream text-muted border border-line',
    };
@endphp

{{--
    Struktur: satu <article>; link utama berupa overlay transparan (absolute inset-0)
    dan tombol Sewa/Beli ada di atasnya (z-20). TIDAK BOLEH ada <a> di dalam <a>.
--}}
<article class="group relative flex flex-col h-full bg-panel border border-line rounded-xl overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-500">

    {{-- Link utama: seluruh kartu bisa diklik --}}
    <a href="{{ route('produk.detail', $produk->sku) }}"
       class="absolute inset-0 z-10"
       aria-label="Lihat {{ $produk->nama }}"></a>

    {{-- Gambar --}}
    <div class="relative aspect-[4/3] bg-cream-dim overflow-hidden shrink-0">
        @if ($produk->foto_produk)
            <img src="{{ asset('storage/' . $produk->foto_produk) }}" alt="{{ $produk->nama }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
        @else
            <div class="w-full h-full flex items-center justify-center bg-cream-dim text-muted text-[10px] uppercase tracking-widest">
                Foto Produk
            </div>
        @endif

        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $badgeClass }}">
            {{ $statusLabel }}
        </span>
    </div>

    {{-- Isi kartu --}}
    <div class="p-3 flex flex-col flex-1">

        {{-- Nama --}}
        <h3 class="font-sans font-semibold text-ink text-[13px] leading-snug line-clamp-2 mb-1 group-hover:text-gold transition-colors min-h-[36px]">
            {{ $produk->nama }}
        </h3>

        {{-- Rating --}}
        <div class="flex items-center gap-1 mb-2 text-[10px] text-muted">
            <div class="flex text-gold">
                @for ($i = 1; $i <= 5; $i++)
                    <x-icon name="{{ $i <= round($produk->rating_rata_rata) ? 'star' : 'star-outline' }}" :size="10" />
                @endfor
            </div>
            <span class="font-medium text-ink">{{ number_format($produk->rating_rata_rata, 1) }}</span>
            <span>({{ $produk->jumlah_rating }})</span>
        </div>

        {{-- Harga & tombol (didorong ke bawah supaya sejajar antar kartu) --}}
        <div class="mt-auto">
            <div class="space-y-1 mb-3 border-t border-line/50 pt-2">
                @if ($produk->dapat_dijual && $produk->harga_jual)
                    <div class="flex items-baseline justify-between">
                        <span class="text-[9px] uppercase tracking-wider text-muted">Beli</span>
                        <span class="font-sans font-bold text-[12px] text-ink">
                            Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                        </span>
                    </div>
                @endif

                @if ($produk->harga_sewa)
                    <div class="flex items-baseline justify-between">
                        <span class="text-[9px] uppercase tracking-wider text-muted">Sewa</span>
                        <span class="font-sans font-bold text-[12px] text-gold">
                            Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Tombol (di atas overlay link: relative z-20) --}}
            <div class="relative z-20 grid {{ $produk->dapat_dijual ? 'grid-cols-2' : 'grid-cols-1' }} gap-1.5">
                <a href="{{ route('produk.detail', ['sku' => $produk->sku, 'mode' => 'sewa']) }}"
                   class="inline-flex items-center justify-center px-2 py-1.5 rounded-md border border-line bg-white text-ink text-[10px] font-bold uppercase tracking-wider font-sans hover:bg-ink hover:text-cream hover:border-ink transition-all duration-300 min-h-[34px]">
                    Sewa
                </a>
                @if ($produk->dapat_dijual)
                    <a href="{{ route('produk.detail', ['sku' => $produk->sku, 'mode' => 'beli']) }}"
                       class="inline-flex items-center justify-center px-2 py-1.5 rounded-md bg-gold text-white text-[10px] font-bold uppercase tracking-wider font-sans hover:bg-gold/90 transition-all duration-300 min-h-[34px]">
                        Beli
                    </a>
                @endif
            </div>
        </div>
    </div>
</article>