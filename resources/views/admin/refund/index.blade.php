@extends('admin.layouts.admin')
@section('title', 'Refund & Return')

@section('content')
<div class="p-6 space-y-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Refund & Return</h1>
        <p class="text-xs text-gray-500 mt-1">Kelola pengajuan pengembalian dana atau barang dari pelanggan.</p>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Menunggu</p>
                <h3 class="text-xl font-black text-gray-900">{{ $totalPending }} Pengajuan</h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Disetujui</p>
                <h3 class="text-xl font-black text-gray-900">{{ $totalDisetujui }} Pengajuan</h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Ditolak</p>
                <h3 class="text-xl font-black text-gray-900">{{ $totalDitolak }} Pengajuan</h3>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="bg-white p-2 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('admin.refund.index', ['status' => 'semua']) }}" class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition {{ $filter === 'semua' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">Semua</a>
        <a href="{{ route('admin.refund.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition {{ $filter === 'pending' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-50' }}">Pending</a>
        <a href="{{ route('admin.refund.index', ['status' => 'disetujui']) }}" class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition {{ $filter === 'disetujui' ? 'bg-emerald-100 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' }}">Disetujui</a>
        <a href="{{ route('admin.refund.index', ['status' => 'ditolak']) }}" class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition {{ $filter === 'ditolak' ? 'bg-rose-100 text-rose-700' : 'text-gray-600 hover:bg-gray-50' }}">Ditolak</a>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-4">Kode Transaksi</th>
                    <th class="px-6 py-4">Pelanggan</th>
                    <th class="px-6 py-4">Alasan</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($refundList as $r)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-900">{{ $r->transaksi->kode_transaksi ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $r->transaksi->detail->first()->produk->nama ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900">{{ $r->transaksi->user->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $r->transaksi->user->no_hp ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-600 max-w-xs truncate">{{ $r->alasan ?? '-' }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($r->status === 'disetujui')
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded text-[10px] font-bold">DISETUJUI</span>
                        @elseif($r->status === 'ditolak')
                            <span class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[10px] font-bold">DITOLAK</span>
                        @else
                            <span class="px-2 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded text-[10px] font-bold">PENDING</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($r->status === 'pending')
                            <form action="{{ route('admin.refund.update-status', $r->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="disetujui">
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-xs font-semibold hover:bg-emerald-700">Setujui</button>
                            </form>
                            <form action="{{ route('admin.refund.update-status', $r->id) }}" method="POST" class="inline ml-1">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="ditolak">
                                <button type="submit" class="px-3 py-1.5 bg-rose-600 text-white rounded text-xs font-semibold hover:bg-rose-700">Tolak</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400 italic">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada pengajuan refund.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $refundList->links() }}</div>

</div>
@endsection