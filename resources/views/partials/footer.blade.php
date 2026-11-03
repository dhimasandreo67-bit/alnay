@php
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
@endphp

<footer class="bg-[#0a0a0a] text-white/80 mt-16 md:mt-24 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-5 sm:px-6 md:px-10 pt-12 pb-8 md:py-16">

        {{-- GRID UTAMA --}}
        <div class="grid grid-cols-2 md:grid-cols-12 gap-x-6 gap-y-10 md:gap-8">

            {{-- 1. BRAND & SOSMED (Full width di HP, 4 kolom di Desktop) --}}
            <div class="col-span-2 md:col-span-4">
                <div class="flex items-center gap-3 mb-5">
                    <img src="{{ asset('images/home/alnaylogo.png') }}" alt="ALNAY.ID" class="w-10 h-10 rounded-md border border-[#c99a32] bg-[#fbf6e9] object-contain p-1">
                    <span class="leading-tight">
                        <span class="block font-display font-bold text-white text-xl tracking-wider">ALNAY.ID</span>
                        <span class="block text-[9px] uppercase tracking-[0.25em] text-[#c99a32]">Jas & Gown Berbudaya</span>
                    </span>
                </div>
                <p class="text-xs md:text-sm text-white/60 leading-relaxed mb-6 max-w-sm">
                    Platform penyewaan dan bespoke atelier busana adat Nusantara nomor satu di Indonesia. Menghadirkan mahakarya budaya dengan standar kualitas tailoring adibusana internasional.
                </p>

                {{-- 5 Social Media Icons (Lengkap) --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    {{-- Instagram --}}
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#c99a32] border border-white/10 flex items-center justify-center transition-all" aria-label="Instagram">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    {{-- WhatsApp --}}
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#c99a32] border border-white/10 flex items-center justify-center transition-all" aria-label="WhatsApp">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    </a>
                    {{-- TikTok --}}
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#c99a32] border border-white/10 flex items-center justify-center transition-all" aria-label="TikTok">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>
                    </a>
                    {{-- Facebook --}}
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#c99a32] border border-white/10 flex items-center justify-center transition-all" aria-label="Facebook">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    {{-- YouTube --}}
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#c99a32] border border-white/10 flex items-center justify-center transition-all" aria-label="YouTube">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                </div>
            </div>

            {{-- 2. KOLEKSI ADAT (Kiri di HP, 3 kolom di Desktop) --}}
            <div class="col-span-1 md:col-span-3">
                <h3 class="font-display text-white text-xs uppercase tracking-wider mb-4">Koleksi Adat</h3>
                <ul class="space-y-2.5 text-[11px] md:text-sm">
                    @foreach ($koleksiAdat as $item)
                        <li>
                            <a href="{{ $item['href'] }}" class="text-white/60 hover:text-[#c99a32] transition-colors leading-relaxed block">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- 3. LAYANAN INFO (Kanan di HP, 3 kolom di Desktop) --}}
            <div class="col-span-1 md:col-span-3">
                <h3 class="font-display text-white text-xs uppercase tracking-wider mb-4">Layanan & Informasi</h3>
                <ul class="space-y-2.5 text-[11px] md:text-sm">
                    @foreach ($layananInfo as $item)
                        <li>
                            <a href="{{ $item['href'] }}" class="text-white/60 hover:text-[#c99a32] transition-colors leading-relaxed block">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- 4. BUTUH BANTUAN (Full width di HP, 2 kolom di Desktop) --}}
            <div class="col-span-2 md:col-span-2">
                <h3 class="font-display text-white text-xs uppercase tracking-wider mb-4">Butuh Bantuan?</h3>
                
                {{-- Teks Jam Operasional --}}
                <ul class="space-y-1 text-[11px] md:text-sm mb-4">
                    @foreach ($butuhBantuan as $item)
                        <li class="text-white/60">{{ $item['label'] }}</li>
                    @endforeach
                </ul>

                {{-- Tombol Chat WA --}}
                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank" 
                   class="inline-flex items-center justify-center w-full gap-2 px-4 py-2.5 rounded-lg border text-xs font-bold transition-all hover:bg-[#c99a32] hover:text-black shadow-lg shadow-black/50"
                   style="border-color: rgba(201, 154, 50, 0.5); color: #c99a32;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Chat WhatsApp
                </a>

                <p class="text-[11px] text-white/50 mt-4 leading-relaxed">
                    Jl. Mahakarya Adat No. 18, Bandung Wetan, Jawa Barat
                </p>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-white/10 mt-10 md:mt-16 mb-6"></div>

        {{-- HAK CIPTA --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-[10px] md:text-xs text-white/40 text-center md:text-left">
            <p>&copy; {{ $tahun }} AL-NAY.ID. All Rights Reserved.</p>
            <div class="flex flex-wrap justify-center gap-x-5 gap-y-2">
                <a href="#" class="hover:text-[#c99a32] transition-colors">Kebijakan Privasi</a>
                <a href="#" class="hover:text-[#c99a32] transition-colors">Syarat & Ketentuan Sewa</a>
                <a href="#" class="hover:text-[#c99a32] transition-colors">Keanggunan Adat</a>
            </div>
        </div>

    </div>
</footer>