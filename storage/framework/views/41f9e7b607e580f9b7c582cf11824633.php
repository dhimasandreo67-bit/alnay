<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman dalam Pengembangan — <?php echo e(config('alnay.brand.nama')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-cream text-ink font-sans min-h-screen flex items-center justify-center p-6">
    <div class="text-center max-w-md">
        <p class="text-xs uppercase tracking-[0.2em] text-muted mb-3">
            <?php echo e(config('alnay.brand.nama')); ?>

        </p>
        <h1 class="font-display text-3xl mb-3">Halaman dalam Pengembangan</h1>
        <p class="text-muted mb-6">Route ini sudah terdaftar, tinggal menunggu implementasi.</p>
        <a href="<?php echo e(route('beranda')); ?>"
           class="inline-flex items-center justify-center px-6 py-3 bg-gold text-white rounded-full min-h-[44px] font-medium text-sm hover:bg-gold/90 transition-colors">
            Kembali ke Beranda
        </a>
    </div>
</body>
</html><?php /**PATH E:\Project_kecil\alnay\resources\views/placeholder.blade.php ENDPATH**/ ?>