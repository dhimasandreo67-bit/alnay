@props([
    'title', 
    'subtitle' => null, 
    'linkText' => null, 
    'linkHref' => null
])

<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display text-2xl md:text-3xl text-ink leading-tight font-bold">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-sm text-muted mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    {{-- Tombol "Lihat Semua" (Jika ada) --}}
    @if ($linkText && $linkHref)
        <a href="{{ $linkHref }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-gold hover:text-gold/80 transition-colors whitespace-nowrap">
            {{ $linkText }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
    @endif

    {{-- Slot Aksi Kustom (Jika ada) --}}
    @if (isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>