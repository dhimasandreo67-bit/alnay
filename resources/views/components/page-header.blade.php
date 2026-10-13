@props(['title', 'subtitle' => null])

<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display text-3xl md:text-4xl text-ink leading-tight">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-sm text-muted mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>