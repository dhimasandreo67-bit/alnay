@extends('admin.layouts.admin')
@section('title', 'Refund Pembelian')

@section('content')
    @php
        $tabs = [
            'semua'    => ['Semua', 'amber'],
            'diajukan' => ['Perlu ditinjau', 'amber'],
            'diproses' => ['Diproses', 'sky'],
            'selesai'  => ['Selesai', 'emerald'],
            'ditutup'  => ['Ditolak / batal', 'rose'],
        ];
        $tabAktif = [
            'amber'   => 'border-amber-600 bg-amber-600 text-white shadow-md shadow-amber-500/25',
            'sky'     => 'border-sky-600 bg-sky-600 text-white shadow-md shadow-sky-500/25',
            'emerald' => 'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-500/25',
            'rose'    => 'border-rose-600 bg-rose-600 text-white shadow-md shadow-rose-500/25',
        ];
        $kartu = [
            'diajukan' => ['Perlu ditinjau', 'border-amber-200/70 from-amber-50', 'text-amber-950'],
            'diproses' => ['Sedang diproses', 'border-sky-200/70 from-sky-50', 'text-sky-950'],
            'selesai'  => ['Selesai', 'border-emerald-200/70 from-emerald-50', 'text-emerald-950'],
            'ditutup'  => ['Ditolak / batal', 'border-rose-200/70 from-rose-50', 'text-rose-950'],
        ];
        $badge = [
            'diajukan'        => 'bg-amber-50 text-amber-700 border-amber-200',
            'disetujui'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'barang_dikirim'  => 'bg-sky-50 text-sky-700 border-sky-200',
            'barang_diterima' => 'bg-sky-50 text-sky-700 border-sky-200',
            'selesai'         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ditolak'         => 'bg-rose-50 text-rose-700 border-rose-200',
            'dibatalkan'      => 'bg-gray-50 text-gray-600 border-gray-200',
        ];
    @endphp

    <div class="space-y-6">
        @foreach (['success' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'error' => 'border-rose-200 bg-rose-50 text-rose-800'] as $tipe => $kelas)
            @if (session($tipe))
                <div class="rounded-2xl border px-4 py-3 text-sm font-medium {{ $kelas }}">{{ session($tipe) }}</div>
            @endif
        @endforeach

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Refund Pembelian</h1>
            <p class="text-sm text-gray-500">Tinjau pengajuan, terima barang kembali, lalu transfer dana ke pelanggan.</p>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($kartu as $kunci => [$label, $wrap, $teks])
                <a href="{{ route('admin.refund.index', ['status' => $kunci]) }}"
                   class="rounded-2xl border bg-gradient-to-br to-white p-4 transition hover:shadow-md {{ $wrap }}">
                    <p class="text-3xl font-black {{ $teks }}">{{ $hitung[$kunci] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-600">{{ $label }}</p>
                </a>
            @endforeach
        </div>

        {{-- Filter + cari --}}
        <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2 overflow-x-auto">
                @foreach ($tabs as $kunci => [$label, $warna])
                    @php $aktif = $filter === $kunci; @endphp
                    <a href="{{ route('admin.refund.index', ['status' => $kunci]) }}"
                       class="whitespace-nowrap rounded-xl border px-4 py-2 text-xs font-bold transition {{ $aktif ? $tabAktif[$warna] : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                        {{ $label }} <span class="ml-1 opacity-70">{{ $hitung[$kunci] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="status" value="{{ $filter }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode transaksi / nama pelanggan"
                       class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:border-amber-500 focus:outline-none md:w-64">
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-xs font-bold text-white">Cari</button>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        <th class="px-4 py-3">Transaksi</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Alasan</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80">
                    @forelse ($refundList as $r)
                        @php
                            $t = $r->transaksi;
                            $daftarItem = $t?->detail ?? collect();
                            $alasanMentah = (string) $r->alasan;
                            $jmlBermasalah = 0;
                            if (preg_match('/^\[Produk bermasalah:\s*(.*?)\]\s*/su', $alasanMentah, $mm)) {
                                $jmlBermasalah = count(array_filter(array_map('trim', explode(';', $mm[1]))));
                                $alasanMentah = trim(substr($alasanMentah, strlen($mm[0])));
                            }
                        @endphp
                        <tr class="transition-colors hover:bg-amber-50/30">
                            <td class="px-4 py-3.5 align-top">
                                <p class="font-bold text-gray-900 whitespace-nowrap">{{ $t->kode_transaksi ?? '-' }}</p>
                                <p class="mt-0.5 max-w-[170px] truncate text-xs text-gray-500">{{ $daftarItem->first()?->produk?->nama ?? '-' }}</p>
                                @if ($daftarItem->count() > 1)
                                    <p class="text-[10px] font-semibold text-amber-700">+{{ $daftarItem->count() - 1 }} produk lainnya</p>
                                @endif
                                <p class="mt-1 text-[10px] text-gray-400">{{ $r->created_at?->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-4 py-3.5 align-top">
                                <p class="max-w-[150px] truncate font-bold text-gray-900">{{ $t->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $t->user->no_hp ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3.5 align-top">
                                @if ($jmlBermasalah > 0)
                                    <span class="mb-1 inline-block rounded-md border border-rose-200 bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700">{{ $jmlBermasalah }} produk bermasalah</span>
                                @endif
                                <p class="line-clamp-2 max-w-[240px] text-xs text-gray-600">{{ $alasanMentah }}</p>
                            </td>
                            <td class="px-4 py-3.5 align-top text-right font-semibold tabular-nums">Rp{{ number_format($r->jumlah, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <span class="inline-block rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badge[$r->status] ?? $badge['diajukan'] }}">{{ str_replace('_', ' ', $r->status) }}</span>
                                @if ($r->status === 'diajukan')
                                    @php $jam = (int) abs($r->created_at->diffInHours(now())); @endphp
                                    <p class="mt-1 text-[10px] {{ $jam >= 24 ? 'font-bold text-rose-600' : 'text-gray-400' }}">Menunggu {{ $jam }} jam</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 align-top text-right">
                                <a href="{{ route('admin.refund.show', $r->id) }}"
                                   class="inline-block rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-amber-700">
                                    {{ $r->status === 'diajukan' ? 'Tinjau' : 'Kelola' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-14 text-center text-sm text-gray-400">Tidak ada pengajuan refund.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($refundList->hasPages())
            <div>{{ $refundList->links() }}</div>
        @endif
    </div>
@endsection