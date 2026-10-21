@props([
    'icon',
    'title',
    'description',
    'count' => null,
    'tone' => 'rose',   // rose | amber | blue
    'href' => '#',
])

@php
    $tones = [
        'rose'  => 'bg-rose-100 text-rose-600',
        'amber' => 'bg-amber-100 text-amber-600',
        'blue'  => 'bg-blue-100 text-blue-600',
    ];
@endphp

<a href="{{ $href }}" class="flex items-start gap-3 rounded-xl border border-black/5 bg-white p-4 transition hover:border-gold/40 hover:shadow-sm">
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $tones[$tone] }}">
        <x-admin::admin-icon :name="$icon" class="h-4.5 w-4.5" />
    </span>

    <span class="min-w-0 flex-1">
        <span class="block text-sm font-semibold text-ink">{{ $title }}</span>
        <span class="mt-0.5 block text-sm text-ink-muted">{{ $description }}</span>
    </span>

    @if ($count !== null)
        <span class="flex h-6 min-w-6 shrink-0 items-center justify-center rounded-full border border-black/10 text-xs font-semibold text-ink">
            {{ $count }}
        </span>
    @endif
</a>
