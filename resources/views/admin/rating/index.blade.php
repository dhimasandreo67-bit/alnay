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
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500">
                {{-- SVG Bintang --}}
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Rating</p>
                <h3 class="text-xl font-black text-gray-900">{{ number_format($rataRata, 1) }} / 5.0</h3>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-600">
                {{-- SVG Chat Bubble --}}
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Ulasan</p>
                <h3 class="text-xl font-black text-gray-900">{{ $totalUlasan }} Ulasan</h3>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                {{-- SVG Api / Fire --}}
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                </svg>
            </div>
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
                        {{-- Foto Profil Dinamis --}}
                        @if($r->user && $r->user->foto_profil)
                            <img src="{{ asset('storage/' . $r->user->foto_profil) }}" 
                                 alt="{{ $r->user->name }}" 
                                 class="w-10 h-10 rounded-full object-cover border border-gray-200">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-amber-300 flex items-center justify-center text-white font-bold">
                                {{ substr($r->user->name ?? '?', 0, 1) }}
                            </div>
                        @endif
                        
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $r->user->name ?? 'User' }}</p>
                            <p class="text-[10px] text-gray-500">{{ $r->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    
                    {{-- Bintang SVG Dinamis --}}
                    <div class="flex gap-0.5">
                        @for($i=1; $i<=5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $r->rating ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
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