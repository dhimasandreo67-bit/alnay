@extends('layouts.public')

@section('title', config('alnay.brand.nama') . ' — ' . config('alnay.brand.tagline'))

@section('content')

{{-- ============================================================
     1. HERO
     ============================================================ --}}
<section class="bg-ink text-cream relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 py-12 md:py-20 grid md:grid-cols-2 gap-10 items-center">

        {{-- Kiri: teks --}}
        <div>
            <p class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.25em] text-gold-soft mb-6">
                <x-icon name="sparkle" :size="14" />
                Jas & Gown Berbudaya — Elegan dalam Setiap Momen
            </p>

            <h1 class="font-display text-3xl md:text-5xl lg:text-6xl leading-[1.1] text-cream mb-6">
                Jas & Gown Berbudaya,<br>
                Dicipta untuk Momen<br>
                Abadi Anda.
            </h1>

            <p class="text-cream/70 text-sm md:text-base leading-relaxed mb-8 max-w-lg">
                Temukan koleksi fashion adat Nusantara dengan sentuhan elegan dan modern.
                Tersedia untuk pembelian maupun penyewaan untuk melengkapi berbagai momen istimewa Anda.
            </p>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('koleksi.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 transition-colors min-h-[44px]">
                    <x-icon name="arrow-right" :size="16" />
                    Jelajahi Koleksi Produk
                </a>
                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-gold-soft text-gold-soft text-sm font-semibold hover:bg-gold-soft/10 transition-colors min-h-[44px]">
                    <x-icon name="whatsapp" :size="16" />
                    Konsultasi WhatsApp
                </a>
            </div>
        </div>

        {{-- Kanan: kolase 3 foto --}}
        <div class="grid grid-cols-2 gap-3 md:gap-4">
            {{-- Foto utama --}}
            <div class="col-span-2 aspect-[16/10] rounded-2xl bg-cream/5 border border-cream/10 flex items-center justify-center text-cream/30 text-xs uppercase tracking-widest">
                Foto Hero 1
            </div>
            <div class="aspect-square rounded-2xl bg-cream/5 border border-cream/10 flex items-center justify-center text-cream/30 text-xs uppercase tracking-widest">
                Foto Hero 2
            </div>
            <div class="aspect-square rounded-2xl bg-cream/5 border border-cream/10 flex items-center justify-center text-cream/30 text-xs uppercase tracking-widest">
                Foto Hero 3
            </div>
        </div>

    </div>
</section>

{{-- ============================================================
     2. BARIS KATEGORI
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="grid grid-cols-3 md:grid-cols-6 gap-4 md:gap-6">
        @foreach ($kategori as $kat)
            <a href="#" {{-- TODO: route asli → koleksi.kategori --}}
               class="flex flex-col items-center text-center group">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-cream-dim border border-line flex items-center justify-center mb-3 group-hover:border-gold transition-colors overflow-hidden">
                    <span class="text-[10px] uppercase tracking-widest text-muted">Foto</span>
                </div>
                <span class="text-xs md:text-sm font-medium text-ink group-hover:text-gold transition-colors leading-tight">
                    {{ $kat->nama_kategori }}
                </span>
                <span class="text-[10px] text-muted mt-0.5">{{ $kat->kode_kategori }}</span>
            </a>
        @endforeach
    </div>
</section>

{{-- ============================================================
     3. PRODUK TERBARU
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <x-section-heading
        title="Produk Terbaru"
        subtitle="Rilisan busana adat paling baru"
        linkText="Lihat Semua"
        :linkHref="route('koleksi.index')" />

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        @foreach ($produkTerbaru as $p)
            <x-product-card :produk="$p" />
        @endforeach
    </div>
</section>

{{-- ============================================================
     4. PRODUK TERPOPULER
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <x-section-heading
        title="Produk Terpopuler"
        subtitle="Favorit pilihan pelanggan dari beberapa waktu terakhir"
        linkText="Lihat Semua"
        :linkHref="route('koleksi.index')" />

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        @foreach ($produkTerpopuler as $i => $p)
            <x-product-card :produk="$p" />
        @endforeach
    </div>
</section>

{{-- ============================================================
     5. BANNER EVENT
     ⚠️ Sistem event TERPISAH. Hanya tautan keluar.
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="grid md:grid-cols-2 gap-4 md:gap-6">

        {{-- Card 1: sedang berlangsung --}}
        <div class="bg-ink text-cream rounded-2xl p-6 md:p-8 flex flex-col justify-between min-h-[260px]">
            <div>
                <p class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.25em] text-gold-soft mb-4">
                    <span class="w-2 h-2 rounded-full bg-gold animate-pulse"></span>
                    Sedang Berlangsung
                </p>
                <h3 class="font-display text-2xl md:text-3xl leading-tight mb-2">
                    Event Hebat ALNAY.ID
                </h3>
                <p class="text-cream/60 text-sm mb-6">
                    Ayo vote busana adat favorit Anda di event yang sedang berlangsung.
                </p>
            </div>
            <a href="{{ route('event') }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 self-start px-6 py-3 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 transition-colors min-h-[44px]">
                Lihat & Vote
                <x-icon name="arrow-right" :size="16" />
            </a>
        </div>

        {{-- Card 2: akan datang --}}
        <div class="bg-ink text-cream rounded-2xl p-6 md:p-8 flex flex-col justify-between min-h-[260px]">
            <div>
                <p class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.25em] text-gold-soft mb-4">
                    <span class="w-2 h-2 rounded-full bg-gold-soft"></span>
                    Akan Datang
                </p>
                <h3 class="font-display text-2xl md:text-3xl leading-tight mb-2">
                    Event Hebat ALNAY.ID
                </h3>
                <p class="text-cream/60 text-sm mb-6">
                    Ayo Daftar — Jadilah bagian dari parade busana adat Nusantara.
                </p>
            </div>
            <a href="{{ route('event') }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 self-start px-6 py-3 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 transition-colors min-h-[44px]">
                Daftar Sekarang
                <x-icon name="arrow-right" :size="16" />
            </a>
        </div>

    </div>
    <p class="text-xs text-muted mt-3 text-center">
        Sistem Event & Voting berada di situs terpisah.
    </p>
</section>

{{-- ============================================================
     6. OPSI FLEKSIBEL: SEWA ATAU BELI
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="text-center max-w-2xl mx-auto mb-10">
        <p class="text-[10px] uppercase tracking-[0.25em] text-gold mb-3">Transparansi Mutu</p>
        <h2 class="font-display text-2xl md:text-3xl text-ink mb-3">Opsi Fleksibel: Sewa atau Beli</h2>
        <p class="text-sm text-muted">
            Keputusan komitmen tinggi, pricing press, dan garansi busana tanpa biaya terselubung.
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-4 md:gap-6">

        {{-- Sewa --}}
        <div class="bg-cream-dim border border-line rounded-2xl p-6 md:p-8 relative">
            <span class="absolute top-4 right-4 px-3 py-1 rounded-full bg-gold text-white text-[10px] font-bold uppercase tracking-wider">
                Paling Diminati
            </span>
            <h3 class="font-display text-xl md:text-2xl text-ink mb-3">Layanan Sewa Busana</h3>
            <p class="text-sm text-muted mb-6">
                Solusi praktis untuk tampil maksimal. Reserve, ambil, dan kembalikan sesuai jadwal.
            </p>
            <ul class="space-y-3 text-sm text-ink mb-8">
                <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold mt-0.5 shrink-0" /> Durasi Sewa Fleksibel — pilih tanggal ambil & tanggal kembali.</li>
                <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold mt-0.5 shrink-0" /> Gratis Atelier — pengukuran akhir, pencucian, dan pengemasan.</li>
                <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold mt-0.5 shrink-0" /> Termasuk Laundry — busana kembali bersih siap dipakai.</li>
                <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold mt-0.5 shrink-0" /> Bersih & Higienis — busana diperiksa dan disterilkan.</li>
            </ul>
            <a href="#" {{-- TODO: route asli → cara-sewa --}}
               class="block text-center px-6 py-3 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold/90 transition-colors min-h-[44px]">
                Baca Panduan Sewa
            </a>
        </div>

        {{-- Beli --}}
        <div class="bg-ink text-cream rounded-2xl p-6 md:p-8">
            <h3 class="font-display text-xl md:text-2xl mb-3">Beli & Custom Bespoke</h3>
            <p class="text-sm text-cream/60 mb-6">
                Miliki busana yang benar-benar milik Anda dengan opsi custom penuh.
            </p>
            <ul class="space-y-3 text-sm text-cream/90 mb-8">
                <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold-soft mt-0.5 shrink-0" /> Pola Tailoring Personal — disesuaikan dengan postur tubuh.</li>
                <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold-soft mt-0.5 shrink-0" /> Pilihan Bahan & Sentuhan Artistik — sesuai karakter Anda.</li>
                <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold-soft mt-0.5 shrink-0" /> Garansi Fitting Ulang — revisi hingga nyaman.</li>
                <li class="flex items-start gap-2"><x-icon name="check" :size="16" class="text-gold-soft mt-0.5 shrink-0" /> Produk Milik Selamanya — tidak ada kewajiban kembali.</li>
            </ul>
            <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}"
               target="_blank" rel="noopener"
               class="block text-center px-6 py-3 rounded-full border border-gold-soft text-gold-soft text-sm font-semibold hover:bg-gold-soft/10 transition-colors min-h-[44px]">
                Konsultasi Custom
            </a>
        </div>

    </div>
</section>

{{-- ============================================================
     7. OFFLINE ATELIER
     ⚠️ Lokasi fitting HANYA SATU. Tidak ada pilihan cabang.
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="bg-panel border border-line rounded-2xl p-6 md:p-10 grid md:grid-cols-2 gap-8 items-center">

        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-gold mb-3">Galeri & Ruang Fitting</p>
            <h2 class="font-display text-2xl md:text-3xl text-ink mb-4">Offline Atelier & Studio ALNAY.ID</h2>
            <p class="text-sm text-muted leading-relaxed mb-6">
                Temui tim kami secara langsung untuk fitting personal, konsultasi desain, dan pengalaman memilih bahan secara tatap muka. Booking terlebih dahulu untuk memastikan slot tersedia.
            </p>

            <div class="flex flex-wrap gap-3">
                <a href="#"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-ink text-cream text-sm font-semibold hover:bg-ink/90 transition-colors min-h-[44px]">
                    <x-icon name="location" :size="16" />
                    Info Lokasi & Booking Jadwal
                </a>
                <a href="{{ route('cara-sewa') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-ink/20 text-ink text-sm font-semibold hover:bg-cream transition-colors min-h-[44px]">
                    Panduan Sewa Online
                </a>
            </div>
        </div>

        <div class="aspect-video rounded-xl bg-cream-dim border border-line flex items-center justify-center text-muted text-xs uppercase tracking-widest">
            Foto Atelier
        </div>

    </div>
</section>

{{-- ============================================================
     8. TESTIMONI
     ============================================================ --}}
<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="flex items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-gold mb-2">Kisah Bahagia Bersama ALNAY.ID</p>
            <h2 class="font-display text-2xl md:text-3xl text-ink">Testimoni Pengantin & Klien</h2>
        </div>
        {{-- Placeholder — angka klaim belum final --}}
        <p class="hidden md:block text-xs text-muted text-right leading-relaxed">
            Kepuasan bintang 4.97 dari 1.800+ Acara<br>
            <span class="text-[10px] italic">(placeholder — angka belum final)</span>
        </p>
    </div>

    <div class="grid md:grid-cols-3 gap-4 md:gap-6">
        @foreach ($testimoni as $t)
            <blockquote class="bg-panel border border-line rounded-2xl p-6 flex flex-col">
                <div class="flex text-gold mb-3">
                    @for ($i = 1; $i <= 5; $i++)
                        <x-icon name="{{ $i <= $t->rating ? 'star' : 'star-outline' }}" :size="14" />
                    @endfor
                </div>
                <p class="text-sm text-ink/80 leading-relaxed mb-6 flex-1">
                    "{{ $t->ulasan }}"
                </p>
                <footer class="pt-4 border-t border-line">
                    <p class="text-sm font-semibold text-ink">{{ $t->user->name ?? 'Pelanggan' }}</p>
                    <p class="text-xs text-muted">{{ $t->produk->nama ?? 'Produk ALNAY' }}</p>
                </footer>
            </blockquote>
        @endforeach
    </div>
</section>

@endsection