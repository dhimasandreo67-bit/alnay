@extends('layouts.public')

@section('title', 'Tentang Kami - ALNAY.ID')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
    
    .font-display { font-family: 'Playfair Display', serif; }
    body { font-family: 'Inter', sans-serif; }

    /* Animasi fade-up saat scroll */
    .fade-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Gold divider */
    .gold-divider {
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #c99a32, #e6c877);
        margin: 0 auto;
    }

    /* Stat card hover */
    .stat-card {
        transition: all 0.4s ease;
    }
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(201, 154, 50, 0.15);
    }

    /* Value card */
    .value-card {
        transition: all 0.4s ease;
        border: 1px solid #f3f4f6;
    }
    .value-card:hover {
        border-color: #c99a32;
        transform: translateY(-4px);
    }
</style>

{{-- HERO SECTION --}}
<section class="relative h-[70vh] min-h-[500px] flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?auto=format&fit=crop&q=80&w=1600" 
             alt="Busana Adat" 
             class="w-full h-full object-cover">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(23,20,15,0.85) 0%, rgba(23,20,15,0.6) 100%);"></div>
    </div>

    <div class="relative z-10 text-center text-white px-4 max-w-4xl mx-auto">
        <span class="inline-block px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-[0.3em] mb-6" 
              style="background-color: rgba(201, 154, 50, 0.2); color: #e6c877; border: 1px solid #c99a32;">
            Tentang ALNAY.ID
        </span>
        <h1 class="font-display text-4xl md:text-6xl font-bold leading-tight mb-6">
            Merajut Warisan,<br>
            <span style="color: #c99a32;">Mengukir Keanggunan</span>
        </h1>
        <div class="gold-divider mb-6"></div>
        <p class="text-base md:text-lg max-w-2xl mx-auto leading-relaxed" style="color: #e5e7eb;">
            Platform busana adat Nusantara pertama yang menghadirkan mahakarya budaya 
            dengan standar kualitas <em>adibusana internasional</em>.
        </p>
    </div>
</section>

{{-- STORY SECTION --}}
<section class="py-20 md:py-28" style="background-color: #faf7f0;">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-16 items-center">
            <div class="fade-up relative">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                    <img src="{{ asset('images/tentang/cerita-kami.png') }}" 
     alt="Cerita ALNAY.ID" 
     class="w-full h-[500px] object-cover">
                </div>
                <div class="absolute -bottom-6 -right-6 w-32 h-32 rounded-2xl -z-10" style="background-color: #c99a32;"></div>
                <div class="absolute -top-6 -left-6 w-24 h-24 rounded-2xl border-2 -z-10" style="border-color: #c99a32;"></div>
            </div>

            <div class="fade-up">
                <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Cerita Kami</span>
                <h2 class="font-display text-3xl md:text-4xl font-bold mt-3 mb-6" style="color: #15233d;">
                    Dari Cinta pada Budaya,<br>Lahir Sebuah Mahakarya
                </h2>
                <div class="gold-divider mb-6" style="margin: 0 0 24px 0;"></div>
                
                <p class="text-sm leading-relaxed mb-4" style="color: #4b5563;">
                    <strong style="color: #15233d;">ALNAY.ID</strong> lahir dari kerinduan akan busana adat yang tidak hanya indah dipandang, 
                    tetapi juga <em>autentik dalam setiap jahitan</em>. Kami percaya bahwa setiap helai kain, 
                    setiap sulaman emas, dan setiap mahkota menyimpan cerita — cerita tentang identitas, 
                    warisan, dan momen sakral yang tak tergantikan.
                </p>
                
                <p class="text-sm leading-relaxed mb-4" style="color: #4b5563;">
                    Berdiri di <strong style="color: #15233d;">Bandar Lampung</strong>, kami berkolaborasi langsung dengan 
                    pengrajin tenun dari berbagai penjuru Nusantara — mulai dari songket Palembang, 
                    sutera Lombok, hingga batik tulis Solo. Setiap koleksi kami adalah hasil kurasi ketat 
                    yang menghormati keaslian budaya sekaligus memenuhi standar fashion modern.
                </p>

                <p class="text-sm leading-relaxed" style="color: #4b5563;">
                    Bagi kami, busana adat bukan sekadar pakaian. Ia adalah <strong style="color: #c99a32;">jembatan antara generasi</strong>, 
                    simbol keanggunan yang abadi, dan cara kita merayakan keindonesiaan di momen paling istimewa.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- VISI & MISI --}}
