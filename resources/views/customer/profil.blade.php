@extends('layouts.akun')

    @section('title', 'Edit Profil — ' . config('alnay.brand.nama'))

    @section('content')

        {{-- Tombol Kembali ke Dashboard Akun --}}
    <div class="max-w-4xl mx-auto px-4 pt-6">
        <a href="{{ route('akun.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-amber-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
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
                        x-show="show"
                        x-transition:enter="transition ease-out duration-300"
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

                    {{-- Alert Error --}}
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
                    <form method="POST" action="{{ route('akun.profil.update') }}"
                        x-data="wilayahForm()"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Foto Profil --}}
                        <div class="md:col-span-2 flex items-center gap-5 mb-2">
                            {{-- Input File Tersembunyi (Letakkan paling atas di dalam form) --}}
                            <input type="file" id="foto_profil" name="foto_profil" class="hidden" accept="image/*" 
                                onchange="previewFoto(this)">

                            {{-- Wrapper Foto --}}
                            <div class="relative shrink-0">
                                <div id="foto-preview" class="w-20 h-20 md:w-24 md:h-24 rounded-full overflow-hidden border-[3px] border-white bg-gradient-to-br from-gold to-[#a37b2a] shadow-md flex items-center justify-center">
                                    @if ($user->foto_profil)
                                        <img src="{{ \Illuminate\Support\Str::startsWith($user->foto_profil, ['http://', 'https://']) ? $user->foto_profil : asset('storage/' . $user->foto_profil) }}" alt="Foto Profil" referrerpolicy="no-referrer" class="w-full h-full object-cover">
                                    @else
                                        {{-- Ikon Siluet User --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 md:w-12 md:h-12 text-white/80" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                    @endif
                                </div>

                                {{-- Tombol Kamera (Gunakan Label for) --}}
                                <label for="foto_profil" 
                                    class="absolute bottom-0 right-0 w-7 h-7 md:w-8 md:h-8 rounded-full bg-white border border-line shadow-md flex items-center justify-center text-ink hover:text-gold hover:scale-110 transition-all duration-300 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </label>
                            </div>

                            {{-- Teks Info --}}
                            <div>
                                <p class="text-xs font-semibold text-ink mb-0.5">Foto Profil</p>
                                <p id="foto-name" class="text-[11px] text-muted">Klik ikon kamera untuk mengubah</p>
                                <p class="text-[10px] text-muted mt-1">JPG, PNG, WEBP. Maks 2MB.</p>
                            </div>
                        </div>


                        {{-- NAMA (TAMBAHKAN INI) --}}
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
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">No. Telepon</label>
                                <input type="tel" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required
                                    class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                            </div>

                            {{-- Alamat --}}
                            <div class="md:col-span-2 group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Alamat Lengkap</label>
                                <textarea name="alamat" rows="3"
                                        class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50 resize-none">{{ old('alamat', $user->alamat) }}</textarea>
                            </div>

                            {{-- Provinsi --}}
                            <div class="relative group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Provinsi</label>
                                <select id="selectProvinsi" name="provinsi"
                                        class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50 appearance-none cursor-pointer">
                                    <option value="">Pilih Provinsi</option>
                                </select>
                                <div class="absolute right-3 top-[34px] pointer-events-none text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </div>

                            {{-- Kota --}}
                            <div class="relative group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Kota / Kabupaten</label>
                                <select id="selectKota" name="kota_kabupaten" disabled
                                        @click="if(!document.getElementById('selectProvinsi').value) { $dispatch('notify', { message: 'Pilih provinsi terlebih dahulu.' }); document.getElementById('selectProvinsi').focus(); }"
                                        class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50 appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">Pilih Kota/Kabupaten</option>
                                </select>
                                <div class="absolute right-3 top-[34px] pointer-events-none text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </div>

                            {{-- Kecamatan --}}
                            <div class="relative group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Kecamatan</label>
                                <select id="selectKecamatan" name="kecamatan" disabled
                                        @click="if(!document.getElementById('selectKota').value) { $dispatch('notify', { message: 'Pilih kota/kabupaten terlebih dahulu.' }); document.getElementById('selectKota').focus(); }"
                                        class="w-full px-4 py-2.5 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50 appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                                <div class="absolute right-3 top-[34px] pointer-events-none text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </div>

                            {{-- Kode Pos --}}
                            <div class="group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Kode Pos</label>
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

                    <hr class="border-line">

                    {{-- ==========================================
                        FORM PASSWORD
                        ========================================== --}}
                    <div x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
                        <h2 class="font-display text-lg text-ink mb-4">Ubah Password</h2>

                        <form method="POST" action="{{ route('akun.profil.password') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-4 group">
                                <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Password Saat Ini</label>
                                <div class="relative">
                                    <input :type="showCurrent ? 'text' : 'password'" name="current_password" required
                                        class="w-full px-4 py-2.5 pr-10 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                                    <button type="button" @click="showCurrent = !showCurrent"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-gold transition-colors">
                                        <svg x-show="!showCurrent" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg x-show="showCurrent" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="group">
                                    <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Password Baru</label>
                                    <div class="relative">
                                        <input :type="showNew ? 'text' : 'password'" name="password" required
                                            class="w-full px-4 py-2.5 pr-10 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                                        <button type="button" @click="showNew = !showNew"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-gold transition-colors">
                                            <svg x-show="!showNew" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg x-show="showNew" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="group">
                                    <label class="block text-xs font-semibold text-ink mb-1.5 transition-colors group-focus-within:text-gold">Konfirmasi Password</label>
                                    <div class="relative">
                                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                                            class="w-full px-4 py-2.5 pr-10 rounded-md border border-line bg-cream text-sm text-ink focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 hover:border-gold/50">
                                        <button type="button" @click="showConfirm = !showConfirm"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-gold transition-colors">
                                            <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg x-show="showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="px-6 py-2.5 rounded-md bg-ink text-white text-sm font-bold hover:bg-ink/90 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 min-h-[44px]">
                                    Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
        Script Alpine.js — Wilayah
        ========================================== --}}
    <script>
