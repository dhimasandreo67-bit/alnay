@php
    $menu = [
        [
            'group' => null,
            'items' => [
                ['label' => 'Dashboard', 'route' => 'owner.dashboard', 'icon' => 'sparkle', 'active' => request()->routeIs('owner.dashboard')],
            ],
        ],
        [
            'group' => 'Master Data',
            'items' => [
                ['label' => 'Kategori',        'route' => 'admin.kategori.index', 'icon' => 'chevron-right', 'active' => false],
                ['label' => 'Produk',   'route' => 'admin.produk.index',   'icon' => 'chevron-right', 'active' => false],
                ['label' => 'Pengguna',        'route' => 'admin.pengguna.index', 'icon' => 'user',          'active' => false],
            ],
        ],
        [
            'group' => 'Transaksi',
            'items' => [
                ['label' => 'Semua Transaksi',      'route' => 'admin.transaksi.index',  'icon' => 'chevron-right', 'active' => false],
                ['label' => 'Buat Transaksi Baru',  'route' => 'admin.transaksi.create', 'icon' => 'chevron-right', 'active' => false],
            ],
        ],
        [
            'group' => 'Sewa & Jaminan',
            'items' => [
                ['label' => 'Data Sewa',            'route' => 'admin.sewa.index',    'icon' => 'calendar', 'active' => false],
                ['label' => 'Verifikasi Jaminan',   'route' => 'admin.jaminan.index', 'icon' => 'check',    'active' => false, 'badge' => 4],
            ],
        ],
        [
            'group' => 'Layanan Atelier',
            'items' => [
                ['label' => 'Refund & Return',    'route' => 'admin.refund.index',     'icon' => 'chevron-right', 'active' => false, 'badge' => 2],
                ['label' => 'Pengiriman',         'route' => 'admin.pengiriman.index', 'icon' => 'chevron-right', 'active' => false],
                ['label' => 'Reservasi Fitting',  'route' => 'admin.fitting.index',    'icon' => 'calendar',      'active' => false],
                ['label' => 'Rating & Ulasan',    'route' => 'admin.rating.index',     'icon' => 'star-outline',  'active' => false],
            ],
        ],
        [
            'group' => 'Sistem',
            'items' => [
                ['label' => 'Laporan',    'route' => 'owner.laporan',    'icon' => 'chevron-right', 'active' => request()->routeIs('owner.laporan')],
                ['label' => 'Kelola Admin','route' => 'owner.admin.index', 'icon' => 'user',          'active' => request()->routeIs('owner.admin.*')],
                ['label' => 'Pengaturan', 'route' => 'owner.pengaturan', 'icon' => 'clock',         'active' => request()->routeIs('owner.pengaturan')],
            ],
        ],
    ];
@endphp

<aside class="bg-ink text-cream/80 w-64 shrink-0 min-h-screen hidden lg:flex flex-col">

    {{-- Logo --}}
    <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-2 px-5 py-5 border-b border-cream/10">
        <span class="w-9 h-9 rounded-md bg-gold text-ink flex items-center justify-center font-display font-bold text-lg">A</span>
        <span class="leading-tight">
            <span class="block font-display font-bold text-cream text-base tracking-tight">ALNAY.ID</span>
            <span class="block text-[9px] uppercase tracking-[0.2em] text-cream/50">Atelier Backoffice</span>
        </span>
    </a>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto py-4">
        @foreach ($menu as $section)
            @if ($section['group'])
                <p class="px-5 pt-4 pb-2 text-[10px] uppercase tracking-[0.2em] text-cream/40 font-semibold">
                    {{ $section['group'] }}
                </p>
            @endif

            <ul class="space-y-0.5">
                @foreach ($section['items'] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-5 py-2.5 text-sm transition-colors
                                  {{ $item['active'] ? 'bg-gold text-ink font-semibold' : 'hover:bg-cream/5 hover:text-cream' }}">
                            <x-icon :name="$item['icon']" :size="16" class="shrink-0" />
                            <span class="flex-1">{{ $item['label'] }}</span>
                            @if (!empty($item['badge']))
                                <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-gold text-ink text-[10px] font-bold flex items-center justify-center">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    {{-- User --}}
    <div class="border-t border-cream/10 p-4">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-full bg-gold text-ink flex items-center justify-center font-bold text-sm">RA</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-cream font-medium truncate">Raden Aditya</p>
                <p class="text-[10px] uppercase tracking-wider text-cream/50">Owner Atelier</p>
            </div>
        </div>
        {{-- ⚠️ Data dummy. Nanti dari auth()->user() --}}
    </div>

</aside>