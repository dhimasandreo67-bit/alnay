@extends('admin.layouts.admin')
@section('title', 'Rating & Ulasan')

@section('content')
<div class="p-6 space-y-6 max-w-6xl mx-auto transition-all duration-500 animate-fade-in">

    <style>
        .group-shine {
            position: relative;
            overflow: hidden;
        }

        .group-shine::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 20%;
            height: 200%;
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(30deg);
            transition: all 0.6s ease;
        }

        .group-shine:hover::after {
            left: 130%;
        }
    </style>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Rating & Ulasan</h1>
        <p class="text-xs text-gray-500 mt-1">Pantau kepuasan pelanggan terhadap busana dan layanan ALNAY.ID.</p>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

        {{-- Card 1: Rata-rata Rating (Amber) --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-amber-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-800/80">Rata-rata Rating</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-md shadow-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-amber-950 tabular-nums">{{ number_format($rataRata, 1) }}</span>
                <span class="text-sm font-bold text-amber-800/60">/ 5.0</span>
            </div>
            <p class="mt-2 text-xs font-medium text-amber-700/80">Kepuasan pelanggan</p>
        </div>

        {{-- Card 2: Total Ulasan (Sky) --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50/80 via-white to-sky-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-sky-800/80">Total Ulasan</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-blue-500 text-white shadow-md shadow-sky-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-sky-950 tabular-nums">{{ $totalUlasan }}</span>
                <span class="text-sm font-bold text-sky-800/60">Ulasan</span>
            </div>
            <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-sky-700 bg-sky-100/70 px-2.5 py-1 rounded-lg">
                <span class="h-2 w-2 rounded-full bg-sky-500 animate-pulse"></span>
                <span>Ulasan pelanggan</span>
            </div>
        </div>

        {{-- Card 3: Bintang 5 (Emerald) --}}
        <div class="group group-shine relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-100/30 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800/80">Bintang 5</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-emerald-950 tabular-nums">{{ $bintang5 }}</span>
                <span class="text-sm font-bold text-emerald-800/60">Ulasan</span>
            </div>
            <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Rating sempurna</span>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex gap-4 items-center">
        <select name="bintang" onchange="this.form.submit()" aria-label="Filter bintang"
                class="px-4 py-2 bg-gray-50/50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all cursor-pointer">
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
            <div class="group group-shine relative overflow-hidden bg-white rounded-2xl border border-gray-100 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        {{-- Foto Profil Dinamis --}}
                        @if($r->user && $r->user->foto_profil)
                            <img src="{{ asset('storage/' . $r->user->foto_profil) }}"
                                 alt="{{ $r->user->name }}"
                                 class="w-10 h-10 rounded-2xl object-cover border border-amber-100">
                        @else
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-400 flex items-center justify-center text-white font-black shadow-md shadow-amber-500/20">
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
                <div class="inline-flex items-center gap-1.5 rounded-full border border-amber-100 bg-amber-50/60 px-2.5 py-1 text-[11px] text-amber-800">
                    Produk: <strong class="font-bold text-amber-900">{{ $r->produk->nama ?? '-' }}</strong>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-500 border border-amber-100">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-400">Belum ada ulasan.</p>
            </div>
        @endforelse
    </div>

    <div>{{ $ratings->links() }}</div>

</div>
@endsection