{{-- Kartu lokasi fitting (dipakai halaman booking, ubah jadwal, dan detail) --}}
{{-- Lokasi Fitting --}}
                    <div class="pd-up bg-panel border border-line/40 rounded-xl p-5" style="--d: .15s;">
                        <h2 class="font-display text-base font-medium text-ink/80 mb-1">Lokasi Fitting</h2>
                        <p class="text-[11px] text-ink/50 mb-3">Sesi fitting dilakukan langsung di lokasi kami — bukan mengirim tim ke tempat Anda.</p>
                        <div class="flex items-start justify-between gap-3 p-3 rounded-lg bg-cream/50 border border-line/30 hover:border-gold/40 transition-all duration-300">
                            <div>
                                <p class="text-[12px] font-medium text-ink/80">ALNAY.ID</p>
                                <p class="text-[10px] text-ink/50 mt-0.5">{{ config('alnay.toko.alamat') }}</p>
                                <span class="inline-block mt-2 px-2 py-0.5 rounded bg-gold/10 text-gold text-[9px] font-medium">{{ config('alnay.toko.jam_buka') }}</span>
                            </div>
                            <a href="{{ config('alnay.toko.maps_url') ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode(config('alnay.toko.alamat') ?: 'ALNAY.ID Bandar Lampung') }}" target="_blank" rel="noopener" class="shrink-0 px-3 py-1.5 rounded-md border border-line/50 text-[10px] font-medium text-ink/60 hover:border-gold/50 hover:text-gold hover:-translate-y-px transition-all duration-300">Buka di Google Maps →</a>
                        </div>
                    </div>