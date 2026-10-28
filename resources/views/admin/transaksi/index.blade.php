@extends('admin.layouts.admin')
@section('title', 'Kelola Transaksi')

@section('content')
<div class="p-6 space-y-6 transition-all duration-500 animate-fade-in">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Kelola Transaksi</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau pesanan masuk, status pembayaran, dan jadwal penyewaan.</p>
        </div>
        <a href="{{ route('admin.transaksi.create') }}"
           class="inline-flex items-center gap-2 bg-gray-900 text-white px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-gray-800 active:scale-95 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Transaksi Baru (Kasir)
        </a>
    </div>

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Pendapatan</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 border border-amber-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Menunggu Pembayaran</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">{{ $pesananBaru }} Pesanan</h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 border border-sky-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Sedang Diproses</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">{{ $pesananDiproses }} Pesanan</h3>
            </div>
        </div>
    </div>

    {{-- Tab Jenis Transaksi --}}
    @php
        $tabs = ['semua' => 'Semua', 'beli' => 'Pembelian', 'sewa' => 'Penyewaan'];
        $adaFilter = request()->filled('search') || request()->filled('status') || $channel !== '';
    @endphp
    <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Jenis transaksi">
        @foreach($tabs as $key => $nama)
            @php $aktif = $tab === $key; @endphp
            <a href="{{ request()->fullUrlWithQuery(['tab' => $key, 'page' => null]) }}"
               role="tab" aria-selected="{{ $aktif ? 'true' : 'false' }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                      {{ $aktif ? 'border-gray-900 bg-gray-900 text-white shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-400 hover:text-amber-700' }}">
                {{ $nama }}
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold tabular-nums {{ $aktif ? 'bg-white/15 text-white' : 'bg-gray-100 text-gray-500' }}">
                    {{ $tabCounts[$key] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('admin.transaksi.index') }}" class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama pelanggan..."
                   class="w-full pl-10 pr-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <select name="channel" onchange="this.form.submit()" aria-label="Asal transaksi" class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
                <option value="">Semua Asal</option>
                <option value="online" {{ $channel === 'online' ? 'selected' : '' }}>Online (Website)</option>
                <option value="kasir" {{ $channel === 'kasir' ? 'selected' : '' }}>Via Kasir</option>
            </select>
            <select name="status" onchange="this.form.submit()" aria-label="Status pembayaran" class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="dp" {{ request('status') === 'dp' ? 'selected' : '' }}>DP</option>
                <option value="dp_dibayar" {{ request('status') === 'dp_dibayar' ? 'selected' : '' }}>DP Dibayar</option>
                <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                <option value="gagal" {{ request('status') === 'gagal' ? 'selected' : '' }}>Gagal</option>
            </select>
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-gray-800 transition-all cursor-pointer">Cari</button>
            @if($adaFilter)
                <a href="{{ route('admin.transaksi.index', ['tab' => $tab]) }}" class="text-xs font-bold text-gray-500 hover:text-amber-600">Reset</a>
            @endif
        </div>
    </form>

    {{-- Tabel Transaksi --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Kode / Tanggal</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelanggan</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Produk / Sewa</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Total</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80">
                    @forelse ($transaksis as $t)
                        @php
                            $items = $t->detail;
                            $jumlahProduk = $items->count();
                            $totalQty = (int) $items->sum('jumlah');
                            $pertama = $items->first();
                            $namaPelanggan = $t->user->name ?? $t->nama_tamu ?? '-';
                        @endphp

                        {{-- Baris utama: 1 transaksi = 1 baris --}}
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $t->kode_transaksi }}</div>
                                <div class="text-xs text-gray-500">{{ $t->tanggal->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-800">{{ $namaPelanggan }}</div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span class="text-[11px] text-gray-500">{{ $t->jenis === 'sewa' ? 'Penyewaan' : 'Pembelian' }}</span>
                                    @if(($t->channel ?? 'online') === 'kasir')
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-indigo-600 ring-1 ring-indigo-200">Kasir</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-50 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-gray-500 ring-1 ring-gray-200">Online</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-700 font-medium">{{ $pertama?->produk->nama ?? '-' }}</div>
                                @if($jumlahProduk > 1 || $totalQty > 1)
                                    <div class="mt-1 text-[10px] font-semibold text-gray-400">
                                        @if($jumlahProduk > 1)+{{ $jumlahProduk - 1 }} produk lain &middot; @endif{{ $totalQty }} barang
                                    </div>
                                @endif
                                @if($t->sewa)
                                    <div class="text-[10px] text-amber-700 mt-1 font-semibold">
                                        {{ \Carbon\Carbon::parse($t->sewa->tanggal_pinjam)->format('d M') }} -
                                        {{ \Carbon\Carbon::parse($t->sewa->tanggal_kembali_rencana)->format('d M') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                Rp {{ number_format($t->total, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColor = match($t->status_pembayaran) {
                                        'lunas', 'dp_dibayar' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        default => 'bg-gray-100 text-gray-600 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold border uppercase tracking-wider {{ $statusColor }}">
                                    {{ str_replace('_', ' ', $t->status_pembayaran) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" data-toggle-rincian="{{ $t->id }}" aria-expanded="false" aria-controls="rincian-{{ $t->id }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 active:scale-95 transition-all shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                                        Item
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" data-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <a href="{{ route('admin.transaksi.show', $t->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 active:scale-95 transition-all shadow-sm">
                                        Detail
                                    </a>
                                    @php
                                        $bisaRestock = ($t->channel ?? null) === 'kasir' && strtolower((string) ($t->sewa->status ?? '')) !== 'selesai';
                                    @endphp
                                    <button type="button"
                                            data-modal-open="modal-hapus-transaksi"
                                            data-hapus-url="{{ route('admin.transaksi.destroy', $t->id) }}"
                                            data-kode="{{ $t->kode_transaksi }}"
                                            data-pelanggan="{{ $namaPelanggan }}"
                                            data-total="Rp {{ number_format($t->total, 0, ',', '.') }}"
                                            data-status="{{ str_replace('_', ' ', $t->status_pembayaran) }}"
                                            data-restock="{{ $bisaRestock ? '1' : '0' }}"
                                            aria-label="Hapus transaksi {{ $t->kode_transaksi }}"
                                            title="Hapus transaksi"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 shadow-sm transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Baris rincian: tampil saat tombol Item diklik --}}
                        <tr id="rincian-{{ $t->id }}" class="hidden bg-gray-50/60">
                            <td colspan="6" class="px-6 py-4">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                    @forelse($items as $d)
                                        @php
                                            $foto = $d->produk->foto_produk ?? null;
                                            if (is_string($foto)) {
                                                $dec = json_decode($foto, true);
                                                $foto = is_array($dec) ? $dec : [$foto];
                                            }
                                            $foto = is_array($foto) ? collect($foto)->filter()->first() : null;
                                            $fotoSrc = $foto ? (preg_match('#^https?://#', $foto) ? $foto : asset('storage/' . ltrim($foto, '/'))) : null;
                                        @endphp
                                        <div class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-3">
                                            <div class="flex h-14 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                                @if($fotoSrc)
                                                    <img src="{{ $fotoSrc }}" alt="{{ $d->produk->nama ?? '' }}" loading="lazy" class="h-full w-full object-cover">
                                                @else
                                                    <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-xs font-bold text-gray-900">{{ $d->produk->nama ?? 'Produk Terhapus' }}</p>
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    @if($d->ukuran)
                                                        <span class="rounded-md bg-gray-50 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 ring-1 ring-gray-200">Ukuran {{ $d->ukuran }}</span>
                                                    @endif
                                                    @if($d->warna)
                                                        <span class="rounded-md bg-gray-50 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 ring-1 ring-gray-200">Warna {{ $d->warna }}</span>
                                                    @endif
                                                    @if($d->produk?->sku)
                                                        <span class="text-[10px] text-gray-400">{{ $d->produk->sku }}</span>
                                                    @endif
                                                </div>
                                                <p class="mt-1 text-[11px] text-gray-500 tabular-nums">{{ $d->jumlah }} &times; Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</p>
                                            </div>
                                            <p class="shrink-0 text-xs font-bold tabular-nums text-gray-900">Rp {{ number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') }}</p>
                                        </div>
                                    @empty
                                        <p class="col-span-full text-xs italic text-gray-400">Belum ada item pada transaksi ini.</p>
                                    @endforelse
                                </div>

                                @if($t->catatan)
                                    <p class="mt-3 rounded-xl bg-amber-50/70 px-3 py-2 text-xs text-amber-900 ring-1 ring-amber-100">
                                        <span class="font-bold">Catatan:</span> {{ $t->catatan }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">
                                {{ $adaFilter || $tab !== 'semua' ? 'Tidak ada transaksi yang cocok dengan filter ini.' : 'Belum ada data transaksi.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $transaksis->links() }}
        </div>
    </div>
</div>

{{-- Modal konfirmasi hapus (satu modal dipakai semua baris) --}}
<x-modal id="modal-hapus-transaksi" title="Hapus transaksi ini?" size="sm">
    <form id="form-hapus-transaksi" method="POST" action="#" class="space-y-4">
        @csrf
        @method('DELETE')

        <p class="text-sm text-gray-600">Transaksi berikut akan dihapus permanen beserta item dan riwayat pembayarannya.</p>

        <dl class="space-y-1.5 rounded-2xl bg-gray-50 px-4 py-3 text-xs ring-1 ring-gray-100">
            <div class="flex justify-between gap-4"><dt class="text-gray-400">Kode</dt><dd id="hapus-kode" class="font-bold text-gray-900"></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-400">Pelanggan</dt><dd id="hapus-pelanggan" class="font-semibold text-gray-800"></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-400">Total</dt><dd id="hapus-total" class="font-bold tabular-nums text-gray-900"></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-400">Status</dt><dd id="hapus-status" class="font-semibold uppercase text-gray-800"></dd></div>
        </dl>

        <input type="hidden" name="restock" value="0">
        <label class="flex cursor-pointer items-start gap-3 text-xs text-gray-700">
            <input type="checkbox" name="restock" value="1" id="hapus-restock" class="mt-0.5 h-4 w-4 rounded border-gray-300 accent-amber-600">
            <span>
                <strong class="block text-gray-900">Kembalikan stok produk</strong>
                <span class="text-gray-400">Centang jika stok barang sudah terpotong oleh transaksi ini.</span>
            </span>
        </label>

        <p class="text-[11px] font-semibold text-rose-600">Total pendapatan ikut berkurang. Tindakan ini tidak bisa dibatalkan.</p>
    </form>

    <x-slot:footer>
        <button type="button" class="ui-btn ui-btn--ghost" data-modal-close>Batal</button>
        <button type="submit" form="form-hapus-transaksi" id="btn-hapus-konfirmasi" class="ui-btn ui-btn--danger">Ya, hapus</button>
    </x-slot:footer>
</x-modal>

<script>
    // Isi modal hapus sesuai baris yang diklik
    (function () {
        var modal = document.getElementById('modal-hapus-transaksi');
        var form = document.getElementById('form-hapus-transaksi');
        var tombol = document.getElementById('btn-hapus-konfirmasi');

        modal.addEventListener('modal:open', function (e) {
            var b = e.detail.trigger;
            if (!b) return;
            form.action = b.dataset.hapusUrl;
            document.getElementById('hapus-kode').textContent = b.dataset.kode;
            document.getElementById('hapus-pelanggan').textContent = b.dataset.pelanggan;
            document.getElementById('hapus-total').textContent = b.dataset.total;
            document.getElementById('hapus-status').textContent = b.dataset.status;
            document.getElementById('hapus-restock').checked = b.dataset.restock === '1';
            tombol.disabled = false;
            tombol.textContent = 'Ya, hapus';
        });

        // Cegah klik ganda
        form.addEventListener('submit', function () {
            tombol.disabled = true;
            tombol.textContent = 'Menghapus...';
        });
    })();

    // Buka/tutup rincian item per transaksi
    document.querySelectorAll('[data-toggle-rincian]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var baris = document.getElementById('rincian-' + btn.dataset.toggleRincian);
            var buka = baris.classList.toggle('hidden') === false;
            btn.setAttribute('aria-expanded', buka ? 'true' : 'false');
            btn.querySelector('[data-chevron]').classList.toggle('rotate-180', buka);
        });
    });
</script>
@endsection