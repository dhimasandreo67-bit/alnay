@extends('layouts.public')

@section('title', 'Keranjang Sewa - ALNAY.ID')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .kr-rise {
            animation: krRise .55s cubic-bezier(.22, 1, .36, 1) both;
            animation-delay: var(--d, 0s);
        }

        @keyframes krRise {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }
    </style>

    <section class="max-w-7xl mx-auto px-4 pt-10 pb-32 lg:pb-14">

        {{-- HEADER & TAB MINIMALIS (Posisi Tengah) --}}
        <div class="kr-rise mb-8">

            {{-- Container Tab di Tengah --}}
            <div class="flex flex-col items-center justify-center w-full border-b border-gray-200 relative">
                <div class="flex items-center gap-8 sm:gap-12 -mb-[1px]">
                    {{-- Tab Keranjang Beli --}}
                    <a href="{{ route('keranjang.index') }}"
                        class="pb-4 text-sm font-medium transition-all flex items-center gap-2 border-b-2 
                   {{ request()->routeIs('keranjang.index')
                       ? 'border-[#c99a32] text-[#c99a32]'
                       : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                        Keranjang Beli
                        @if (session()->has('cart') && count(session('cart')) > 0)
                            <span
                                class="bg-[#fbf6e9] text-[#a37b2a] text-[10px] px-2 py-0.5 rounded-full">{{ count(session('cart')) }}</span>
                        @endif
                    </a>

                    {{-- Tab Keranjang Sewa --}}
                    <a href="{{ route('sewa.keranjang.index') }}"
                        class="pb-4 text-sm font-medium transition-all flex items-center gap-2 border-b-2 
                   {{ request()->routeIs('sewa.keranjang.index')
                       ? 'border-[#c99a32] text-[#c99a32]'
                       : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                        Keranjang Sewa
                        @if (session()->has('keranjang_sewa') && count(session('keranjang_sewa')) > 0)
                            <span
                                class="bg-[#fbf6e9] text-[#a37b2a] text-[10px] px-2 py-0.5 rounded-full">{{ count(session('keranjang_sewa')) }}</span>
                        @endif
                    </a>
                </div>

                {{-- Tombol Tambah Produk Lain (Diposisikan di Kanan) --}}
                <div class="absolute right-0 bottom-3 hidden md:block">
                    <a href="{{ route('koleksi.index') }}"
                    class="group hidden md:flex items-center gap-2.5 text-xs font-bold text-gray-400 hover:text-[#a37b2a] transition-colors duration-300">

                        {{-- Lingkaran Ikon --}}
                        <span class="flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 bg-white text-gray-400 
                                    group-hover:border-[#c99a32] group-hover:bg-[#fbf6e9] group-hover:text-[#c99a32] 
                                    group-hover:-translate-y-0.5 group-hover:shadow-sm transition-all duration-300">
                            
                            {{-- Ikon Shopping Bag (Tas Belanja) Minimalis --}}
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </span>

                        {{-- Teks --}}
                        <span class="tracking-wide uppercase">Lanjut Belanja</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- KONTEN KERANJANG SEWA --}}
        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 font-semibold text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (empty($keranjangSewa))
            {{-- Empty State (Lebih Ramping & Elegan) --}}
            <div class="kr-rise rounded-3xl border border-dashed bg-white px-6 py-14 md:py-16 text-center max-w-2xl mx-auto shadow-sm"
                style="border-color: #d9cfb8;">
                <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full"
                    style="background-color: #f8f1df; color: #c99a32;">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <h3 class="font-display text-2xl font-bold mb-2" style="color: #15233d;">Keranjang sewa masih kosong</h3>
                <p class="mx-auto mb-8 max-w-sm text-sm leading-relaxed text-gray-500">Yuk, cari busana adat favoritmu dan
                    mulai sewa dengan memilih tanggal.</p>
                <a href="{{ route('koleksi.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 transition hover:-translate-y-1 hover:shadow-amber-500/50 active:translate-y-0"
                    style="background-color: #c99a32;">
                    Mulai Sewa
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3">
                        </path>
                    </svg>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 space-y-4">
                    @foreach ($keranjangSewa as $item)
                        <div
                            class="kr-rise bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center hover:border-[#c99a32]/40 transition-colors">
                            <div class="flex gap-4 items-center">
                                @php
                                    $foto = $item['foto_produk'];
                                    if (is_array($foto)) {
                                        $foto = $foto[0] ?? null;
                                    }
                                @endphp
                                @if ($foto)
                                    <img src="{{ asset('storage/' . $foto) }}"
                                        class="w-24 h-24 object-cover rounded-xl shadow-sm" alt="">
                                @else
                                    <div
                                        class="w-24 h-24 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 text-xs border border-gray-100">
                                        Foto</div>
                                @endif

                                <div>
                                    <h2 class="font-bold text-lg" style="color: #15233d;">{{ $item['nama'] }}</h2>
                                    <p class="text-sm text-gray-600 mt-1">Ukuran: <span
                                            class="font-semibold text-gray-900">{{ $item['ukuran'] }}</span></p>
                                    <p class="text-sm text-gray-600">Tanggal:
                                        {{ \Carbon\Carbon::parse($item['tanggal_ambil'])->format('d M') }} s/d
                                        {{ \Carbon\Carbon::parse($item['tanggal_kembali'])->format('d M Y') }}
                                        ({{ $item['durasi'] }} hari)</p>
                                </div>
                            </div>

                            <div
                                class="text-left sm:text-right w-full sm:w-auto mt-2 sm:mt-0 pt-3 sm:pt-0 border-t sm:border-0 border-dashed border-gray-200">
                                <p class="text-xs text-gray-500">Total Sewa</p>
                                <p class="text-lg font-bold tabular-nums" style="color: #15233d;">Rp
                                    {{ number_format($item['total_sewa'], 0, ',', '.') }}</p>
                                <p
                                    class="text-xs text-rose-500 font-bold mt-1.5 bg-rose-50 inline-block px-2.5 py-1 rounded-md">
                                    DP: Rp {{ number_format($item['dp'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Ringkasan Checkout --}}
                <div class="kr-rise lg:col-span-1" style="--d: .18s;">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:sticky lg:top-24">
                        <h2 class="font-display mb-6 text-xl font-bold" style="color: #15233d;">Ringkasan Sewa</h2>
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-sm text-gray-500">Total Pembayaran Sewa</span>
                            <span class="text-2xl font-bold tabular-nums" style="color: #c99a32;">Rp
                                {{ number_format($totalKeseluruhan, 0, ',', '.') }}</span>
                        </div>
                        <div class="my-5 border-t border-dashed border-gray-200"></div>
                        <a href="{{ route('sewa.checkout') }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 transition hover:-translate-y-1 hover:shadow-amber-500/50 active:translate-y-0"
                            style="background-color: #c99a32;">
                            Lanjut ke Checkout Sewa
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection
