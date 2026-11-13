{{-- Panduan refund / return. Variabel opsional: $compact (bool) = tampil sebagai akordeon tertutup --}}
@php
    $compact = $compact ?? false;
    $batas = \App\Models\Refund::BATAS_HARI;

    $langkahPanduan = [
        ['Ajukan refund',     "Isi alasan, unggah 1–3 foto bukti, dan isi rekening tujuan. Maksimal {$batas} hari setelah barang diterima."],
        ['Ditinjau admin',    'Admin memeriksa pengajuan Anda (maksimal 1x24 jam) dan menentukan nominal refund.'],
        ['Kirim barang',      'Setelah disetujui, kemas barang dan kirim kembali. Gunakan no. resi penjemputan bila diberikan admin.'],
        ['Barang dicek',      'Admin mengecek kondisi barang yang sampai. Status berubah menjadi "Barang diterima".'],
        ['Dana kembali',      'Dana ditransfer ke rekening/e-wallet Anda. Bukti transfer tersedia di halaman detail refund.'],
    ];

    $faq = [
        ['Berapa nominal yang dikembalikan?', 'Nominal akhir ditentukan admin setelah pengajuan ditinjau dan barang dicek. Angka di formulir hanya usulan awal.'],
        ['Kapan dana masuk ke rekening?', 'Dana ditransfer setelah barang diterima dan lolos pengecekan. Pantau statusnya di halaman detail refund.'],
        ['Apakah pengajuan bisa dibatalkan?', 'Bisa, selama status masih "Diajukan". Setelah disetujui, pembatalan perlu lewat CS.'],
        ['Bagaimana jika pengajuan ditolak?', 'Alasan penolakan akan tampil di halaman detail. Jika ada yang kurang jelas, hubungi CS lewat WhatsApp.'],
        ['Bagaimana dengan pesanan sewa?', 'Refund hanya berlaku untuk pembelian. Pada sewa, DP tidak dapat dikembalikan.'],
    ];
@endphp

@if ($compact)
    <details class="group bg-panel border border-line/40 rounded-2xl overflow-hidden">
        <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between gap-3">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-ink/60">Panduan &amp; ketentuan refund</span>
            <x-icon name="chevron-right" :size="14" class="text-ink/40 transition-transform group-open:rotate-90" />
        </summary>
        <div class="px-5 pb-5 border-t border-line/40 pt-4">
@else
    <section class="bg-panel border border-line/40 rounded-2xl p-5 md:p-6">
@endif

    {{-- Alur --}}
    <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-4">Cara refund / return</h2>
    <ol class="space-y-4">
        @foreach ($langkahPanduan as $i => [$judul, $isi])
            <li class="flex gap-3">
                <span class="w-6 h-6 shrink-0 rounded-full bg-gold text-white text-[10px] font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                <div class="min-w-0">
                    <p class="text-[12px] font-bold text-ink/85">{{ $judul }}</p>
                    <p class="text-[11px] text-ink/60 leading-relaxed">{{ $isi }}</p>
                </div>
            </li>
        @endforeach
    </ol>

    {{-- Syarat --}}
    <div class="grid sm:grid-cols-2 gap-3 mt-6">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
            <p class="text-[11px] font-bold text-emerald-800 mb-2">Yang perlu disiapkan</p>
            <ul class="space-y-1.5 text-[11px] text-emerald-900 leading-relaxed list-disc pl-4">
                <li>Foto bukti jelas (kerusakan, ukuran/warna tidak sesuai, dll.)</li>
                <li>Barang dalam kondisi seperti saat diterima</li>
                <li>Kelengkapan barang (aksesori/tag) bila ada</li>
                <li>Kemasan yang aman agar barang tidak rusak saat dikirim</li>
            </ul>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4">
            <p class="text-[11px] font-bold text-rose-800 mb-2">Tidak dapat direfund</p>
            <ul class="space-y-1.5 text-[11px] text-rose-900 leading-relaxed list-disc pl-4">
                <li>Pesanan sewa (DP tidak dapat dikembalikan)</li>
                <li>Diajukan lebih dari {{ $batas }} hari setelah barang diterima</li>
                <li>Pengajuan tanpa foto bukti</li>
            </ul>
        </div>
    </div>

    {{-- FAQ --}}
    <h3 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mt-6 mb-2">Pertanyaan umum</h3>
    <div class="divide-y divide-line/40 border border-line/40 rounded-xl bg-white overflow-hidden">
        @foreach ($faq as [$tanya, $jawab])
            <details class="group/faq">
                <summary class="cursor-pointer list-none px-4 py-3 flex items-center justify-between gap-3 text-[12px] font-semibold text-ink/80">
                    {{ $tanya }}
                    <x-icon name="chevron-right" :size="12" class="shrink-0 text-ink/35 transition-transform group-open/faq:rotate-90" />
                </summary>
                <p class="px-4 pb-3 text-[11px] text-ink/60 leading-relaxed">{{ $jawab }}</p>
            </details>
        @endforeach
    </div>

    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya ingin bertanya tentang refund/return.') }}"
       target="_blank" rel="noopener"
       class="inline-block mt-4 text-[12px] font-bold text-gold hover:underline">Masih bingung? Tanya CS via WhatsApp</a>

@if ($compact)
        </div>
    </details>
@else
    </section>
@endif