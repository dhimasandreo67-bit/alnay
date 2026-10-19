@props(['produk'])

@php
    use App\Support\ProdukStatus;

    $statusLabel = ProdukStatus::label($produk->status, $produk->dapat_dijual);
    $statusVariant = ProdukStatus::variant($produk->status, $produk->dapat_dijual);

    $badgeClass = match ($statusVariant) {
        'success' => 'bg-gold text-white',
        'warning' => 'bg-gold-soft text-ink',
        'danger'  => 'bg-red-600 text-white',
        'ink'     => 'bg-ink text-cream',
        default   => 'bg-cream-dim text-ink',
    };
@endphp

<article class="group flex flex-col bg-panel border border-line rounded-2xl overflow-hidden hover:shadow-lg transition-shadow">

    <a href="{{ route('produk.detail', $produk->sku) }}" class="block relative aspect-[4/5] bg-cream-dim overflow-hidden">
        @if ($produk->foto_produk)
            <img src="{{ asset($produk->foto_produk) }}" alt="{{ $produk->nama }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-cream-dim to-cream text-muted text-xs uppercase tracking-widest">
                Foto Produk
            </div>
        @endif

        <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $badgeClass }}">
            {{ $statusLabel }}
        </span>
    </a>

    <div class="p-4 flex flex-col flex-1">
        <h3 class="font-medium text-ink text-sm md:text-base line-clamp-2 mb-2">
            {{ $produk->nama }}
        </h3>

        <div class="flex items-center gap-1 mb-3 text-xs text-muted">
            <div class="flex text-gold">
                @for ($i = 1; $i <= 5; $i++)
                    <x-icon name="{{ $i <= round($produk->rating_rata_rata) ? 'star' : 'star-outline' }}" :size="12" />
                @endfor
            </div>
            <span>{{ number_format($produk->rating_rata_rata, 1) }}</span>
            <span>({{ $produk->jumlah_rating }} ulasan)</span>
        </div>

        <div class="mt-auto">
            @if ($produk->dapat_dijual && $produk->harga_jual)
                <p class="text-[11px] text-muted uppercase tracking-wider">Beli</p>
                <p class="font-display text-lg text-ink mb-1">
                    Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                </p>
            @endif

            @if ($produk->harga_sewa)
                <p class="text-[11px] text-muted uppercase tracking-wider">Sewa mulai</p>
                <p class="font-display text-lg text-ink">
                    Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }}
                </p>
            @endif
        </div>

        <div class="grid {{ $produk->dapat_dijual ? 'grid-cols-2' : 'grid-cols-1' }} gap-2 mt-4">
            <a href="#"
               class="inline-flex items-center justify-center px-3 py-2.5 rounded-full border border-ink/20 text-ink text-xs font-semibold uppercase tracking-wider hover:bg-ink hover:text-cream transition-colors min-h-[44px]">
                Sewa
            </a>
            @if ($produk->dapat_dijual)
                <a href="#"
                   class="inline-flex items-center justify-center px-3 py-2.5 rounded-full bg-gold text-white text-xs font-semibold uppercase tracking-wider hover:bg-gold/90 transition-colors min-h-[44px]">
                    Beli
                </a>
            @endif
        </div>
    </div>
</article>