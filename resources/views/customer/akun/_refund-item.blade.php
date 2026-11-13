{{-- Baris detail produk untuk halaman refund. Variabel: $d (DetailTransaksi) --}}
@php
    $p = $d->produk;

    // Kolom foto: produk.foto_produk (JSON array atau string), sama seperti dashboard akun.
    $raw = $p?->foto_produk ?? null;
    if (is_string($raw)) {
        $dec = json_decode($raw, true);
        $raw = is_array($dec) ? $dec : [$raw];
    }
    $raw = is_array($raw) ? collect($raw)->filter()->first() : null;
    $img = $raw ? (preg_match('#^https?://#', $raw) ? $raw : asset('storage/' . ltrim($raw, '/'))) : null;

    $qty = (int) ($d->jumlah ?? 1);
    $harga = (int) ($d->harga_satuan ?? 0);
@endphp

<div class="flex gap-3 items-start">
    <div class="w-16 h-16 shrink-0 rounded-lg overflow-hidden border border-line/50 bg-cream flex items-center justify-center text-ink/30">
        @if ($img)
            <img src="{{ $img }}" alt="{{ $p->nama ?? 'Produk' }}" loading="lazy"
                 class="w-full h-full object-cover" onerror="this.remove()">
        @else
            <x-icon name="image" :size="20" />
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <p class="text-[13px] font-semibold text-ink/85 leading-snug">{{ $p->nama ?? 'Produk' }}</p>

        <div class="mt-1.5 flex flex-wrap gap-1.5 text-[10px]">
            @if ($d->ukuran)
                <span class="px-2 py-0.5 rounded-md bg-cream border border-line/50 text-ink/65">Ukuran {{ $d->ukuran }}</span>
            @endif
            @if ($d->warna)
                <span class="px-2 py-0.5 rounded-md bg-cream border border-line/50 text-ink/65">Warna {{ $d->warna }}</span>
            @endif
            <span class="px-2 py-0.5 rounded-md bg-cream border border-line/50 text-ink/65">{{ $qty }} item</span>
        </div>

        <p class="mt-1.5 text-[11px] text-ink/50 tabular-nums">
            {{ $qty }} × Rp{{ number_format($harga, 0, ',', '.') }}
            <span class="mx-1">·</span>
            <span class="font-semibold text-ink/80">Rp{{ number_format($qty * $harga, 0, ',', '.') }}</span>
        </p>
    </div>
</div>