<?php
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
                ['label' => 'Produk', 'route' => 'admin.produk.*', 'icon' => 'hanger'],
                ['label' => 'Pengguna', 'route' => 'admin.pengguna.*', 'icon' => 'users'],
            ],
        ],
        [
            'heading' => 'Transaksi',
            'items' => [
                ['label' => 'Semua Transaksi', 'route' => 'admin.transaksi.index', 'icon' => 'wallet'],
                ['label' => 'Buat Transaksi Baru', 'route' => 'admin.transaksi.create', 'icon' => 'plus-circle'],
                ['label' => 'Laporan Pendapatan', 'route' => 'admin.laporan.*', 'icon' => 'file-text'],
            ],
        ],
        [
            'heading' => 'Sewa & Jaminan',
            'items' => [
                ['label' => 'Data Sewa', 'route' => 'admin.sewa.*', 'icon' => 'calendar', 'badge' => fn () => \App\Models\JaminanSewa::where('status_verifikasi', 'pending')->count()],
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
?>

<style>
    /* Animasi masuk menu item */
    @keyframes sidebarSlideIn {
        from { opacity: 0; transform: translateX(-8px); }
        to   { opacity: 1; transform: translateX(0); }
    }

    /* Glow lembut untuk item aktif */
    @keyframes activeGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        50%      { box-shadow: 0 0 0 4px rgba(245, 158, 11, 0); }
    }

    /* Shine sweep pada brand */
    .brand-shine {
        position: relative;
        overflow: hidden;
    }
    .brand-shine::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -60%;
        width: 30%;
        height: 200%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
        transform: rotate(30deg);
        animation: brandShine 6s ease-in-out infinite;
    }
    @keyframes brandShine {
        0%, 80% { left: -60%; }
        100%    { left: 130%; }
    }

    /* Scrollbar tipis untuk nav */
    .sidebar-nav::-webkit-scrollbar { width: 4px; }
    .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(245, 158, 11, 0.25);
        border-radius: 4px;
    }
    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: rgba(245, 158, 11, 0.5);
    }
</style>

