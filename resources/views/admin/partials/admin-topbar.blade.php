@php
    $notifCount = $notifCount ?? 0; // pass from controller/view-composer, e.g. pending actions count
@endphp

<header class="flex items-center justify-between gap-6 border-b border-black/5 bg-cream px-8 py-6">
    <div>
        <h1 class="font-display text-3xl font-semibold text-ink">@yield('title', 'Dashboard')</h1>
        <p class="mt-1 text-sm text-ink-muted">@yield('subtitle', \Illuminate\Support\Carbon::now()->translatedFormat('l, j F Y'))</p>
    </div>

    <div class="flex shrink-0 items-center gap-3">
        <label class="relative hidden md:block">
            <x-admin.icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" />
            <input type="search" placeholder="Cari pesanan, gaun, klien..."
                   class="w-72 rounded-xl border border-black/10 bg-white py-2.5 pl-10 pr-12 text-sm placeholder:text-ink-muted focus:border-gold focus:outline-none focus:ring-2 focus:ring-gold/30">
            <kbd class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-black/5 px-1.5 py-0.5 text-[11px] font-medium text-ink-muted">⌘K</kbd>
        </label>

        <a href="{{ Route::has('admin.notifications') ? route('admin.notifications') : '#' }}"
           class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-black/10 bg-white text-ink hover:bg-black/5">
            <x-admin.icon name="bell" class="h-5 w-5" />
            @if ($notifCount > 0)
                <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
            @endif
        </a>

        <form action="{{ route('admin.logout') }}" method="POST"
              onsubmit="return confirm('Yakin ingin keluar dari backoffice?')">
            @csrf
            <button type="submit"
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-black/10 bg-white text-ink hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-colors"
                    title="Keluar">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </form>
    </div>
</header>