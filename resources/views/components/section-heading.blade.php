@props(['title', 'subtitle' => null, 'linkText' => null, 'linkHref' => '#'])

<div class="flex items-end justify-between gap-4 mb-6 md:mb-8">
    <div>
        <h2 class="font-display text-2xl md:text-3xl text-ink leading-tight">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-sm text-muted mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($linkText)
        <a href="{{ $linkHref }}"
           class="text-sm font-medium text-gold hover:text-ink transition-colors inline-flex items-center gap-1 min-h-[44px]">
            {{ $linkText }}
            <x-icon name="arrow-right" :size="16" />
        </a>
    @endif
</div>