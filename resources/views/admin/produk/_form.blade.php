{{--
Partial form produk, dipakai oleh create.blade.php dan edit.blade.php.
--}}
<form method="POST" action="{{ $actionUrl }}" enctype="multipart/form-data"
    class="space-y-8"
    x-data="produkForm(@js($formData), @js($paletWarna))">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    {{-- GRID UTAMA: KOLOM KIRI (MEDIA & KLASIFIKASI) vs KOLOM KANAN (INFORMASI & HARGA) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- ============ KOLOM KIRI (5 KOLOM) ============ --}}
        <div class="lg:col-span-5 space-y-6">
            
            {{-- Foto Produk Panel --}}
            <div class="bg-panel p-6 rounded-2xl border border-line shadow-sm space-y-5">
                <div class="border-b border-line pb-4">
                    <h3 class="text-base font-bold text-ink flex items-center gap-2">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Foto Produk
                    </h3>
                    <p class="text-xs text-muted mt-1">Foto pertama tampil sebagai sampul utama. Klik ikon kaca pembesar untuk zoom.</p>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    @foreach ([0 => 'SAMPUL', 1 => 'Slot 2', 2 => 'Slot 3', 3 => 'Detail'] as $idx => $label)
                        <label class="relative rounded-xl border-2 border-dashed border-line bg-cream/20 cursor-pointer overflow-hidden hover:border-gold hover:bg-gold/5 transition-all duration-300 group {{ $idx === 0 ? 'col-span-3 aspect-[16/9]' : 'aspect-square' }}">
                            <input type="file" name="foto[{{ $idx }}]" accept="image/*" class="hidden" @change="handleFoto($event, {{ $idx }})">

                            <span class="absolute top-2.5 left-2.5 z-10 text-[10px] font-bold {{ $idx === 0 ? 'bg-ink text-cream px-2.5 py-1' : 'bg-white/90 backdrop-blur text-ink px-2 py-0.5' }} rounded-md pointer-events-none shadow-sm">{{ $label }}</span>

                            <template x-if="!previews[{{ $idx }}]">
                                <div class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 opacity-60 group-hover:opacity-100 group-hover:scale-105 transition-all duration-300 pointer-events-none">
                                    <svg class="{{ $idx === 0 ? 'w-8 h-8' : 'w-5 h-5' }} text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                    <span class="text-[11px] font-semibold text-ink/70">Upload Foto</span>
                                </div>
                            </template>

                            <img x-show="previews[{{ $idx }}]" :src="previews[{{ $idx }}]" class="absolute inset-0 w-full h-full object-cover pointer-events-none transition-transform duration-500">

                            {{-- Tombol Zoom --}}
                            <button type="button" x-show="previews[{{ $idx }}]" @click.stop.prevent="openLightbox(previews[{{ $idx }}])"
                                    class="absolute bottom-2 right-2 z-10 w-7 h-7 rounded-full bg-ink/70 text-cream backdrop-blur flex items-center justify-center hover:bg-ink transition" title="Lihat besar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>

                            {{-- Tombol Hapus --}}
                            <button type="button" x-show="previews[{{ $idx }}]" @click.stop.prevent="removeFoto({{ $idx }})"
                                    class="absolute top-2 right-2 z-10 w-7 h-7 rounded-full bg-rose-500/90 text-white backdrop-blur text-xs flex items-center justify-center hover:bg-rose-600 transition">
                                &times;
                            </button>

                            <input type="hidden" name="foto_existing[{{ $idx }}]" :value="existingFoto[{{ $idx }}] || ''">
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Klasifikasi, Jenis Transaksi & SKU --}}
            <div class="bg-panel p-6 rounded-2xl border border-line shadow-sm space-y-5">
                <div class="border-b border-line pb-4">
                    <h3 class="text-base font-bold text-ink flex items-center gap-2">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.023.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Klasifikasi Produk
                    </h3>
                </div>

                {{-- Kode Produk SKU --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Kode Produk (SKU)</label>
                    <input type="text" name="sku" x-model="sku" :placeholder="skuPlaceholder"
                           class="input-elegant w-full px-4 py-2.5 bg-cream/30 border border-line rounded-xl text-sm font-mono focus:bg-white focus:outline-none transition">
                    <p class="text-[11px] text-muted">Biarkan kosong untuk pembuatan otomatis berdasarkan kode kategori.</p>
                </div>

                {{-- Kategori --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Kategori <span class="text-rose-500">*</span></label>
                    <select name="kategori_id" x-model="kategoriId" required
                            class="input-elegant w-full px-4 py-2.5 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none cursor-pointer transition">
                        <option value="">Pilih kategori...</option>
                        @foreach($categories as $kategori)
                            <option value="{{ $kategori->id }}" data-kode="{{ $kategori->kode_kategori ?? '' }}">{{ $kategori->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis Transaksi --}}
                <div class="space-y-2 pt-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Jenis Transaksi <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="bisaDisewa = !bisaDisewa"
                                :class="bisaDisewa ? 'bg-amber-500/10 border-gold text-amber-900 shadow-sm ring-1 ring-gold/30' : 'bg-cream/20 border-line text-muted hover:bg-cream/50'"
                                class="flex items-center justify-center gap-2 border rounded-xl py-2.5 px-3 text-xs font-bold transition-all duration-200 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Bisa Disewa
                        </button>
                        <button type="button" @click="dapatDijual = !dapatDijual"
                                :class="dapatDijual ? 'bg-amber-500/10 border-gold text-amber-900 shadow-sm ring-1 ring-gold/30' : 'bg-cream/20 border-line text-muted hover:bg-cream/50'"
                                class="flex items-center justify-center gap-2 border rounded-xl py-2.5 px-3 text-xs font-bold transition-all duration-200 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.023.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Bisa Dijual
                        </button>
                    </div>
                    <template x-if="!bisaDisewa && !dapatDijual">
                        <p class="text-[12px] text-rose-500 font-medium mt-1.5 flex items-center gap-1">
                            ⚠️ Pilih minimal satu jenis transaksi.
                        </p>
                    </template>
                    <input type="hidden" name="bisa_disewa" :value="bisaDisewa ? 1 : 0">
                    <input type="hidden" name="dapat_dijual" :value="dapatDijual ? 1 : 0">
                </div>

                {{-- Status Stok & Ulasan --}}
                <div class="pt-3 border-t border-line space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Status Stok <span class="text-rose-500">*</span></label>
                        <select name="status" x-model="status" required class="input-elegant w-full px-4 py-2.5 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none cursor-pointer transition">
                            @foreach($opsiStatus as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="bg-cream/20 p-3.5 rounded-xl border border-line/60 flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-ink">Rating Produk</h4>
                            <p class="text-[11px] text-muted">Berdasarkan ulasan klien</p>
                        </div>
                        <div class="text-right">
                            @php
                                $ratingRataRata = isset($produk) ? ($produk->rating()->avg('rating') ?? 0) : ($formData['ratingRataRata'] ?? 0);
                                $jumlahRating = isset($produk) ? $produk->rating()->count() : ($formData['jumlahRating'] ?? 0);
                            @endphp
                            <p class="text-lg font-black text-amber-600">★ {{ number_format($ratingRataRata, 1) }}</p>
                            <p class="text-[10px] font-semibold text-muted">{{ $jumlahRating > 0 ? ($jumlahRating . ' Ulasan') : 'Belum ada' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ KOLOM KANAN (7 KOLOM) ============ --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- Informasi Utama & Detail Fisik (Gabung dalam 1 Kontainer Utama) --}}
            <div class="bg-panel p-7 rounded-2xl border border-line shadow-sm space-y-6">
                <div>
                    <h3 class="text-base font-bold text-ink flex items-center gap-2">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Informasi Utama & Spesifikasi
                    </h3>
                    <p class="text-xs text-muted mt-1">Lengkapi nama, deskripsi, dan detail fisik busana.</p>
                </div>

                <div class="space-y-4">
                    {{-- Nama Produk --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Nama Produk <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" x-model="nama" required placeholder="mis. Kebaya Encim Modern Sutra"
                               class="input-elegant w-full px-4 py-3 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none transition font-medium">
                    </div>

                    {{-- Deskripsi --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Deskripsi Produk</label>
                        <textarea name="deskripsi" x-model="deskripsi" rows="3" placeholder="Jelaskan material kain, detail bordir, filosofi busana..."
                                  class="input-elegant w-full px-4 py-3 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none resize-none transition leading-relaxed"></textarea>
                    </div>

                    {{-- Detail Fisik Grid 3-Kolom --}}
                    <div class="pt-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">Spesifikasi Fisik</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-[11px] font-bold text-ink/70">Motif</label>
                                <input type="text" name="motif" x-model="motif" placeholder="Bordir Peony"
                                       class="input-elegant w-full px-3.5 py-2.5 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[11px] font-bold text-ink/70">Jenis Bahan</label>
                                <input type="text" name="bahan" x-model="bahan" placeholder="Sutra Organza"
                                       class="input-elegant w-full px-3.5 py-2.5 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[11px] font-bold text-ink/70">Berat (gram)</label>
                                <input type="number" step="1" min="0" name="berat" x-model.number="berat" placeholder="450"
                                       class="input-elegant w-full px-3.5 py-2.5 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none transition">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Harga & Skema Biaya Panel --}}
            <div class="bg-panel p-7 rounded-2xl border border-line shadow-sm space-y-6">
                <div>
                    <h3 class="text-base font-bold text-ink flex items-center gap-2">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Harga & Skema Biaya
                    </h3>
                    <p class="text-xs text-muted mt-1">Atur harga sesuai jenis transaksi yang diaktifkan.</p>
                </div>

                {{-- Row 1: Harga Sewa, DP, & Harga Jual --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Blok Sewa (Harga & DP) --}}
                    <div class="space-y-4 sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="bisaDisewa" x-transition>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Harga Sewa <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-ink/50 pointer-events-none">Rp</span>
                                <input type="text" inputmode="numeric" :value="formatNumber(hargaSewa)" @input="hargaSewa = parseNumber($event.target.value)" :required="bisaDisewa" placeholder="350.000"
                                       class="input-elegant w-full pl-10 pr-3.5 py-2.5 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold text-ink transition">
                                <input type="hidden" name="harga_sewa" :value="hargaSewa">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">DP / Uang Muka <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-ink/50 pointer-events-none">Rp</span>
                                <input type="text" inputmode="numeric" 
                                       :value="formatNumber(dp)" 
                                       @input="dp = parseNumber($event.target.value)" 
                                       :required="bisaDisewa" 
                                       placeholder="150.000"
                                       class="input-elegant w-full pl-10 pr-3.5 py-2.5 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold text-ink transition">
                                <input type="hidden" name="dp" :value="dp">
                            </div>
                        </div>
                    </div>

                    {{-- Harga Jual --}}
                    <div class="space-y-1.5 sm:col-span-2" x-show="dapatDijual" x-transition>
                        <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Harga Jual <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-ink/50 pointer-events-none">Rp</span>
                            <input type="text" inputmode="numeric" :value="formatNumber(hargaJual)" @input="hargaJual = parseNumber($event.target.value)" :required="dapatDijual" placeholder="1.850.000"
                                   class="input-elegant w-full pl-10 pr-3.5 py-2.5 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold text-ink transition">
                            <input type="hidden" name="harga_jual" :value="hargaJual">
                        </div>
                    </div>
                </div>

                {{-- Row 2: Biaya Tambahan Sewa --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-line/60" x-show="bisaDisewa" x-transition>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-ink/70">Denda / Hari</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-ink/50 pointer-events-none">Rp</span>
                            <input type="text" inputmode="numeric" :value="formatNumber(dendaPerHari)" @input="dendaPerHari = parseNumber($event.target.value)" placeholder="50.000"
                                   class="input-elegant w-full pl-9 pr-3 py-2 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold transition">
                            <input type="hidden" name="denda_per_hari_telat" :value="dendaPerHari">
                        </div>
                        <p class="text-[10px] text-muted">Dikenakan jika pelanggan terlambat mengembalikan.</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-ink/70">Perpanjangan / Hari</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-ink/50 pointer-events-none">Rp</span>
                            <input type="text" inputmode="numeric" :value="formatNumber(perpanjanganPerHari)" @input="perpanjanganPerHari = parseNumber($event.target.value)" placeholder="30.000"
                                   class="input-elegant w-full pl-9 pr-3 py-2 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none font-semibold transition">
                            <input type="hidden" name="harga_perpanjangan_per_hari" :value="perpanjanganPerHari">
                        </div>
                        <p class="text-[10px] text-muted">Tarif perpanjangan resmi.</p>
                    </div>
                </div>

                {{-- Row 3: Diskon terpisah untuk sewa dan jual --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-line/60">
                    <div class="space-y-1.5" x-show="bisaDisewa" x-transition>
                        <label class="block text-[11px] font-bold text-ink/70">Diskon Sewa (%)</label>
                        <input type="number" step="1" min="0" max="100" name="diskon_sewa_persen" x-model.number="diskonSewa" placeholder="0"
                               class="input-elegant w-full px-3 py-2 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none transition">
                        <p class="text-[10px] text-muted">Potongan khusus harga sewa.</p>
                    </div>
                    <div class="space-y-1.5" x-show="dapatDijual" x-transition>
                        <label class="block text-[11px] font-bold text-ink/70">Diskon Jual (%)</label>
                        <input type="number" step="1" min="0" max="100" name="diskon_jual_persen" x-model.number="diskonJual" placeholder="0"
                               class="input-elegant w-full px-3 py-2 bg-cream/30 border border-line rounded-xl text-sm focus:bg-white focus:outline-none transition">
                        <p class="text-[10px] text-muted">Potongan khusus harga jual.</p>
                    </div>
                </div>

                {{-- Banner Preview Potongan Diskon --}}
                <div class="bg-amber-500/10 border border-gold/30 rounded-xl p-3.5 text-xs font-semibold text-ink flex items-center gap-2.5 shadow-sm"
                     x-show="(bisaDisewa && diskonSewa > 0) || (dapatDijual && diskonJual > 0)" x-transition>
                    <svg class="w-4 h-4 text-gold flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>
                        <span x-show="bisaDisewa && diskonSewa > 0">Harga Sewa Net: <span class="text-amber-800 font-bold" x-text="formatRupiah(hargaSewa * (1 - diskonSewa / 100))"></span></span>
                        <span x-show="bisaDisewa && diskonSewa > 0 && dapatDijual && diskonJual > 0">&nbsp;·&nbsp;</span>
                        <span x-show="dapatDijual && diskonJual > 0">Harga Jual Net: <span class="text-amber-800 font-bold" x-text="formatRupiah(hargaJual * (1 - diskonJual / 100))"></span></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ FULL WIDTH: WARNA, UKURAN & STOK ============ --}}
    <div class="bg-panel p-7 rounded-2xl border border-line shadow-sm space-y-5">
        <div class="border-b border-line pb-4">
            <h3 class="text-base font-bold text-ink flex items-center gap-2">
                <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Manajemen Stok, Ukuran & Warna
            </h3>
            <p class="text-xs text-muted mt-1">Kelola variasi ukuran dan ketersediaan unit untuk tiap opsi transaksi.</p>
        </div>

        <template x-if="pisah">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                @include('admin.produk._blok-stok', [
                    'set' => 'setUtama', 'akhiran' => '', 'judulExpr' => "'Penjualan'",
                    'sub' => 'Stok, warna, dan ukuran yang tersedia untuk DIJUAL. Terpisah dari stok sewa.',
                    'kelasAksen' => 'border-l-4 border-l-emerald-400',
                ])
                @include('admin.produk._blok-stok', [
                    'set' => 'setSewa', 'akhiran' => '_sewa', 'judulExpr' => "'Penyewaan'",
                    'sub' => 'Stok, warna, dan ukuran yang tersedia untuk DISEWA. Terpisah dari stok jual.',
                    'kelasAksen' => 'border-l-4 border-l-sky-400',
                ])
            </div>
        </template>
        <template x-if="!pisah">
            @include('admin.produk._blok-stok', [
                'set' => 'setUtama', 'akhiran' => '', 'judulExpr' => 'judulUtama',
                'sub' => 'Warna, ukuran, dan stok produk ini.',
                'kelasAksen' => '',
            ])
        </template>
    </div>

    {{-- ============ STICKY ACTION BAR ============ --}}
    <div class="sticky bottom-4 z-30 bg-white/90 backdrop-blur-md border border-line/80 p-4 px-6 rounded-2xl shadow-xl flex items-center justify-end gap-3">
        <a href="{{ route('admin.produk.index') }}"
           class="px-5 py-2.5 rounded-xl border border-line bg-panel text-ink font-bold text-xs hover:bg-cream/60 active:scale-95 transition">
            Batalkan
        </a>
        <button type="submit"
                :disabled="!bisaDisewa && !dapatDijual"
                :class="(!bisaDisewa && !dapatDijual) ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5 hover:shadow-lg hover:shadow-amber-500/20 active:scale-95'"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 text-white font-bold py-2.5 px-7 rounded-xl text-xs transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
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
        
        {{-- Kontrol Zoom & Close --}}
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

{{-- Script Alpine tetap tidak diubah --}}
<script>
document.addEventListener('alpine:init', () => {
    // Satu set stok: warna, variasi ukuran (+ rincian warna), dan stok manual.
    const buatSet = (s) => ({
        warna: [...((s && s.warna) || [])],
        customColorHex: '#CA9A3E',
        customColorName: '',
        // Setiap ukuran: { ukuran, stok, warna: [{ nama, stok }] }
        ukuranVariasi: ((s && s.ukuranVariasi) || []).map((r) => ({
            ukuran: r.ukuran || '',
            stok: Number(r.stok) || 0,
            warna: (r.warna || []).map((w) => ({ nama: w.nama || '', stok: Number(w.stok) || 0 })),
        })),
        stok: (s && s.stok !== undefined) ? s.stok : null,
    });
    const salin = (o) => JSON.parse(JSON.stringify(o));

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
        // setUtama = penjualan (atau satu-satunya set); setSewa = penyewaan (hanya kalau dijual & disewa)
        setUtama: buatSet(initial.setUtama),
        setSewa: buatSet(initial.setSewa),
        modeSebelumnya: null,
        hargaSewa: initial.hargaSewa ?? null,
        dp: initial.dp ?? null,
        hargaJual: initial.hargaJual ?? null,
        dendaPerHari: initial.dendaPerHari ?? null,
        perpanjanganPerHari: initial.perpanjanganPerHari ?? null,
        diskonSewa: initial.diskonSewa ?? 0,
        diskonJual: initial.diskonJual ?? 0,
        existingFoto: initial.existingFoto || {},
        previews: Object.assign({}, initial.previewFoto || {}),
        paletWarna: paletWarnaRaw || {}, // Simpan data palet warna

        // State untuk Lightbox
        lightbox: null,
        zoomScale: 1,

        init() {
            this.modeSebelumnya = this.modeJenis();
            this.$watch('bisaDisewa', () => this.sinkronJenis());
            this.$watch('dapatDijual', () => this.sinkronJenis());
        },

        get pisah() { return this.bisaDisewa && this.dapatDijual; },
        get judulUtama() { return this.bisaDisewa && !this.dapatDijual ? 'Penyewaan' : 'Penjualan'; },

        modeJenis() {
            if (this.bisaDisewa && this.dapatDijual) return 'dual';
            if (this.bisaDisewa) return 'sewa';
            if (this.dapatDijual) return 'jual';
            return 'kosong';
        },
        // Jaga supaya data tidak salah tempat saat admin mengubah jenis produk.
        sinkronJenis() {
            const baru = this.modeJenis();
            if (baru === 'kosong') return; // tunggu admin memilih lagi, data jangan diubah
            const lama = this.modeSebelumnya;
            this.modeSebelumnya = baru;
            if (lama === baru) return;

            if (lama === 'dual' && baru === 'sewa') {
                this.setUtama = salin(this.setSewa);          // set sewa jadi satu-satunya set
            } else if (lama === 'sewa' && baru === 'dual') {
                this.setSewa = salin(this.setUtama);          // data lama itu data sewa
                this.setUtama = buatSet({ stok: 0 });         // stok jual mulai kosong
            } else if (lama === 'jual' && baru === 'dual') {
                this.setSewa = buatSet({ stok: 0 });          // stok sewa mulai kosong
            }
            // dual -> jual dan jual <-> sewa: set utama dipakai apa adanya
        },

        get skuPlaceholder() {
            const select = document.querySelector('select[name="kategori_id"]');
            if (!select) return 'KBY-000';
            const opt = select.querySelector(`option[value="${this.kategoriId}"]`);
            const kode = opt?.dataset?.kode || 'KBY';
            return `${kode.toUpperCase()}-000`;
        },
        totalStok(s) {
            return s.ukuranVariasi.reduce((sum, row) => sum + this.stokUkuran(row), 0);
        },

        // Stok sebuah ukuran: jumlah stok warnanya kalau ada rincian, selain itu angka manual.
        stokUkuran(row) {
            if (row.warna && row.warna.length > 0) {
                return row.warna.reduce((s, w) => s + (Number(w.stok) || 0), 0);
            }
            return Number(row.stok) || 0;
        },

        // Pilihan warna di dropdown: warna set ini + warna yang sedang terpilih (kalau sudah dihapus dari daftar).
        opsiWarna(s, current) {
            const list = [...s.warna];
            if (current && !list.includes(current)) list.push(current);
            return list;
        },
        addWarnaUkuran(s, row) {
            if (s.warna.length === 0) return;
            const dipakai = row.warna.map((w) => w.nama);
            const berikut = s.warna.find((w) => !dipakai.includes(w)) || '';
            // Warna pertama mewarisi stok manual yang sudah diisi, supaya angkanya tidak hilang.
            const stokAwal = row.warna.length === 0 ? (Number(row.stok) || 0) : 0;
            row.warna.push({ nama: berikut, stok: stokAwal });
        },
        isiSemuaWarna(s, row) {
            const dipakai = row.warna.map((w) => w.nama);
            const adaStokManual = row.warna.length === 0 ? (Number(row.stok) || 0) : 0;
            s.warna.forEach((w, idx) => {
                if (!dipakai.includes(w)) {
                    row.warna.push({ nama: w, stok: idx === 0 && adaStokManual ? adaStokManual : 0 });
                }
            });
        },
        toggleWarnaPreset(s, label) {
            const idx = s.warna.indexOf(label);
            if (idx === -1) s.warna.push(label);
            else s.warna.splice(idx, 1);
        },
        addCustomWarna(s) {
            const name = s.customColorName.trim();
            if (!name) return;
            if (!s.warna.includes(name)) s.warna.push(name);
            // Simpan hex warna kustom ke dalam paletWarna sementara
            this.paletWarna[name] = s.customColorHex;
            s.customColorName = '';
        },
        getHexColor(name) {
            // Cari hex berdasarkan nama di paletWarna, jika tidak ada cek apakah itu warna kustom
            return this.paletWarna[name] || '#CCCCCC'; // Fallback ke abu-abu jika tidak ditemukan
        },
        addUkuranVariasi(s) { s.ukuranVariasi.push({ ukuran: '', stok: 0, warna: [] }); },
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