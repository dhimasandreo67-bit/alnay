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
            <div class="relative" x-data="{ notifOpen: false }">
                <button @click="notifOpen = !notifOpen" class="relative p-2 rounded-full hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    
                    {{-- TITIK MERAH JIKA ADA NOTIFIKASI --}}
                    @if(isset($totalNotif) && $totalNotif > 0)
                        <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                        </span>
                    @endif
                </button>

                {{-- PANEL NOTIFIKASI --}}
                <div x-show="notifOpen" x-cloak @click.outside="notifOpen = false" class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Notifikasi</h3>
                        @if(isset($totalNotif) && $totalNotif > 0)
                            <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $totalNotif }} Baru</span>
                        @endif
                    </div>
                    
                    <div class="max-h-80 overflow-y-auto">
                        @if(isset($pesanNotif))
                            @foreach($pesanNotif as $notif)
                                <a href="{{ $notif['url'] }}" class="block p-4 border-b border-gray-50 hover:bg-gray-50 transition">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-white border flex items-center justify-center shrink-0 {{ $notif['warna'] }}">
                                            @if($notif['icon'] === 'check')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800">{{ $notif['judul'] }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $notif['pesan'] }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                    </div>
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