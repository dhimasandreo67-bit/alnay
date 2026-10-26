@props(['tone' => 'muted']) {{-- muted | gold | blue | green | rose --}}

@php
    $tones = [
        'muted' => 'bg-black/5 text-ink-muted',
        'gold'  => 'bg-gold/15 text-gold-dark',
        'blue'  => 'bg-blue-100 text-blue-600',
        'green' => 'bg-emerald-100 text-emerald-700',
        'rose'  => 'bg-rose-100 text-rose-600',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ' . $tones[$tone]]) }}>
    {{ $slot }}
</span>
