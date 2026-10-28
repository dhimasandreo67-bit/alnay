<?php
    $notifCount = $notifCount ?? 0; // pass from controller/view-composer, e.g. pending actions count
?>

<header class="flex items-center justify-between gap-6 border-b border-black/5 bg-cream px-8 py-6">
    <div>
        <h1 class="font-display text-3xl font-semibold text-ink"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
        <p class="mt-1 text-sm text-ink-muted"><?php echo $__env->yieldContent('subtitle', \Illuminate\Support\Carbon::now()->translatedFormat('l, j F Y')); ?></p>
    </div>

    <div class="flex shrink-0 items-center gap-3">
        <label class="relative hidden md:block">
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted']); ?>
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
            <input type="search" placeholder="Cari pesanan, gaun, klien..."
                   class="w-72 rounded-xl border border-black/10 bg-white py-2.5 pl-10 pr-12 text-sm placeholder:text-ink-muted focus:border-gold focus:outline-none focus:ring-2 focus:ring-gold/30">
            <kbd class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-black/5 px-1.5 py-0.5 text-[11px] font-medium text-ink-muted">⌘K</kbd>
        </label>

        <a href="<?php echo e(Route::has('admin.notifications') ? route('admin.notifications') : '#'); ?>"
           class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-black/10 bg-white text-ink hover:bg-black/5">
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'bell','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'h-5 w-5']); ?>
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
            <?php if($notifCount > 0): ?>
                <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
            <?php endif; ?>
        </a>

        <form action="<?php echo e(route('admin.logout')); ?>" method="POST"
              onsubmit="return confirm('Yakin ingin keluar dari backoffice?')">
            <?php echo csrf_field(); ?>
            <button type="submit"
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-black/10 bg-white text-ink hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-colors"
                    title="Keluar">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </form>
    </div>
</header><?php /**PATH E:\Project_kecil\alnay\resources\views/partials/admin-topbar.blade.php ENDPATH**/ ?>