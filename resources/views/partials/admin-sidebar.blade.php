@php
    // Keep this list in sync with routes/admin.php.
    // 'routes' accepts a single routeIs() pattern or an array of patterns.
    $navGroups = [
        [
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'grid'],
            ],
        ],
        [
            'heading' => 'Master Data',
            'items' => [
                ['label' => 'Kategori', 'route' => 'admin.kategori.*', 'icon' => 'box'],
                ['label' => 'Produk & Gaun', 'route' => 'admin.produk.*', 'icon' => 'hanger'],
                ['label' => 'Pengguna', 'route' => 'admin.pengguna.*', 'icon' => 'users'],
            ],
        ],
        [
            'heading' => 'Transaksi',
            'items' => [
                ['label' => 'Semua Transaksi', 'route' => 'admin.transaksi.index', 'icon' => 'wallet'],
                ['label' => 'Buat Transaksi Baru', 'route' => 'admin.transaksi.create', 'icon' => 'plus-circle'],
            ],
        ],
        [
            'heading' => 'Sewa & Jaminan',
            'items' => [
                ['label' => 'Data Sewa', 'route' => 'admin.sewa.*', 'icon' => 'calendar', 'badge' => null],
                ['label' => 'Verifikasi Jaminan', 'route' => 'admin.jaminan.*', 'icon' => 'shield', 'badge' => fn () => \App\Models\JaminanSewa::where('status_verifikasi', 'pending')->count()],
            ],
        ],
        [
            'heading' => 'Layanan Atelier',
            'items' => [
                ['label' => 'Refund & Return', 'route' => 'admin.refund.*', 'icon' => 'undo', 'badge' => fn () => \App\Models\Refund::where('status', 'pending')->count()],
                ['label' => 'Pengiriman', 'route' => 'admin.pengiriman.*', 'icon' => 'truck'],
                ['label' => 'Reservasi Fitting', 'route' => 'admin.fitting.*', 'icon' => 'clock'],
                ['label' => 'Rating & Ulasan', 'route' => 'admin.rating.*', 'icon' => 'star'],
            ],
        ],
        [
            'heading' => 'Sistem',
            'items' => [
                ['label' => 'Audit Log', 'route' => 'admin.audit.*', 'icon' => 'file-text'],
                ['label' => 'Kelola Admin', 'route' => 'admin.kelola-admin.*', 'icon' => 'shield-check'],
            ],
        ],
    ];

    // Saring menu sesuai hak akses admin yang login: owner selalu lihat semua,
    // admin biasa cuma lihat menu yang ada di admin_permissions-nya (lihat
    // App\Models\User::canAccessAdminMenu()). Dashboard selalu tampil untuk semua.
    $currentUser = auth()->user();

    $visibleNavGroups = collect($navGroups)->map(function ($group) use ($currentUser) {
        $group['items'] = array_values(array_filter($group['items'], function ($item) use ($currentUser) {
            $pattern = is_array($item['route']) ? $item['route'][0] : $item['route'];
            $key = explode('.', \Illuminate\Support\Str::after($pattern, 'admin.'))[0] ?? null;

            if (!$key || $key === 'dashboard') {
                return true;
            }

            return $currentUser?->canAccessAdminMenu($key) ?? false;
        }));

        return $group;
    })->filter(fn ($group) => count($group['items']) > 0)->values();
@endphp

<aside class="flex w-64 shrink-0 flex-col justify-between bg-ink text-cream">

    <div>
        <div class="flex items-center gap-3 px-6 py-6">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold font-display text-lg font-bold text-ink">A</span>
            <div>
                <p class="font-display text-base font-semibold leading-none text-cream">ALNAY.ID</p>
                <p class="mt-1 flex items-center gap-1.5 text-[11px] text-cream/50">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Atelier Backoffice
                </p>
            </div>
        </div>

        <nav class="mt-2 space-y-6 px-3 pb-6">
            @foreach ($visibleNavGroups as $group)
                <div>
                    @isset($group['heading'])
                        <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-gold/70">
                            {{ strtoupper($group['heading']) }}
                        </p>
                    @endisset

                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            @php
                                $patterns = (array) $item['route'];
                                $active = request()->routeIs(...$patterns);
                                $count = isset($item['badge']) ? value($item['badge']) : null;
                            @endphp
                            <a href="{{ Route::has(str_replace('*', 'index', $item['route'])) ? route(str_replace('*', 'index', $item['route'])) : '#' }}"
                               class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm transition
                                      {{ $active ? 'bg-gold font-semibold text-ink' : 'text-cream/70 hover:bg-white/5 hover:text-cream' }}">
                                <span class="flex items-center gap-3">
                                    <x-admin.icon :name="$item['icon']" class="h-4 w-4 shrink-0" />
                                    {{ $item['label'] }}
                                </span>

                                @if ($count)
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-semibold text-white">
                                        {{ $count }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </div>

    <div class="flex items-center gap-3 border-t border-white/10 px-6 py-5">
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gold/20 text-sm font-semibold text-gold">
            {{ \Illuminate\Support\Str::of(auth()->user()->name ?? 'A')->substr(0, 2)->upper() }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-cream">{{ auth()->user()->name ?? 'Admin' }}</p>
            <p class="truncate text-xs text-cream/50">Admin Backoffice</p>
        </div>
        <a href="{{ Route::has('admin.settings') ? route('admin.settings') : '#' }}" class="text-cream/50 hover:text-cream">
            <x-admin.icon name="settings" class="h-4 w-4" />
        </a>
    </div>
</aside>