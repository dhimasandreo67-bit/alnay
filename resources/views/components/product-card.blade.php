@props(['produk', 'peringkat' => null])

@php
    use App\Support\ProdukStatus;

    $statusLabel = ProdukStatus::label($produk['status'], $produk['dapat_dijual']);
    $statusVariant = ProdukStatus::variant($produk['status'], $produk['dapat_dijual']);

    $badgeClass = match ($statusVariant) {
        'success' => 'bg-gold text-white',
        'warning' => 'bg-gold-soft text-ink',
        'danger'  => 'bg-red-600 text-white',
        'ink'     => 'bg-ink text-cream',
        default   => 'bg-cream-dim text-ink',
    };

    // foto_produk = path relatif ke public/ (dummy: 'image/produk/x.jpg',
    // database: 'storage/produk/x.jpg'). Kosong → placeholder.
    $foto = $produk['foto_produk'] ?? null;
@endphp

<article class="group flex flex-col bg-panel border border-line rounded-2xl overflow-hidden hover:shadow-lg transition-shadow">

    {{-- Foto --}}
    <a href="{{ route('produk.detail', ['sku' => $produk['sku']]) }}" class="block relative aspect-[4/5] bg-cream-dim overflow-hidden">
        @if ($foto)
            <img src="{{ asset($foto) }}" alt="{{ $produk['nama'] }}" loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-cream-dim to-cream text-muted text-xs uppercase tracking-widest">
                Foto Produk
            </div>
        @endif

        {{-- Badge status --}}
        <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $badgeClass }}">
            {{ $statusLabel }}
        </span>

        {{-- Badge peringkat (hanya dipakai di section "Produk Terpopuler") --}}
        @if ($peringkat)
            <span class="absolute top-3 right-3 px-3 py-1 rounded-full bg-ink text-gold-soft text-[10px] font-bold uppercase tracking-wider">
                Top #{{ $peringkat }}
            </span>
        @endif
    </a>

    {{-- Isi --}}
    <div class="p-4 flex flex-col flex-1">

        <h3 class="font-medium text-ink text-sm md:text-base line-clamp-2">
            {{ $produk['nama'] }}
        </h3>

        @if (! empty($produk['deskripsi']))
            <p class="text-xs text-muted line-clamp-1 mt-0.5 mb-2">{{ $produk['deskripsi'] }}</p>
        @endif

        {{-- Rating --}}
        <div class="flex items-center gap-1 mb-3 text-xs text-muted">
            <div class="flex text-gold">
                @for ($i = 1; $i <= 5; $i++)
                    <x-icon name="{{ $i <= round($produk['rating_rata_rata']) ? 'star' : 'star-outline' }}" :size="12" />
                @endfor
            </div>
            <span>{{ number_format($produk['rating_rata_rata'], 1) }}</span>
            <span>({{ $produk['jumlah_rating'] }} ulasan)</span>
            {{-- Prompt: jangan tulis "(N sewa)" — pakai "(N ulasan)" --}}
        </div>

        {{-- Harga --}}
        <div class="mt-auto">
            @if ($produk['dapat_dijual'])
                <p class="text-[11px] text-muted uppercase tracking-wider">Beli</p>
                <p class="font-display text-lg text-ink mb-1">
                    Rp {{ number_format($produk['harga_jual'], 0, ',', '.') }}
                </p>
            @endif

            <p class="text-[11px] text-muted uppercase tracking-wider">Sewa mulai</p>
            <p class="font-display text-lg text-ink">
                Rp {{ number_format($produk['harga_sewa'], 0, ',', '.') }}
            </p>
            {{-- Prompt: jangan tulis "/3hr" — pakai "Sewa mulai" --}}
        </div>

        {{-- Tombol --}}
        <div class="grid {{ $produk['dapat_dijual'] ? 'grid-cols-2' : 'grid-cols-1' }} gap-2 mt-4">
            <a href="{{ route('produk.detail', ['sku' => $produk['sku']]) }}"
               class="inline-flex items-center justify-center px-3 py-2.5 rounded-full border border-ink/20 text-ink text-xs font-semibold uppercase tracking-wider hover:bg-ink hover:text-cream transition-colors min-h-[44px]">
                Sewa
            </a>
            @if ($produk['dapat_dijual'])
                <a href="{{ route('produk.detail', ['sku' => $produk['sku']]) }}"
                   class="inline-flex items-center justify-center px-3 py-2.5 rounded-full bg-gold text-white text-xs font-semibold uppercase tracking-wider hover:bg-gold/90 transition-colors min-h-[44px]">
                    Beli
                </a>
            @endif
        </div>

    </div>
</article>