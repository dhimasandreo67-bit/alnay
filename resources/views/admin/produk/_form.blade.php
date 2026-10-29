{{--
Partial form produk, dipakai oleh create.blade.php dan edit.blade.php.
--}}
<form method="POST" action="{{ $actionUrl }}" enctype="multipart/form-data"
    class="grid grid-cols-1 lg:grid-cols-3 gap-6"
    x-data="produkForm(@js($formData), @js($paletWarna))"> <!-- Tambahkan $paletWarna ke Alpine -->
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    {{-- ============ KOLOM KIRI ============ --}}
    <div class="lg:col-span-1 space-y-6">
        
        {{-- Kode Produk --}}
        <div class="bg-panel p-6 rounded-3xl border border-line shadow-sm space-y-3">
            <label class="block text-sm font-bold text-ink">Kode Produk (SKU)</label>
            <input type="text" name="sku" x-model="sku" :placeholder="skuPlaceholder"
                   class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm font-mono focus:bg-white focus:outline-none">
            <p class="text-[11px] text-muted">Kosongkan untuk dibuat otomatis dari kategori.</p>
        </div>

        {{-- Foto Produk --}}
        <div class="bg-panel p-6 rounded-3xl border border-line shadow-sm space-y-5">
            <div>
                <h3 class="text-lg font-black text-ink">Foto Produk</h3>
                <p class="text-xs text-muted mt-1">Foto pertama tampil sebagai sampul. Klik ikon kaca pembesar untuk melihat ukuran penuh.</p>
            </div>

            <div class="grid grid-cols-3 gap-3">
                @foreach ([0 => 'SAMPUL', 1 => 'Slot 2', 2 => 'Slot 3', 3 => 'Detail'] as $idx => $label)
                    <label class="relative rounded-2xl border-2 border-dashed border-line bg-cream/30 cursor-pointer overflow-hidden hover:border-gold hover:bg-gold/5 transition-all duration-300 group {{ $idx === 0 ? 'col-span-3 aspect-[4/3]' : 'aspect-square' }}">
                        <input type="file" name="foto[{{ $idx }}]" accept="image/*" class="hidden" @change="handleFoto($event, {{ $idx }})">

                        <span class="absolute top-3 left-3 z-10 text-[10px] font-black {{ $idx === 0 ? 'bg-ink text-cream px-2.5 py-1' : 'bg-white/90 backdrop-blur text-ink px-2 py-0.5' }} rounded-lg pointer-events-none shadow-sm">{{ $label }}</span>

                        <template x-if="!previews[{{ $idx }}]">
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 opacity-60 group-hover:opacity-100 group-hover:scale-105 transition-all duration-300 pointer-events-none">
                                <svg class="{{ $idx === 0 ? 'w-10 h-10' : 'w-6 h-6' }} text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                <span class="text-[11px] font-bold text-ink/60">Upload Foto</span>
                            </div>
                        </template>

                        <img x-show="previews[{{ $idx }}]" :src="previews[{{ $idx }}]" class="absolute inset-0 w-full h-full object-cover pointer-events-none transition-transform duration-500">

                        {{-- Tombol zoom --}}
                        <button type="button" x-show="previews[{{ $idx }}]" @click.stop.prevent="openLightbox(previews[{{ $idx }}])"
                                class="absolute bottom-2 right-2 z-10 w-8 h-8 rounded-full bg-ink/70 text-cream backdrop-blur flex items-center justify-center hover:bg-ink transition" title="Lihat besar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>

                        {{-- Tombol hapus --}}
                        <button type="button" x-show="previews[{{ $idx }}]" @click.stop.prevent="removeFoto({{ $idx }})"
                                class="absolute top-2 right-2 z-10 w-8 h-8 rounded-full bg-rose-500/90 text-white backdrop-blur text-sm flex items-center justify-center hover:bg-rose-600 transition">
                            &times;
                        </button>

                        <input type="hidden" name="foto_existing[{{ $idx }}]" :value="existingFoto[{{ $idx }}] || ''">
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Kategori & Jenis Transaksi --}}
        <div class="bg-panel p-6 rounded-3xl border border-line shadow-sm space-y-5">
            <div>
                <label class="block text-sm font-bold text-ink mb-2">Kategori <span class="text-rose-500">*</span></label>
                <select name="kategori_id" x-model="kategoriId" required
                        class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none cursor-pointer">
                    <option value="">Pilih kategori...</option>
                    @foreach($categories as $kategori)
                        <option value="{{ $kategori->id }}" data-kode="{{ $kategori->kode_kategori ?? '' }}">{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-ink mb-2">Jenis Transaksi <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="bisaDisewa = !bisaDisewa"
                            :class="bisaDisewa ? 'bg-gold/10 border-gold text-gold shadow-sm' : 'bg-panel border-line text-muted hover:bg-cream'"
                            class="flex items-center justify-center gap-2 border-2 rounded-xl py-3 text-xs font-bold transition-all duration-300 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Bisa disewa
                    </button>
                    <button type="button" @click="dapatDijual = !dapatDijual"
                            :class="dapatDijual ? 'bg-gold/10 border-gold text-gold shadow-sm' : 'bg-panel border-line text-muted hover:bg-cream'"
                            class="flex items-center justify-center gap-2 border-2 rounded-xl py-3 text-xs font-bold transition-all duration-300 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.023.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Bisa dijual
                    </button>
                </div>
                <template x-if="!bisaDisewa && !dapatDijual">
                    <p class="text-[12px] text-red-500 font-bold mt-2 animate-pulse">💀 Pilih minimal satu jenis transaksi</p>
                </template>
                <input type="hidden" name="bisa_disewa" :value="bisaDisewa ? 1 : 0">
                <input type="hidden" name="dapat_dijual" :value="dapatDijual ? 1 : 0">
            </div>
        </div>

        {{-- Status & Rating --}}
        <div class="bg-panel p-6 rounded-3xl border border-line shadow-sm space-y-5">
            <div>
                <label class="block text-sm font-bold text-ink mb-2">Status Stok <span class="text-rose-500">*</span></label>
                <select name="status" x-model="status" required class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none cursor-pointer">
                    @foreach($opsiStatus as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="border-t border-line pt-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-ink">Rating Produk</h3>
                        <p class="text-[11px] text-muted mt-0.5">Kepuasan Klien</p>
                    </div>
                    <div class="text-right">
                        @php
                            // Ambil data rating langsung dari relasi (jika produk sudah ada)
                            $ratingRataRata = isset($produk) ? ($produk->rating()->avg('rating') ?? 0) : ($formData['ratingRataRata'] ?? 0);
                            $jumlahRating = isset($produk) ? $produk->rating()->count() : ($formData['jumlahRating'] ?? 0);
                        @endphp

                        <p class="text-xl font-black text-gold">★ {{ number_format($ratingRataRata, 1) }}</p>
                        <p class="text-[10px] font-semibold text-muted">{{ $jumlahRating > 0 ? ($jumlahRating . ' Ulasan') : 'Belum ada' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ KOLOM KANAN ============ --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Informasi Produk --}}
        <div class="bg-panel p-8 rounded-3xl border border-line shadow-sm space-y-5">
            <div>
                <h3 class="text-xl font-black text-ink">Informasi Utama</h3>
                <p class="text-xs text-muted mt-1">Nama dan deskripsi yang akan tampil kepada pelanggan.</p>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Nama Produk <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" x-model="nama" required placeholder="mis. Kebaya Encim Modern Sutra"
                       class="input-elegant w-full px-5 py-3.5 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none">
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Deskripsi</label>
                <textarea name="deskripsi" x-model="deskripsi" rows="4" placeholder="Jelaskan material kain, detail bordir, filosofi busana..."
                          class="input-elegant w-full px-5 py-3.5 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none resize-none"></textarea>
            </div>
        </div>

        {{-- Detail Fisik --}}
        <div class="bg-panel p-8 rounded-3xl border border-line shadow-sm space-y-6">
            <div>
                <h3 class="text-xl font-black text-ink">Detail Fisik</h3>
                <p class="text-xs text-muted mt-1">Warna, motif, ukuran, dan berat memengaruhi pencarian & pengiriman.</p>
            </div>

            {{-- Warna --}}
            <div class="space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Warna Busana</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($paletWarna as $label => $hex)
                        <button type="button" title="{{ $label }}" @click="toggleWarnaPreset('{{ $label }}')"
                                class="w-10 h-10 rounded-full border-2 transition-all duration-300 hover:scale-110 active:scale-95 shadow-sm"
                                :class="warna.includes('{{ $label }}') ? 'border-gold ring-2 ring-gold/30 scale-110' : 'border-white'"
                                style="background-color: {{ $hex }};">
                            <template x-if="warna.includes('{{ $label }}')">
                                <svg class="w-4 h-4 mx-auto text-white drop-shadow-md" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </template>
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="color" x-model="customColorHex" class="w-12 h-12 rounded-xl border border-line cursor-pointer p-1 bg-panel shadow-sm">
                    <input type="text" x-model="customColorName" placeholder="Nama warna kustom..."
                           class="input-elegant flex-1 px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none"
                           @keyup.enter="addCustomWarna()">
                    <button type="button" @click="addCustomWarna()"
                            class="px-5 py-3 rounded-xl border border-gold/30 bg-gold/10 text-gold font-bold text-sm hover:bg-gold/20 active:scale-95 transition-all whitespace-nowrap shadow-sm">
                        + Tambah
                    </button>
                </div>

                <div class="flex flex-wrap gap-2 pt-1">
                    <template x-for="(w, i) in warna" :key="w + '-' + i">
                        <span class="inline-flex items-center gap-2 bg-cream border border-line text-ink text-xs font-bold px-3 py-1.5 rounded-full shadow-sm animate-fade-up">
                            {{-- Tambahkan indikator dot warna --}}
                            <span class="w-3 h-3 rounded-full border border-black/10 shadow-inner" :style="`background-color: ${getHexColor(w)}`"></span>
                            <span x-text="w"></span>
                            <input type="hidden" name="warna[]" :value="w">
                            <button type="button" @click="warna.splice(i, 1)" class="text-muted hover:text-rose-500 font-black transition-colors ml-1">&times;</button>
                        </span>
                    </template>
                    <template x-if="warna.length === 0">
                        <span class="text-xs text-muted italic bg-cream/50 px-3 py-2 rounded-lg border border-dashed border-line">Belum ada warna dipilih</span>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Motif</label>
                    <input type="text" name="motif" x-model="motif" placeholder="mis. Bordir Bunga Peony"
                           class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Berat (gram)</label>
                    <input type="number" step="1" min="0" name="berat" x-model.number="berat" placeholder="450"
                           class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Jenis Bahan</label>
                <input type="text" name="bahan" x-model="bahan" placeholder="mis. Sutra Organza Premium"
                       class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none">
            </div>

            {{-- Variasi Ukuran --}}
            <div class="space-y-3 border-t border-line pt-5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Variasi Ukuran & Stok</label>
                    <button type="button" @click="addUkuranVariasi()"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-gold hover:text-ink bg-gold/10 hover:bg-gold/20 px-3 py-1.5 rounded-lg transition-all active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah
                    </button>
                </div>

                <p class="text-xs text-muted" x-show="ukuranVariasi.length === 0">
                    Kosongkan jika produk ini tidak punya varian ukuran (mis. aksesoris All Size).
                </p>

                <div class="space-y-3">
                    <template x-for="(row, i) in ukuranVariasi" :key="'uk-' + i">
                        <div class="flex items-center gap-3 animate-fade-up">
                            <select x-model="row.ukuran" :name="'ukuran[' + i + '][ukuran]'"
                                    class="input-elegant flex-1 px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm font-bold focus:bg-white focus:outline-none cursor-pointer">
                                <option value="">Pilih ukuran...</option>
                                @foreach($opsiUkuran as $ukuran)
                                    <option value="{{ $ukuran }}">{{ $ukuran }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="1" min="0" x-model.number="row.stok" :name="'ukuran[' + i + '][stok]'" placeholder="Stok"
                                   class="input-elegant w-32 px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm font-bold focus:bg-white focus:outline-none">
                            <button type="button" @click="ukuranVariasi.splice(i, 1)"
                                    class="w-11 h-11 shrink-0 rounded-xl border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition-all active:scale-90 font-black">
                                &times;
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between bg-gold/5 border border-gold/20 rounded-xl px-5 py-3.5 mt-2" x-show="ukuranVariasi.length > 0">
                    <span class="text-xs font-bold text-ink/70 uppercase tracking-wider">Total stok dari variasi</span>
                    <span class="text-lg font-black text-gold" x-text="totalStokVariasi + ' unit'"></span>
                </div>
            </div>
        </div>

        {{-- Harga & Stok --}}
        <div class="bg-panel p-8 rounded-3xl border border-line shadow-sm space-y-6">
            <div>
                <h3 class="text-xl font-black text-ink">Harga & Stok</h3>
                <p class="text-xs text-muted mt-1">Diskon berlaku untuk harga sewa maupun harga jual.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Kolom Harga Sewa & DP --}}
                <div class="space-y-5" x-show="bisaDisewa" x-transition>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Harga Sewa <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-ink/50 pointer-events-none">Rp</span>
                            <input type="text" inputmode="numeric" :value="formatNumber(hargaSewa)" @input="hargaSewa = parseNumber($event.target.value)" :required="bisaDisewa" placeholder="350.000"
                                   class="input-elegant w-full pl-12 pr-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold text-ink">
                            <input type="hidden" name="harga_sewa" :value="hargaSewa">
                        </div>
                    </div>

                    {{-- Input DP (Uang Muka) --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">
                            DP / Uang Muka <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-ink/50 pointer-events-none">Rp</span>
                            
                            <input type="text" inputmode="numeric" 
                                   :value="formatNumber(dp)" 
                                   @input="dp = parseNumber($event.target.value)" 
                                   :required="bisaDisewa" 
                                   placeholder="150.000"
                                   class="input-elegant w-full pl-12 pr-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold text-ink">
                            
                            <input type="hidden" name="dp" :value="dp">
                        </div>
                    </div>
                </div>

                {{-- Kolom Harga Jual --}}
                <div class="space-y-2" x-show="dapatDijual" x-transition>
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Harga Jual <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-ink/50 pointer-events-none">Rp</span>
                        <input type="text" inputmode="numeric" :value="formatNumber(hargaJual)" @input="hargaJual = parseNumber($event.target.value)" :required="dapatDijual" placeholder="1.850.000"
                               class="input-elegant w-full pl-12 pr-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold text-ink">
                        <input type="hidden" name="harga_jual" :value="hargaJual">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="space-y-2" x-show="ukuranVariasi.length === 0">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Stok (unit) <span class="text-rose-500">*</span></label>
                    <input type="number" step="1" min="0" name="stok" x-model.number="stok" :required="ukuranVariasi.length === 0" placeholder="1"
                           class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none">
                </div>
                <div class="space-y-2" x-show="ukuranVariasi.length > 0">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Stok (unit)</label>
                    <input type="text" :value="totalStokVariasi + ' unit'" disabled
                           class="w-full px-4 py-3 bg-cream border border-line rounded-xl text-sm text-muted cursor-not-allowed font-bold">
                </div>
                <div class="space-y-2" x-show="bisaDisewa" x-transition>
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Denda / hari</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-ink/50 pointer-events-none">Rp</span>
                        <input type="text" inputmode="numeric" :value="formatNumber(dendaPerHari)" @input="dendaPerHari = parseNumber($event.target.value)" placeholder="50.000"
                               class="input-elegant w-full pl-12 pr-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold text-ink">
                        <input type="hidden" name="denda_per_hari_telat" :value="dendaPerHari">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Diskon (%)</label>
                    <input type="number" step="1" min="0" max="100" name="diskon_persen" x-model.number="diskon" placeholder="0"
                           class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="bg-gold/10 border border-gold/30 rounded-2xl p-4 text-sm font-bold text-ink flex items-center gap-3 shadow-inner" x-show="diskon > 0 && (bisaDisewa || dapatDijual)" x-transition>
                <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>
                    <span x-show="bisaDisewa">Sewa jadi <span class="text-gold font-black" x-text="formatRupiah(hargaSewa * (1 - diskon / 100))"></span></span>
                    <span x-show="bisaDisewa && dapatDijual">&nbsp;·&nbsp;</span>
                    <span x-show="dapatDijual">Jual jadi <span class="text-gold font-black" x-text="formatRupiah(hargaJual * (1 - diskon / 100))"></span></span>
                </span>
            </div>
        </div>
    </div>

    {{-- ============ STICKY ACTION BAR ============ --}}
    <div class="lg:col-span-3 sticky bottom-0 z-30 bg-white/80 backdrop-blur-md border-t border-line p-4 px-6 mt-4 flex items-center justify-end gap-4 rounded-b-3xl shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">
        <a href="{{ route('admin.produk.index') }}"
           class="px-6 py-3 rounded-xl border border-line bg-panel text-ink font-bold text-sm hover:bg-cream active:scale-95 transition-all duration-300">
            Batalkan
        </a>
        <button type="submit"
                :disabled="!bisaDisewa && !dapatDijual"
                :class="(!bisaDisewa && !dapatDijual) ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5 hover:shadow-lg'"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all duration-200 text-sm font-black py-3 px-8 rounded-xl shadow-md shadow-gold/30 active:scale-95 transition-all duration-300 text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            {{ $submitLabel }}
        </button>
    </div>

    {{-- ============ LIGHTBOX DENGAN ZOOM ============ --}}
    <style>[x-cloak]{display:none!important}</style>
    <div x-show="lightbox" x-cloak 
         @click.self="closeLightbox()" 
         @keydown.escape.window="closeLightbox()"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-ink/95 backdrop-blur-md">
        
        {{-- Area Gambar --}}
        <div class="relative flex-1 w-full flex items-center justify-center p-4 overflow-hidden">
            <img :src="lightbox" 
                 :style="`transform: scale(${zoomScale});`"
                 @wheel.prevent="zoomScale += $event.deltaY > 0 ? -0.2 : 0.2; if(zoomScale < 1) zoomScale = 1; if(zoomScale > 4) zoomScale = 4;"
                 class="max-w-[90vw] max-h-[75vh] w-auto h-auto object-contain rounded-2xl shadow-2xl border-2 border-white/10 transition-transform duration-300 ease-out select-none">
        </div>
        
        {{-- Kontrol Zoom & Close (Dibuat Melayang di Bawah) --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-3 bg-black/70 backdrop-blur-xl px-5 py-3 rounded-2xl border border-white/20 shadow-2xl z-10">
            <button type="button" @click="zoomOut()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20 transition text-xl font-bold">-</button>
            <span class="text-white font-mono text-sm w-16 text-center" x-text="Math.round(zoomScale * 100) + '%'"></span>
            <button type="button" @click="zoomIn()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20 transition text-xl font-bold">+</button>
            <div class="w-px h-6 bg-white/20 mx-1"></div>
            <button type="button" @click="resetZoom()" class="px-4 py-2 rounded-lg bg-white/10 text-white text-xs font-bold hover:bg-white/20 transition">Reset</button>
        </div>

        {{-- Tombol Close (X) --}}
        <button type="button" @click="closeLightbox()" class="absolute top-6 right-6 w-12 h-12 rounded-full bg-rose-500/90 text-white hover:bg-rose-600 backdrop-blur border border-white/20 shadow-lg transition flex items-center justify-center z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</form>

{{-- Script Alpine dengan Logika Warna & Zoom --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('produkForm', (initial, paletWarnaRaw) => ({
        sku: initial.sku || '',
        kategoriId: initial.kategoriId || '',
        bisaDisewa: initial.bisaDisewa ?? true,
        dapatDijual: initial.dapatDijual ?? true,
        status: initial.status || 'tersedia',
        nama: initial.nama || '',
        deskripsi: initial.deskripsi || '',
        bahan: initial.bahan || '',
        motif: initial.motif || '',
        berat: initial.berat ?? null,
        warna: initial.warna || [],
        customColorHex: initial.customColorHex || '#CA9A3E',
        customColorName: initial.customColorName || '',
        ukuranVariasi: initial.ukuranVariasi || [],
        hargaSewa: initial.hargaSewa ?? null,
        dp: initial.dp ?? null,
        hargaJual: initial.hargaJual ?? null,
        stok: initial.stok ?? null,
        dendaPerHari: initial.dendaPerHari ?? null,
        diskon: initial.diskon ?? 0,
        existingFoto: initial.existingFoto || {},
        previews: Object.assign({}, initial.previewFoto || {}),
        paletWarna: paletWarnaRaw || {}, // Simpan data palet warna
        
        // State untuk Lightbox
        lightbox: null,
        zoomScale: 1,

        get skuPlaceholder() {
            const select = document.querySelector('select[name="kategori_id"]');
            if (!select) return 'KBY-000';
            const opt = select.querySelector(`option[value="${this.kategoriId}"]`);
            const kode = opt?.dataset?.kode || 'KBY';
            return `${kode.toUpperCase()}-000`;
        },
        get totalStokVariasi() {
            return this.ukuranVariasi.reduce((sum, row) => sum + (Number(row.stok) || 0), 0);
        },
        toggleWarnaPreset(label) {
            const idx = this.warna.indexOf(label);
            if (idx === -1) this.warna.push(label);
            else this.warna.splice(idx, 1);
        },
        addCustomWarna() {
            const name = this.customColorName.trim();
            if (!name) return;
            if (!this.warna.includes(name)) this.warna.push(name);
            // Simpan hex warna kustom ke dalam paletWarna sementara
            this.paletWarna[name] = this.customColorHex; 
            this.customColorName = '';
        },
        getHexColor(name) {
            // Cari hex berdasarkan nama di paletWarna, jika tidak ada cek apakah itu warna kustom
            return this.paletWarna[name] || '#CCCCCC'; // Fallback ke abu-abu jika tidak ditemukan
        },
        addUkuranVariasi() { this.ukuranVariasi.push({ ukuran: '', stok: 0 }); },
        handleFoto(event, slotIndex) {
            const file = event.target.files[0];
            if (!file) return;
            this.previews[slotIndex] = URL.createObjectURL(file);
            this.existingFoto[slotIndex] = '';
        },
        removeFoto(slotIndex) {
            this.previews[slotIndex] = null;
            this.existingFoto[slotIndex] = '';
            const input = document.querySelector('input[name="foto[' + slotIndex + ']"]');
            if (input) input.value = '';
        },
        formatNumber(value) {
            if (value === null || value === undefined || value === '') return '';
            
            // Ambil angka sebelum titik desimal (jika ada)
            let stringValue = value.toString().split('.')[0];
            
            // Hapus semua karakter selain angka
            const numberString = stringValue.replace(/[^0-9]/g, '');
            if (numberString === '') return '';
            
            // Format dengan titik ribuan
            return numberString.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },
        parseNumber(value) {
            if (!value) return null;
            const numberString = value.toString().replace(/[^0-9]/g, '');
            return numberString ? parseInt(numberString, 10) : null;
        },
        formatRupiah(value) {
            const num = Math.round(Number(value) || 0);
            return 'Rp ' + num.toLocaleString('id-ID');
        },
        
        // Logika Lightbox & Zoom
        openLightbox(url) {
            this.lightbox = url;
            this.zoomScale = 1;
        },
        closeLightbox() {
            this.lightbox = null;
            this.zoomScale = 1;
        },
        zoomIn() {
            if (this.zoomScale < 4) this.zoomScale += 0.5;
        },
        zoomOut() {
            if (this.zoomScale > 1) this.zoomScale -= 0.5;
        },
        resetZoom() {
            this.zoomScale = 1;
        }
    }));
});
</script>