<section class="py-8 md:py-12" style="background-color: #17140f; color: white;">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Arah Kami</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-2">Visi & Misi</h2>
            <div class="gold-divider mt-6"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="fade-up p-8 md:p-10 rounded-2xl border" style="border-color: rgba(201, 154, 50, 0.3); background-color: rgba(201, 154, 50, 0.05);">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-6" style="background-color: #c99a32;">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-2xl font-bold mb-4">Visi</h3>
                <p class="text-sm leading-relaxed" style="color: #d1d5db;">
                    Menjadi <strong style="color: #c99a32;">platform busana adat Nusantara nomor satu</strong> yang 
                    dikenal dunia — tempat di mana warisan budaya bertemu dengan kemewahan modern.
                </p>
            </div>

            <div class="fade-up p-8 md:p-10 rounded-2xl border" style="border-color: rgba(201, 154, 50, 0.3); background-color: rgba(201, 154, 50, 0.05);">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-6" style="background-color: #c99a32;">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h3 class="font-display text-2xl font-bold mb-4">Misi</h3>
                <ul class="space-y-3 text-sm" style="color: #d1d5db;">
                    <li class="flex items-start gap-2"><span style="color: #c99a32;">✦</span><span>Melestarikan busana adat melalui kurasi berkualitas tinggi</span></li>
                    <li class="flex items-start gap-2"><span style="color: #c99a32;">✦</span><span>Memberdayakan pengrajin lokal di seluruh Nusantara</span></li>
                    <li class="flex items-start gap-2"><span style="color: #c99a32;">✦</span><span>Menyediakan pengalaman sewa & beli yang mudah dan terpercaya</span></li>
                    <li class="flex items-start gap-2"><span style="color: #c99a32;"></span><span>Mengedukasi generasi muda tentang kekayaan budaya Indonesia</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- NILAI-NILAI BRAND --}}
<section class="py-20 md:py-28" style="background-color: #faf7f0;">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16 fade-up">
            <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Yang Kami Pegang Teguh</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-3" style="color: #15233d;">Nilai-Nilai ALNAY.ID</h2>
            <div class="gold-divider mt-6"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="fade-up value-card bg-white p-8 rounded-2xl">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Keaslian Budaya</h3>
                <p class="text-xs leading-relaxed" style="color: #6b7280;">Setiap produk kami autentik — dibuat oleh pengrajin asli dengan teknik turun-temurun, bukan tirangan massal.</p>
            </div>

            <div class="fade-up value-card bg-white p-8 rounded-2xl">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Kualitas Adibusana</h3>
                <p class="text-xs leading-relaxed" style="color: #6b7280;">Standar <em>haute couture</em> dalam setiap jahitan. Fitting presisi, bahan premium, dan finishing yang sempurna.</p>
            </div>

            <div class="fade-up value-card bg-white p-8 rounded-2xl">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-5" style="background-color: #fbf6e9;">
                    <svg class="w-6 h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold mb-3" style="color: #15233d;">Pelayanan Tulus</h3>
                <p class="text-xs leading-relaxed" style="color: #6b7280;">Kami melayani dengan hati — dari konsultasi, fitting, hingga pengiriman. Kepuasan Anda adalah kebanggaan kami.</p>
            </div>
        </div>
    </div>
</section>

{{-- STATISTIK --}}
<section class="py-20" style="background-color: white;">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="fade-up stat-card text-center p-6 rounded-2xl bg-white border" style="border-color: #f3f4f6;">
                <div class="font-display text-4xl md:text-5xl font-bold mb-2" style="color: #c99a32;">5+</div>
                <p class="text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Tahun Berdiri</p>
            </div>
            <div class="fade-up stat-card text-center p-6 rounded-2xl bg-white border" style="border-color: #f3f4f6;">
                <div class="font-display text-4xl md:text-5xl font-bold mb-2" style="color: #c99a32;">1.800+</div>
                <p class="text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Acara Sukses</p>
            </div>
            <div class="fade-up stat-card text-center p-6 rounded-2xl bg-white border" style="border-color: #f3f4f6;">
                <div class="font-display text-4xl md:text-5xl font-bold mb-2" style="color: #c99a32;">34</div>
                <p class="text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Provinsi Terjangkau</p>
            </div>
            <div class="fade-up stat-card text-center p-6 rounded-2xl bg-white border" style="border-color: #f3f4f6;">
                <div class="font-display text-4xl md:text-5xl font-bold mb-2" style="color: #c99a32;">4.97</div>
                <p class="text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Rating Kepuasan</p>
            </div>
        </div>
    </div>
