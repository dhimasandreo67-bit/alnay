@extends('admin.layouts.admin')
@section('title', 'Rating & Ulasan')

@section('content')
<div class="p-6 space-y-6 max-w-6xl mx-auto">
    
    <div>
        <h1 class="text-2xl font-black text-gray-900">Rating & Ulasan</h1>
        <p class="text-xs text-gray-500 mt-1">Pantau kepuasan pelanggan terhadap busana dan layanan ALNAY.ID.</p>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 text-xl font-bold">★</div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Rating</p>
                <h3 class="text-xl font-black text-gray-900">{{ number_format($rataRata, 1) }} / 5.0</h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-600 text-xl font-bold">💬</div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Ulasan</p>
                <h3 class="text-xl font-black text-gray-900">{{ $totalUlasan }} Ulasan</h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl font-bold">🔥</div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bintang 5</p>
                <h3 class="text-xl font-black text-gray-900">{{ $bintang5 }} Ulasan</h3>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex gap-4 items-center">
        <select name="bintang" onchange="this.form.submit()" class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none">
            <option value="">Semua Bintang</option>
            <option value="5" {{ request('bintang') == '5' ? 'selected' : '' }}>Bintang 5</option>
            <option value="4" {{ request('bintang') == '4' ? 'selected' : '' }}>Bintang 4</option>
            <option value="3" {{ request('bintang') == '3' ? 'selected' : '' }}>Bintang 3</option>
            <option value="2" {{ request('bintang') == '2' ? 'selected' : '' }}>Bintang 2</option>
            <option value="1" {{ request('bintang') == '1' ? 'selected' : '' }}>Bintang 1</option>
        </select>
    </form>

    {{-- List Ulasan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($ratings as $r)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-amber-300 flex items-center justify-center text-white font-bold">
                            {{ substr($r->user->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $r->user->name ?? 'User' }}</p>
                            <p class="text-[10px] text-gray-500">{{ $r->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex text-amber-400 text-sm">
                        @for($i=1; $i<=5; $i++) {{ $i <= $r->rating ? '★' : '☆' }} @endfor
                    </div>
                </div>
                <p class="text-sm text-gray-700 italic mb-3">"{{ $r->ulasan ?? 'Tanpa komentar.' }}"</p>
                <div class="text-[11px] text-gray-500 bg-gray-50 p-2 rounded-lg inline-block">
                    Produk: <strong>{{ $r->produk->nama ?? '-' }}</strong>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-10 bg-white rounded-2xl border border-gray-100 text-gray-500 text-sm">Belum ada ulasan.</div>
        @endforelse
    </div>

    <div>{{ $ratings->links() }}</div>

</div>
@endsection