function wilayahForm() {
    // Data tersimpan (atau old() bila validasi gagal) supaya dropdown terisi ulang
    const saved = {
        provinsi:  @json(old('provinsi', $user->provinsi)),
        kota:      @json(old('kota_kabupaten', $user->kota_kabupaten)),
        kecamatan: @json(old('kecamatan', $user->kecamatan)),
    };

    // API boleh mengembalikan array langsung atau { data: [...] }
    const toList = (json) => Array.isArray(json) ? json : (Array.isArray(json?.data) ? json.data : []);
    // ID wilayah: pakai "id", kalau tidak ada pakai "code"/"kode"
    const idOf = (item) => item.id ?? item.code ?? item.kode;
    // Banding nama tanpa peduli huruf besar/kecil
    const same = (a, b) => (a ?? '').toString().toLowerCase() === (b ?? '').toString().toLowerCase();

    async function getList(url) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
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
            const selKec  = document.getElementById('selectKecamatan');

            const loadCities = async (provName, selected = null) => {
                this.cities = [];
                this.districts = [];
                fill(selKota, [], 'Pilih Kota/Kabupaten');
                fill(selKec, [], 'Pilih Kecamatan');

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
                this.districts = [];
                fill(selKec, [], 'Pilih Kecamatan');

                const city = this.cities.find(c => same(c.name, cityName));
                if (!city) return;

                try {
                    this.districts = await getList(`/api/wilayah/districts/${idOf(city)}`);
                    fill(selKec, this.districts, 'Pilih Kecamatan', selected);
                } catch (err) {
                    console.error('Gagal fetch kecamatan:', err);
                }
            };

            // 1. Provinsi
            try {
                this.provinces = await getList('/api/wilayah/provinces');
            } catch (err) {
                console.error('Gagal fetch provinsi:', err);
                return;
            }
            fill(selProv, this.provinces, 'Pilih Provinsi', saved.provinsi);

            // 2. Listener (didaftarkan sekali saja)
            selProv.addEventListener('change', (e) => loadCities(e.target.value));
            selKota.addEventListener('change', (e) => loadDistricts(e.target.value));

            // 3. Isi ulang Kota & Kecamatan dari data tersimpan
            if (saved.provinsi) {
                await loadCities(saved.provinsi, saved.kota);
                if (saved.kota) {
                    await loadDistricts(saved.kota, saved.kecamatan);
                }
            }
        }
    }
}

function previewFoto(input) {
    const label = document.getElementById('foto-name');
    const file = input.files && input.files[0];
    if (!file) { label.innerText = 'Klik ikon kamera untuk mengubah'; return; }

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