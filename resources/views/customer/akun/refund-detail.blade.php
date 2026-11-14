@extends('layouts.akun')

@section('title', 'Detail Refund — ' . config('alnay.brand.nama'))

@section('content')
@php
    $st = $refund->status;
    $t = $refund->transaksi;
    $gagal = in_array($st, ['ditolak', 'dibatalkan'], true);
    $chip = $st === 'selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
        : ($gagal ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');

    $urutan = ['diajukan', 'disetujui', 'barang_dikirim', 'barang_diterima', 'selesai'];
    $posisi = array_search($st, $urutan, true);
    $langkah = [
        'diajukan'        => 'Diajukan',
        'disetujui'       => 'Disetujui',
        'barang_dikirim'  => 'Barang dikirim',
        'barang_diterima' => 'Barang diterima',
        'selesai'         => 'Dana kembali',
    ];

    $foto = $refund->fotoBuktiUrl();
    $buktiTf = $refund->buktiTransferUrl();
    $tfPdf = $buktiTf && str_ends_with(strtolower((string) $refund->bukti_transfer), '.pdf');

    $items = $t->detail ?? collect();
    $subtotalBarang = $items->sum(fn ($x) => (int) $x->jumlah * (int) $x->harga_satuan);
    $selisih = (int) $t->total - $subtotalBarang;
    $ongkir = (int) ($t->pengiriman?->biaya_kirim ?? 0);
    $lain = $selisih - $ongkir;

    // Pisahkan daftar "produk bermasalah" (dicatat di awal alasan) dari teks alasan
    $labelItem = fn ($d) => ($d->produk->nama ?? 'Produk')
        . ($d->ukuran || $d->warna ? ' (' . collect([$d->ukuran, $d->warna])->filter()->implode(', ') . ')' : '')
        . ' ×' . $d->jumlah;
    $alasanBersih = (string) $refund->alasan;
    $bermasalah = [];
    if (preg_match('/^\[Produk bermasalah:\s*(.*?)\]\s*/su', $alasanBersih, $mm)) {
        $bermasalah = array_values(array_filter(array_map('trim', explode(';', $mm[1]))));
        $alasanBersih = trim(substr($alasanBersih, strlen($mm[0])));
    }

    // Warna kartu status
    $nada = match ($st) {
        'diajukan'                          => 'bg-amber-50/70 border-amber-200',
        'disetujui', 'selesai'              => 'bg-emerald-50/70 border-emerald-200',
        'barang_dikirim', 'barang_diterima' => 'bg-sky-50/70 border-sky-200',
        'ditolak'                           => 'bg-rose-50/70 border-rose-200',
        default                             => 'bg-gray-50 border-line/50',
    };

    $keterangan = [
        'diajukan'        => 'Admin sedang meninjau pengajuan dan foto bukti Anda. Tidak ada yang perlu dilakukan saat ini.',
        'disetujui'       => 'Langkah Anda: kemas barang dan kirim kembali, lalu tekan tombol "Saya sudah mengirim barang".',
        'barang_dikirim'  => 'Admin menunggu barang sampai. Status akan diperbarui setelah barang diterima dan dicek.',
        'barang_diterima' => 'Barang sedang/telah dicek admin. Setelah lolos pengecekan, dana ditransfer ke rekening Anda.',
        'selesai'         => 'Proses refund selesai. Dana sudah dikirim ke rekening tujuan.',
    ];

    $kartu = 'break-inside-avoid mb-4 rounded-2xl border border-line/40 bg-panel p-4 sm:p-5';
    $judul = 'text-[11px] font-semibold uppercase tracking-wider text-ink/60';
    $btn = 'w-full sm:w-auto min-h-[44px] sm:min-h-0 px-4 py-2.5 sm:py-2 rounded-lg text-[12px] font-semibold transition-colors';
@endphp

<div class="bg-cream min-h-screen py-6 md:py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">

        {{-- Header --}}
        <div class="grid grid-cols-[auto_1fr] sm:grid-cols-[auto_1fr_auto] items-center gap-x-3 gap-y-2 mb-5">
            <a href="{{ route('akun.refund.index') }}" aria-label="Kembali"
               class="w-10 h-10 shrink-0 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <div class="min-w-0">
                <h1 class="font-display text-xl sm:text-2xl font-medium text-ink/90 leading-tight">Detail Refund</h1>
                <a href="{{ route('akun.pesanan.detail', $refund->transaksi_id) }}" class="text-[11px] text-gold font-mono hover:underline break-all">
                    {{ $t->kode_transaksi }}
                </a>
            </div>
            <span class="col-span-2 sm:col-span-1 w-fit px-2.5 py-1 rounded-md border text-[11px] font-bold {{ $chip }}">{{ $refund->label_status }}</span>
        </div>

        @foreach (['success' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'error' => 'bg-rose-50 border-rose-200 text-rose-700'] as $tipe => $kelas)
            @if (session($tipe))
                <div class="mb-4 px-4 py-3 rounded-xl border text-sm {{ $kelas }}">{{ session($tipe) }}</div>
            @endif
        @endforeach

        {{-- Progres (lebar penuh) --}}
        @if (! $gagal)
            <section class="mb-4 rounded-2xl border border-line/40 bg-panel px-3 sm:px-6 py-4 sm:py-5">
                <ol class="flex items-start">
                    @foreach ($langkah as $kunci => $label)
                        @php $selesaiLangkah = $posisi !== false && array_search($kunci, $urutan, true) <= $posisi; @endphp
                        <li class="flex-1 min-w-0 flex flex-col items-center text-center relative">
                            @if (! $loop->last)
                                <span class="absolute top-3 left-1/2 w-full h-px {{ $selesaiLangkah && array_search($kunci, $urutan, true) < $posisi ? 'bg-gold' : 'bg-line/70' }}"></span>
                            @endif
                            <span class="relative z-10 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold
                                {{ $selesaiLangkah ? 'bg-gold text-white' : 'bg-white border border-line/60 text-ink/40' }}">{{ $selesaiLangkah ? '✓' : $loop->iteration }}</span>
                            <span class="mt-1.5 px-0.5 text-[9px] sm:text-[10px] leading-tight font-medium {{ $selesaiLangkah ? 'text-ink/80' : 'text-ink/40' }}">{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>
                <p class="mt-3 sm:mt-4 text-[11px] sm:text-xs text-ink/55 leading-relaxed text-center">{{ $keterangan[$st] ?? '' }}</p>
            </section>
        @endif

        {{-- Konten: 2 kolom di desktop (mengalir kiri → kanan), 1 kolom di mobile --}}
        <div class="lg:columns-2 lg:gap-6">

            {{-- Status & tindakan --}}
            <section class="break-inside-avoid mb-4 rounded-2xl border p-4 sm:p-5 {{ $nada }}">
                @if ($st === 'diajukan')
                    <p class="text-[13px] font-semibold text-amber-900">Menunggu tinjauan admin</p>
                    <p class="text-[12px] text-amber-900/80 mt-0.5 mb-3">Pengajuan Anda sedang ditinjau (maksimal 1x24 jam). Anda masih bisa membatalkannya.</p>
                    <form action="{{ route('akun.refund.batal', $refund->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan refund ini?')">
                        @csrf @method('PATCH')
                        <button class="{{ $btn }} border border-rose-200 bg-white text-rose-600 hover:bg-rose-50">Batalkan Pengajuan</button>
                    </form>

                @elseif ($st === 'disetujui')
                    <p class="text-[13px] font-semibold text-emerald-800 mb-1">Refund disetujui — kirim barang kembali</p>
                    @if ($refund->no_resi_penjemputan)
                        <p class="text-[12px] text-emerald-900">No. resi penjemputan: <span class="font-mono font-bold break-all">{{ $refund->no_resi_penjemputan }}</span></p>
                    @endif
                    @if ($refund->catatan_negosiasi)
                        <p class="mt-1 text-[12px] text-emerald-900 whitespace-pre-line">{{ $refund->catatan_negosiasi }}</p>
                    @endif

                    <div class="mt-3 rounded-lg bg-white/70 border border-emerald-200 p-3">
                        <p class="text-[11px] font-bold text-emerald-800 mb-1.5">Panduan mengirim barang kembali</p>
                        <ol class="list-decimal pl-4 space-y-1 text-[11px] text-emerald-900 leading-relaxed">
                            <li>Periksa barang dan kelengkapannya sesuai pesanan.</li>
                            <li>Kemas rapi dan aman agar tidak rusak/kusut selama perjalanan.</li>
                            <li>Serahkan ke kurir, atau tunggu penjemputan bila admin memberi no. resi penjemputan.</li>
                            <li>Simpan bukti pengiriman/resi sampai refund selesai.</li>
                            <li>Tekan tombol di bawah setelah barang diserahkan.</li>
                        </ol>
                    </div>

                    <form action="{{ route('akun.refund.kirim', $refund->id) }}" method="POST" class="mt-3"
                          onsubmit="return confirm('Pastikan barang sudah diserahkan ke kurir / dikirim. Lanjutkan?')">
                        @csrf @method('PATCH')
                        <button class="{{ $btn }} bg-emerald-600 text-white font-bold hover:bg-emerald-700">Saya sudah mengirim barang</button>
                    </form>

                @elseif ($st === 'barang_dikirim')
                    <p class="text-[13px] font-semibold text-sky-900">Barang dalam perjalanan</p>
                    <p class="text-[12px] text-sky-900/80 mt-0.5">Admin akan memperbarui status setelah barang diterima dan dicek.</p>
                    @if ($refund->no_resi_penjemputan)
                        <p class="mt-1 text-[12px] text-sky-900">No. resi: <span class="font-mono font-bold break-all">{{ $refund->no_resi_penjemputan }}</span></p>
                    @endif

                @elseif ($st === 'barang_diterima')
                    <p class="text-[13px] font-semibold text-sky-900">Barang sudah diterima</p>
                    <p class="text-[12px] text-sky-900/80 mt-0.5">
                        {{ $refund->tanggal_barang_diterima ? 'Diterima pada ' . $refund->tanggal_barang_diterima->format('d M Y') . '. ' : '' }}
                        Dana sedang diproses dan akan ditransfer ke rekening Anda.
                    </p>

                @elseif ($st === 'selesai')
                    <p class="text-[13px] font-semibold text-emerald-900">
                        Dana Rp{{ number_format($refund->jumlah, 0, ',', '.') }} sudah ditransfer{{ $refund->tanggal_selesai ? ' pada ' . $refund->tanggal_selesai->format('d M Y') : '' }}.
                    </p>
                    <p class="text-[12px] text-emerald-800/80 mt-0.5 break-words">Ke {{ $refund->bank_tujuan }} · {{ $refund->no_rekening }} · a.n. {{ $refund->atas_nama }}</p>

                    @if ($buktiTf)
                        <div class="mt-3 rounded-xl bg-white/70 border border-emerald-200 p-3">
                            <p class="text-[11px] font-bold text-emerald-800 mb-2">Bukti transfer dari admin</p>
                            @unless ($tfPdf)
                                <a href="{{ $buktiTf }}" target="_blank" rel="noopener" class="block w-fit max-w-full">
                                    <img src="{{ $buktiTf }}" alt="Bukti transfer refund" loading="lazy"
                                         class="max-h-56 sm:max-h-64 max-w-full rounded-lg border border-emerald-200 bg-white object-contain"
                                         onerror="this.replaceWith(document.createTextNode('Gambar gagal dimuat. Gunakan tombol di bawah.'))">
                                </a>
                            @else
                                <p class="text-[11px] text-emerald-900/80">Bukti berformat PDF.</p>
                            @endunless
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[12px]">
                                <a href="{{ $buktiTf }}" target="_blank" rel="noopener" class="font-bold text-emerald-700 hover:underline py-1">{{ $tfPdf ? 'Buka PDF' : 'Lihat ukuran penuh' }}</a>
                                <a href="{{ $buktiTf }}" download class="font-bold text-emerald-700 hover:underline py-1">Unduh</a>
                            </div>
                        </div>
                    @else
                        <p class="mt-2 text-[12px] text-emerald-900">Bukti transfer belum tersedia. Hubungi CS bila dana belum Anda terima.</p>
                    @endif

                @elseif ($st === 'ditolak')
                    <p class="text-[13px] font-semibold text-rose-800 mb-1">Pengajuan ditolak</p>
                    <p class="text-[12px] text-rose-900 whitespace-pre-line">{{ $refund->alasan_penolakan ?: 'Tidak ada keterangan.' }}</p>
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya ingin menanyakan penolakan refund pesanan ' . $t->kode_transaksi) }}"
                       target="_blank" rel="noopener" class="inline-block mt-2 py-1 text-[12px] font-bold text-rose-700 hover:underline">Tanyakan ke CS via WhatsApp</a>

                @else
                    <p class="text-[12px] text-ink/60">Anda membatalkan pengajuan ini.</p>
                @endif
            </section>

            {{-- Barang dalam pesanan --}}
            <section class="{{ $kartu }}">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="{{ $judul }}">Barang dalam pesanan</h2>
                    <span class="text-[10px] text-ink/50">{{ $items->count() }} produk</span>
                </div>
                <div class="space-y-3">
                    @forelse ($items as $d)
                        <div class="{{ ! $loop->last ? 'pb-3 border-b border-line/30' : '' }}">
                            @include('customer.akun._refund-item', ['d' => $d, 'tandaiBermasalah' => count($bermasalah) > 0 && in_array($labelItem($d), $bermasalah, true)])
                        </div>
                    @empty
                        <p class="text-[12px] text-ink/50">Data barang tidak tersedia.</p>
                    @endforelse
                </div>

                <dl class="mt-4 pt-3 border-t border-line/40 space-y-1.5 text-[12px]">
                    @if ($items->count() > 1 || $selisih !== 0)
                        <div class="flex justify-between gap-3"><dt class="text-ink/50">Subtotal barang</dt><dd class="text-ink/80 tabular-nums">Rp{{ number_format($subtotalBarang, 0, ',', '.') }}</dd></div>
                    @endif
                    @if ($ongkir > 0)
                        <div class="flex justify-between gap-3"><dt class="text-ink/50">Ongkos kirim</dt><dd class="text-ink/80 tabular-nums">Rp{{ number_format($ongkir, 0, ',', '.') }}</dd></div>
                    @endif
                    @if ($lain !== 0)
                        <div class="flex justify-between gap-3"><dt class="text-ink/50">{{ $lain < 0 ? 'Potongan / diskon' : 'Biaya lain' }}</dt><dd class="text-ink/80 tabular-nums">{{ $lain < 0 ? '−' : '' }}Rp{{ number_format(abs($lain), 0, ',', '.') }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-3 text-[13px]"><dt class="font-semibold text-ink/70">Total pesanan</dt><dd class="font-bold text-ink/90 tabular-nums">Rp{{ number_format($t->total, 0, ',', '.') }}</dd></div>
                </dl>
            </section>

            {{-- Rincian pengajuan --}}
            <section class="{{ $kartu }}">
                <h2 class="{{ $judul }} mb-3">Rincian pengajuan</h2>

                @if (count($bermasalah) > 0)
                    <div class="mb-3 flex flex-wrap gap-1.5">
                        @foreach ($bermasalah as $b)
                            <span class="px-2 py-0.5 rounded-md border border-rose-200 bg-rose-50 text-[11px] font-semibold text-rose-700 break-words">{{ $b }}</span>
                        @endforeach
                    </div>
                @endif

                <dl class="grid grid-cols-[6.5rem_1fr] sm:grid-cols-[7.5rem_1fr] gap-x-3 gap-y-2.5 text-[12px]">
                    <dt class="text-ink/50">Diajukan</dt>
                    <dd class="font-semibold text-ink/85">{{ $refund->created_at->format('d M Y, H:i') }}</dd>

                    <dt class="text-ink/50">Alasan</dt>
                    <dd class="font-semibold text-ink/85 whitespace-pre-line break-words">{{ $alasanBersih !== '' ? $alasanBersih : '-' }}</dd>

                    <dt class="text-ink/50">Nominal refund</dt>
                    <dd class="font-semibold text-ink/85 tabular-nums">
                        Rp{{ number_format($refund->jumlah, 0, ',', '.') }}
                        @if ($st === 'diajukan')<span class="font-normal text-ink/45">(usulan, ditentukan admin)</span>@endif
                    </dd>

                    <dt class="text-ink/50">Dana ke</dt>
                    <dd class="font-semibold text-ink/85 break-words">{{ $refund->bank_tujuan }} · {{ $refund->no_rekening }} · {{ $refund->atas_nama }}</dd>
                </dl>

                @if ($foto)
                    <p class="mt-4 mb-2 text-[11px] text-ink/50">Foto bukti yang Anda unggah ({{ count($foto) }})</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($foto as $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg overflow-hidden border border-line/50 bg-cream">
                                <img src="{{ $url }}" alt="Bukti refund {{ $loop->iteration }}" loading="lazy" class="w-full h-full object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Riwayat --}}
            @if ($riwayat->isNotEmpty())
                <section class="{{ $kartu }}">
                    <h2 class="{{ $judul }} mb-4">Riwayat status</h2>
                    <div class="relative border-l-2 border-gold/30 ml-2 space-y-4">
                        @foreach ($riwayat as $r)
                            <div class="relative pl-4">
                                <span class="absolute -left-[5px] top-1 w-2 h-2 rounded-full ring-2 ring-panel bg-gold"></span>
                                <p class="text-[12px] font-bold text-ink/85">{{ \App\Models\Refund::LABEL[$r->status_baru] ?? $r->status_baru }}</p>
                                <p class="text-[10px] text-ink/50">{{ ($r->waktu_ubah ?? $r->created_at)->format('d M Y, H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- Panduan (lebar penuh, 2 kolom di dalamnya) --}}
        @include('customer.akun._refund-panduan', ['compact' => true, 'grid' => true])
    </div>
</div>
@endsection