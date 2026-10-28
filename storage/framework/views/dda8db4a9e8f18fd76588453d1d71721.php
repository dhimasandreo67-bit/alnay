

<?php $__env->startSection('title', 'Kelola Produk'); ?>

<?php $__env->startSection('content'); ?>

    
    <style>
        @keyframes float-subtle {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-4px); }
        }
        @keyframes shine {
            100% { left: 125%; }
        }
        .animate-float { animation: float-subtle 4s ease-in-out infinite; }
        .group-shine { position: relative; overflow: hidden; }
        .group-shine::after {
            content: '';
            position: absolute;
            top: -50%; left: -60%;
            width: 20%; height: 200%;
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(30deg);
            transition: all 0.6s ease;
        }
        .group-shine:hover::after {
            left: 130%;
        }
    </style>

    <div class="p-8 space-y-8 animate-fade-up">

        <?php if(session('success')): ?>
            <div class="flex items-center gap-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm animate-fade-up" role="alert">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-sm font-semibold"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <nav class="text-xs text-muted font-bold tracking-wider uppercase mb-1.5 flex items-center gap-2">
                    <span>Katalog Busana</span> <span class="text-gold">/</span> <span class="text-ink">Manajemen Inventaris</span>
                </nav>
                <h1 class="text-3xl font-display font-bold tracking-tight text-ink">Kelola Produk</h1>
                <p class="text-sm text-muted mt-1">Katalog busana & aksesoris etnik, inventaris sewa, dan penjualan couture AL-NAY.ID</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold text-sm hover:bg-gray-50 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                    Unduh CSV
                </button>
                <a href="<?php echo e(route('admin.produk.create')); ?>"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Tambah Produk
                </a>
            </div>
        </div>

        
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            
            
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Total Koleksi</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-amber-950"><?php echo e($totalKoleksi); ?></span>
                    <span class="text-sm font-bold text-amber-800/60">Unit</span>
                </div>
                <p class="mt-1 text-xs font-semibold text-amber-700/80"><?php echo e($totalKategori); ?> Kategori Tersedia</p>
            </div>

            
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Siap Sewa / Jual</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-emerald-950"><?php echo e($siapCount); ?></span>
                    <span class="text-sm font-bold text-emerald-800/60">Unit</span>
                </div>
                <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Tersedia di atelier</span>
                </div>
            </div>

            
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Sedang Disewa</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-sky-950"><?php echo e($disewaCount); ?></span>
                    <span class="text-sm font-bold text-sky-800/60">Unit</span>
                </div>
                <p class="mt-2 text-xs font-medium text-sky-700/80">Dalam masa reservasi</p>
            </div>

            
            <div class="group group-shine relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50/80 via-white to-rose-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-500/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-800/80">Perawatan / Reparasi</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-rose-950"><?php echo e($perawatanCount); ?></span>
                    <span class="text-sm font-bold text-rose-800/60">Unit</span>
                </div>
                <p class="mt-2 text-xs font-semibold text-rose-600">Dry clean & perbaikan</p>
            </div>

        </div>

        
        <form method="GET" action="<?php echo e(route('admin.produk.index')); ?>"
              class="bg-panel p-3 rounded-2xl border border-line shadow-sm flex flex-col md:flex-row items-center gap-3">
            <div class="relative w-full md:w-80 group">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-muted group-focus-within:text-gold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Cari busana, kode SKU..."
                       class="w-full pl-11 pr-4 py-2.5 bg-cream/50 border border-transparent rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-gold/20 focus:border-gold transition-all duration-300">
            </div>

            <select name="kategori_id" onchange="this.form.submit()"
                    class="w-full md:w-auto px-4 py-2.5 bg-cream/50 border border-transparent rounded-xl text-sm font-semibold text-ink focus:outline-none focus:ring-2 focus:ring-gold/20 focus:border-gold transition-all duration-300 cursor-pointer">
                <option value="">Semua Kategori</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($kategori->id); ?>" <?php if(($filters['kategori_id'] ?? null) == $kategori->id): echo 'selected'; endif; ?>><?php echo e($kategori->nama_kategori); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select name="status" onchange="this.form.submit()"
                    class="w-full md:w-auto px-4 py-2.5 bg-cream/50 border border-transparent rounded-xl text-sm font-semibold text-ink focus:outline-none focus:ring-2 focus:ring-gold/20 focus:border-gold transition-all duration-300 cursor-pointer">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = $opsiStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(($filters['status'] ?? null) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <div class="flex-1 flex items-center justify-end gap-3 w-full md:w-auto mt-2 md:mt-0 pr-2">
                <span class="text-xs text-muted font-medium whitespace-nowrap">
                    Menampilkan <strong class="text-ink"><?php echo e($produkList->count()); ?></strong> dari <strong class="text-ink"><?php echo e($totalKoleksi); ?></strong> busana
                </span>
                <?php if(array_filter($filters)): ?>
                    <a href="<?php echo e(route('admin.produk.index')); ?>" class="text-xs font-bold text-rose-500 hover:text-rose-700 hover:underline transition-all whitespace-nowrap">
                        ⟲ Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>

        
        <div class="bg-panel rounded-3xl shadow-sm border border-line overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line">
                    <thead class="bg-cream/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-ink/60 uppercase tracking-wider">Foto & Siluet</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-ink/60 uppercase tracking-wider">Nama & Motif</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-ink/60 uppercase tracking-wider">Kode SKU</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-ink/60 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-ink/60 uppercase tracking-wider">Tarif Sewa & Jual</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-ink/60 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-ink/60 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-ink/60 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/50 bg-panel">
                        <?php $__empty_1 = true; $__currentLoopData = $produkList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $sampul = $produk->sampulUrl();
                                $statusClass = match($produk->status) {
                                    'tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'disewa'   => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'dicuci'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'rusak'    => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default    => 'bg-gray-50 text-gray-500 border-gray-200',
                                };
                            ?>
                            <tr class="transition-colors duration-200 hover:bg-cream/40 group">
                                <td class="px-6 py-4">
                                    <div class="relative aspect-square w-16 h-16 overflow-hidden rounded-xl bg-cream border border-line shadow-sm">
                                        <?php if($sampul): ?>
                                            <img src="<?php echo e($sampul); ?>" alt="<?php echo e($produk->nama); ?>" loading="lazy"
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        <?php else: ?>
                                            <div class="w-full h-full flex flex-col items-center justify-center text-muted">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 max-w-[200px]">
                                    <p class="text-sm font-bold text-ink group-hover:text-gold transition-colors duration-300"><?php echo e($produk->nama); ?></p>
                                    <?php if($produk->motif): ?>
                                        <p class="text-xs text-muted mt-0.5"><?php echo e($produk->motif); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block bg-cream border border-line rounded-lg px-2.5 py-1 text-[11px] font-mono font-bold text-ink/80"><?php echo e($produk->sku); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block bg-ink/5 text-ink rounded-full px-3 py-1 text-xs font-semibold"><?php echo e($produk->kategori->nama_kategori ?? '-'); ?></span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <?php if($produk->bisa_disewa && $produk->harga_sewa): ?>
                                        <p class="font-bold text-gold">Sewa: Rp <?php echo e(number_format($produk->harga_sewa, 0, ',', '.')); ?><span class="text-muted font-normal">/3hr</span></p>
                                    <?php endif; ?>
                                    <?php if($produk->dapat_dijual): ?>
                                        <p class="text-ink/70 mt-1 font-medium">Jual: <?php echo e($produk->harga_jual ? 'Rp ' . number_format($produk->harga_jual, 0, ',', '.') : '-'); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-ink"><?php echo e($produk->stok); ?> <span class="text-xs font-normal text-muted">unit</span></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 border rounded-lg px-2.5 py-1 text-[11px] font-bold <?php echo e($statusClass); ?>">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                        <?php echo e($opsiStatus[$produk->status] ?? $produk->status); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="<?php echo e(route('admin.produk.edit', $produk->id)); ?>"
                                           class="p-2 rounded-xl text-ink/50 hover:text-gold hover:bg-gold/10 active:scale-90 transition-all duration-200" title="Edit Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form method="POST" action="<?php echo e(route('admin.produk.destroy', $produk->id)); ?>" onsubmit="return confirm('Hapus produk &quot;<?php echo e($produk->nama); ?>&quot;?');">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="p-2 rounded-xl text-ink/50 hover:text-rose-600 hover:bg-rose-50 active:scale-90 transition-all duration-200" title="Hapus Produk">
                                                <svg class="bg-red w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-muted">
                                        <svg class="w-12 h-12 mb-3 text-line" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="text-sm font-medium">Belum ada produk di katalog.</p>
                                        <a href="<?php echo e(route('admin.produk.create')); ?>" class="mt-3 text-sm text-gold font-bold hover:underline">+ Tambah Produk Pertama</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Project_kecil\alnay\resources\views/admin/produk/index.blade.php ENDPATH**/ ?>