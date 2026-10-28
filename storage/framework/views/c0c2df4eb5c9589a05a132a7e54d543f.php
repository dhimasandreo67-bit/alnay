

<?php $__env->startSection('title', 'Keranjang Sewa - ALNAY.ID'); ?>

<?php $__env->startSection('content'); ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
    .font-display { font-family: 'Playfair Display', serif; }
    body { font-family: 'Inter', sans-serif; }
    .kr-rise { animation: krRise .55s cubic-bezier(.22, 1, .36, 1) both; animation-delay: var(--d, 0s); }
    @keyframes krRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
</style>

<section class="max-w-7xl mx-auto px-4 pt-10 pb-32 lg:pb-14">
    
    
    <div class="kr-rise mb-8">
        <div class="mb-3 flex items-center gap-2">
            <span class="inline-block h-1.5 w-1.5 rotate-45" style="background-color: #c99a32;"></span>
            <span class="text-[10px] font-bold uppercase tracking-[.24em]" style="color: #a37b2a;">Belanja</span>
        </div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <div class="inline-flex p-1.5 bg-gray-100/80 rounded-2xl border border-gray-200 shadow-sm backdrop-blur-sm">
                <a href="<?php echo e(route('keranjang.index')); ?>" 
                   class="px-6 py-2.5 text-sm font-semibold text-gray-500 hover:text-ink rounded-xl transition-all flex items-center gap-2">
                    Keranjang Beli
                    <?php if(session()->has('cart') && count(session('cart')) > 0): ?>
                        <span class="bg-gray-300 text-ink text-[10px] px-2 py-0.5 rounded-full shadow-sm"><?php echo e(count(session('cart'))); ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo e(route('sewa.keranjang.index')); ?>" 
                   class="px-6 py-2.5 text-sm font-bold rounded-xl bg-white text-ink shadow-sm transition-all">
                    Keranjang Sewa
                </a>
            </div>
            
            <a href="<?php echo e(route('koleksi.index')); ?>" class="text-sm font-bold text-gold hover:text-ink transition-colors">
                + Tambah Produk Lain
            </a>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 font-semibold text-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(empty($keranjangSewa)): ?>
        <div class="kr-rise rounded-2xl border border-dashed bg-white px-6 py-20 text-center max-w-2xl mx-auto" style="border-color: #d9cfb8;">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full" style="background-color: #f8f1df; color: #c99a32;">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="font-display text-xl font-bold" style="color: #15233d;">Keranjang sewa masih kosong</h3>
            <p class="mx-auto mt-2 mb-7 max-w-sm text-sm leading-relaxed text-gray-500">Yuk, cari busana adat favoritmu dan mulai sewa dengan memilih tanggal.</p>
            <a href="<?php echo e(route('koleksi.index')); ?>" class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0" style="background-color: #c99a32;">Mulai Sewa <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg></a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <div class="lg:col-span-2 space-y-4">
                <?php $__currentLoopData = $keranjangSewa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="kr-rise bg-white p-5 rounded-2xl border border-line/60 shadow-sm flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                        <div class="flex gap-4 items-center">
                            <?php
                                $foto = $item['foto_produk'];
                                if (is_array($foto)) $foto = $foto[0] ?? null;
                            ?>
                            <?php if($foto): ?>
                                <img src="<?php echo e(asset('storage/' . $foto)); ?>" class="w-24 h-24 object-cover rounded-xl shadow-sm" alt="">
                            <?php else: ?>
                                <div class="w-24 h-24 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 text-xs">Foto</div>
                            <?php endif; ?>
                            
                            <div>
                                <h2 class="font-bold text-lg" style="color: #15233d;"><?php echo e($item['nama']); ?></h2>
                                <p class="text-sm text-gray-600 mt-0.5">Ukuran: <span class="font-semibold"><?php echo e($item['ukuran']); ?></span></p>
                                <p class="text-sm text-gray-600">Tanggal: <?php echo e(\Carbon\Carbon::parse($item['tanggal_ambil'])->format('d M')); ?> s/d <?php echo e(\Carbon\Carbon::parse($item['tanggal_kembali'])->format('d M Y')); ?> (<?php echo e($item['durasi']); ?> hari)</p>
                            </div>
                        </div>
                        
                        <div class="text-left sm:text-right w-full sm:w-auto mt-2 sm:mt-0 pt-3 sm:pt-0 border-t sm:border-0 border-dashed border-gray-200">
                            <p class="text-xs text-gray-500">Total Sewa</p>
                            <p class="text-lg font-bold tabular-nums" style="color: #15233d;">Rp <?php echo e(number_format($item['total_sewa'], 0, ',', '.')); ?></p>
                            <p class="text-xs text-rose-500 font-bold mt-1 bg-rose-50 inline-block px-2 py-1 rounded">DP: Rp <?php echo e(number_format($item['dp'], 0, ',', '.')); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="kr-rise lg:col-span-1" style="--d: .18s;">
                <div class="rounded-2xl border border-line/60 bg-white p-6 shadow-sm lg:sticky lg:top-24">
                    <h2 class="font-display mb-6 text-xl font-bold" style="color: #15233d;">Ringkasan Sewa</h2>
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total Pembayaran Sewa</span>
                        <span class="text-2xl font-bold tabular-nums" style="color: #c99a32;">Rp <?php echo e(number_format($totalKeseluruhan, 0, ',', '.')); ?></span>
                    </div>
                    <div class="my-5 border-t border-dashed border-gray-200"></div>
                    <a href="<?php echo e(route('sewa.checkout')); ?>" class="flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0" style="background-color: #c99a32;">
                        Lanjut ke Checkout Sewa 
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\alnay\resources\views/customer/sewa-keranjang.blade.php ENDPATH**/ ?>