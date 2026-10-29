@extends('admin.layouts.admin')
@section('title', 'Pengiriman')

@section('content')
    <div class="p-6 space-y-6 max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengiriman</h1>
                <p class="text-xs text-gray-500 mt-1">Kelola pengiriman busana ke pelanggan ALNAY.ID.</p>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Menunggu Dikirim</p>
                    <h3 class="text-xl font-black text-gray-900">{{ $totalMenunggu }} Pesanan</h3>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sedang Dikirim</p>
                    <h3 class="text-xl font-black text-gray-900">{{ $totalDikirim }} Pesanan</h3>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Diterima</p>
                    <h3 class="text-xl font-black text-gray-900">{{ $totalDiterima }} Pesanan</h3>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <form method="GET" action="{{ route('admin.pengiriman.index') }}"
            class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode transaksi atau nama..."
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()"
                    class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none">
                    <option value="semua" {{ $filter === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="menunggu" {{ $filter === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="dikirim" {{ $filter === 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                    <option value="diterima" {{ $filter === 'diterima' ? 'selected' : '' }}>Diterima</option>
                </select>
                <button type="submit"
                    class="bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-gray-800 transition">Cari</button>
                @if (request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.pengiriman.index') }}"
                        class="text-xs font-semibold text-gray-500 hover:text-gold">Reset</a>
                @endif
            </div>
        </form>

        {{-- Tabel Pengiriman --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-4">Kode Transaksi</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Alamat Tujuan</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengirimanList as $p)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">{{ $p->transaksi->kode_transaksi ?? '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $p->transaksi->detail->first()->produk->nama ?? 'Produk' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $p->transaksi->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $p->transaksi->user->no_hp ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                {{ $p->kota_tujuan ?? '-' }}, {{ $p->provinsi_tujuan ?? '-' }}
                                <br><span class="text-gray-400">{{ Str::limit($p->alamat_tujuan, 40) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($p->status_kirim === 'diterima')
                                    <span
                                        class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded text-[10px] font-bold">DITERIMA</span>
                                @elseif($p->status_kirim === 'dikirim')
                                    <span
                                        class="px-2 py-1 bg-sky-50 text-sky-600 border border-sky-200 rounded text-[10px] font-bold">DIKIRIM</span>
                                @else
                                    <span
                                        class="px-2 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded text-[10px] font-bold">MENUNGGU</span>
                                @endif
                            </td>
                                                        <td class="px-6 py-4 text-right">
                                @if ($p->status_kirim === 'menunggu')
                                    {{-- Form Kirim Barang --}}
                                    <form action="{{ route('admin.pengiriman.update-status', $p->id) }}" method="POST" class="flex flex-col gap-1 items-end">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status_kirim" value="dikirim">
                                        <div class="flex gap-1">
                                            <input type="text" name="kurir" placeholder="Kurir (JNE, dll)" required
                                                class="px-2 py-1 text-xs border border-gray-200 rounded w-24">
                                            <input type="text" name="no_resi" placeholder="No. Resi" required
                                                class="px-2 py-1 text-xs border border-gray-200 rounded w-28">
                                        </div>
                                        <button type="submit" class="px-3 py-1.5 bg-sky-600 text-white rounded text-xs font-semibold hover:bg-sky-700 w-full">
                                            Kirim
                                        </button>
                                    </form>

                                @elseif($p->status_kirim === 'dikirim')
                                    {{-- Info Kurir & Tombol Tandai Diterima --}}
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-gray-800">{{ $p->kurir ?? '-' }} • {{ $p->no_resi ?? '-' }}</p>
                                        <form action="{{ route('admin.pengiriman.update-status', $p->id) }}" method="POST" class="inline mt-1">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status_kirim" value="diterima">
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-xs font-semibold hover:bg-emerald-700">
                                                Tandai Diterima
                                            </button>
                                        </form>
                                    </div>

                                @else
                                    {{-- Status Diterima & Tombol Batalkan Konfirmasi --}}
                                    <div class="text-right">
                                        <span class="text-xs text-emerald-600 font-bold block mb-1">Diterima ✓</span>
                                        <form action="{{ route('admin.pengiriman.batalkan-terima', $p->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Batalkan konfirmasi penerimaan? Status akan kembali ke Dikirim.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-[10px] text-rose-600 font-semibold hover:underline">
                                                Batalkan Konfirmasi
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada data pengiriman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $pengirimanList->links() }}</div>

    </div>
@endsection
