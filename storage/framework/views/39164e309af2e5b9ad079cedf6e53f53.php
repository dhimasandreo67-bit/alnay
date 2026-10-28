<?php
    $tahun = date('Y');

    $koleksiAdat = [
        ['label' => 'Jas Beskap Pria',       'href' => '#'],
        ['label' => 'Gown Kebaya Pengantin', 'href' => '#'],
        ['label' => 'Set Baju Pasangan',     'href' => '#'],
        ['label' => 'Mahkota & Suntiang',    'href' => '#'],
        ['label' => 'Tenun Songket Asli',    'href' => '#'],
    ];

    $butuhBantuan = [
        ['label' => 'Senin - Jumat: 08:00 - 20:00 WIB', 'href' => '#', 'isText' => true],
        ['label' => 'Sabtu - Minggu: 09:00 - 18:00 WIB', 'href' => '#', 'isText' => true],
    ];

    $layananInfo = [
        ['label' => 'Cara & Syarat Sewa Busana',    'href' => '#'],
        ['label' => 'Aturan Deposit & Asuransi',    'href' => '#'],
        ['label' => 'Jadwal Fitting Studio',        'href' => '#'],
        ['label' => 'Panduan Ukuran Badan Online',  'href' => '#'],
        ['label' => 'Pemesanan Custom Bespoke',     'href' => '#'],
    ];

    $pembayaran = ['BCA', 'MANDIRI', 'BNI', 'BRI', 'QRIS', 'VISA / MASTER'];
    $ekspedisi  = ['JNE YES', 'GoSend Instant', 'GrabExpress', 'Paxel Kargo'];
?>

