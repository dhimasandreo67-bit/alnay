@php
    $tahun = date('Y');

    $koleksiAdat = [
        ['label' => 'Jas Beskap',     'href' => '#'],
        ['label' => 'Gown & Kebaya',  'href' => '#'],
        ['label' => 'Dress',          'href' => '#'],
        ['label' => 'Aksesoris',      'href' => '#'],
        ['label' => 'Tenun & Songket','href' => '#'],
        ['label' => 'Kain Batik',     'href' => '#'],
    ];

    $butuhBantuan = [
        ['label' => 'Cara Sewa',          'route' => 'cara-sewa'],
        ['label' => 'Panduan Fitting',    'href' => '#'],
        ['label' => 'Kebijakan Refund',   'href' => '#'],
        ['label' => 'Syarat & Ketentuan', 'href' => '#'],
    ];

    $layananInfo = [
        ['label' => 'Tentang Kami',   'route' => 'tentang'],
        ['label' => 'Testimoni',      'route' => 'testimoni'],
        ['label' => 'Kontak',         'route' => 'kontak'],
        ['label' => 'Event & Voting', 'route' => 'event'],
    ];

    $pembayaran = ['BCA', 'Mandiri', 'BNI', 'BRI', 'VISA', 'Mastercard'];
    $ekspedisi  = ['J&T', 'JNE', 'SiCepat', 'AnterAja', 'Pos Indonesia'];
@endphp

<footer class="bg-ink text-cream/80 mt-20">
    <div class="max-w-7xl mx-auto px-4 py-12 md:py-16">

        {{-- Grid utama --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12">

            {{-- Brand --}}
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-9 h-9 rounded-md bg-gold text-ink flex items-center justify-center font-display font-bold text-lg">A</span>
                    <span class="leading-tight">
                        <span class="block font-display font-bold text-cream text-lg tracking-tight">ALNAY.ID</span>
                        <span class="block text-[9px] uppercase tracking-[0.2em] text-cream/50">Fashion Adat Nusantara</span>
                    </span>
                </div>
                <p class="text-sm text-cream/60 leading-relaxed mb-4">
                    Menghadirkan koleksi fashion adat Nusantara untuk jual, sewa, dan custom.
                </p>
                <p class="text-xs text-cream/50 leading-relaxed">
                    {{ config('alnay.studio.alamat') }}
                </p>
            </div>

            {{-- Koleksi Adat --}}
            <div>
                <h3 class="font-display text-cream text-sm uppercase tracking-wider mb-4">Koleksi Adat</h3>
                <ul class="space-y-2.5 text-sm">
                    @foreach ($koleksiAdat as $item)
                        <li>
                            <a href="{{ $item['href'] }}" class="hover:text-gold transition-colors min-h-[32px] inline-flex items-center">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Butuh Bantuan --}}
            <div>
                <h3 class="font-display text-cream text-sm uppercase tracking-wider mb-4">Butuh Bantuan</h3>
                <ul class="space-y-2.5 text-sm">
                    @foreach ($butuhBantuan as $item)
                        <li>
                            @if (isset($item['route']))
                                <a href="{{ route($item['route']) }}" class="hover:text-gold transition-colors min-h-[32px] inline-flex items-center">
                                    {{ $item['label'] }}
                                </a>
                            @else
                                <a href="{{ $item['href'] }}" class="hover:text-gold transition-colors min-h-[32px] inline-flex items-center">
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Layanan & Informasi --}}
            <div>
                <h3 class="font-display text-cream text-sm uppercase tracking-wider mb-4">Layanan & Informasi</h3>
                <ul class="space-y-2.5 text-sm">
                    @foreach ($layananInfo as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="hover:text-gold transition-colors min-h-[32px] inline-flex items-center">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Kontak singkat --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-10 pt-8 border-t border-cream/10 text-sm">
            <div class="flex items-center gap-2 text-cream/70">
                <x-icon name="clock" :size="16" class="text-gold shrink-0" />
                <span>{{ config('alnay.studio.jam_buka') }}</span>
            </div>
            <div class="flex items-center gap-2 text-cream/70">
                <x-icon name="whatsapp" :size="16" class="text-gold shrink-0" />
                <span>CS: {{ config('alnay.kontak.wa_cs_display') }}</span>
            </div>
        </div>

        {{-- Metode Pembayaran & Ekspedisi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10 pt-8 border-t border-cream/10">

            <div>
                <h3 class="text-[10px] uppercase tracking-[0.2em] text-cream/50 mb-3">Metode Pembayaran</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($pembayaran as $m)
                        {{-- Placeholder blok abu-abu (prompt: belum ada file logo) --}}
                        <span class="px-3 py-1.5 rounded bg-cream/10 text-cream/60 text-xs font-medium">
                            {{ $m }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="text-[10px] uppercase tracking-[0.2em] text-cream/50 mb-3">Ekspedisi & Kurir Aman</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($ekspedisi as $e)
                        <span class="px-3 py-1.5 rounded bg-cream/10 text-cream/60 text-xs font-medium">
                            {{ $e }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Hak cipta --}}
        <div class="mt-10 pt-6 border-t border-cream/10 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-cream/50">
            <p>&copy; {{ $tahun }} {{ config('alnay.brand.nama') }} — Fashion Adat Nusantara. Hak cipta dilindungi.</p>
            <p class="flex items-center gap-3">
                <a href="#" class="hover:text-gold transition-colors">Kebijakan Privasi</a>
                <span class="text-cream/20">|</span>
                <a href="#" class="hover:text-gold transition-colors">Syarat & Ketentuan</a>
            </p>
        </div>

    </div>
</footer>