@php
    // Notifikasi dikirim via View Composer (lihat bagian 2 di bawah).
    // Kalau tidak di-set, fallback ke array kosong supaya tidak error.
    $notifications = $notifications ?? [];
    $notifCount    = $notifCount    ?? count($notifications);
@endphp

<header class="sticky top-0 z-40 border-b border-amber-100/60 bg-white/85 backdrop-blur-xl">
    {{-- Accent bar --}}
    <div class="h-0.5 w-full bg-gradient-to-r from-amber-600 via-amber-500 to-amber-400"></div>

    <div class="flex items-center justify-between gap-4 px-6 lg:px-8 py-4">

        {{-- Judul halaman --}}
        <div class="min-w-0">
            <h1 class="truncate text-xl lg:text-2xl font-bold tracking-tight text-gray-900">
                @yield('title', 'Dashboard')
            </h1>
            <p class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="truncate">@yield('subtitle', \Illuminate\Support\Carbon::now()->translatedFormat('l, j F Y'))</span>
            </p>
        </div>

        {{-- Aksi --}}
        <div class="flex shrink-0 items-center gap-2 lg:gap-3">

            {{-- ============ NOTIFIKASI ============ --}}
            <div class="relative">
                <button type="button" id="notif-btn"
                        aria-label="Notifikasi" aria-haspopup="true" aria-expanded="false"
                        class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 shadow-sm transition-all hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if ($notifCount > 0)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-gradient-to-r from-rose-500 to-pink-500 px-1.5 text-[10px] font-bold text-white ring-2 ring-white">
                            {{ $notifCount > 99 ? '99+' : $notifCount }}
                        </span>
                    @endif
                </button>

                {{-- Dropdown --}}
                <div id="notif-dropdown"
                     class="hidden absolute right-0 mt-2 w-80 sm:w-96 origin-top-right rounded-2xl border border-amber-100/60 bg-white shadow-xl shadow-amber-500/10 overflow-hidden">

                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 bg-amber-50/40">
                        <p class="text-sm font-bold text-gray-900">Notifikasi</p>
                        @if ($notifCount > 0)
                            <span class="rounded-full bg-gradient-to-r from-amber-500 to-amber-400 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                {{ $notifCount }} baru
                            </span>
                        @endif
                    </div>

                    <div class="max-h-80 overflow-y-auto">
                        @forelse ($notifications as $n)
                            <a href="{{ $n['url'] ?? '#' }}"
                               class="flex items-start gap-3 border-b border-gray-50 px-4 py-3 transition-colors hover:bg-amber-50/50 last:border-0">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                                    @switch($n['color'] ?? 'amber')
                                        @case('sky') bg-sky-100 text-sky-600 @break
                                        @case('emerald') bg-emerald-100 text-emerald-600 @break
                                        @case('rose') bg-rose-100 text-rose-600 @break
                                        @case('violet') bg-violet-100 text-violet-600 @break
                                        @default bg-amber-100 text-amber-600
                                    @endswitch">
                                    @switch($n['icon'] ?? 'bell')
                                        @case('shopping-cart')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            @break
                                        @case('truck')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                            @break
                                        @case('shield')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            @break
                                        @case('refresh')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            @break
                                        @case('calendar')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            @break
                                        @default
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    @endswitch
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-900 leading-snug">{{ $n['title'] ?? '' }}</p>
                                    @if (!empty($n['subtitle']))
                                        <p class="mt-0.5 text-[11px] text-gray-500 leading-snug">{{ $n['subtitle'] }}</p>
                                    @endif
                                </div>
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 px-4">
                                <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 border border-emerald-100">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-xs font-bold text-gray-900">Semua aman</p>
                                <p class="mt-0.5 text-[11px] text-gray-400 text-center">Tidak ada notifikasi baru.</p>
                            </div>
                        @endforelse
                    </div>

                    @if (Route::has('admin.notifications'))
                        <a href="{{ route('admin.notifications') }}"
                           class="block border-t border-gray-100 bg-amber-50/40 px-4 py-2.5 text-center text-[11px] font-bold text-amber-700 hover:bg-amber-100/60 transition-colors">
                            Lihat semua notifikasi &rarr;
                        </a>
                    @endif
                </div>
            </div>

            {{-- ============ USER INFO ============ --}}
            @auth
                <div class="hidden md:flex items-center gap-2 rounded-xl border border-gray-200 bg-white py-1 pl-1 pr-3 shadow-sm">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-tr from-amber-500 to-amber-400 text-xs font-black text-white shadow-sm shadow-amber-500/20">
                        {{ strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate text-xs font-bold text-gray-900">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] capitalize text-gray-400">{{ auth()->user()->role ?? 'admin' }}</p>
                    </div>
                </div>
            @endauth

            {{-- ============ LOGOUT ============ --}}
            <form action="{{ route('admin.logout') }}" method="POST"
                  onsubmit="return confirm('Yakin ingin keluar dari backoffice?')">
                @csrf
                <button type="submit"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 shadow-sm transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400"
                        title="Keluar">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>

<script>
    (function () {
        var btn      = document.getElementById('notif-btn');
        var dropdown = document.getElementById('notif-dropdown');
        if (!btn || !dropdown) return;

        function close() {
            dropdown.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var hidden = dropdown.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        });

        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && !btn.contains(e.target)) close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });
    })();
</script>