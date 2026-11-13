{{--
    Dialog konfirmasi HAPUS reservasi fitting (satu dialog dipakai semua kartu fitting).
    Pasang SEKALI per halaman: @include('customer.partials.modal-hapus-fitting')

    Pemicu (lihat customer/akun/_fitting-terdekat.blade.php):
      <button type="button" data-hapus-fitting
              data-action="{{ route('fitting.destroy', $id) }}"
              data-kode="ALN-FT-XXXX" data-jadwal="13 Nov 2026 pukul 12:00 WIB"
              data-status="pending|batal" data-edit-url="{{ route('fitting.edit', $id) }}">

    Catatan: Tailwind membuang margin bawaan <dialog>, jadi "m-auto" wajib agar tampil di tengah layar.
--}}
<dialog id="dlg-hapus-fitting" class="m-auto w-[92vw] max-w-md rounded-2xl border border-line/40 bg-white p-0 text-ink shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-sm">
    <form id="form-hapus-fitting" method="POST" action="" class="p-6 space-y-4">
        @csrf
        @method('DELETE')

        <div class="flex items-start gap-3">
            <span class="w-10 h-10 shrink-0 rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </span>
            <div>
                <h3 class="font-display text-lg font-medium text-ink/85">Hapus reservasi fitting?</h3>
                <p class="text-[11px] text-ink/55 mt-0.5 leading-relaxed">
                    <span id="hf-kode"></span><span id="hf-jadwal"></span>
                </p>
            </div>
        </div>

        {{-- Hanya untuk booking yang masih terjadwal --}}
        <div id="hf-catatan-terjadwal" class="hidden p-3 rounded-lg bg-amber-50 border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
            Jadwal ini akan dilepas. Busana baru bisa diambil setelah fitting, jadi Anda perlu menjadwalkan ulang sebelum tanggal pengambilan.
            Kalau hanya ingin ganti hari atau jam, gunakan
            <a id="hf-ubah" href="#" class="font-semibold underline">Ubah Jadwal</a>.
        </div>

        <p class="text-[11px] text-ink/60 leading-relaxed">
            Data reservasi akan <b class="text-ink/80">hilang permanen</b> dan tidak bisa dikembalikan.
        </p>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="this.closest('dialog').close()"
                    class="px-4 py-2.5 rounded-lg border border-line/60 bg-white text-[11px] font-semibold text-ink/65 hover:bg-cream/60 transition-colors">Kembali</button>
            <button type="submit" id="hf-ya"
                    class="px-4 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white text-[11px] font-semibold transition-colors">Ya, Hapus</button>
        </div>
    </form>
</dialog>

<script>
    (function () {
        const dlg   = document.getElementById('dlg-hapus-fitting');
        if (!dlg) return;
        const form  = document.getElementById('form-hapus-fitting');
        const kode  = document.getElementById('hf-kode');
        const jadwal = document.getElementById('hf-jadwal');
        const catatan = document.getElementById('hf-catatan-terjadwal');
        const linkUbah = document.getElementById('hf-ubah');
        const btnYa = document.getElementById('hf-ya');

        // Delegasi klik: tetap jalan walau kartu fitting dirender ulang
        document.addEventListener('click', function (e) {
            const pemicu = e.target.closest('[data-hapus-fitting]');
            if (!pemicu) return;
            e.preventDefault();

            form.action = pemicu.dataset.action;
            kode.textContent = pemicu.dataset.kode || '';
            jadwal.textContent = pemicu.dataset.jadwal ? ' · ' + pemicu.dataset.jadwal : '';

            const terjadwal = pemicu.dataset.status !== 'batal';
            catatan.classList.toggle('hidden', !terjadwal);
            if (terjadwal && pemicu.dataset.editUrl) linkUbah.href = pemicu.dataset.editUrl;

            btnYa.disabled = false;
            dlg.showModal();
        });

        // Cegah klik ganda saat dikirim
        form.addEventListener('submit', function () { btnYa.disabled = true; });

        // Klik di luar kotak = tutup
        dlg.addEventListener('click', function (e) {
            const r = dlg.getBoundingClientRect();
            const di_dalam = e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom;
            if (!di_dalam) dlg.close();
        });
    })();
</script>