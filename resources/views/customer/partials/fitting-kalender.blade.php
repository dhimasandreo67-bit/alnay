{{-- Langkah 1 (tanggal) & 2 (jam) --- dipakai bersama halaman booking & ubah jadwal. Butuh x-data="fittingForm(...)" di induknya. --}}
{{-- 1. Pilih Tanggal --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .2s;">
                        <h2 class="font-display text-base font-medium text-ink/80 mb-1">1. Pilih Tanggal</h2>
                        <p class="text-[11px] text-ink/50 mb-4">Pilih hari kedatangan Anda. Kuota dibatasi per jam (maks. {{ $kapasitas ?? 2 }} tamu), bukan per hari, jadi ketersediaan jam dilihat di langkah berikutnya.</p>

                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-semibold text-ink/80 capitalize" x-text="namaBulan"></span>
                            <div class="flex gap-1">
                                <button type="button" @click="bulanSebelumnya()" :disabled="!bisaMundur" class="w-6 h-6 rounded-md border border-line/50 bg-white flex items-center justify-center text-ink/40 hover:border-gold/60 hover:text-gold/80 disabled:opacity-40 disabled:cursor-not-allowed hover:-translate-x-0.5 transition-all duration-300">
                                    <x-icon name="chevron-right" :size="10" class="rotate-180" />
                                </button>
                                <button type="button" @click="bulanBerikutnya()" class="w-6 h-6 rounded-md border border-line/50 bg-white flex items-center justify-center text-ink/40 hover:border-gold/60 hover:text-gold/80 hover:translate-x-0.5 transition-all duration-300">
                                    <x-icon name="chevron-right" :size="10" />
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-7 gap-0.5 text-center transition-all duration-150" :class="gantiBulan ? 'opacity-0 translate-y-1' : 'opacity-100'">
                            <template x-for="(h, idx) in namaHari" :key="idx">
                                <div class="text-[9px] text-ink/40 py-1 font-medium" x-text="h"></div>
                            </template>
                            <template x-for="c in sel" :key="c.t">
                                <button type="button" @click="pilihTanggal(c.t)" :disabled="c.luar || nonaktif(c.t)" :class="kelasTanggal(c)" class="h-7 sm:h-8 mx-0.5 rounded-md text-[10px] font-medium transition-all duration-300 ease-out">
                                    <span x-text="c.hari"></span>
                                </button>
                            </template>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-3 text-[9px] text-ink/40">
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-ink/80"></span> Dipilih</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full ring-1 ring-gold/60"></span> Hari ini</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-ink/10"></span> Tidak bisa dipilih</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full border border-line/50"></span> Tersedia</div>
                        </div>

                        <p x-show="tanggalMaksimal" x-cloak class="mt-3 text-[10px] text-ink/50 leading-relaxed">
                            Tanggal setelah <span class="font-semibold text-ink/70" x-text="formatTanggal(tanggalMaksimal)"></span> tidak bisa dipilih karena fitting harus selesai sebelum busana diambil.
                        </p>
                        <p x-show="pesanTanggal" x-cloak x-transition x-text="pesanTanggal" class="mt-2 text-xs text-rose-600"></p>
                    </div>

                    {{-- 2. Pilih Jam --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .25s;">
                        <h2 class="font-display text-base font-medium text-ink/80 mb-1">2. Pilih Jam Kedatangan</h2>
                        <p class="text-[11px] text-ink/50 mb-4" x-text="tanggalDipilih ? 'Tanggal: ' + formatTanggal(tanggalDipilih) + ' — Toko ALNAY' : 'Pilih tanggal terlebih dahulu.'"></p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <template x-for="jam in slotJam" :key="jam">
                                <button type="button" @click="pilihJam(jam)" :disabled="!tanggalDipilih || !bisaJam(jam)" :class="kelasJam(jam)" class="h-[62px] rounded-md border flex flex-col items-center justify-center transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                                    <span class="text-[13px] font-semibold tabular-nums" x-text="jam"></span>
                                    <span class="text-[9px] mt-0.5" x-text="labelJam(jam)"></span>
                                    <span x-show="tanggalDipilih && !jamTutup(jam)" class="flex gap-1 mt-1">
                                        <template x-for="n in kapasitas" :key="n">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="n <= terisi(jam) ? 'bg-rose-400' : 'bg-emerald-400'"></span>
                                        </template>
                                    </span>
                                </button>
                            </template>
                        </div>

                        <div x-show="tanggalDipilih" x-cloak class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-3 text-[9px] text-ink/40">
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Kursi kosong</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-400"></span> Kursi terisi</div>
                            <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-300"></span> Sisa terbatas</div>
                            <span>· Maks. <span x-text="kapasitas"></span> tamu per jam</span>
                        </div>

                        <p x-show="semuaTidakTersedia" x-cloak class="mt-3 p-2.5 rounded-md bg-amber-50 border border-amber-200 text-[11px] text-amber-800">
                            Semua jam di tanggal ini sudah penuh, ditutup, atau terlewat. Silakan pilih tanggal lain.
                        </p>
                        <p x-show="!tanggalDipilih" class="text-[10px] text-ink/40 mt-3">Pilih tanggal dulu untuk melihat ketersediaan jam.</p>
                    </div>