@extends('admin.layouts.admin')
@section('title', 'Verifikasi Jaminan')

@section('content')
<div class="p-6 space-y-6 max-w-6xl mx-auto">
    
    <div>
        <h1 class="text-2xl font-black text-gray-900">Verifikasi Jaminan</h1>
        <p class="text-xs text-gray-500 mt-1">Kelola jaminan fisik dan digital dari pelanggan ALNAY.ID.</p>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Menunggu Verifikasi</p>
                <h3 class="text-xl font-black text-gray-900">{{ $totalPending }} Jaminan</h3>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Terverifikasi</p>
                <h3 class="text-xl font-black text-gray-900">{{ $totalVerified }} Jaminan</h3>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Deposit Tertahan</p>
                <h3 class="text-xl font-black text-gray-900">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="bg-white p-2 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('admin.jaminan.index', ['status' => 'semua']) }}" 
           class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition {{ $filter === 'semua' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
            Semua Jaminan
        </a>
        <a href="{{ route('admin.jaminan.index', ['status' => 'pending']) }}" 
           class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition {{ $filter === 'pending' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-50' }}">
            Pending
        </a>
        <a href="{{ route('admin.jaminan.index', ['status' => 'verified']) }}" 
           class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition {{ $filter === 'verified' ? 'bg-emerald-100 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' }}">
            Terverifikasi
        </a>
    </div>

    {{-- Tabel Jaminan --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-4">Pelanggan</th>
                    <th class="px-6 py-4">Produk</th>
                    <th class="px-6 py-4">Metode Jaminan</th>
                    <th class="px-6 py-4 text-right">Deposit</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($jaminanList as $j)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-900">{{ $j->sewa->transaksi->user->name ?? 'User' }}</p>
                        <p class="text-xs text-gray-500">{{ $j->sewa->transaksi->user->no_hp ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-900">{{ $j->sewa->transaksi->detail->first()->produk->nama ?? 'Produk' }}</p>
                        <p class="text-xs text-gray-500">#{{ $j->sewa->transaksi->kode_transaksi ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($j->metode_jaminan === 'ktp_tatap_muka')
                            <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-xs font-semibold border border-blue-200">Fisik (KTP)</span>
                        @else
                            <span class="px-2 py-1 bg-purple-50 text-purple-600 rounded text-xs font-semibold border border-purple-200">Digital (Upload)</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-gray-900">
                        Rp {{ number_format($j->jumlah_deposit, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($j->status_verifikasi === 'verified')
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded text-[10px] font-bold border border-emerald-200">TERVERIFIKASI</span>
                        @else
                            <span class="px-2 py-1 bg-amber-50 text-amber-600 rounded text-[10px] font-bold border border-amber-200">PENDING</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.sewa.show', $j->sewa_id) }}" class="inline-flex items-center gap-1 bg-gray-900 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-gray-800 transition">
                            Cek Jaminan &rarr;
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada data jaminan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $jaminanList->links() }}</div>

</div>
@endsection