<aside class="relative flex w-64 shrink-0 flex-col overflow-hidden sticky top-0 h-screen
              bg-gradient-to-b from-[#15140f] via-[#1a1812] to-[#0f0e0a] text-cream
              border-r border-amber-500/10">

    
    <div class="pointer-events-none absolute -top-24 -left-16 h-48 w-48 rounded-full bg-amber-500/10 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-0 h-40 w-40 rounded-full bg-amber-600/5 blur-3xl"></div>

    
    <div class="relative brand-shine flex items-center gap-3 px-5 py-5 border-b border-white/5">
        <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                    bg-gradient-to-tr from-amber-600 via-amber-500 to-amber-400
                    shadow-lg shadow-amber-500/30">
            <span class="font-display text-lg font-black text-white drop-shadow">A</span>
            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-[#15140f]"></span>
        </div>
        <div class="min-w-0">
            <p class="font-display text-base font-bold leading-none text-white tracking-tight">ALNAY.ID</p>
            <p class="mt-1.5 flex items-center gap-1.5 text-[10px] font-medium uppercase tracking-wider text-amber-200/60">
                <span class="h-1 w-1 rounded-full bg-emerald-400 animate-pulse"></span>
                Atelier Backoffice
            </p>
        </div>
    </div>

    
    <div class="sidebar-nav flex-1 overflow-y-auto no-scrollbar" data-sidebar-scroll>
        <nav class="space-y-5 px-3 py-5">
            <?php $__currentLoopData = $visibleNavGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gi => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="animation: sidebarSlideIn 0.35s ease-out <?php echo e($gi * 0.04); ?>s both;">

                    <?php if(isset($group['heading'])): ?>
                        <div class="mb-2 flex items-center gap-2 px-3">
                            <span class="h-px flex-1 bg-gradient-to-r from-amber-500/40 to-transparent"></span>
                            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-amber-300/70 whitespace-nowrap">
                                <?php echo e($group['heading']); ?>

                            </p>
                            <span class="h-px flex-1 bg-gradient-to-l from-amber-500/40 to-transparent"></span>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-0.5">
                        <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $patterns = (array) $item['route'];
                                $active   = request()->routeIs(...$patterns);
                                $count    = isset($item['badge']) ? value($item['badge']) : null;
                            ?>

                            <a href="<?php echo e(Route::has(str_replace('*', 'index', $item['route'])) ? route(str_replace('*', 'index', $item['route'])) : '#'); ?>"
                               <?php if($active): ?> aria-current="page" <?php endif; ?>
                               class="group/item relative flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-sm font-medium
                                      transition-all duration-200
                                      <?php echo e($active
                                            ? 'bg-gradient-to-r from-amber-500/20 via-amber-500/10 to-transparent text-white shadow-sm shadow-amber-500/10'
                                            : 'text-cream/60 hover:bg-white/[0.04] hover:text-cream'); ?>">

                                
                                <?php if($active): ?>
                                    <span class="absolute left-0 top-1/2 h-6 w-0.5 -translate-y-1/2 rounded-r-full bg-gradient-to-b from-amber-400 to-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                                <?php endif; ?>

                                <span class="flex min-w-0 items-center gap-3">
                                    
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition-all duration-200
                                                 <?php echo e($active
                                                        ? 'bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/30'
                                                        : 'bg-white/[0.03] text-cream/50 group-hover/item:bg-amber-500/10 group-hover/item:text-amber-300'); ?>">
                                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $item['icon'],'class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['icon']),'class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                                    </span>

                                    <span class="truncate <?php echo e($active ? 'font-bold' : ''); ?>"><?php echo e($item['label']); ?></span>
                                </span>

                                <?php if($count): ?>
                                    <span class="relative flex h-5 min-w-[20px] shrink-0 items-center justify-center rounded-full
                                                 bg-gradient-to-r from-rose-500 to-pink-500 px-1.5 text-[10px] font-bold text-white
                                                 shadow-sm shadow-rose-500/40">
                                        <?php echo e($count > 99 ? '99+' : $count); ?>

                                        <span class="absolute inset-0 rounded-full bg-rose-400 animate-ping opacity-20"></span>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    </div>

    
    <div class="relative shrink-0 border-t border-white/5 bg-gradient-to-t from-black/30 to-transparent p-3">
        <div class="group/user flex items-center gap-3 rounded-xl border border-white/5 bg-white/[0.02] p-2.5
                    transition-all duration-200 hover:border-amber-500/20 hover:bg-white/[0.04]">

            
            <?php if(auth()->user()->foto_profil): ?>
                <img src="<?php echo e(asset('storage/' . auth()->user()->foto_profil)); ?>"
                     alt="<?php echo e(auth()->user()->name); ?>"
                     class="h-9 w-9 shrink-0 rounded-xl object-cover border-2 border-amber-500/30 group-hover/user:border-amber-400/60 transition-colors">
            <?php else: ?>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                             bg-gradient-to-tr from-amber-600 to-amber-400
                             text-xs font-black text-white shadow-sm shadow-amber-500/30">
                    <?php echo e(\Illuminate\Support\Str::of(auth()->user()->name ?? 'A')->substr(0, 2)->upper()); ?>

                </span>
            <?php endif; ?>

            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-bold leading-tight text-cream"><?php echo e(auth()->user()->name ?? 'Admin'); ?></p>
                <p class="mt-0.5 flex items-center gap-1 truncate text-[10px] leading-tight text-amber-200/50">
                    <span class="h-1 w-1 rounded-full bg-emerald-400"></span>
                    Admin Backoffice
                </p>
            </div>
        </div>
    </div>
</aside><?php /**PATH E:\Project_kecil\alnay\resources\views/partials/admin-sidebar.blade.php ENDPATH**/ ?>