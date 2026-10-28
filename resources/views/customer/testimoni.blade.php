@extends('layouts.public')

@section('title', 'Testimoni - ' . config('alnay.brand.nama'))
@section('meta_description', 'Ulasan pengantin dan klien yang sudah menyewa atau membeli busana adat di ' . config('alnay.brand.nama'))

@section('content')

{{-- ============================================================
     1. HERO + RINGKASAN RATING
     ============================================================ --}}
<section class="bg-ink text-white">
    <div class="max-w-7xl mx-auto px-4 py-14 md:py-20 grid gap-10 md:grid-cols-2 md:items-center">
        <div class="pd-up">
            <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-gold-soft">Kisah Bahagia Bersama {{ config('alnay.brand.nama') }}</p>
            <h1 class="font-display mt-3 text-3xl md:text-5xl font-bold leading-tight">Testimoni Pengantin &amp; Klien</h1>
            <p class="mt-4 max-w-md text-sm leading-relaxed text-white/70">
                Cerita nyata dari mereka yang sudah mempercayakan momen istimewanya pada busana adat kami.
            </p>
        </div>

        <div class="pd-up rounded-2xl border border-white/10 bg-white/5 p-6" style="--d: .15s">
            <div class="flex items-center gap-5">
                <div>
                    <p class="font-display text-5xl font-bold text-gold-soft">{{ $totalRating > 0 ? number_format($rataRata, 1, ',', '') : '-' }}</p>
                    <p class="mt-1 text-xs text-white/60">{{ number_format($totalRating, 0, ',', '.') }} ulasan</p>
                </div>

                <div class="flex-1 space-y-1.5">
                    @foreach ([5, 4, 3, 2, 1] as $b)
                        @php
                            $jumlah = $sebaran[$b] ?? 0;
                            $persen = $totalRating > 0 ? round($jumlah / $totalRating * 100) : 0;
                        @endphp
                        <a href="{{ route('testimoni', ['bintang' => $b]) }}" class="flex items-center gap-2 text-[11px] text-white/70 hover:text-white">
                            <span class="w-3 text-right">{{ $b }}</span>
                            <span class="text-gold-soft">★</span>
                            <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-white/10">
                                <span class="block h-full rounded-full bg-gold" style="width: {{ $persen }}%"></span>
                            </span>
                            <span class="w-8 text-right">{{ $jumlah }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     2. FILTER + DAFTAR ULASAN
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('testimoni') }}"
           class="rounded-full border px-4 py-2 text-xs font-semibold transition-colors min-h-[36px] inline-flex items-center
                  {{ $bintang === null ? 'border-gold bg-gold text-white' : 'border-line bg-panel text-ink hover:border-gold' }}">
            Semua
        </a>
        @foreach ([5, 4, 3, 2, 1] as $b)
            <a href="{{ route('testimoni', ['bintang' => $b]) }}"
               class="rounded-full border px-4 py-2 text-xs font-semibold transition-colors min-h-[36px] inline-flex items-center gap-1
                      {{ $bintang === $b ? 'border-gold bg-gold text-white' : 'border-line bg-panel text-ink hover:border-gold' }}">
                {{ $b }} <span>★</span>
            </a>
        @endforeach
    </div>

    @if ($ulasan->count() > 0)
        <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($ulasan as $i => $r)
                @php
                    $nama    = $r->user->name ?? 'Pelanggan';
                    $inisial = strtoupper(mb_substr($nama, 0, 2));
                @endphp

                <article class="pd-reveal flex flex-col rounded-2xl border border-line bg-panel p-6 transition-shadow hover:shadow-lg"
                         style="--d: {{ ($i % 3) * 0.08 }}s">
                    <p class="text-gold tracking-wider" aria-label="{{ $r->rating }} dari 5 bintang">
                        {{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5 - $r->rating) }}
                    </p>

                    <p class="mt-3 flex-1 text-sm leading-relaxed text-ink/80">&ldquo;{{ $r->ulasan }}&rdquo;</p>

                    <div class="mt-5 flex items-center gap-3 border-t border-line pt-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cream text-[11px] font-bold text-gold">
                            {{ $inisial }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-ink">{{ $nama }}</p>
                            <p class="truncate text-[11px] text-muted">
                                @if ($r->produk)
                                    <a href="{{ route('produk.detail', $r->produk->sku) }}" class="hover:text-gold">{{ $r->produk->nama }}</a>
                                    &middot;
                                @endif
                                {{ $r->tanggal?->translatedFormat('F Y') }}
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $ulasan->links() }}
        </div>
    @else
        <div class="mt-8">
            <x-empty-state
                title="Belum ada testimoni"
                :message="$bintang ? 'Belum ada ulasan dengan ' . $bintang . ' bintang.' : 'Jadilah yang pertama membagikan pengalaman Anda.'" />
        </div>
    @endif
</section>

{{-- ============================================================
     3. CTA
     ============================================================ --}}
<section class="bg-cream-dim">
    <div class="max-w-4xl mx-auto px-4 py-14 text-center">
        <h2 class="font-display text-2xl md:text-3xl font-bold text-ink">Siap Menciptakan Kisah Anda?</h2>
        <p class="mt-3 text-sm text-muted">Jelajahi koleksi busana adat kami untuk momen terbaik Anda.</p>
        <a href="{{ route('koleksi.index') }}"
           class="btn-gold mt-6 inline-flex items-center gap-2 rounded-lg bg-gold px-8 py-3.5 text-sm font-bold uppercase tracking-wider text-white">
            Jelajahi Koleksi
            <x-icon name="arrow-right" :size="16" />
        </a>
    </div>
</section>

@endsection