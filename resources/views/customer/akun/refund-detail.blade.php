@extends('layouts.akun')

@section('title', 'Detail Refund — ' . config('alnay.brand.nama'))

@section('content')
@php
    $st = $refund->status;
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
    $items = $refund->transaksi->detail ?? collect();
    $subtotalBarang = $items->sum(fn ($x) => (int) $x->jumlah * (int) $x->harga_satuan);
    $selisih = (int) $refund->transaksi->total - $subtotalBarang;

    // Penjelasan singkat "apa yang terjadi sekarang" per status
    $keterangan = [
        'diajukan'        => 'Admin sedang meninjau pengajuan dan foto bukti Anda. Tidak ada yang perlu dilakukan saat ini.',
        'disetujui'       => 'Langkah Anda: kemas barang dan kirim kembali, lalu tekan tombol "Saya sudah mengirim barang".',
        'barang_dikirim'  => 'Admin menunggu barang sampai. Status akan diperbarui setelah barang diterima dan dicek.',
        'barang_diterima' => 'Barang sedang/telah dicek admin. Setelah lolos pengecekan, dana ditransfer ke rekening Anda.',
        'selesai'         => 'Proses refund selesai. Dana sudah dikirim ke rekening tujuan.',
        'ditolak'         => 'Pengajuan tidak dapat diproses. Lihat alasan di bawah atau hubungi CS.',
        'dibatalkan'      => 'Pengajuan ini dibatalkan oleh Anda.',
    ];
