@extends('admin.layouts.admin')
@section('title', 'Kelola Refund')

@section('content')
    @php
        $st = $refund->status;
        $t = $refund->transaksi;
        $u = $t->user;
        $foto = $refund->fotoBuktiUrl();
        $badge = [
            'diajukan'        => 'bg-amber-50 text-amber-700 border-amber-200',
            'disetujui'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'barang_dikirim'  => 'bg-sky-50 text-sky-700 border-sky-200',
            'barang_diterima' => 'bg-sky-50 text-sky-700 border-sky-200',
            'selesai'         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ditolak'         => 'bg-rose-50 text-rose-700 border-rose-200',
            'dibatalkan'      => 'bg-gray-50 text-gray-600 border-gray-200',
        ];
        $input = 'w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500';
        $hp = preg_replace('/\D/', '', (string) ($u->no_hp ?? ''));
        if ($hp !== '' && str_starts_with($hp, '0')) { $hp = '62' . substr($hp, 1); }
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.refund.index') }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:text-amber-600" aria-label="Kembali">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="flex-1">
                <h1 class="text-xl font-bold text-gray-900">Refund {{ $t->kode_transaksi }}</h1>
                <p class="text-xs text-gray-500">Diajukan {{ $refund->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-wider {{ $badge[$st] ?? $badge['diajukan'] }}">{{ $refund->label_status }}</span>
        </div>

        @foreach (['success' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'error' => 'border-rose-200 bg-rose-50 text-rose-800'] as $tipe => $kelas)
            @if (session($tipe))
                <div class="rounded-2xl border px-4 py-3 text-sm font-medium {{ $kelas }}">{{ session($tipe) }}</div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="space-y-1 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- Kiri: data pengajuan --}}
            <div class="space-y-6 lg:col-span-2">

                <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Pelanggan</h2>
                    <p class="font-bold text-gray-900">{{ $u->name ?? '-' }}</p>
                    @if ($hp !== '')
                        <a href="https://wa.me/{{ $hp }}" target="_blank" rel="noopener" class="text-sm text-gray-500 hover:text-emerald-600 hover:underline">{{ $u->no_hp }}</a>
                    @endif
                </section>

                <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Barang</h2>
                    @foreach ($t->detail as $d)
                        <div class="flex justify-between gap-3 text-sm {{ ! $loop->last ? 'mb-2 border-b border-gray-100 pb-2' : '' }}">
                            <span>{{ $d->produk->nama ?? 'Produk' }}
                                <span class="text-xs text-gray-400">@if ($d->ukuran) · {{ $d->ukuran }} @endif @if ($d->warna) · {{ $d->warna }} @endif · {{ $d->jumlah }} item</span></span>
                            <span class="font-semibold tabular-nums">Rp{{ number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <p class="mt-3 text-xs text-gray-400">Total transaksi Rp{{ number_format($t->total, 0, ',', '.') }}
                        @if ($t->pengiriman) · Diterima {{ $t->pengiriman->tanggal_diterima?->format('d M Y') ?? '-' }} @endif</p>
                </section>

                <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Alasan & bukti pelanggan</h2>
                    <p class="whitespace-pre-line text-sm text-gray-700">{{ $refund->alasan }}</p>
                    @if ($foto)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($foto as $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="h-24 w-24 overflow-hidden rounded-xl border border-gray-200">
                                    <img src="{{ $url }}" alt="Bukti" class="h-full w-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-4 rounded-xl bg-gray-50 p-3 text-xs text-gray-600">
                        <p class="font-bold text-gray-700">Transfer dana ke</p>
                        {{ $refund->bank_tujuan }} · <span class="font-mono">{{ $refund->no_rekening }}</span> · a.n. {{ $refund->atas_nama }}
                    </div>
                </section>

                @if ($riwayat->isNotEmpty())
                    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h2 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Riwayat status</h2>
                        <div class="relative ml-2 space-y-4 border-l-2 border-amber-200">
                            @foreach ($riwayat as $r)
                                <div class="relative pl-4">
                                    <span class="absolute -left-[5px] top-1 h-2 w-2 rounded-full bg-amber-500 ring-2 ring-white"></span>
                                    <p class="text-sm font-bold text-gray-800">{{ \App\Models\Refund::LABEL[$r->status_baru] ?? $r->status_baru }}</p>
                                    <p class="text-[11px] text-gray-400">{{ ($r->waktu_ubah ?? $r->created_at)->format('d M Y, H:i') }} · {{ $r->pengubah->name ?? 'Pelanggan' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Kanan: tindakan --}}
            <div class="space-y-6">

                @if ($st === 'diajukan')
                    <section class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 text-sm font-bold text-emerald-800">Setujui refund</h2>
                        <form action="{{ route('admin.refund.setujui', $refund->id) }}" method="POST" class="space-y-3">
                            @csrf @method('PATCH')
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">Nominal refund (Rp)</label>
                                <input type="number" name="jumlah" min="1" max="{{ (int) $t->total }}" value="{{ old('jumlah', (int) $refund->jumlah) }}" required class="{{ $input }}">
                                <p class="mt-1 text-[11px] text-gray-400">Usulan: nilai barang. Maks total transaksi.</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">No. resi penjemputan <span class="font-normal text-gray-400">(opsional)</span></label>
                                <input type="text" name="no_resi_penjemputan" maxlength="100" value="{{ old('no_resi_penjemputan') }}" placeholder="Kosongkan bila pelanggan kirim sendiri" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">Catatan / instruksi untuk pelanggan</label>
                                <textarea name="catatan_negosiasi" rows="3" maxlength="1000" placeholder="Alamat pengiriman balik, potongan, dll." class="{{ $input }}">{{ old('catatan_negosiasi') }}</textarea>
                            </div>
                            <button class="w-full rounded-xl bg-emerald-600 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Setujui</button>
                        </form>
                    </section>

                    <section class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 text-sm font-bold text-rose-800">Tolak refund</h2>
                        <form action="{{ route('admin.refund.tolak', $refund->id) }}" method="POST" class="space-y-3">
                            @csrf @method('PATCH')
                            <textarea name="alasan_penolakan" rows="3" required minlength="5" maxlength="500" placeholder="Alasan penolakan (dilihat pelanggan)" class="{{ $input }}">{{ old('alasan_penolakan') }}</textarea>
                            <button class="w-full rounded-xl border border-rose-300 py-2.5 text-sm font-bold text-rose-700 hover:bg-rose-50" onclick="return confirm('Tolak pengajuan refund ini?')">Tolak</button>
                        </form>
                    </section>

                @elseif (in_array($st, ['disetujui', 'barang_dikirim'], true))
                    <section class="rounded-2xl border border-sky-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-1 text-sm font-bold text-sky-800">Terima barang kembali</h2>
                        <p class="mb-3 text-xs text-gray-500">
                            {{ $st === 'barang_dikirim' ? 'Pelanggan menandai barang sudah dikirim.' : 'Menunggu pelanggan mengirim barang.' }}
                            Catat setelah barang benar-benar sampai dan dicek.
                        </p>
                        <p class="mb-3 text-xs text-gray-600">Nominal disetujui: <b>Rp{{ number_format($refund->jumlah, 0, ',', '.') }}</b>
                            @if ($refund->no_resi_penjemputan)<br>Resi: <span class="font-mono">{{ $refund->no_resi_penjemputan }}</span>@endif</p>
                        <form action="{{ route('admin.refund.terima-barang', $refund->id) }}" method="POST" class="space-y-3">
                            @csrf @method('PATCH')
                            <label class="flex items-start gap-2 text-xs text-gray-700">
                                <input type="checkbox" name="restock" value="1" class="mt-0.5 accent-amber-600">
                                <span>Barang layak dijual kembali — <b>kembalikan stok</b> produk</span>
                            </label>
                            <button class="w-full rounded-xl bg-sky-600 py-2.5 text-sm font-bold text-white hover:bg-sky-700">Barang diterima</button>
                        </form>
                    </section>

                @elseif ($st === 'barang_diterima')
                    <section class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-1 text-sm font-bold text-amber-800">Transfer dana</h2>
                        <div class="mb-3 rounded-xl bg-amber-50 p-3 text-xs text-amber-900">
                            <p class="text-lg font-black">Rp{{ number_format($refund->jumlah, 0, ',', '.') }}</p>
                            {{ $refund->bank_tujuan }} · <span class="font-mono">{{ $refund->no_rekening }}</span><br>a.n. {{ $refund->atas_nama }}
                        </div>
                        <form action="{{ route('admin.refund.selesai', $refund->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">Bukti transfer <span class="text-rose-500">*</span></label>
                                <input type="file" name="bukti_transfer" required accept="image/jpeg,image/png,image/webp,application/pdf" class="block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-amber-600 file:px-3 file:py-2 file:font-semibold file:text-white">
                            </div>
                            <button class="w-full rounded-xl bg-amber-600 py-2.5 text-sm font-bold text-white hover:bg-amber-700" onclick="return confirm('Pastikan dana sudah ditransfer. Tandai refund selesai?')">Tandai selesai</button>
                        </form>
                    </section>

                @elseif ($st === 'selesai')
                    <section class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-5 text-sm text-emerald-900 shadow-sm">
                        <p class="font-bold">Refund selesai</p>
                        <p class="text-xs">Rp{{ number_format($refund->jumlah, 0, ',', '.') }} · {{ $refund->tanggal_selesai?->format('d M Y, H:i') }}</p>
                        @if ($refund->bukti_transfer)
                            <a href="{{ asset('storage/' . $refund->bukti_transfer) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-xs font-bold underline">Lihat bukti transfer</a>
                        @endif
                        @if ($refund->stok_dikembalikan)<p class="mt-2 text-xs">Stok produk sudah dikembalikan.</p>@endif
                    </section>

                @elseif ($st === 'ditolak')
                    <section class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5 text-sm text-rose-900 shadow-sm">
                        <p class="font-bold">Ditolak</p>
                        <p class="mt-1 whitespace-pre-line text-xs">{{ $refund->alasan_penolakan }}</p>
                    </section>
                @else
                    <section class="rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm text-gray-600 shadow-sm">Dibatalkan oleh pelanggan.</section>
                @endif
            </div>
        </div>
    </div>
@endsection