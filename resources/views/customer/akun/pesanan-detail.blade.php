@extends('layouts.akun')

@section('title', 'Detail Pesanan — ' . config('alnay.brand.nama'))

@section('content')

    <style>
        @keyframes pd-up {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: none; }
        }
        .pd-up { animation: pd-up .6s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
        .pd-shine { position: relative; overflow: hidden; }
        .pd-shine::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%);
            transform: translateX(-120%); transition: transform .8s ease; pointer-events: none;
        }
        .pd-shine:hover::after { transform: translateX(120%); }
        .pd-bar { transition: width .9s cubic-bezier(.22, .8, .3, 1); }
        @media (prefers-reduced-motion: reduce) {
            .pd-up, .pd-bar { animation: none !important; transition: none !important; }
        }
    </style>

    @php
        $statusKey = strtolower((string) $transaksi->status_pembayaran);
        $statusKirim = $transaksi->pengiriman->status_kirim ?? null;
        $isSewa = $transaksi->jenis === 'sewa';

        // Bayar tunai di toko: tidak ada pembayaran online. Status dilunasi admin lewat panel "Kelola Transaksi".
        $bayarTunai = $transaksi->pembayaran->contains(fn ($p) => strtolower((string) $p->metode) === 'tunai');
        $menungguTunai = $bayarTunai && $statusKey === 'pending';

        $statusBayar = [
            'pending'    => ['Menunggu pembayaran', 'bg-amber-50 text-amber-700 border-amber-200'],
            'dp'         => ['DP dibayar', 'bg-sky-50 text-sky-700 border-sky-200'],
            'lunas'      => ['Lunas', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'settlement' => ['Lunas', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'success'    => ['Lunas', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'batal'      => ['Dibatalkan', 'bg-rose-50 text-rose-700 border-rose-200'],
            'expire'     => ['Kedaluwarsa', 'bg-rose-50 text-rose-700 border-rose-200'],
        ];
        [$teksStatus, $gayaStatus] = $statusBayar[$statusKey]
            ?? [ucfirst(str_replace('_', ' ', $statusKey)), 'bg-gray-50 text-gray-600 border-gray-200'];
        if ($menungguTunai) {
            $teksStatus = 'Bayar di toko';
        }

        $teksKirim = [
            'diterima' => 'Diterima',
            'dikirim'  => 'Dikirim',
            'menunggu' => 'Menunggu',
        ][$statusKirim] ?? ($statusKirim ? ucfirst($statusKirim) : 'Belum dikirim');
        $gayaKirim = [
            'diterima' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dikirim'  => 'bg-sky-50 text-sky-700 border-sky-200',
        ][$statusKirim] ?? 'bg-amber-50 text-amber-700 border-amber-200';

        $jenisBayarLabel = [
            'dp'        => 'Uang Muka (DP)',
            'deposit'   => 'Deposit Jaminan',
            'pelunasan' => 'Pelunasan',
            'lunas'     => 'Pembayaran Penuh',
            'penuh'     => 'Pembayaran Penuh',
        ];

        $statusBayarItem = function ($s, $metode = null) {
            $s = strtolower((string) $s);
            if (in_array($s, ['success', 'settlement', 'lunas'])) {
                return ['Berhasil', 'text-emerald-600'];
            }
            if ($s === 'pending') {
                return [strtolower((string) $metode) === 'tunai' ? 'Bayar di toko' : 'Menunggu', 'text-amber-600'];
            }
            return ['Gagal', 'text-rose-600'];
        };

        $sudahDibayar = $transaksi->pembayaran
            ->filter(fn ($p) => in_array(strtolower((string) $p->status_midtrans), ['success', 'settlement', 'lunas']))
            ->sum('jumlah');
        $totalTagihan = (int) $transaksi->total;
        $sisaTagihan = max(0, $totalTagihan - (int) $sudahDibayar);
        $persenBayar = $totalTagihan > 0 ? min(100, (int) round($sudahDibayar / $totalTagihan * 100)) : 0;

        $sudahBayarAwal = in_array($statusKey, ['dp', 'lunas', 'settlement', 'success']);
        $langkah = [
            ['label' => 'Bayar', 'selesai' => $sudahBayarAwal],
            ['label' => 'Diproses', 'selesai' => $sudahBayarAwal],
            ['label' => $isSewa ? 'Diantar' : 'Dikirim', 'selesai' => in_array($statusKirim, ['dikirim', 'diterima'])],
            ['label' => 'Diterima', 'selesai' => $statusKirim === 'diterima'],
        ];

        // Sewa ambil di toko tidak punya tahap "Diantar/Diterima": ganti dengan tahap fitting.
        $perluFitting = $isSewa && $transaksi->perluFitting();
        if ($perluFitting) {
            $fit = $transaksi->fittingAktif();
            $langkah = [
                ['label' => 'Bayar DP', 'selesai' => $transaksi->dpSudahDibayar()],
                ['label' => 'Jadwal fitting', 'selesai' => (bool) $fit],
                ['label' => 'Fitting selesai', 'selesai' => $fit && strtolower($fit->status) === 'hadir'],
            ];
        }

        // Beli & ambil di toko: tidak ada tahap "Dikirim/Diterima".
        $ambilDiToko = ! $isSewa && ! $transaksi->pengiriman;
        if ($ambilDiToko) {
            $langkah = $bayarTunai
                ? [
                    ['label' => 'Pesanan dibuat', 'selesai' => true],
                    ['label' => 'Bayar di toko', 'selesai' => $sudahBayarAwal],
                ]
                : [
                    ['label' => 'Bayar', 'selesai' => $sudahBayarAwal],
                    ['label' => 'Siap diambil', 'selesai' => $sudahBayarAwal],
                ];
        }

        $alamat = $transaksi->pengiriman->alamat_tujuan ?? null;
        $alamatTampil = $alamat ? str_replace(' | ', "\n", $alamat) : null;

        $riwayatQc = $transaksi->sewa?->pengecekan?->sortByDesc('created_at') ?? collect();
        // Tombol bayar online tidak ditampilkan untuk pesanan bayar tunai di toko.
        $bisaBayar = in_array($statusKey, ['pending', 'dp']) && ! $bayarTunai;

        // Ulasan hanya untuk pesanan yang sudah diterima pelanggan:
        // pengiriman berstatus "diterima", atau sewa yang sudah "selesai".
        $sudahDiterima = $statusKirim === 'diterima'
            || ($isSewa && strtolower((string) ($transaksi->sewa->status ?? '')) === 'selesai');
    @endphp

    <div class="bg-cream min-h-screen py-6 md:py-10 pb-12">
        <div class="max-w-2xl mx-auto px-4">

            {{-- HEADER --}}
            <div class="pd-up flex items-center gap-3 mb-5" style="--d: .05s;">
                <a href="{{ route('akun.dashboard') }}#ringkasan" aria-label="Kembali"
                    class="w-9 h-9 shrink-0 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all duration-300">
                    <x-icon name="chevron-right" :size="14" class="rotate-180" />
                </a>
                <div class="min-w-0 flex-1">
                    <h1 class="font-display text-xl md:text-2xl font-medium text-ink/90 leading-tight">Detail Pesanan</h1>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[11px] text-ink/50 font-mono">{{ $transaksi->kode_transaksi }}</span>
                        <button type="button" data-salin="{{ $transaksi->kode_transaksi }}"
                            class="text-[10px] font-semibold text-gold hover:underline">Salin</button>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-md border text-[11px] font-bold shrink-0 {{ $gayaStatus }}">{{ $teksStatus }}</span>
            </div>

            {{-- PANEL UTAMA: semua bagian di dalam satu panel --}}
            <div class="pd-up bg-panel border border-line/40 rounded-2xl shadow-sm overflow-hidden" style="--d: .1s;">

                {{-- RINGKASAN TAGIHAN --}}
                <div class="p-5 md:p-6">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-ink/50">Sisa tagihan</p>
                            <p class="mt-1 font-display text-3xl text-gold tabular-nums leading-none">
                                Rp{{ number_format($sisaTagihan, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[10px] uppercase tracking-wider text-ink/50">Dipesan</p>
                            <p class="mt-1 text-[12px] font-semibold text-ink/80">{{ $transaksi->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 h-1.5 rounded-full bg-line/40 overflow-hidden">
                        <div class="pd-bar h-full bg-gold rounded-full" style="width: {{ $persenBayar }}%"></div>
                    </div>
                    <div class="mt-2 flex justify-between text-[11px] text-ink/55 tabular-nums">
                        <span>Sudah dibayar Rp{{ number_format($sudahDibayar, 0, ',', '.') }}</span>
                        <span>dari Rp{{ number_format($totalTagihan, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- PROGRES --}}
                <div class="px-5 md:px-6 py-4 border-t border-line/40">
                    <ol class="flex items-start">
                        @foreach ($langkah as $i => $l)
                            <li class="flex-1 flex flex-col items-center text-center relative">
                                @if (! $loop->last)
                                    <span class="absolute top-3 left-1/2 w-full h-px {{ $l['selesai'] ? 'bg-gold' : 'bg-line/70' }}"></span>
                                @endif
                                <span class="relative z-10 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold
                                    {{ $l['selesai'] ? 'bg-gold text-white' : 'bg-white border border-line/60 text-ink/40' }}">
                                    {{ $l['selesai'] ? '✓' : $i + 1 }}
                                </span>
                                <span class="mt-1.5 text-[10px] font-medium {{ $l['selesai'] ? 'text-ink/80' : 'text-ink/40' }}">{{ $l['label'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>

                {{-- FITTING (hanya sewa ambil di toko yang DP-nya sudah dibayar) --}}
                @if ($perluFitting && $transaksi->dpSudahDibayar())
                    <div class="px-5 md:px-6 py-4 border-t border-line/40">
                        @include('customer.partials.fitting-status', ['transaksi' => $transaksi])
                    </div>
                @endif

                {{-- PRODUK --}}
                <section class="p-5 md:p-6 border-t border-line/40">
                    <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Produk</h2>

                    @foreach ($transaksi->detail as $d)
                        @php
                            $foto = $d->produk->foto_produk ?? null;
                            if (is_array($foto)) {
                                $foto = $foto[0] ?? null;
                            }
                        @endphp
                        <div class="flex gap-4 items-center {{ ! $loop->last ? 'mb-4 pb-4 border-b border-line/30' : '' }}">
                            <div class="w-16 h-16 shrink-0 rounded-xl bg-cream border border-line/50 overflow-hidden">
                                @if ($foto)
                                    <img src="{{ asset('storage/' . $foto) }}" alt="{{ $d->produk->nama ?? 'Produk' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-ink/20">
                                        <x-icon name="sparkle" :size="18" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-[14px] font-semibold text-ink/90 truncate">{{ $d->produk->nama ?? 'Produk' }}</p>
                                <p class="mt-1 text-[11px] text-ink/55">
                                    @if (! empty($d->ukuran)) Ukuran {{ $d->ukuran }} @endif
                                    @if (! empty($d->warna)) · {{ $d->warna }} @endif
                                    · {{ $d->jumlah }} item
                                </p>
                                <p class="mt-0.5 text-[11px] text-ink/45">Rp{{ number_format($d->harga_satuan, 0, ',', '.') }} / item</p>
                            </div>

                            <p class="text-[14px] font-semibold text-ink/85 tabular-nums shrink-0">
                                Rp{{ number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </section>

                {{-- JADWAL SEWA --}}
                @if ($transaksi->sewa)
                    <section class="p-5 md:p-6 border-t border-line/40">
                        <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Jadwal Penyewaan</h2>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <p class="text-[11px] text-ink/50">Tanggal ambil</p>
                                <p class="mt-0.5 text-[13px] font-semibold text-ink/85">
                                    {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_pinjam)->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] text-ink/50">Tanggal kembali</p>
                                <p class="mt-0.5 text-[13px] font-semibold text-ink/85">
                                    {{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_kembali_rencana)->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[11px] text-ink/50">Status sewa</p>
                                <p class="mt-0.5 text-[13px] font-semibold text-ink/85">{{ ucfirst(str_replace('_', ' ', $transaksi->sewa->status)) }}</p>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- PENGIRIMAN --}}
                @if ($transaksi->pengiriman)
                    <section class="p-5 md:p-6 border-t border-line/40">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60">
                                {{ $isSewa ? 'Pengantaran' : 'Pengiriman' }}
                            </h2>
                            <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase {{ $gayaKirim }}">{{ $teksKirim }}</span>
                        </div>

                        <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-[12px]">
                            @if ($isSewa)
                                <dt class="text-ink/50">Metode</dt>
                                <dd class="font-semibold text-ink/85">Diantar manual via WA</dd>
                                <dt class="text-ink/50">Biaya kirim</dt>
                                <dd class="font-semibold text-ink/85">
                                    @if (($transaksi->pengiriman->biaya_kirim ?? 0) > 0)
                                        Rp{{ number_format($transaksi->pengiriman->biaya_kirim, 0, ',', '.') }}
                                    @else
                                        Diinformasikan via WA
                                    @endif
                                </dd>
                            @else
                                <dt class="text-ink/50">Kurir</dt>
                                <dd class="font-semibold text-ink/85">{{ $transaksi->pengiriman->kurir ?: 'Belum ditentukan' }}</dd>
                                <dt class="text-ink/50">No. resi</dt>
                                <dd class="font-semibold text-ink/85">{{ $transaksi->pengiriman->no_resi ?: 'Belum tersedia' }}</dd>
                            @endif
                        </dl>

                        @if ($alamatTampil)
                            <div class="mt-4 rounded-xl bg-cream/60 border border-line/30 px-4 py-3">
                                <p class="text-[10px] text-ink/50 uppercase tracking-wider mb-1">Alamat tujuan</p>
                                <p class="text-[13px] font-medium text-ink/85 whitespace-pre-line leading-relaxed">{{ $alamatTampil }}</p>
                            </div>
                        @endif

                        @if ($statusKirim === 'dikirim')
                            <button type="button" onclick="bukaModalTerima()"
                                class="w-full mt-4 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-colors">
                                Pesanan Diterima
                            </button>
                        @endif
                    </section>
                @endif

                {{-- PENGAMBILAN (beli, ambil di toko) --}}
                @if ($ambilDiToko)
                    <section class="p-5 md:p-6 border-t border-line/40">
                        <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Pengambilan</h2>
                        <div class="rounded-xl bg-cream/60 border border-line/30 px-4 py-3">
                            <p class="text-[13px] font-semibold text-ink/85">Ambil di Toko</p>
                            <p class="mt-0.5 text-[12px] text-ink/60">{{ config('alnay.toko.alamat', 'Jl. Terusan Rajabasa No.11, Bandar Lampung') }}</p>
                        </div>
                    </section>
                @endif

                {{-- RIWAYAT PEMBAYARAN --}}
                <section class="p-5 md:p-6 border-t border-line/40">
                    <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Riwayat Pembayaran</h2>

                    @forelse ($transaksi->pembayaran->sortByDesc('created_at') as $p)
                        @php
                            $kodeBayar = strtolower((string) $p->jenis_bayar);
                            [$teksBayar, $warnaBayar] = $statusBayarItem($p->status_midtrans, $p->metode);
                        @endphp
                        <div class="flex justify-between items-center gap-3 py-2.5 {{ ! $loop->last ? 'border-b border-line/30' : '' }}">
                            <div class="min-w-0">
                                <p class="text-[12px] font-semibold text-ink/85">{{ $jenisBayarLabel[$kodeBayar] ?? ucfirst($kodeBayar) }}</p>
                                <p class="text-[10px] text-ink/50">{{ $p->created_at->format('d M Y, H:i') }} · {{ strtoupper($p->metode ?? '-') }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[13px] font-semibold text-ink/85 tabular-nums">Rp{{ number_format($p->jumlah, 0, ',', '.') }}</p>
                                <p class="text-[10px] font-bold uppercase {{ $warnaBayar }}">{{ $teksBayar }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[12px] text-ink/50">Belum ada pembayaran yang tercatat.</p>
                    @endforelse
                </section>

                {{-- REFUND --}}
                @if (! empty($refund))
                    @php
                        $chipRefund = $refund->status === 'selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : (in_array($refund->status, ['ditolak', 'dibatalkan']) ? 'bg-rose-50 text-rose-700 border-rose-200'
                            : 'bg-amber-50 text-amber-700 border-amber-200');
                    @endphp
                    <section class="p-5 md:p-6 border-t border-line/40">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60">Pengajuan Refund</h2>
                            <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase {{ $chipRefund }}">{{ $refund->label_status }}</span>
                        </div>
                        <p class="text-[12px] text-ink/65">Alasan: <span class="font-semibold text-ink/85">{{ $refund->alasan }}</span></p>
                        <p class="mt-1 text-[10px] text-ink/45">Diajukan {{ $refund->created_at->format('d M Y, H:i') }}</p>
                        <a href="{{ route('akun.refund.show', $refund->id) }}" class="inline-block mt-3 text-[12px] font-bold text-gold hover:underline">Lihat status & langkah berikutnya →</a>
                    </section>
                @endif

                {{-- RIWAYAT KONDISI BARANG (QC) --}}
                @if ($riwayatQc->count() > 0)
                    <section class="p-5 md:p-6 border-t border-line/40">
                        <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-4">Riwayat Kondisi Barang</h2>
                        <div class="relative border-l-2 border-gold/30 ml-2 space-y-5">
                            @foreach ($riwayatQc as $qc)
                                <div class="relative pl-4">
                                    <span class="absolute -left-[5px] top-1 w-2 h-2 rounded-full ring-2 ring-panel
                                        {{ $qc->tahap === 'sebelum_cuci' ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                                    <p class="text-[12px] font-bold text-ink/85">
                                        {{ $qc->tahap === 'sebelum_cuci' ? 'Pengecekan saat dikembalikan' : 'Pengecekan sebelum diserahkan' }}
                                    </p>
                                    <p class="text-[10px] text-ink/50">{{ $qc->created_at->format('d M Y, H:i') }} WIB</p>
                                    <p class="text-[11px] text-ink/65 mt-1 italic">"{{ $qc->catatan }}"</p>
                                    @if ($qc->foto_bukti)
                                        <a href="{{ asset('storage/' . $qc->foto_bukti) }}" target="_blank"
                                            class="inline-block mt-1.5 text-[10px] font-bold text-gold hover:underline">Lihat foto bukti</a>
                                    @endif
                                    <p class="text-[9px] text-ink/40 mt-1">Petugas: {{ $qc->petugas }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- RATING --}}
                @if ($sudahDiterima && in_array($statusKey, ['lunas', 'settlement', 'success']))
                    @if (! ($sudahRating ?? true) && ! empty($ratingDetailId))
                        <section class="p-5 md:p-6 border-t border-line/40">
                            <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Beri Ulasan</h2>
                            <form action="{{ route('rating.store', $ratingDetailId) }}" method="POST" x-data="{ rating: 5 }">
                                @csrf
                                <div class="flex gap-1.5 mb-2">
                                    <template x-for="i in 5" :key="i">
                                        <button type="button" @click="rating = i"
                                            :class="i <= rating ? 'text-amber-400' : 'text-gray-300'"
                                            class="text-3xl leading-none transition-transform hover:scale-110">★</button>
                                    </template>
                                    <input type="hidden" name="rating" x-model="rating">
                                </div>
                                <p class="text-[10px] text-ink/50 mb-2">Rating: <span x-text="rating"></span> / 5</p>
                                <textarea name="ulasan" rows="2" placeholder="Ceritakan pengalaman Anda {{ $isSewa ? 'menyewa' : 'berbelanja' }} di ALNAY..."
                                    class="w-full px-3 py-2 rounded-lg border border-line/50 bg-white text-xs mb-3 focus:outline-none focus:border-gold"></textarea>
                                <button type="submit"
                                    class="px-4 py-2 bg-gold text-white text-xs font-bold rounded-lg hover:bg-gold/90">Kirim Ulasan</button>
                            </form>
                        </section>
                    @elseif (($sudahRating ?? false))
                        <div class="px-5 md:px-6 py-3 border-t border-line/40 text-[12px] font-semibold text-emerald-700 text-center bg-emerald-50/60">
                            Terima kasih, ulasan untuk pesanan ini sudah terkirim.
                        </div>
                    @endif
                @endif

                {{-- AKSI --}}
                <section class="p-5 md:p-6 border-t border-line/40 space-y-2.5">
                    @if ($bisaBayar)
                        <a href="{{ $isSewa ? route('sewa.konfirmasi', $transaksi->id) : route('beli.konfirmasi', $transaksi->id) }}"
                            class="pd-shine flex items-center justify-between w-full px-5 py-3 rounded-xl bg-gold text-white hover:bg-gold/90 transition-all duration-300">
                            <span class="text-[13px] font-semibold">Lanjutkan Pembayaran</span>
                            <span class="text-[13px] font-bold tabular-nums">Rp{{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                        </a>
                    @elseif ($menungguTunai)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[13px] font-semibold text-amber-800">Bayar tunai di toko</p>
                                <p class="text-[13px] font-bold tabular-nums text-amber-800">Rp{{ number_format($sisaTagihan, 0, ',', '.') }}</p>
                            </div>
                            <p class="mt-1 text-[12px] leading-relaxed text-amber-700">
                                Tidak perlu membayar online. Bayar langsung saat mengambil pesanan. Status akan diperbarui oleh admin setelah pembayaran diterima.
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('akun.dashboard') }}#ringkasan"
                            class="text-center py-2.5 rounded-xl border border-line/50 text-ink/70 text-[12px] font-semibold hover:bg-white transition-all duration-300">
                            Kembali ke Pesanan
                        </a>
                        <a href="https://wa.me/{{ config('alnay.kontak.wa_cs') }}?text={{ urlencode('Halo, saya butuh bantuan untuk pesanan ' . $transaksi->kode_transaksi) }}"
                            target="_blank" rel="noopener"
                            class="text-center py-2.5 rounded-xl border border-emerald-600/40 text-emerald-800 text-[12px] font-semibold hover:bg-emerald-50 transition-all duration-300">
                            Hubungi WA CS
                        </a>
                    </div>

                    @if (! empty($bisaRefund))
                        <a href="{{ route('akun.refund.create', $transaksi->id) }}"
                            class="block text-center w-full py-2.5 rounded-xl border border-rose-200 text-rose-600 text-[12px] font-semibold hover:bg-rose-50 transition-all duration-300">
                            Ajukan Refund
                        </a>
                    @elseif (! $isSewa && $statusKey === 'lunas' && empty($refund) && ! empty($alasanTakBisaRefund))
                        <p class="text-center text-[11px] text-ink/45">{{ $alasanTakBisaRefund }}</p>
                    @endif
                </section>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI TERIMA BARANG --}}
    @if ($transaksi->pengiriman)
        <div id="modalTerimaBarang" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-display text-lg font-bold text-gray-900 mb-2">Konfirmasi Terima Barang</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Apakah Anda yakin barang sudah diterima dengan baik?</p>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-2">
                    <button type="button" onclick="tutupModalTerima()"
                        class="flex-1 py-2.5 rounded-lg border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-100 transition-colors">
                        Batal
                    </button>
                    <form action="{{ route('akun.pengiriman.terima', $transaksi->pengiriman->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="w-full py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-md shadow-emerald-500/20">
                            Ya, terima
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        function bukaModalTerima() {
            const modal = document.getElementById('modalTerimaBarang');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }

        function tutupModalTerima() {
            const modal = document.getElementById('modalTerimaBarang');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        document.querySelectorAll('[data-salin]').forEach((btn) => {
            btn.addEventListener('click', () => {
                navigator.clipboard.writeText(btn.dataset.salin).then(() => {
                    btn.textContent = 'Tersalin';
                    setTimeout(() => (btn.textContent = 'Salin'), 1500);
                });
            });
        });
    </script>
@endsection