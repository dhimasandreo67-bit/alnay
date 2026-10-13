@props(['pageTitle' => null, 'pageSubtitle' => null])

<header class="bg-panel border-b border-line sticky top-0 z-30">
    <div class="px-4 md:px-6 py-3 flex items-center gap-4">

        {{-- Judul halaman --}}
        <div class="flex-1 min-w-0">
            @if ($pageTitle)
                <h1 class="font-display text-xl md:text-2xl text-ink leading-tight truncate">{{ $pageTitle }}</h1>
            @endif
            @if ($pageSubtitle)
                <p class="text-xs text-muted truncate hidden md:block">{{ $pageSubtitle }}</p>
            @endif
        </div>

        {{-- Search --}}
        <form action="#" class="hidden md:flex relative w-72">
            <x-icon name="search" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none" />
            <input type="text" placeholder="Cari pesanan, gaun, klien..."
                   class="w-full pl-9 pr-4 py-2 rounded-full border border-line bg-cream text-base placeholder:text-muted focus:outline-none focus:border-gold transition-colors">
        </form>

        {{-- Notifikasi --}}
        <button type="button"
                class="relative w-10 h-10 rounded-full flex items-center justify-center text-ink hover:bg-cream transition-colors"
                aria-label="Notifikasi">
            <x-icon name="clock" :size="18" />
            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-gold"></span>
            {{-- TODO: komponen bell. Sementara pakai clock. --}}
        </button>

        {{-- Avatar --}}
        <span class="w-10 h-10 rounded-full bg-ink text-gold flex items-center justify-center font-bold text-sm shrink-0">
            SA
        </span>
    </div>
</header>