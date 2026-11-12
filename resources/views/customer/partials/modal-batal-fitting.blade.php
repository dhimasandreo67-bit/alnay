{{--
    Dialog pembatalan fitting oleh pelanggan (alasan wajib).
    Butuh: $reservasi
    Buka dengan: onclick="document.getElementById('dlg-batal-fitting').showModal()"

    Catatan: Tailwind membuang margin bawaan <dialog>, jadi "m-auto" wajib agar tampil di tengah layar.
--}}
<dialog id="dlg-batal-fitting" class="m-auto w-[92vw] max-w-md rounded-2xl border border-line/40 bg-white p-0 text-ink shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-sm">
    <form method="POST" action="{{ route('fitting.cancel', $reservasi->id) }}" class="p-6 space-y-4">
        @csrf
        @method('PATCH')

        <div class="flex items-start gap-3">
            <span class="w-10 h-10 shrink-0 rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M10.3 4.3L2.6 17.6A2 2 0 004.3 20.6h15.4a2 2 0 001.7-3L13.7 4.3a2 2 0 00-3.4 0z"/></svg>
            </span>
            <div>
                <h3 class="font-display text-lg font-medium text-ink/85">Batalkan jadwal fitting?</h3>
                <p class="text-[11px] text-ink/55 mt-0.5 leading-relaxed">
                    {{ $reservasi->kode_booking }}
                    @if ($reservasi->slot)
                        · {{ \Carbon\Carbon::parse($reservasi->slot->tanggal)->locale('id')->translatedFormat('d M Y') }}
                        pukul {{ \Carbon\Carbon::parse($reservasi->slot->jam_mulai)->format('H:i') }} WIB
                    @endif
                </p>
            </div>
        </div>

        <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
            Busana baru bisa diambil setelah fitting. Setelah dibatalkan, Anda perlu menjadwalkan ulang sebelum tanggal pengambilan.
            Kalau hanya ingin ganti hari atau jam, gunakan
            <a href="{{ route('fitting.edit', $reservasi->id) }}" class="font-semibold underline">Ubah Jadwal</a>.
        </div>

        <div x-data="{ teks: @js(old('alasan', '')) }">
            <label for="alasan-batal-fitting" class="block text-[11px] font-semibold text-ink/70 mb-1">Alasan pembatalan</label>
            <textarea id="alasan-batal-fitting" name="alasan" rows="3" required minlength="3" maxlength="200" x-model="teks"
                      placeholder="Contoh: Jadwal bentrok dengan pekerjaan"
                      class="w-full px-3 py-2 rounded-md border bg-white text-[12px] text-ink/80 placeholder:text-ink/30 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/20 transition-all resize-none {{ $errors->has('alasan') ? 'border-rose-300' : 'border-line/50' }}"></textarea>
            <div class="flex items-center justify-between mt-1">
                @error('alasan')
                    <p class="text-[10px] text-rose-600">{{ $message }}</p>
                @else
                    <span></span>
                @enderror
                <span class="text-[10px] text-ink/40 tabular-nums"><span x-text="teks.length"></span>/200</span>
            </div>

            <div class="flex flex-wrap gap-1.5 mt-2">
                @foreach (['Jadwal bentrok', 'Berhalangan hadir', 'Tidak jadi menyewa', 'Sakit'] as $cepat)
                    <button type="button" @click="teks = @js($cepat)"
                            class="px-2.5 py-1 rounded-full border border-line/50 bg-cream/50 text-[10px] font-medium text-ink/60 hover:border-gold/50 hover:text-gold transition-colors">{{ $cepat }}</button>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="this.closest('dialog').close()"
                    class="px-4 py-2.5 rounded-lg border border-line/60 bg-white text-[11px] font-semibold text-ink/65 hover:bg-cream/60 transition-colors">Kembali</button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-semibold transition-colors">Ya, Batalkan Fitting</button>
        </div>
    </form>
</dialog>

<script>
    (function () {
        const dlg = document.getElementById('dlg-batal-fitting');
        if (!dlg) return;

        // Klik di luar kotak = tutup
        dlg.addEventListener('click', function (e) {
            const r = dlg.getBoundingClientRect();
            const di_dalam = e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom;
            if (!di_dalam) dlg.close();
        });

        // Validasi alasan gagal di server -> buka lagi dialognya
        @if ($errors->has('alasan'))
            dlg.showModal();
        @endif
    })();
</script>