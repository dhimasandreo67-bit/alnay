
@extends('admin.layouts.admin')
@section('title', 'Reservasi Fitting')

@section('content')
<div class="p-6 space-y-6 transition-all duration-500 animate-fade-in">

    {{-- ALERT --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm text-sm font-bold" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Reservasi Fitting</h1>
            <p class="text-xs text-gray-500 mt-1">Daftar jadwal kedatangan pelanggan untuk pengukuran busana di studio.</p>
        </div>
    </div>

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('admin.fitting.index') }}" class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode booking atau nama..." 
                   class="w-full pl-10 pr-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <input type="date" name="tanggal" value="{{ request('tanggal') }}" 
                   class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
            
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-gray-800 transition-all cursor-pointer">Cari</button>
            <a href="{{ route('admin.fitting.index') }}" class="text-xs text-gray-500 hover:text-amber-600 font-bold ml-2">Reset</a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Pelanggan</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Jadwal</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Kode Booking</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80 bg-white">
                    @forelse ($reservasis as $r)
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $r->user->name ?? 'User Terhapus' }}</div>
                                <div class="text-xs text-gray-500">{{ $r->user->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($r->slot->tanggal)->format('d M Y') }}</div>
                                <div class="text-[11px] text-amber-600 font-bold">{{ $r->slot->jam_mulai ?? '-' }} WIB</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black font-mono bg-gray-900 text-white tracking-wider">
                                    {{ $r->kode_booking }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badge = match(strtolower($r->status)) {
                                        'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'hadir' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'batal' => 'bg-rose-100 text-rose-700 border-rose-200',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-md border text-[10px] font-bold uppercase tracking-wider {{ $badge }}">
                                    {{ $r->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.fitting.update-status', $r->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-[11px] font-bold cursor-pointer focus:outline-none">
                                        <option value="pending" {{ $r->status == 'pending' ? 'selected' : '' }}>Tandai Pending</option>
                                        <option value="hadir" {{ $r->status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                        <option value="batal" {{ $r->status == 'batal' ? 'selected' : '' }}>Batal</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">Belum ada jadwal reservasi fitting.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $reservasis->links() }}
        </div>
    </div>
</div>
@endsection