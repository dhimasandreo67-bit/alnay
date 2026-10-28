<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title><?php echo $__env->yieldContent('title', 'Admin'); ?> — <?php echo e(config('alnay.brand.nama')); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-cream text-ink font-sans antialiased h-screen flex overflow-hidden">

    <?php echo $__env->make('partials.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        <?php echo $__env->make('partials.admin-topbar', [
            'pageTitle' => $__env->yieldContent('pageTitle'),
            'pageSubtitle' => $__env->yieldContent('pageSubtitle'),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="flex-1 p-4 md:p-6 lg:p-8">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html><?php /**PATH E:\Project_kecil\alnay\resources\views/admin/layouts/admin.blade.php ENDPATH**/ ?>