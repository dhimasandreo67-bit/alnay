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
        'barang_dikirim'  => 'Dikirim',
        'barang_diterima' => 'Diterima',
        'selesai'         => 'Dana Kembali',
    ];

    $foto = $refund->fotoBuktiUrl();
    $buktiTf = $refund->buktiTransferUrl();
    $tfPdf = $buktiTf && str_ends_with(strtolower((string) $refund->bukti_transfer), '.pdf');

    $items = $t->detail ?? collect();
    $subtotalBarang = $items->sum(fn ($x) => (int) $x->jumlah * (int) $x->harga_satuan);
    $selisih = (int) $t->total - $subtotalBarang;
    $ongkir = (int) ($t->pengiriman?->biaya_kirim ?? 0);
    $lain = $selisih - $ongkir;

    // Pisahkan daftar "produk bermasalah"
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
        'diajukan'                          => 'bg-amber-50/50 border-amber-200/60',
        'disetujui', 'selesai'              => 'bg-emerald-50/50 border-emerald-200/60',
        'barang_dikirim', 'barang_diterima' => 'bg-sky-50/50 border-sky-200/60',
        'ditolak'                           => 'bg-rose-50/50 border-rose-200/60',
        default                             => 'bg-gray-50 border-gray-200',
    };

    $keterangan = [
        'diajukan'        => 'Admin sedang meninjau pengajuan dan foto bukti Anda. Tidak ada yang perlu dilakukan saat ini.',
        'disetujui'       => 'Langkah Anda: kemas barang dan kirim kembali sesuai metode yang ditentukan, lalu konfirmasi di bawah.',
        'barang_dikirim'  => 'Admin menunggu barang sampai. Status akan diperbarui setelah barang diterima dan dicek.',
        'barang_diterima' => 'Barang sedang/telah dicek admin. Setelah lolos pengecekan, dana ditransfer ke rekening Anda.',
        'selesai'         => 'Proses refund selesai. Dana sudah dikirim ke rekening tujuan.',
    ];

    $kartu = 'bg-panel rounded-2xl border border-line/40 p-5 sm:p-6 shadow-sm';
    $judul = 'text-[11px] font-bold uppercase tracking-wider text-ink/50 mb-4';
    $btn = 'w-full sm:w-auto min-h-[44px] px-5 py-2.5 rounded-xl text-[13px] font-bold transition-all shadow-sm';
