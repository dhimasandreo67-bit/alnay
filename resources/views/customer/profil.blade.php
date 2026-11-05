@extends('layouts.akun')

@section('title', 'Edit Profil — ' . config('alnay.brand.nama'))

@section('content')

    {{-- Tombol Kembali ke Dashboard Akun --}}
    <div class="max-w-4xl mx-auto px-4 pt-6">
        <a href="{{ route('akun.dashboard') }}"
            class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-amber-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-cream min-h-screen py-8 md:py-12">
        <div class="max-w-4xl mx-auto px-4">

            {{-- Card Utama dengan Animasi Masuk --}}
            <div class="bg-panel border border-line rounded-2xl shadow-sm overflow-hidden animate-fade-up">

                {{-- Header Card --}}
                <div class="px-6 py-5 border-b border-line bg-[#FBF9F5] flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gold/10 text-gold flex items-center justify-center">
                        <x-icon name="user" :size="20" />
                    </div>
                    <h1 class="font-display text-xl md:text-2xl text-ink">Edit Profil</h1>
                </div>

                <div class="p-6 md:p-8 space-y-8">

                    {{-- Alert Error --}}
                    @if (session('error'))
                        <div class="p-3 rounded-md bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2 mb-4">
                            <x-icon name="close" :size="16" />
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Alert Success --}}
                    @if (session('success'))
                        <div class="p-3 rounded-md bg-green-50 border border-green-200 text-xs text-green-700 flex items-center gap-2">
                            <x-icon name="check" :size="16" />
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Alert Error Validasi --}}
                    @if ($errors->any())
                        <div class="p-3 rounded-md bg-red-50 border border-red-200 text-xs text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{-- ==========================================
                        FORM PROFIL
                        ========================================== --}}
                    <form method="POST" action="{{ route('akun.profil.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Foto Profil --}}
                            <div class="md:col-span-2 flex items-center gap-5 mb-2">
                                <input type="file" id="foto_profil" name="foto_profil" class="hidden" accept="image/*"
                                    onchange="previewFoto(this)">

                                <div class="relative shrink-0">
                                    <div id="foto-preview"
                                        class="w-20 h-20 md:w-24 md:h-24 rounded-full overflow-hidden border-[3px] border-white bg-gradient-to-br from-gold to-[#a37b2a] shadow-md flex items-center justify-center">
                                        @if ($user->foto_profil)
                                            <img src="{{ \Illuminate\Support\Str::startsWith($user->foto_profil, ['http://', 'https://']) ? $user->foto_profil : asset('storage/' . $user->foto_profil) }}"
                                                alt="Foto Profil" referrerpolicy="no-referrer"
                                                class="w-full h-full object-cover">
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-10 h-10 md:w-12 md:h-12 text-white/80" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                            </svg>
                                        @endif
                                    </div>

                                    <label for="foto_profil"
                                        class="absolute bottom-0 right-0 w-7 h-7 md:w-8 md:h-8 rounded-full bg-white border border-line shadow-md flex items-center justify-center text-ink hover:text-gold hover:scale-110 transition-all duration-300 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
                                            </path>
                                            <circle cx="12" cy="13" r="4"></circle>
                                        </svg>
                                    </label>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold text-ink mb-0.5">Foto Profil</p>
                                    <p id="foto-name" class="text-[11px] text-muted">Klik ikon kamera untuk mengubah</p>
                                    <p class="text-[10px] text-muted mt-1">JPG, PNG, WEBP. Maks 2MB.</p>
                                </div>
                            </div>

                            {{-- Nama Lengkap --}}
                            <div class="group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                            </div>

                            {{-- Email --}}
                            <div class="group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                            </div>

                            {{-- No HP --}}
                            <div class="md:col-span-2 group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">No. Telepon / WhatsApp</label>
                                <input type="tel" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                            </div>

                            {{-- Info Alamat (Pengganti Form Alamat yang Dihapus) --}}
                            <div class="md:col-span-2 p-4 rounded-xl bg-blue-50 border border-blue-200 mt-2">
                                <div class="flex items-start gap-3">
                                    <x-icon name="map-pin" :size="20" class="text-blue-500 mt-0.5 shrink-0" />
                                    <div>
                                        <p class="text-sm font-bold text-blue-900">Butuh mengubah alamat pengiriman?</p>
                                        <p class="text-xs text-blue-800 mt-1 leading-relaxed">
                                            Alamat pengiriman kini dikelola secara terpisah agar lebih rapi dan akurat. Silakan buka menu 
                                            <a href="{{ route('akun.dashboard') }}#alamat" class="font-bold underline hover:text-blue-600">Alamat Saya</a> 
                                            di Dashboard Akun untuk menambah atau mengubah alamat Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit"
                                class="px-6 py-2.5 rounded-md bg-gold text-white text-sm font-bold hover:bg-gold/90 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 min-h-[44px]">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- Script Preview Foto --}}
    <script>
        function previewFoto(input) {
            const label = document.getElementById('foto-name');
            const file = input.files && input.files[0];
            if (!file) {
                label.innerText = 'Klik ikon kamera untuk mengubah';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran foto maksimal 2MB.');
                input.value = '';
                label.innerText = 'Klik ikon kamera untuk mengubah';
                return;
            }

            label.innerText = file.name;
            const wrap = document.getElementById('foto-preview');
            wrap.innerHTML = '';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = 'Foto Profil';
            img.className = 'w-full h-full object-cover';
            wrap.appendChild(img);
        }
    </script>

@endsection