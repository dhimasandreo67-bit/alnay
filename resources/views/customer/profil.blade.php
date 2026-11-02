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

                    {{-- Notifikasi Toast (Global untuk halaman ini) --}}
                    <div x-data="{ show: false, message: '' }"
                        @notify.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3000)"
                        x-show="show" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 px-4 py-2 bg-ink text-cream text-xs font-medium rounded-lg shadow-lg"
                        style="display: none;">
                        <span x-text="message"></span>
                    </div>

                    {{-- Alert Error --}}
                    @if (session('error'))
                        <div
                            class="p-3 rounded-md bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2 mb-4">
                            <x-icon name="close" :size="16" />
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Alert Success --}}
                    @if (session('success'))
                        <div
                            class="p-3 rounded-md bg-green-50 border border-green-200 text-xs text-green-700 flex items-center gap-2">
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
                    <form method="POST" action="{{ route('akun.profil.update') }}" x-data="wilayahForm()"
                        enctype="multipart/form-data">
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
                                <label
                                    class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Nama
                                    Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                            </div>

                            {{-- Email --}}
                            <div class="group">
                                <label
                                    class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                            </div>

                            {{-- No HP --}}
                            <div class="md:col-span-2 group">
                                <label
                                    class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">No.
                                    Telepon</label>
                                <input type="tel" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                            </div>

                            {{-- Alamat --}}
                            <div class="md:col-span-2 group">
                                <label
                                    class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Alamat
                                    Lengkap</label>
                                <textarea name="alamat" rows="3"
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50 resize-none">{{ old('alamat', $user->alamat) }}</textarea>
                            </div>

                            {{-- Provinsi --}}
                            <div class="relative group">
                                <label
                                    class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Provinsi</label>
                                <select id="selectProvinsi" name="provinsi"
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50 appearance-none cursor-pointer">
                                    <option value="">Pilih Provinsi</option>
                                </select>
                                <div class="absolute right-3 top-[34px] pointer-events-none text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Kota --}}
                            <div class="relative group">
                                <label
                                    class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Kota
                                    / Kabupaten</label>
                                <select id="selectKota" name="kota_kabupaten" disabled
                                    @click="if(!document.getElementById('selectProvinsi').value) { $dispatch('notify', { message: 'Pilih provinsi terlebih dahulu.' }); document.getElementById('selectProvinsi').focus(); }"
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50 appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">Pilih Kota/Kabupaten</option>
                                </select>
                                <input type="hidden" name="kota_id" id="inputKotaId"
                                    value="{{ old('kota_id', $user->kota_id) }}">
                                <input type="hidden" name="kota_nama" id="inputKotaNama"
                                    value="{{ old('kota_nama', $user->kota_nama) }}">
                                <input type="hidden" name="provinsi_nama" id="inputProvinsiNama"
                                    value="{{ old('provinsi_nama', $user->provinsi_nama) }}">
                                <div class="absolute right-3 top-[34px] pointer-events-none text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Kecamatan --}}
                            <div class="relative group">
                                <label
                                    class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Kecamatan</label>
                                <input type="text" name="kecamatan" value="{{ old('kecamatan', $user->kecamatan) }}"
                                    placeholder="Ketik nama kecamatan..."
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                                <div class="absolute right-3 top-[34px] pointer-events-none text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Kode Pos --}}
                            <div class="group">
                                <label
                                    class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Kode
                                    Pos</label>
                                <input type="text" name="kode_pos" value="{{ old('kode_pos', $user->kode_pos) }}"
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
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

    {{-- ==========================================
        Script Alpine.js — Wilayah
        ========================================== --}}
    <script>
        function wilayahForm() {
            const saved = {
                provinsi: @json(old('provinsi', $user->provinsi)),
                kota: @json(old('kota_kabupaten', $user->kota_kabupaten)),
                kecamatan: @json(old('kecamatan', $user->kecamatan)),
            };

            const toList = (json) => Array.isArray(json) ? json : (Array.isArray(json?.data) ? json.data : []);
            const idOf = (item) => item.id ?? item.code ?? item.kode;
            const same = (a, b) => (a ?? '').toString().toLowerCase() === (b ?? '').toString().toLowerCase();

            async function getList(url) {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error(url + ' -> HTTP ' + res.status);
                return toList(await res.json());
            }

            function fill(select, items, placeholder, selectedName = null) {
                select.innerHTML = '';
                select.add(new Option(placeholder, ''));
                items.forEach(i => select.add(new Option(i.name, i.name, false, same(i.name, selectedName))));
                select.disabled = items.length === 0;
            }

            return {
                provinces: [],
                cities: [],
                districts: [],

                async init() {
                    const selProv = document.getElementById('selectProvinsi');
                    const selKota = document.getElementById('selectKota');

                    const loadCities = async (provName, selected = null) => {
                        this.cities = [];
                        this.districts = [];
                        fill(selKota, [], 'Pilih Kota/Kabupaten');

                        const prov = this.provinces.find(p => same(p.name, provName));
                        if (!prov) return;

                        try {
                            this.cities = await getList(`/api/wilayah/cities/${idOf(prov)}`);
                            fill(selKota, this.cities, 'Pilih Kota/Kabupaten', selected);
                        } catch (err) {
                            console.error('Gagal fetch kota:', err);
                        }
                    };

                    const loadDistricts = async (cityName, selected = null) => {
                        // Kecamatan sekarang input teks manual, tidak perlu load dari API.
                    };

                    try {
                        this.provinces = await getList('/api/wilayah/provinces');
                    } catch (err) {
                        console.error('Gagal fetch provinsi:', err);
                        return;
                    }
                    fill(selProv, this.provinces, 'Pilih Provinsi', saved.provinsi);

                    selProv.addEventListener('change', (e) => {
                        const selectedProv = this.provinces.find(p => same(p.name, e.target.value));
                        if (selectedProv) {
                            document.getElementById('inputProvinsiNama').value = selectedProv.name;
                        }
                        loadCities(e.target.value);
                    });
                    selKota.addEventListener('change', (e) => {
                        const selectedCity = this.cities.find(c => same(c.name, e.target.value));
                        if (selectedCity) {
                            document.getElementById('inputKotaId').value = selectedCity.id;
                            document.getElementById('inputKotaNama').value = selectedCity.name;
                        }
                    });
                    if (saved.provinsi) {
                        await loadCities(saved.provinsi, saved.kota);
                    }
                }
            }
        }

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