<footer class="bg-[#0a0a0a] text-white/80 mt-20">
    <div class="max-w-7xl mx-auto px-6 md:px-10 py-12 md:py-16">

        
        <div class="grid grid-cols-2 md:grid-cols-12 gap-8 md:gap-12">

            
            <div class="col-span-2 md:col-span-4">
                <div class="flex items-center gap-3 mb-5">
                    <img src="<?php echo e(asset('images/home/alnaylogo.png')); ?>" alt="ALNAY.ID" class="w-10 h-10 rounded-md border border-[#c99a32] bg-[#fbf6e9] object-contain p-1">
                    <span class="leading-tight">
                        <span class="block font-display font-bold text-white text-xl tracking-wider">ALNAY.ID</span>
                        <span class="block text-[9px] uppercase tracking-[0.25em] text-[#c99a32]">Jas & Gown Berbudaya</span>
                    </span>
                </div>
                <p class="text-sm text-white/60 leading-relaxed mb-6 max-w-sm">
                    Platform penyewaan dan bespoke atelier busana adat Nusantara nomor satu di Indonesia. Menghadirkan mahakarya budaya dengan standar kualitas tailoring adibusana internasional.
                </p>

                
                <div class="flex items-center gap-3">
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-[#c99a32] flex items-center justify-center transition-colors" aria-label="Instagram">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-[#c99a32] flex items-center justify-center transition-colors" aria-label="WhatsApp">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-[#c99a32] flex items-center justify-center transition-colors" aria-label="TikTok">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-[#c99a32] flex items-center justify-center transition-colors" aria-label="Facebook">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-[#c99a32] flex items-center justify-center transition-colors" aria-label="YouTube">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-[#25D366]/20 hover:bg-[#25D366] flex items-center justify-center transition-colors" aria-label="Line">
                        <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M19.365 9.863c.349 0 .63.227.63.509 0 .282-.281.509-.63.509-.348 0-.63-.227-.63-.509 0-.282.282-.509.63-.509m-12.731 0c.348 0 .63.227.63.509 0 .282-.282.509-.63.509-.349 0-.63-.227-.63-.509 0-.282.281-.509.63-.509m6.365 0c.348 0 .63.227.63.509 0 .282-.282.509-.63.509-.349 0-.63-.227-.63-.509 0-.282.281-.509.63-.509m6.365 0c.348 0 .63.227.63.509 0 .282-.282.509-.63.509-.349 0-.63-.227-.63-.509 0-.282.281-.509.63-.509M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.303 2.246.464 3.443.464 6.627 0 12-4.975 12-11.111C24 4.974 18.627 0 12 0m0 19.63c-1.095 0-2.153-.153-3.152-.437l-2.218 1.22.594-2.174C4.734 16.42 3.11 13.93 3.11 11.111 3.11 6.627 7.14 3 12 3s8.89 3.627 8.89 8.111-3.99 8.519-8.89 8.519"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-[#25D366]/20 hover:bg-[#25D366] flex items-center justify-center transition-colors" aria-label="Shopee">
                        <svg class="w-4 h-4 text-[#EE4D2D]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    </a>
                </div>
            </div>

            
            <div class="md:col-span-3">
                <h3 class="font-display text-white text-sm uppercase tracking-wider mb-4">Koleksi Adat</h3>
                <ul class="space-y-2.5 text-sm">
                    <?php $__currentLoopData = $koleksiAdat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="<?php echo e($item['href']); ?>" class="text-white/70 hover:text-[#c99a32] transition-colors">
                                <?php echo e($item['label']); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            
            <div class="md:col-span-2">
                <h3 class="font-display text-white text-sm uppercase tracking-wider mb-4">Butuh Bantuan?</h3>
                <ul class="space-y-2.5 text-sm">
                    <?php $__currentLoopData = $butuhBantuan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <?php if(isset($item['isText']) && $item['isText']): ?>
                                <span class="text-white/70"><?php echo e($item['label']); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($item['href']); ?>" class="text-white/70 hover:text-[#c99a32] transition-colors">
                                    <?php echo e($item['label']); ?>

                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                
                <a href="https://wa.me/<?php echo e(config('alnay.kontak.wa_cs')); ?>" target="_blank" 
                   class="inline-flex items-center gap-2 mt-5 px-4 py-2 rounded-md border text-xs font-semibold transition-colors hover:bg-[#c99a32]/10"
                   style="border-color: #c99a32; color: #c99a32;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Chat WhatsApp CS
                </a>

                <p class="text-xs text-white/50 mt-5 leading-relaxed">
                    Jl. Mahakarya Adat No. 18, Bandung Wetan, Jawa Barat
                </p>
            </div>

            
            <div class="md:col-span-3">
                <h3 class="font-display text-white text-sm uppercase tracking-wider mb-4">Layanan & Informasi</h3>
                <ul class="space-y-2.5 text-sm">
                    <?php $__currentLoopData = $layananInfo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="<?php echo e($item['href']); ?>" class="text-white/70 hover:text-[#c99a32] transition-colors">
                                <?php echo e($item['label']); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>

        
        <div class="border-t border-white/10 mt-12"></div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">

            <div>
                <h3 class="text-[10px] uppercase tracking-[0.2em] text-white/50 mb-3 font-semibold">Metode Pembayaran Resmi</h3>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $pembayaran; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="px-3 py-1.5 rounded bg-white/10 text-white/70 text-xs font-medium border border-white/5">
                            <?php echo e($m); ?>

                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="md:text-right">
                <h3 class="text-[10px] uppercase tracking-[0.2em] text-white/50 mb-3 font-semibold">Ekspedisi Khusus & Kurir Aman</h3>
                <div class="flex flex-wrap gap-2 md:justify-end">
                    <?php $__currentLoopData = $ekspedisi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="px-3 py-1.5 rounded bg-white/10 text-white/70 text-xs font-medium border border-white/5">
                            <?php echo e($e); ?>

                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="mt-8 pt-6 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-white/50">
            <p>&copy; <?php echo e($tahun); ?> AL-NAY.ID (Jas & Gown Berbudaya). All Rights Reserved.</p>
            <p class="flex items-center gap-3">
                <a href="#" class="hover:text-[#c99a32] transition-colors">Kebijakan Privasi</a>
                <span class="text-white/20">•</span>
                <a href="#" class="hover:text-[#c99a32] transition-colors">Syarat & Ketentuan Sewa</a>
                <span class="text-white/20">•</span>
                <a href="#" class="hover:text-[#c99a32] transition-colors">Keanggunan Adat Nusantara</a>
            </p>
        </div>

    </div>
</footer><?php /**PATH D:\alnay\resources\views/partials/footer.blade.php ENDPATH**/ ?>