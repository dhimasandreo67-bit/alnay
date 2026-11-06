@extends('layouts.public')

@section('title', 'Tentang Kami - ALNAY.ID')

@section('content')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

        :root {
            /* Motif etnik Lampung (sama dengan header, beranda & footer) */
            --tapis: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='44'%3E%3Cg fill='none' stroke='%23c99a32' stroke-width='1.1'%3E%3Cpath d='M0 1.5H64M0 42.5H64'/%3E%3Cpath d='M32 11 L45 22 L32 33 L19 22Z'/%3E%3Cpath d='M32 16 L39.5 22 L32 28 L24.5 22Z' fill='%23c99a32' fill-opacity='.55'/%3E%3Cpath d='M0 1.5 L8 13 L16 1.5M48 1.5 L56 13 L64 1.5M0 42.5 L8 31 L16 42.5M48 42.5 L56 31 L64 42.5'/%3E%3C/g%3E%3Ccircle cx='8' cy='22' r='1.8' fill='%23c99a32'/%3E%3Ccircle cx='56' cy='22' r='1.8' fill='%23c99a32'/%3E%3C/svg%3E");
            --tumpal: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='12'%3E%3Cpath d='M14 1.5 L23 6 L14 10.5 L5 6Z' fill='none' stroke='%23c99a32' stroke-width='1.2'/%3E%3Cpath d='M14 4.2 L17.4 6 L14 7.8 L10.6 6Z' fill='%23c99a32'/%3E%3C/svg%3E");
            --ketupat: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='8'%3E%3Cpath d='M7 .8 L12.5 4 L7 7.2 L1.5 4Z' fill='%23c99a32'/%3E%3C/svg%3E");
        }

        .font-display { font-family: 'Playfair Display', serif; }
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #17140f; }
        ::-webkit-scrollbar-thumb { background: #c99a32; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #e6c877; }

        .fade-up {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .hero-img {
            animation: subtle-zoom 20s infinite alternate linear;
        }
        @keyframes subtle-zoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        /* Efek Parallax HANYA aktif di Desktop/Laptop */
        @media (min-width: 768px) {
            .parallax-img {
                transition: transform 0.1s ease-out;
                will-change: transform;
            }
        }

        /* Pembatas judul: deretan belah ketupat */
        .gold-divider {
            width: 56px;
            height: 8px;
            margin: 0 auto;
            background: var(--ketupat) repeat-x left center / 14px 8px;
        }

        /* Eyebrow dengan ornamen ketupat */
        .eyebrow-k { display: inline-flex; align-items: center; gap: 10px; }
        .eyebrow-k::before,
        .eyebrow-k.eyebrow-c::after {
            content: '';
            width: 6px; height: 6px;
            background: #c99a32;
            transform: rotate(45deg);
            border-radius: 1px;
            flex-shrink: 0;
        }

        /* HERO: tekstur tapis + pita tumpal di dasar */
        .k-hero::before {
            content: '';
            position: absolute; inset: 0; z-index: 1;
            background: var(--tapis) repeat center / 64px 44px;
            opacity: .2;
            -webkit-mask-image: radial-gradient(ellipse at 50% 45%, #000 0%, transparent 72%);
            mask-image: radial-gradient(ellipse at 50% 45%, #000 0%, transparent 72%);
            pointer-events: none;
        }
        .k-hero::after,
        .k-cta::before {
            content: '';
            position: absolute; left: 0; right: 0; z-index: 2;
            height: 14px;
            border-top: 1px solid rgba(201, 154, 50, .7);
            border-bottom: 1px solid #c99a32;
            background: var(--tumpal) repeat-x center / 28px 12px, #17140f;
            pointer-events: none;
        }
        .k-hero::after { bottom: 0; }
        .k-cta::before { top: 0; }

        /* Bingkai foto: pita tumpal di tepi bawah gambar */
        .tapis-frame::after {
            content: '';
            position: absolute; left: 0; right: 0; bottom: 0;
            height: 14px;
            border-top: 1px solid rgba(201, 154, 50, .8);
            background: var(--tumpal) repeat-x center / 28px 12px, #17140f;
            pointer-events: none;
        }

        /* Section gelap & krem: watermark tapis sangat tipis */
        .k-dark, .k-soft { position: relative; overflow: hidden; }
        .k-dark::before, .k-soft::before {
            content: '';
            position: absolute; inset: 0;
            background: var(--tapis) repeat center / 64px 44px;
            pointer-events: none;
        }
        .k-dark::before {
            opacity: .1;
            -webkit-mask-image: radial-gradient(ellipse at 0% 0%, #000, transparent 55%);
            mask-image: radial-gradient(ellipse at 0% 0%, #000, transparent 55%);
        }
        .k-soft::before {
            opacity: .08;
            -webkit-mask-image: radial-gradient(ellipse at 100% 0%, #000, transparent 55%);
            mask-image: radial-gradient(ellipse at 100% 0%, #000, transparent 55%);
        }
        .k-dark > div, .k-soft > div { position: relative; z-index: 1; }

        .stat-card, .value-card {
            transition: all 0.4s ease;
            border: 1px solid rgba(243, 244, 246, 0.8);
        }
        .stat-card:hover, .value-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(201, 154, 50, 0.25);
            border-color: rgba(201, 154, 50, 0.4);
        }

        .team-item { transition: all 0.3s ease; }
        .team-item:hover { transform: translateX(10px); }
        .team-item:hover .team-number {
            box-shadow: 0 0 15px rgba(201, 154, 50, 0.6);
            transform: scale(1.1);
        }

        .btn-premium {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(201, 154, 50, 0.4);
        }
        .btn-premium::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.3) 50%, rgba(255, 255, 255, 0) 100%);
            transform: skewX(-25deg);
            transition: all 0.7s ease;
        }
        .btn-premium:hover::before { left: 150%; }

        .float-animation { animation: float 6s ease-in-out infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .fade-up { opacity: 1; transform: none; transition: none; }
            .hero-img, .float-animation { animation: none; }
        }
    </style>

    {{-- HERO SECTION --}}
    <section class="k-hero relative h-[50vh] md:h-[70vh] min-h-[350px] md:min-h-[500px] flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?auto=format&fit=crop&q=80&w=1600"
                alt="Busana Adat" class="w-full h-full object-cover hero-img">
            <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(23,20,15,0.9) 0%, rgba(23,20,15,0.6) 100%);"></div>
        </div>
        <div class="relative z-10 text-center text-white px-5 max-w-4xl mx-auto mt-0 md:mt-10">
            <span class="inline-block px-3 py-1.5 rounded-full text-[9px] md:text-[10px] font-bold uppercase tracking-[0.2em] md:tracking-[0.3em] mb-4 md:mb-6 backdrop-blur-sm"
                style="background-color: rgba(201, 154, 50, 0.2); color: #e6c877; border: 1px solid rgba(201, 154, 50, 0.5);">
                Tentang ALNAY.ID
            </span>
            <h1 class="font-display text-3xl sm:text-4xl md:text-6xl lg:text-7xl font-bold leading-tight mb-4 md:mb-6 drop-shadow-2xl">
                Merajut Warisan,<br>
                <span style="color: #c99a32;">Mengukir Keanggunan</span>
            </h1>
            <div class="gold-divider mb-4 md:mb-6"></div>
            <p class="text-sm md:text-lg max-w-2xl mx-auto leading-relaxed font-light" style="color: #f3f4f6;">
                Platform busana adat Nusantara pertama yang menghadirkan mahakarya budaya dengan standar kualitas <em>adibusana internasional</em>.
            </p>
        </div>
    </section>

    {{-- STORY SECTION --}}
    <section class="py-12 md:py-24" style="background-color: #faf7f0;">
        <div class="max-w-6xl mx-auto px-5 md:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16 items-center">

                <div class="fade-up relative group order-1 md:order-1">
                    {{-- Tinggi tetap (h-[300px] md:h-[500px]) dan overflow-hidden --}}
                    <div class="tapis-frame relative w-full h-[300px] md:h-[500px] rounded-2xl overflow-hidden shadow-xl md:shadow-2xl bg-gray-100">
                        <img src="{{ asset('images/tentang/cerita-kami.png') }}" alt="Cerita ALNAY.ID"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    <div class="absolute -bottom-4 -right-4 md:-bottom-6 md:-right-6 w-20 h-20 md:w-32 md:h-32 rounded-2xl -z-10 float-animation" style="background-color: #c99a32;"></div>
                    <div class="absolute -top-4 -left-4 md:-top-6 md:-left-6 w-16 h-16 md:w-24 md:h-24 rounded-2xl border-2 -z-10 float-animation" style="border-color: #c99a32; animation-delay: 1s;"></div>
                </div>

                <div class="fade-up order-2 md:order-2" style="transition-delay: 200ms;">
                    <span class="eyebrow-k text-[9px] md:text-[10px] font-bold uppercase tracking-[0.2em] md:tracking-[0.3em]" style="color: #c99a32;">Cerita Kami</span>
                    <h2 class="font-display text-2xl md:text-4xl font-bold mt-2 md:mt-3 mb-4 md:mb-6 leading-snug" style="color: #15233d;">
                        Dari Cinta pada Budaya,<br>Lahir Sebuah Mahakarya
                    </h2>
                    <div class="gold-divider mb-4 md:mb-6" style="margin: 0 0 16px 0;"></div>
                    <p class="text-xs md:text-sm leading-relaxed mb-3 md:mb-4" style="color: #4b5563;">
                        <strong style="color: #15233d;">ALNAY.ID</strong> lahir dari kerinduan akan busana adat yang tidak hanya indah dipandang, tetapi juga <em>autentik dalam setiap jahitan</em>. Kami percaya bahwa setiap helai kain, setiap sulaman emas, dan setiap mahkota menyimpan cerita.
                    </p>
                    <p class="text-xs md:text-sm leading-relaxed mb-3 md:mb-4" style="color: #4b5563;">
                        Berdiri di <strong style="color: #15233d;">Bandar Lampung</strong>, kami berkolaborasi langsung dengan pengrajin tenun dari berbagai penjuru Nusantara — mulai dari songket Palembang, sutera Lombok, hingga batik tulis Solo.
                    </p>
                    <p class="text-xs md:text-sm leading-relaxed" style="color: #4b5563;">
                        Bagi kami, busana adat bukan sekadar pakaian. Ia adalah <strong style="color: #c99a32;">jembatan antara generasi</strong>, simbol keanggunan yang abadi, dan cara kita merayakan keindonesiaan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- VISI & MISI --}}
    <section class="k-dark py-12 md:py-24" style="background-color: #17140f; color: white;">
        <div class="max-w-6xl mx-auto px-5 md:px-6">
            <div class="text-center mb-8 md:mb-16 fade-up">
                <span class="eyebrow-k eyebrow-c text-[9px] md:text-[10px] font-bold uppercase tracking-[0.2em] md:tracking-[0.3em]" style="color: #c99a32;">Arah Kami</span>
                <h2 class="font-display text-2xl md:text-4xl font-bold mt-2">Visi & Misi</h2>
                <div class="gold-divider mt-4 md:mt-6"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-8">
                <div class="fade-up p-6 md:p-10 rounded-2xl border backdrop-blur-sm transition-all hover:bg-white/5" style="border-color: rgba(201, 154, 50, 0.3); background-color: rgba(201, 154, 50, 0.05);">
                    <div class="w-10 h-10 md:w-14 md:h-14 rounded-full flex items-center justify-center mb-4 md:mb-6 shadow-lg shadow-amber-500/20" style="background-color: #c99a32;">
                        <svg class="w-5 h-5 md:w-7 md:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl md:text-2xl font-bold mb-2 md:mb-4">Visi</h3>
                    <p class="text-xs md:text-sm leading-relaxed" style="color: #d1d5db;">
                        Menjadi <strong style="color: #c99a32;">platform busana adat Nusantara nomor satu</strong> yang dikenal dunia — tempat di mana warisan budaya bertemu dengan kemewahan modern.
                    </p>
                </div>
                <div class="fade-up p-6 md:p-10 rounded-2xl border backdrop-blur-sm transition-all hover:bg-white/5" style="border-color: rgba(201, 154, 50, 0.3); background-color: rgba(201, 154, 50, 0.05); transition-delay: 150ms;">
                    <div class="w-10 h-10 md:w-14 md:h-14 rounded-full flex items-center justify-center mb-4 md:mb-6 shadow-lg shadow-amber-500/20" style="background-color: #c99a32;">
                        <svg class="w-5 h-5 md:w-7 md:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl md:text-2xl font-bold mb-2 md:mb-4">Misi</h3>
                    <ul class="space-y-3 md:space-y-4 text-xs md:text-sm" style="color: #d1d5db;">
                        <li class="flex items-start gap-2 md:gap-3">
                            <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span>Melestarikan busana adat melalui kurasi berkualitas tinggi</span>
                        </li>
                        <li class="flex items-start gap-2 md:gap-3">
                            <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span>Memberdayakan pengrajin lokal di seluruh Nusantara</span>
                        </li>
                        <li class="flex items-start gap-2 md:gap-3">
                            <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span>Menyediakan pengalaman sewa & beli yang mudah dan terpercaya</span>
                        </li>
                        <li class="flex items-start gap-2 md:gap-3">
                            <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0 mt-0.5" style="color: #c99a32;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span>Mengedukasi generasi muda tentang kekayaan budaya Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- NILAI-NILAI BRAND --}}
    <section class="k-soft py-12 md:py-24" style="background-color: #faf7f0;">
        <div class="max-w-6xl mx-auto px-5 md:px-6">
            <div class="text-center mb-8 md:mb-16 fade-up">
                <span class="eyebrow-k eyebrow-c text-[9px] md:text-[10px] font-bold uppercase tracking-[0.2em] md:tracking-[0.3em]" style="color: #c99a32;">Yang Kami Pegang Teguh</span>
                <h2 class="font-display text-2xl md:text-4xl font-bold mt-2 md:mt-3" style="color: #15233d;">Nilai-Nilai ALNAY.ID</h2>
                <div class="gold-divider mt-4 md:mt-6"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8">
                <div class="fade-up value-card bg-white p-5 md:p-8 rounded-2xl group">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-lg flex items-center justify-center mb-3 md:mb-5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3" style="background-color: #fbf6e9;">
                        <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="font-display text-base md:text-lg font-bold mb-2 md:mb-3" style="color: #15233d;">Keaslian Budaya</h3>
                    <p class="text-[11px] md:text-xs leading-relaxed" style="color: #6b7280;">Setiap produk kami autentik — dibuat oleh pengrajin asli dengan teknik turun-temurun, bukan tirangan massal.</p>
                </div>
                <div class="fade-up value-card bg-white p-5 md:p-8 rounded-2xl group" style="transition-delay: 100ms;">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-lg flex items-center justify-center mb-3 md:mb-5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3" style="background-color: #fbf6e9;">
                        <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <h3 class="font-display text-base md:text-lg font-bold mb-2 md:mb-3" style="color: #15233d;">Kualitas Adibusana</h3>
                    <p class="text-[11px] md:text-xs leading-relaxed" style="color: #6b7280;">Standar <em>haute couture</em> dalam setiap jahitan. Fitting presisi, bahan premium, dan finishing yang sempurna.</p>
                </div>
                <div class="fade-up value-card bg-white p-5 md:p-8 rounded-2xl group" style="transition-delay: 200ms;">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-lg flex items-center justify-center mb-3 md:mb-5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3" style="background-color: #fbf6e9;">
                        <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </div>
                    <h3 class="font-display text-base md:text-lg font-bold mb-2 md:mb-3" style="color: #15233d;">Pelayanan Tulus</h3>
                    <p class="text-[11px] md:text-xs leading-relaxed" style="color: #6b7280;">Kami melayani dengan hati — dari konsultasi, fitting, hingga pengiriman. Kepuasan Anda adalah kebanggaan kami.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- STATISTIK --}}
    <section class="py-10 md:py-16" style="background-color: white;">
        <div class="max-w-6xl mx-auto px-5 md:px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
                <div class="fade-up stat-card text-center p-4 md:p-6 rounded-2xl bg-white border bg-gradient-to-b from-white to-gray-50/50">
                    <div class="font-display text-3xl md:text-5xl font-bold mb-1 md:mb-2" style="color: #c99a32;">5+</div>
                    <p class="text-[9px] md:text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Tahun Berdiri</p>
                </div>
                <div class="fade-up stat-card text-center p-4 md:p-6 rounded-2xl bg-white border bg-gradient-to-b from-white to-gray-50/50" style="transition-delay: 100ms;">
                    <div class="font-display text-3xl md:text-5xl font-bold mb-1 md:mb-2" style="color: #c99a32;">1.800+</div>
                    <p class="text-[9px] md:text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Acara Sukses</p>
                </div>
                <div class="fade-up stat-card text-center p-4 md:p-6 rounded-2xl bg-white border bg-gradient-to-b from-white to-gray-50/50" style="transition-delay: 200ms;">
                    <div class="font-display text-3xl md:text-5xl font-bold mb-1 md:mb-2" style="color: #c99a32;">34</div>
                    <p class="text-[9px] md:text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Provinsi Terjangkau</p>
                </div>
                <div class="fade-up stat-card text-center p-4 md:p-6 rounded-2xl bg-white border bg-gradient-to-b from-white to-gray-50/50" style="transition-delay: 300ms;">
                    <div class="font-display text-3xl md:text-5xl font-bold mb-1 md:mb-2" style="color: #c99a32;">4.97</div>
                    <p class="text-[9px] md:text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Rating Kepuasan</p>
                </div>
            </div>
        </div>
    </section>

    {{-- TIM / ATELIER --}}
    <section class="py-12 md:py-24" style="background-color: #faf7f0;">
        <div class="max-w-6xl mx-auto px-5 md:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16 items-center">
                <div class="fade-up order-2 md:order-1">
                    <span class="eyebrow-k text-[9px] md:text-[10px] font-bold uppercase tracking-[0.2em] md:tracking-[0.3em]" style="color: #c99a32;">Tim Kami</span>
                    <h2 class="font-display text-2xl md:text-4xl font-bold mt-2 md:mt-3 mb-4 md:mb-6" style="color: #15233d;">Dibalik Setiap Mahakarya</h2>
                    <div class="gold-divider mb-4 md:mb-6" style="margin: 0 0 16px 0;"></div>
                    <p class="text-xs md:text-sm leading-relaxed mb-6" style="color: #4b5563;">
                        ALNAY.ID digerakkan oleh tim yang terdiri dari <strong style="color: #15233d;">desainer berbakat</strong>, <strong style="color: #15233d;">pengrajin senior</strong>, dan <strong style="color: #15233d;">kurator budaya</strong> yang berdedikasi penuh.
                    </p>
                    <div class="space-y-4 md:space-y-6 mt-6 md:mt-8">
                        <div class="team-item flex items-start gap-3 md:gap-4">
                            <div class="team-number w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-md shadow-amber-500/20 transition-all duration-300 text-xs md:text-base" style="background-color: #c99a32; color: white; font-weight: bold;">01</div>
                            <div>
                                <h4 class="font-bold text-xs md:text-sm" style="color: #15233d;">Desainer Adibusana</h4>
                                <p class="text-[11px] md:text-xs mt-0.5 md:mt-1 leading-relaxed" style="color: #6b7280;">Menghadirkan sentuhan modern tanpa menghilangkan esensi budaya.</p>
                            </div>
                        </div>
                        <div class="team-item flex items-start gap-3 md:gap-4">
                            <div class="team-number w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-md shadow-amber-500/20 transition-all duration-300 text-xs md:text-base" style="background-color: #c99a32; color: white; font-weight: bold;">02</div>
                            <div>
                                <h4 class="font-bold text-xs md:text-sm" style="color: #15233d;">Pengrajin Tenun & Songket</h4>
                                <p class="text-[11px] md:text-xs mt-0.5 md:mt-1 leading-relaxed" style="color: #6b7280;">Mitra kami dari Palembang, Lombok, Solo, dan berbagai daerah penghasil kain warisan.</p>
                            </div>
                        </div>
                        <div class="team-item flex items-start gap-3 md:gap-4">
                            <div class="team-number w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-md shadow-amber-500/20 transition-all duration-300 text-xs md:text-base" style="background-color: #c99a32; color: white; font-weight: bold;">03</div>
                            <div>
                                <h4 class="font-bold text-xs md:text-sm" style="color: #15233d;">Kurator Budaya</h4>
                                <p class="text-[11px] md:text-xs mt-0.5 md:mt-1 leading-relaxed" style="color: #6b7280;">Memastikan setiap koleksi autentik dan sesuai dengan pakem adat masing-masing daerah.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fade-up relative group order-1 md:order-2" style="transition-delay: 200ms;">
                    {{-- Tinggi tetap (h-[300px] md:h-[500px]) dan overflow-hidden --}}
                    <div class="tapis-frame relative w-full h-[300px] md:h-[500px] rounded-2xl overflow-hidden shadow-xl md:shadow-2xl bg-gray-100">
                        <img src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&q=80&w=800"
                            alt="Tim ALNAY"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    <div class="absolute -top-4 -right-4 md:-top-6 md:-right-6 w-20 h-20 md:w-32 md:h-32 rounded-2xl -z-10 float-animation" style="background-color: #c99a32;"></div>
                    <div class="absolute -bottom-4 -left-4 md:-bottom-6 md:-left-6 w-16 h-16 md:w-24 md:h-24 rounded-2xl border-2 -z-10 float-animation" style="border-color: #c99a32; animation-delay: 1.5s;"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA SECTION --}}
    <section class="k-cta py-16 md:py-28 relative overflow-hidden" style="background-color: #17140f;">
        <div class="absolute top-0 left-0 w-64 h-64 rounded-full opacity-10 animate-pulse"
            style="background-color: #c99a32; filter: blur(80px);"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 rounded-full opacity-10 animate-pulse"
            style="background-color: #c99a32; filter: blur(80px); animation-delay: 1s;"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-5 md:px-6 text-center fade-up">
            <span class="eyebrow-k eyebrow-c text-[9px] md:text-[10px] font-bold uppercase tracking-[0.2em] md:tracking-[0.3em]" style="color: #c99a32;">Bergabung Bersama Kami</span>
            <h2 class="font-display text-2xl md:text-5xl font-bold mt-2 md:mt-3 mb-4 md:mb-6 text-white leading-tight">
                Jadikan Momen Anda<br>
                <span style="color: #c99a32;">Abadi & Berbudaya</span>
            </h2>
            <div class="gold-divider mb-6 md:mb-8"></div>
            <p class="text-xs md:text-base mb-8 md:mb-10 max-w-2xl mx-auto font-light" style="color: #d1d5db;">
                Jelajahi koleksi kami atau konsultasikan kebutuhan busana adat Anda.
                Tim ALNAY.ID siap membantu mewujudkan penampilan impian Anda.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-3 md:gap-4">
                <a href="{{ route('koleksi.index') }}"
                    class="btn-premium inline-flex items-center justify-center gap-2 px-6 md:px-8 py-3 md:py-4 rounded-lg text-xs md:text-sm font-bold uppercase tracking-wider text-white"
                    style="background-color: #c99a32;">
                    Jelajahi Koleksi
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>

                {{-- Ikon WhatsApp --}}
                <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}" target="_blank"
                    class="btn-premium inline-flex items-center justify-center gap-2 px-6 md:px-8 py-3 md:py-4 rounded-lg text-xs md:text-sm font-bold uppercase tracking-wider text-white border transition-all hover:bg-white/10"
                    style="border-color: rgba(255,255,255,0.2);">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    Konsultasi Gratis
                </a>
            </div>
        </div>
    </section>

    {{-- JAVASCRIPT UNTUK ANIMASI FADE-UP & PARALLAX --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Animasi Fade-up (Muncul saat di-scroll)
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

            // 2. Efek Parallax sederhana saat scroll (Hanya berjalan jika lebar layar >= 768px)
            window.addEventListener('scroll', () => {
                if (window.innerWidth >= 768) {
                    const parallaxImages = document.querySelectorAll('.parallax-img');
                    parallaxImages.forEach(img => {
                        const speed = 0.08;
                        const yPos = -(window.scrollY * speed);
                        const rect = img.getBoundingClientRect();
                        if (rect.top < window.innerHeight && rect.bottom > 0) {
                            img.style.transform = `translateY(${yPos}px) scale(1.05)`;
                        }
                    });
                }
            });
        });
    </script>

@endsection