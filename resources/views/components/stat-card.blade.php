@props(['label', 'value', 'hint' => null, 'icon' => null, 'trend' => null])

<div class="bg-panel border border-line rounded-2xl p-5">
    <div class="flex items-start justify-between mb-3">
        <p class="text-[10px] uppercase tracking-[0.2em] text-muted font-semibold">{{ $label }}</p>
        @if ($icon)
            <span class="w-8 h-8 rounded-lg bg-cream flex items-center justify-center text-gold">
                <x-icon :name="$icon" :size="16" />
            </span>
        @endif
    </div>

    <p class="font-display text-3xl md:text-4xl text-ink leading-none mb-2">{{ $value }}</p>

    <div class="flex items-center gap-2 text-xs">
        @if ($trend)
            <span class="{{ str_starts_with($trend, '+') ? 'text-green-700' : 'text-red-700' }} font-semibold">
                {{ $trend }}
            </span>
        @endif
        @if ($hint)
            <span class="text-muted">{{ $hint }}</span>
        @endif
    </div>
</div>