{{--
Blok warna, variasi ukuran & stok untuk SATU set stok. Dipakai oleh _form.blade.php.
Parameter:
  $set        nama properti Alpine: 'setUtama' | 'setSewa'
  $akhiran    akhiran nama field: '' | '_sewa'
  $judulExpr  ekspresi Alpine untuk label judul, mis. "'Penjualan'" atau 'judulUtama'
  $sub        teks bantuan di bawah judul
  $kelasAksen kelas tambahan untuk pembeda visual (boleh kosong)
--}}
<div class="bg-panel p-8 rounded-3xl border border-line shadow-sm space-y-6 {{ $kelasAksen ?? '' }}">
    <div>
        <h3 class="text-xl font-black text-ink">
            Warna, Ukuran & Stok
            <span class="text-gold" x-text="'· ' + ({{ $judulExpr }})"></span>
        </h3>
        <p class="text-xs text-muted mt-1">{{ $sub }}</p>
    </div>

    {{-- Warna --}}
    <div class="space-y-3">
        <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Warna Busana</label>
        <div class="flex flex-wrap gap-3">
            @foreach($paletWarna as $label => $hex)
                <button type="button" title="{{ $label }}" @click="toggleWarnaPreset({{ $set }}, '{{ $label }}')"
                        class="w-10 h-10 rounded-full border-2 transition-all duration-300 hover:scale-110 active:scale-95 shadow-sm"
                        :class="{{ $set }}.warna.includes('{{ $label }}') ? 'border-gold ring-2 ring-gold/30 scale-110' : 'border-white'"
                        style="background-color: {{ $hex }};">
                    <template x-if="{{ $set }}.warna.includes('{{ $label }}')">
                        <svg class="w-4 h-4 mx-auto text-white drop-shadow-md" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </template>
                </button>
            @endforeach
        </div>

        <div class="flex items-center gap-3 pt-2">
            <input type="color" x-model="{{ $set }}.customColorHex" class="w-12 h-12 rounded-xl border border-line cursor-pointer p-1 bg-panel shadow-sm">
            <input type="text" x-model="{{ $set }}.customColorName" placeholder="Nama warna kustom..."
                   class="input-elegant flex-1 px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none"
                   @keydown.enter.prevent="addCustomWarna({{ $set }})">
            <button type="button" @click="addCustomWarna({{ $set }})"
                    class="px-5 py-3 rounded-xl border border-gold/30 bg-gold/10 text-gold font-bold text-sm hover:bg-gold/20 active:scale-95 transition-all whitespace-nowrap shadow-sm">
                + Tambah
            </button>
        </div>

        <div class="flex flex-wrap gap-2 pt-1">
            <template x-for="(w, i) in {{ $set }}.warna" :key="w + '-' + i">
                <span class="inline-flex items-center gap-2 bg-cream border border-line text-ink text-xs font-bold px-3 py-1.5 rounded-full shadow-sm animate-fade-up">
                    <span class="w-3 h-3 rounded-full border border-black/10 shadow-inner" :style="`background-color: ${getHexColor(w)}`"></span>
                    <span x-text="w"></span>
                    <input type="hidden" name="warna{{ $akhiran }}[]" :value="w">
                    <button type="button" @click="{{ $set }}.warna.splice(i, 1)" class="text-muted hover:text-rose-500 font-black transition-colors ml-1">&times;</button>
                </span>
            </template>
            <template x-if="{{ $set }}.warna.length === 0">
                <span class="text-xs text-muted italic bg-cream/50 px-3 py-2 rounded-lg border border-dashed border-line">Belum ada warna dipilih</span>
            </template>
        </div>
    </div>

    {{-- Variasi Ukuran, Warna & Stok --}}
    <div class="space-y-3 border-t border-line pt-5">
        <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Variasi Ukuran, Warna & Stok</label>
            <button type="button" @click="addUkuranVariasi({{ $set }})"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-gold hover:text-ink bg-gold/10 hover:bg-gold/20 px-3 py-1.5 rounded-lg transition-all active:scale-95">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Tambah Ukuran
            </button>
        </div>

        <p class="text-xs text-muted" x-show="{{ $set }}.ukuranVariasi.length === 0">
            Kosongkan jika produk ini tidak punya varian ukuran (mis. aksesoris All Size).
        </p>
        <p class="text-xs text-muted" x-show="{{ $set }}.ukuranVariasi.length > 0">
            Tambahkan rincian warna untuk membagi stok per warna (stok ukuran dihitung otomatis). Tanpa rincian warna, stok ukuran diisi manual.
        </p>

        <div class="space-y-4">
            <template x-for="(row, i) in {{ $set }}.ukuranVariasi" :key="'uk-' + i">
                <div class="rounded-2xl border border-line bg-cream/20 p-4 space-y-3 animate-fade-up">

                    <div class="flex items-center gap-3">
                        <select x-model="row.ukuran" :name="'ukuran{{ $akhiran }}[' + i + '][ukuran]'"
                                class="input-elegant flex-1 px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm font-bold focus:bg-white focus:outline-none cursor-pointer">
                            <option value="">Pilih ukuran...</option>
                            @foreach($opsiUkuran as $ukuran)
                                <option value="{{ $ukuran }}">{{ $ukuran }}</option>
                            @endforeach
                        </select>

                        <input type="number" step="1" min="0" x-show="row.warna.length === 0" x-model.number="row.stok" placeholder="Stok"
                               class="input-elegant w-32 px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm font-bold focus:bg-white focus:outline-none">

                        <div x-show="row.warna.length > 0" x-cloak
                             class="w-32 px-4 py-3 bg-cream border border-line rounded-xl text-sm text-center font-bold text-muted"
                             x-text="stokUkuran(row) + ' unit'"></div>

                        <input type="hidden" :name="'ukuran{{ $akhiran }}[' + i + '][stok]'" :value="stokUkuran(row)">

                        <button type="button" @click="{{ $set }}.ukuranVariasi.splice(i, 1)"
                                class="w-11 h-11 shrink-0 rounded-xl border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition-all active:scale-90 font-black">
                            &times;
                        </button>
                    </div>

                    <div class="pl-3 ml-1 border-l-2 border-gold/30 space-y-2">
                        <template x-for="(w, j) in row.warna" :key="'w-' + i + '-' + j">
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 shrink-0 rounded-full border border-black/10 shadow-inner" :style="`background-color: ${getHexColor(w.nama)}`"></span>
                                <select x-model="w.nama" :name="'ukuran{{ $akhiran }}[' + i + '][warna][' + j + '][nama]'"
                                        class="input-elegant flex-1 px-3 py-2.5 bg-cream/40 border border-line rounded-xl text-sm font-semibold focus:bg-white focus:outline-none cursor-pointer">
                                    <option value="">Pilih warna...</option>
                                    <template x-for="opt in opsiWarna({{ $set }}, w.nama)" :key="opt">
                                        <option :value="opt" x-text="opt" :selected="opt === w.nama"></option>
                                    </template>
                                </select>
                                <input type="number" step="1" min="0" x-model.number="w.stok" :name="'ukuran{{ $akhiran }}[' + i + '][warna][' + j + '][stok]'" placeholder="Stok"
                                       class="input-elegant w-28 px-3 py-2.5 bg-cream/40 border border-line rounded-xl text-sm font-bold focus:bg-white focus:outline-none">
                                <button type="button" @click="row.warna.splice(j, 1)"
                                        class="w-9 h-9 shrink-0 rounded-lg border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center transition-all active:scale-90 font-black">
                                    &times;
                                </button>
                            </div>
                        </template>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <button type="button" @click="addWarnaUkuran({{ $set }}, row)" :disabled="{{ $set }}.warna.length === 0"
                                    :class="{{ $set }}.warna.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gold/20'"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-gold bg-gold/10 px-2.5 py-1.5 rounded-lg transition-all active:scale-95">
                                + Warna
                            </button>
                            <button type="button" @click="isiSemuaWarna({{ $set }}, row)" x-show="{{ $set }}.warna.length > 0"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-ink/70 bg-cream border border-line px-2.5 py-1.5 rounded-lg hover:bg-white transition-all active:scale-95">
                                Isi semua warna
                            </button>
                            <p class="text-[11px] text-muted" x-show="{{ $set }}.warna.length === 0">Pilih warna busana di atas dulu untuk membagi stok per warna.</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Stok --}}
    <div class="border-t border-line pt-5">
        <div class="space-y-2 max-w-xs" x-show="{{ $set }}.ukuranVariasi.length === 0">
            <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Stok (unit) <span class="text-rose-500">*</span></label>
            <input type="number" step="1" min="0" name="stok{{ $akhiran }}" x-model.number="{{ $set }}.stok"
                   :required="{{ $set }}.ukuranVariasi.length === 0" placeholder="1"
                   class="input-elegant w-full px-4 py-3 bg-cream/40 border border-line rounded-xl text-sm focus:bg-white focus:outline-none">
        </div>
        <div class="flex items-center justify-between bg-gold/5 border border-gold/20 rounded-xl px-5 py-3.5" x-show="{{ $set }}.ukuranVariasi.length > 0">
            <span class="text-xs font-bold text-ink/70 uppercase tracking-wider">Total stok dari variasi</span>
            <span class="text-lg font-black text-gold" x-text="totalStok({{ $set }}) + ' unit'"></span>
        </div>
    </div>
</div>