@endphp

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4">

        <div class="flex items-center gap-3 mb-5">
            <a href="{{ route('akun.refund.index') }}" aria-label="Kembali"
               class="w-9 h-9 shrink-0 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="font-display text-xl md:text-2xl font-medium text-ink/90 leading-tight">Detail Refund</h1>
                <a href="{{ route('akun.pesanan.detail', $refund->transaksi_id) }}" class="text-[11px] text-gold font-mono hover:underline">
                    {{ $refund->transaksi->kode_transaksi }}
                </a>
            </div>
            <span class="px-2.5 py-1 rounded-md border text-[11px] font-bold shrink-0 {{ $chip }}">{{ $refund->label_status }}</span>
        </div>

        @foreach (['success' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'error' => 'bg-rose-50 border-rose-200 text-rose-700'] as $tipe => $kelas)
            @if (session($tipe))
                <div class="mb-4 px-4 py-3 rounded-xl border text-sm {{ $kelas }}">{{ session($tipe) }}</div>
            @endif
        @endforeach

        <div class="bg-panel border border-line/40 rounded-2xl shadow-sm overflow-hidden mb-4">

            {{-- Progres --}}
            @if (! $gagal)
                <div class="px-5 md:px-6 py-5">
                    <ol class="flex items-start">
                        @foreach ($langkah as $kunci => $label)
                            @php $selesaiLangkah = $posisi !== false && array_search($kunci, $urutan, true) <= $posisi; @endphp
                            <li class="flex-1 flex flex-col items-center text-center relative">
                                @if (! $loop->last)
                                    <span class="absolute top-3 left-1/2 w-full h-px {{ $selesaiLangkah && array_search($kunci, $urutan, true) < $posisi ? 'bg-gold' : 'bg-line/70' }}"></span>
                                @endif
                                <span class="relative z-10 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold
                                    {{ $selesaiLangkah ? 'bg-gold text-white' : 'bg-white border border-line/60 text-ink/40' }}">{{ $selesaiLangkah ? '✓' : $loop->iteration }}</span>
                                <span class="mt-1.5 text-[10px] font-medium {{ $selesaiLangkah ? 'text-ink/80' : 'text-ink/40' }}">{{ $label }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <p class="mt-4 text-[11px] text-ink/55 leading-relaxed text-center">{{ $keterangan[$st] ?? '' }}</p>
                </div>
            @endif

            {{-- Tindakan sesuai status --}}
            @if ($st === 'diajukan')
                <div class="px-5 md:px-6 py-4 border-t border-line/40 bg-amber-50/50">
                    <p class="text-[12px] text-amber-900 mb-3">Pengajuan Anda sedang ditinjau admin (maksimal 1x24 jam).</p>
                    <form action="{{ route('akun.refund.batal', $refund->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan refund ini?')">
                        @csrf @method('PATCH')
                        <button class="px-4 py-2 rounded-lg border border-rose-200 text-rose-600 text-[12px] font-semibold hover:bg-rose-50 transition-colors">Batalkan Pengajuan</button>
                    </form>
                </div>
            @elseif ($st === 'disetujui')
                <div class="px-5 md:px-6 py-4 border-t border-line/40 bg-emerald-50/60">
                    <p class="text-[13px] font-semibold text-emerald-800 mb-1">Refund disetujui — kirim barang kembali</p>
                    @if ($refund->no_resi_penjemputan)
                        <p class="text-[12px] text-emerald-900">No. resi penjemputan: <span class="font-mono font-bold">{{ $refund->no_resi_penjemputan }}</span></p>
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
                        <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-[12px] font-bold hover:bg-emerald-700 transition-colors">Saya sudah mengirim barang</button>
                    </form>
                </div>
            @elseif ($st === 'barang_dikirim')
                <div class="px-5 md:px-6 py-4 border-t border-line/40 bg-sky-50/60 text-[12px] text-sky-900">
                    Barang Anda dalam perjalanan. Admin akan memperbarui status setelah barang diterima dan dicek.
                    @if ($refund->no_resi_penjemputan)
                        <p class="mt-1">No. resi: <span class="font-mono font-bold">{{ $refund->no_resi_penjemputan }}</span></p>
                    @endif
                </div>
            @elseif ($st === 'barang_diterima')
                <div class="px-5 md:px-6 py-4 border-t border-line/40 bg-sky-50/60 text-[12px] text-sky-900">
                    Barang sudah diterima{{ $refund->tanggal_barang_diterima ? ' pada ' . $refund->tanggal_barang_diterima->format('d M Y') : '' }}.
                    Dana sedang diproses dan akan ditransfer ke rekening Anda.
                </div>
            @elseif ($st === 'selesai')
                <div class="px-5 md:px-6 py-4 border-t border-line/40 bg-emerald-50/60 text-[12px] text-emerald-900">
                    <p class="font-semibold">Dana Rp{{ number_format($refund->jumlah, 0, ',', '.') }} sudah ditransfer
                        {{ $refund->tanggal_selesai ? 'pada ' . $refund->tanggal_selesai->format('d M Y') : '' }}.</p>
                    @if ($refund->bukti_transfer)
                        <a href="{{ asset('storage/' . $refund->bukti_transfer) }}" target="_blank" rel="noopener" class="inline-block mt-1 font-bold text-emerald-700 hover:underline">Lihat bukti transfer</a>
                    @endif
                </div>
            @elseif ($st === 'ditolak')
                <div class="px-5 md:px-6 py-4 border-t border-line/40 bg-rose-50/60">
                    <p class="text-[13px] font-semibold text-rose-800 mb-1">Pengajuan ditolak</p>
                    <p class="text-[12px] text-rose-900 whitespace-pre-line">{{ $refund->alasan_penolakan ?: 'Tidak ada keterangan.' }}</p>
                    <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya ingin menanyakan penolakan refund pesanan ' . $refund->transaksi->kode_transaksi) }}"
                       target="_blank" rel="noopener" class="inline-block mt-2 text-[12px] font-bold text-rose-700 hover:underline">Tanyakan ke CS via WhatsApp</a>
                </div>
            @elseif ($st === 'dibatalkan')
                <div class="px-5 md:px-6 py-4 border-t border-line/40 bg-gray-50 text-[12px] text-ink/60">Anda membatalkan pengajuan ini.</div>
            @endif

            {{-- Barang yang direfund --}}
            <section class="p-5 md:p-6 border-t border-line/40">
                <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-1">Barang dalam pesanan ({{ $items->count() }} produk)</h2>
                @if ($items->count() > 1)
                    <p class="text-[11px] text-ink/50 mb-3 leading-relaxed">Produk yang diajukan refund tercantum di awal alasan pada rincian di bawah.</p>
                @else
                    <div class="mb-2"></div>
                @endif
                <div class="space-y-3">
                    @forelse ($items as $d)
                        <div class="{{ ! $loop->last ? 'pb-3 border-b border-line/30' : '' }}">
                            @include('customer.akun._refund-item', ['d' => $d])
                        </div>
                    @empty
                        <p class="text-[12px] text-ink/50">Data barang tidak tersedia.</p>
                    @endforelse
                </div>
            </section>

            {{-- Rincian --}}
            <section class="p-5 md:p-6 border-t border-line/40">
                <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Rincian pengajuan</h2>
                <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-[12px]">
                    <dt class="text-ink/50">Diajukan</dt><dd class="font-semibold text-ink/85">{{ $refund->created_at->format('d M Y, H:i') }}</dd>
                    @if ($items->count() > 1 || $selisih !== 0)
                        <dt class="text-ink/50">Subtotal barang</dt>
                        <dd class="text-ink/80 tabular-nums">Rp{{ number_format($subtotalBarang, 0, ',', '.') }}</dd>
                    @endif
                    @if ($selisih !== 0)
                        <dt class="text-ink/50">Ongkir / biaya lain</dt>
                        <dd class="text-ink/80 tabular-nums">{{ $selisih < 0 ? '−' : '' }}Rp{{ number_format(abs($selisih), 0, ',', '.') }}</dd>
                    @endif
                    <dt class="text-ink/50">Total pesanan</dt>
                    <dd class="font-semibold text-ink/85 tabular-nums">Rp{{ number_format($refund->transaksi->total, 0, ',', '.') }}</dd>
                    <dt class="text-ink/50">Alasan</dt><dd class="font-semibold text-ink/85 whitespace-pre-line">{{ $refund->alasan }}</dd>
                    <dt class="text-ink/50">Nominal refund</dt>
                    <dd class="font-semibold text-ink/85 tabular-nums">Rp{{ number_format($refund->jumlah, 0, ',', '.') }}
                        @if ($st === 'diajukan')<span class="font-normal text-ink/45">(usulan, ditentukan admin)</span>@endif</dd>
                    <dt class="text-ink/50">Dana ke</dt>
                    <dd class="font-semibold text-ink/85">{{ $refund->bank_tujuan }} · {{ $refund->no_rekening }} · {{ $refund->atas_nama }}</dd>
                </dl>

                @if ($foto)
                    <p class="mt-4 mb-2 text-[11px] text-ink/50">Foto bukti yang Anda unggah</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($foto as $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="w-20 h-20 rounded-lg overflow-hidden border border-line/50 bg-cream">
                                <img src="{{ $url }}" alt="Bukti refund" class="w-full h-full object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Riwayat --}}
            @if ($riwayat->isNotEmpty())
                <section class="p-5 md:p-6 border-t border-line/40">
                    <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-4">Riwayat status</h2>
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

        {{-- Panduan --}}
        @include('customer.akun._refund-panduan', ['compact' => true])
    </div>
</div>
@endsection