</section>

{{-- TIM / ATELIER --}}
<section class="py-20 md:py-28" style="background-color: #faf7f0;">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-16 items-center">
            <div class="fade-up">
                <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Tim Kami</span>
                <h2 class="font-display text-3xl md:text-4xl font-bold mt-3 mb-6" style="color: #15233d;">
                    Dibalik Setiap Mahakarya
                </h2>
                <div class="gold-divider mb-6" style="margin: 0 0 24px 0;"></div>
                
                <p class="text-sm leading-relaxed mb-4" style="color: #4b5563;">
                    ALNAY.ID digerakkan oleh tim yang terdiri dari <strong style="color: #15233d;">desainer berbakat</strong>, 
                    <strong style="color: #15233d;">pengrajin senior</strong>, dan <strong style="color: #15233d;">kurator budaya</strong> yang berdedikasi penuh.
                </p>

                <div class="space-y-4 mt-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #c99a32; color: white; font-weight: bold;">01</div>
                        <div>
                            <h4 class="font-bold text-sm" style="color: #15233d;">Desainer Adibusana</h4>
                            <p class="text-xs mt-1" style="color: #6b7280;">Menghadirkan sentuhan modern tanpa menghilangkan esensi budaya.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #c99a32; color: white; font-weight: bold;">02</div>
                        <div>
                            <h4 class="font-bold text-sm" style="color: #15233d;">Pengrajin Tenun & Songket</h4>
                            <p class="text-xs mt-1" style="color: #6b7280;">Mitra kami dari Palembang, Lombok, Solo, dan berbagai daerah penghasil kain warisan.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #c99a32; color: white; font-weight: bold;">03</div>
                        <div>
                            <h4 class="font-bold text-sm" style="color: #15233d;">Kurator Budaya</h4>
                            <p class="text-xs mt-1" style="color: #6b7280;">Memastikan setiap koleksi autentik dan sesuai dengan pakem adat masing-masing daerah.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fade-up relative">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                    <img src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&q=80&w=800" 
                         alt="Tim ALNAY" 
                         class="w-full h-[500px] object-cover">
                </div>
                <div class="absolute -top-6 -right-6 w-32 h-32 rounded-2xl -z-10" style="background-color: #c99a32;"></div>
                <div class="absolute -bottom-6 -left-6 w-24 h-24 rounded-2xl border-2 -z-10" style="border-color: #c99a32;"></div>
            </div>
        </div>
    </div>
</section>

{{-- CTA SECTION --}}
<section class="py-20 md:py-28 relative overflow-hidden" style="background-color: #17140f;">
    <div class="absolute top-0 left-0 w-64 h-64 rounded-full opacity-10" style="background-color: #c99a32; filter: blur(80px);"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 rounded-full opacity-10" style="background-color: #c99a32; filter: blur(80px);"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 text-center fade-up">
        <span class="text-[10px] font-bold uppercase tracking-[0.3em]" style="color: #c99a32;">Bergabung Bersama Kami</span>
        <h2 class="font-display text-3xl md:text-5xl font-bold mt-3 mb-6 text-white">
            Jadikan Momen Anda<br>
            <span style="color: #c99a32;">Abadi & Berbudaya</span>
        </h2>
        <div class="gold-divider mb-8"></div>
        <p class="text-base mb-10 max-w-2xl mx-auto" style="color: #d1d5db;">
            Jelajahi koleksi kami atau konsultasikan kebutuhan busana adat Anda. 
            Tim ALNAY.ID siap membantu mewujudkan penampilan impian Anda.
        </p>
        
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('koleksi.index') }}" 
               class="inline-flex items-center gap-2 px-8 py-4 rounded-lg text-sm font-bold uppercase tracking-wider text-white transition-all hover:scale-105" 
               style="background-color: #c99a32;">
                Jelajahi Koleksi
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
            <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank"
               class="inline-flex items-center gap-2 px-8 py-4 rounded-lg text-sm font-bold uppercase tracking-wider text-white border transition-all hover:bg-white/10" 
               style="border-color: rgba(255,255,255,0.3);">
                💬 Konsultasi Gratis
            </a>
        </div>
    </div>
</section>

{{-- JAVASCRIPT UNTUK ANIMASI FADE-UP --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fadeElements = document.querySelectorAll('.fade-up');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        fadeElements.forEach(el => observer.observe(el));
    });
</script>

@endsection