@endphp

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('akun.dashboard') }}#refund" aria-label="Kembali"
                class="w-10 h-10 shrink-0 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/60 hover:text-gold hover:border-gold/50 hover:bg-gold/5 transition-all">
                    <x-icon name="chevron-right" :size="16" class="rotate-180" />
                </a>
                <div>
                    <h1 class="font-display text-2xl font-bold text-ink">Detail Refund</h1>
                    <a href="{{ route('akun.pesanan.detail', $refund->transaksi_id) }}" class="text-xs text-gold font-mono font-semibold hover:underline">
                        {{ $t->kode_transaksi }}
                    </a>
                </div>
            </div>
            <span class="w-fit px-3 py-1.5 rounded-lg border text-xs font-bold {{ $chip }} uppercase tracking-wide">
                {{ $refund->label_status }}
            </span>
        </div>

        @foreach (['success' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'error' => 'bg-rose-50 border-rose-200 text-rose-700'] as $tipe => $kelas)
            @if (session($tipe))
                <div class="mb-6 px-4 py-3 rounded-xl border text-sm font-medium shadow-sm {{ $kelas }}">{{ session($tipe) }}</div>
            @endif
        @endforeach

        {{-- PROGRES BAR --}}
        @if (! $gagal)
            <section class="mb-6 bg-panel rounded-2xl border border-line/40 p-5 sm:p-6 shadow-sm">
                <ol class="flex items-start">
                    @foreach ($langkah as $kunci => $label)
                        @php $selesaiLangkah = $posisi !== false && array_search($kunci, $urutan, true) <= $posisi; @endphp
                        <li class="flex-1 min-w-0 flex flex-col items-center text-center relative">
                            @if (! $loop->last)
                                <span class="absolute top-[14px] left-1/2 w-full h-[2px] {{ $selesaiLangkah && array_search($kunci, $urutan, true) < $posisi ? 'bg-gold' : 'bg-line/60' }}"></span>
                            @endif
                            <span class="relative z-10 w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold shadow-sm
                                {{ $selesaiLangkah ? 'bg-gold text-white ring-4 ring-amber-50' : 'bg-white border-2 border-line/60 text-ink/40' }}">
                                {{ $selesaiLangkah ? '✓' : $loop->iteration }}
                            </span>
                            <span class="mt-2 text-[10px] sm:text-[11px] leading-tight font-semibold {{ $selesaiLangkah ? 'text-ink' : 'text-ink/50' }}">
                                {{ $label }}
                            </span>
                        </li>
                    @endforeach
                </ol>
                <p class="mt-5 text-xs text-ink/60 leading-relaxed text-center bg-cream/50 p-3 rounded-lg">{{ $keterangan[$st] ?? '' }}</p>
            </section>
        @endif

        {{-- GRID KONTEN --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

            {{-- KOLOM KIRI --}}
            <div class="space-y-6">
                
                {{-- Status & Tindakan --}}
                <section class="rounded-2xl border p-5 sm:p-6 {{ $nada }}">
                    @if ($st === 'diajukan')
                        <p class="text-[14px] font-bold text-amber-900">Menunggu tinjauan admin</p>
                        <p class="text-[12px] text-amber-800/80 mt-1 mb-4 leading-relaxed">Pengajuan Anda sedang ditinjau (maksimal 1x24 jam). Anda masih bisa membatalkannya.</p>
                        <form action="{{ route('akun.refund.batal', $refund->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan refund ini?')">
                            @csrf @method('PATCH')
                            <button class="{{ $btn }} border border-rose-200 bg-white text-rose-600 hover:bg-rose-50">Batalkan Pengajuan</button>
                        </form>

                    @elseif ($st === 'disetujui')
                        <p class="text-[14px] font-bold text-emerald-900 mb-1">Refund disetujui — {{ $refund->metode_pengembalian === 'dijemput' ? 'siapkan barang untuk dijemput kurir' : 'kirim barang kembali' }}</p>
                        @if ($refund->label_metode)
                            <p class="text-[12px] text-emerald-800">Metode: <b>{{ $refund->label_metode }}</b></p>
                        @endif
                        @if ($refund->metode_pengembalian === 'dijemput' && $refund->kurir_retur)
                            <p class="text-[12px] text-emerald-800">Kurir penjemput: <b>{{ $refund->kurir_retur }}</b></p>
                        @endif
                        @if ($refund->no_resi_penjemputan)
                            <p class="text-[12px] text-emerald-800">No. resi penjemputan: <span class="font-mono font-bold">{{ $refund->no_resi_penjemputan }}</span></p>
                        @endif
                        @if ($refund->batas_kirim)
                            <p class="mt-2 text-[12px] font-bold text-rose-700 bg-rose-50 p-2 rounded-lg inline-block">{{ $refund->metode_pengembalian === 'dijemput' ? 'Serahkan barang ke kurir' : 'Kirim barang' }} paling lambat {{ $refund->batas_kirim->format('d M Y') }}.</p>
                        @endif

                        @if ($refund->metode_pengembalian === 'kirim_sendiri' && ($alamatRetur = \App\Models\Refund::alamatRetur()))
                            <div class="mt-4 rounded-xl bg-white/80 border border-emerald-200 p-4 text-[12px] text-emerald-900 shadow-sm">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 mb-1">Kirim barang ke</p>
                                <p class="font-bold">{{ $alamatRetur['nama'] ?? '' }}</p>
                                <p class="whitespace-pre-line mt-0.5">{{ $alamatRetur['alamat'] }}</p>
                                @if (!empty($alamatRetur['telepon']))<p class="mt-1">Telp: {{ $alamatRetur['telepon'] }}</p>@endif
                            </div>
                        @endif

                        <div class="mt-4 rounded-xl bg-white/80 border border-emerald-200 p-4 shadow-sm">
                            <p class="text-[11px] font-bold text-emerald-800 mb-2">Panduan mengirim barang kembali</p>
                            <ol class="list-decimal pl-4 space-y-1.5 text-[11px] text-emerald-900/80 leading-relaxed">
                                <li>Periksa barang dan kelengkapannya sesuai pesanan.</li>
                                <li>Kemas rapi dan aman agar tidak rusak selama perjalanan.</li>
                                <li>{{ $refund->metode_pengembalian === 'kirim_sendiri' ? 'Antar ke agen kurir, simpan struk dan resinya.' : 'Tunggu kurir menjemput. Pengembalian tanpa biaya.' }}</li>
                                <li>Simpan bukti pengiriman/resi sampai refund selesai.</li>
                                <li>Tekan tombol di bawah setelah barang diserahkan.</li>
                            </ol>
                        </div>

                        <form action="{{ route('akun.refund.kirim', $refund->id) }}" method="POST" class="mt-4 space-y-3" enctype="multipart/form-data"
                              onsubmit="return confirm('{{ $refund->metode_pengembalian === 'dijemput' ? 'Pastikan barang sudah diambil oleh kurir. Lanjutkan?' : 'Pastikan barang sudah diserahkan ke kurir / dikirim. Lanjutkan?' }}')">
                            @csrf @method('PATCH')
                            @if ($refund->metode_pengembalian === 'kirim_sendiri')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[11px] font-bold text-emerald-800 mb-1">Nama Kurir <span class="text-rose-500">*</span></label>
                                        <input type="text" name="kurir_retur" maxlength="50" required value="{{ old('kurir_retur') }}" placeholder="JNE / J&T / SiCepat" class="w-full rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                        @error('kurir_retur')<p class="mt-1 text-[10px] text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-emerald-800 mb-1">No. Resi <span class="text-rose-500">*</span></label>
                                        <input type="text" name="no_resi_retur" maxlength="100" required value="{{ old('no_resi_retur') }}" class="w-full rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                        @error('no_resi_retur')<p class="mt-1 text-[10px] text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-emerald-800 mb-1">Ongkir yang Anda bayar (Rp) <span class="text-rose-500">*</span> <span class="font-normal text-emerald-700">(akan diganti)</span></label>
                                    <input type="text" inputmode="numeric" data-rupiah name="biaya_ongkir_retur" required value="{{ old('biaya_ongkir_retur') }}" class="w-full rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                    @error('biaya_ongkir_retur')<p class="mt-1 text-[10px] text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-emerald-800 mb-1">Foto Struk / Resi <span class="text-rose-500">*</span> <span class="font-normal text-emerald-700">(Maks 3 MB)</span></label>
                                    <input type="file" name="bukti_pengiriman" accept="image/*" required class="block w-full text-xs text-emerald-800 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200 cursor-pointer">
                                    @error('bukti_pengiriman')<p class="mt-1 text-[10px] text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            @endif
                            <button class="{{ $btn }} bg-emerald-600 text-white hover:bg-emerald-700 shadow-emerald-500/30 w-full">{{ $refund->metode_pengembalian === 'dijemput' ? 'Barang sudah diambil kurir' : 'Saya sudah mengirim barang' }}</button>
                        </form>

                    @elseif ($st === 'barang_dikirim')
                        <p class="text-[14px] font-bold text-sky-900">Barang dalam perjalanan</p>
                        <p class="text-[12px] text-sky-800/80 mt-1">Admin akan memperbarui status setelah barang diterima dan dicek.</p>
                        @if ($refund->no_resi_retur)
                            <p class="mt-2 text-[12px] text-sky-900 bg-sky-100/50 p-2.5 rounded-lg">Resi ({{ $refund->kurir_retur }}): <span class="font-mono font-bold">{{ $refund->no_resi_retur }}</span></p>
                        @elseif ($refund->no_resi_penjemputan)
                            <p class="mt-2 text-[12px] text-sky-900 bg-sky-100/50 p-2.5 rounded-lg">{{ $refund->kurir_retur ? $refund->kurir_retur . ' · ' : '' }}Resi Penjemputan: <span class="font-mono font-bold">{{ $refund->no_resi_penjemputan }}</span></p>
                        @endif

                    @elseif ($st === 'barang_diterima')
                        <p class="text-[14px] font-bold text-sky-900">Barang sudah diterima</p>
                        <p class="text-[12px] text-sky-800/80 mt-1">
                            {{ $refund->tanggal_barang_diterima ? 'Diterima pada ' . $refund->tanggal_barang_diterima->format('d M Y') . '. ' : '' }}
                            Dana sedang diproses dan akan ditransfer ke rekening Anda.
                        </p>

                    @elseif ($st === 'selesai')
                        <p class="text-[14px] font-bold text-emerald-900">
                            Dana Rp{{ number_format($refund->jumlah, 0, ',', '.') }} sudah ditransfer{{ $refund->tanggal_selesai ? ' pada ' . $refund->tanggal_selesai->format('d M Y') : '' }}.
                        </p>
                        <p class="text-[12px] text-emerald-800/80 mt-1">Ke {{ $refund->bank_tujuan }} · {{ $refund->no_rekening }} · a.n. {{ $refund->atas_nama }}</p>

                        @if ($buktiTf)
                            <div class="mt-4 rounded-xl bg-white border border-emerald-200 p-4 shadow-sm">
                                <p class="text-[11px] font-bold text-emerald-800 mb-2">Bukti transfer dari admin</p>
                                @unless ($tfPdf)
                                    <a href="{{ $buktiTf }}" target="_blank" rel="noopener" class="block w-fit max-w-full">
                                        <img src="{{ $buktiTf }}" alt="Bukti transfer" class="max-h-56 rounded-lg border border-emerald-100 object-contain">
                                    </a>
                                @else
                                    <p class="text-[11px] text-emerald-900/80">Bukti berformat PDF.</p>
                                @endunless
                                <div class="mt-3 flex gap-4 text-[12px]">
                                    <a href="{{ $buktiTf }}" target="_blank" class="font-bold text-emerald-700 hover:underline">{{ $tfPdf ? 'Buka PDF' : 'Lihat Penuh' }}</a>
                                    <a href="{{ $buktiTf }}" download class="font-bold text-emerald-700 hover:underline">Unduh</a>
                                </div>
                            </div>
                        @else
                            <p class="mt-3 text-[11px] text-emerald-800 bg-emerald-100/50 p-3 rounded-lg">Bukti transfer belum tersedia. Hubungi CS bila dana belum diterima.</p>
                        @endif

                    @elseif ($st === 'ditolak')
                        <p class="text-[14px] font-bold text-rose-800 mb-1">Pengajuan ditolak</p>
                        <p class="text-[12px] text-rose-900 bg-white/50 p-3 rounded-lg mb-3">{{ $refund->alasan_penolakan ?: 'Tidak ada keterangan.' }}</p>
                        <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya ingin menanyakan penolakan refund pesanan ' . $t->kode_transaksi) }}" target="_blank" class="inline-flex items-center justify-center gap-2 {{ $btn }} border border-rose-200 bg-white text-rose-600 hover:bg-rose-50">
                            Tanyakan via WhatsApp
                        </a>
                    @else
                        <p class="text-[12px] text-ink/60">{{ $refund->alasan_penolakan ?: 'Pengajuan ini dibatalkan.' }}</p>
                    @endif
                </section>

                {{-- Barang dalam pesanan --}}
                <section class="{{ $kartu }}">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="{{ $judul }} !mb-0">Barang dalam pesanan</h2>
                        <span class="text-[10px] font-semibold bg-gray-100 px-2 py-0.5 rounded text-gray-500">{{ $items->count() }} produk</span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($items as $d)
                            <div class="{{ ! $loop->last ? 'pb-4 border-b border-line/30' : '' }}">
                                @include('customer.akun._refund-item', ['d' => $d, 'tandaiBermasalah' => count($bermasalah) > 0 && in_array($labelItem($d), $bermasalah, true)])
                            </div>
                        @empty
                            <p class="text-[12px] text-ink/50">Data barang tidak tersedia.</p>
                        @endforelse
                    </div>

                    <dl class="mt-5 pt-4 border-t border-line/40 space-y-2 text-[12px]">
                        @if ($items->count() > 1 || $selisih !== 0)
                            <div class="flex justify-between gap-3"><dt class="text-ink/50">Subtotal barang</dt><dd class="font-semibold text-ink/80 tabular-nums">Rp{{ number_format($subtotalBarang, 0, ',', '.') }}</dd></div>
                        @endif
                        @if ($ongkir > 0)
                            <div class="flex justify-between gap-3"><dt class="text-ink/50">Ongkos kirim</dt><dd class="font-semibold text-ink/80 tabular-nums">Rp{{ number_format($ongkir, 0, ',', '.') }}</dd></div>
                        @endif
                        @if ($lain !== 0)
                            <div class="flex justify-between gap-3"><dt class="text-ink/50">{{ $lain < 0 ? 'Potongan / diskon' : 'Biaya lain' }}</dt><dd class="font-semibold text-ink/80 tabular-nums">{{ $lain < 0 ? '−' : '' }}Rp{{ number_format(abs($lain), 0, ',', '.') }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-3 text-[14px] pt-2"><dt class="font-bold text-ink/80">Total pesanan</dt><dd class="font-bold text-gold tabular-nums">Rp{{ number_format($t->total, 0, ',', '.') }}</dd></div>
                    </dl>
                </section>

            </div>

            {{-- KOLOM KANAN --}}
            <div class="space-y-6">
                
                {{-- Rincian pengajuan --}}
                <section class="{{ $kartu }}">
                    <h2 class="{{ $judul }}">Rincian pengajuan</h2>

                    @if (count($bermasalah) > 0)
                        <div class="mb-4 flex flex-wrap gap-1.5">
                            @foreach ($bermasalah as $b)
                                <span class="px-2 py-1 rounded-md border border-rose-200 bg-rose-50 text-[10px] font-bold text-rose-700">{{ $b }}</span>
                            @endforeach
                        </div>
                    @endif

                    <dl class="space-y-4 text-[12px]">
                        <div>
                            <dt class="text-ink/50 mb-0.5">Waktu Diajukan</dt>
                            <dd class="font-bold text-ink/85">{{ $refund->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-ink/50 mb-0.5">Alasan Refund</dt>
                            <dd class="font-medium text-ink/85 whitespace-pre-line bg-gray-50 p-3 rounded-xl border border-gray-100">{{ $alasanBersih !== '' ? $alasanBersih : '-' }}</dd>
                        </div>
                        <div class="p-3.5 rounded-xl bg-amber-50/50 border border-amber-100">
                            <dt class="text-amber-700/80 font-bold mb-1">Nominal Refund</dt>
                            <dd class="text-lg font-bold text-gold">Rp{{ number_format($refund->jumlah, 0, ',', '.') }}
                                @if ($st === 'diajukan')<span class="text-[10px] font-normal text-ink/50 ml-1 block">(Usulan, ditentukan admin)</span>@endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-ink/50 mb-0.5">Tujuan Transfer</dt>
                            <dd class="font-bold text-ink/85">{{ $refund->bank_tujuan }}</dd>
                            <dd class="font-semibold text-ink/70 mt-0.5">{{ $refund->no_rekening }}</dd>
                            <dd class="text-ink/60 mt-0.5">a.n. {{ $refund->atas_nama }}</dd>
                        </div>
                    </dl>

                    @if ($foto)
                        <div class="mt-5 border-t border-line/40 pt-4">
                            <p class="text-[11px] font-bold text-ink/50 mb-3 uppercase tracking-wide">Foto Bukti ({{ count($foto) }})</p>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($foto as $url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="block aspect-square rounded-xl overflow-hidden border border-line/50 bg-cream relative group">
                                        <img src="{{ $url }}" alt="Bukti" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                                {{-- Riwayat Timeline --}}
                @if ($riwayat->isNotEmpty())
                    <section class="{{ $kartu }}">
                        <h2 class="{{ $judul }}">Riwayat Status</h2>
                        <div class="relative pl-6 space-y-6">
                            {{-- Garis Vertikal --}}
                            <div class="absolute top-2 bottom-2 left-[11px] w-0.5 bg-gray-100"></div>

                            @foreach ($riwayat as $r)
                                <div class="relative">
                                    {{-- Titik Timeline (Warna Emas untuk status terbaru / paling akhir) --}}
                                    <div class="absolute -left-6 top-0.5 w-3 h-3 rounded-full border-2 border-white shadow-sm {{ $loop->last ? 'bg-gold ring-4 ring-amber-100' : 'bg-gray-300' }}"></div>
                                    
                                    <p class="text-[13px] font-bold {{ $loop->last ? 'text-ink' : 'text-ink/70' }}">
                                        {{ \App\Models\Refund::LABEL[$r->status_baru] ?? $r->status_baru }}
                                    </p>
                                    <p class="text-[10px] text-ink/50 mt-0.5">
                                        {{ ($r->waktu_ubah ?? $r->created_at)->format('d M Y, H:i') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
                
            </div>
        </div>

        {{-- Panduan Full Width --}}
        <div class="mt-6">
            @include('customer.akun._refund-panduan', ['compact' => true, 'grid' => true])
        </div>
    </div>
</div>

<script>
    (function () {
        var fmt = function (v) { v = String(v).replace(/\D/g, ''); return v ? v.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''; };
        window.fmtRupiah = fmt;
        document.querySelectorAll('input[data-rupiah]').forEach(function (el) {
            el.value = fmt(el.value);
            el.addEventListener('input', function () { el.value = fmt(el.value); });
            if (el.form) {
                el.form.addEventListener('submit', function (e) {
                    if (e.defaultPrevented) return;
                    el.value = el.value.replace(/\./g, '');
                });
            }
        });
    })();
</script>
@endsection