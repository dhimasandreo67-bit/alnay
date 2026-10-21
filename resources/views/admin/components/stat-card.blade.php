@props([
    'label',
    'value',
    'icon' => 'grid',
    'accent' => 'gold',     // gold | rose | blue | green
    'trend' => null,        // e.g. '+12%'
    'trendLabel' => null,   // e.g. 'dibanding kemarin'
    'trendType' => 'up',    // up | down | neutral
    'note' => null,         // e.g. '3 gaun jatuh tempo besok' — plain supporting line
    'noteType' => 'muted',  // muted | warn | danger
])

@php
    $accents = [
        'gold'  => 'bg-gold/15 text-gold-dark',
        'rose'  => 'bg-rose-100 text-rose-600',
        'blue'  => 'bg-blue-100 text-blue-600',
        'green' => 'bg-emerald-100 text-emerald-600',
    ];
    $trendColor = match ($trendType) {
        'up' => 'text-emerald-600',
        'down' => 'text-rose-600',
        default => 'text-ink-muted',
    };
    $noteColor = match ($noteType) {
        'warn' => 'text-amber-600',
        'danger' => 'text-rose-600',
        default => 'text-ink-muted',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-black/5 bg-white p-6 shadow-sm']) }}>
    <div class="flex items-start justify-between">
        <span class="text-xs font-semibold tracking-wide text-ink-muted">{{ strtoupper($label) }}</span>
        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $accents[$accent] }}">
            <x-admin::admin-icon :name="$icon" class="h-4.5 w-4.5" />
        </span>
    </div>

    <p class="mt-4 font-display text-4xl font-semibold text-ink">{{ $value }}</p>

    @if ($trend)
        <p class="mt-2 text-sm">
            <span class="font-semibold {{ $trendColor }}">{{ $trend }}</span>
            <span class="text-ink-muted"> {{ $trendLabel }}</span>
        </p>
    @elseif ($note)
        <p class="mt-2 text-sm {{ $noteColor }}">{{ $note }}</p>
    @endif
</div>
