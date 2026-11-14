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

        /* Style Checkbox Khusus */
        .kr-check {
            appearance: none;
            -webkit-appearance: none;
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            background: #fff center / 12px no-repeat;
            cursor: pointer;
            transition: all .2s ease;
        }

        .kr-check:hover {
            border-color: #c99a32;
        }

        .kr-check:checked {
            background-color: #c99a32;
            border-color: #c99a32;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7.5'/%3E%3C/svg%3E");
        }

        .kr-check:indeterminate {
            background-color: #c99a32;
            border-color: #c99a32;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round'%3E%3Cpath d='M6 12h12'/%3E%3C/svg%3E");
        }

        .kr-item {
            transition: box-shadow .3s ease, border-color .3s ease;
        }

        .kr-item:hover {
            box-shadow: 0 14px 30px -18px rgba(21, 35, 61, .28);
            border-color: #e6d3a7;
        }

        .kr-item.is-selected {
            border-color: #c99a32;
            background-color: #fffdf7;
        }
    </style>

    <section class="max-w-7xl mx-auto px-4 pt-10 pb-32 lg:pb-14">
        {{-- Header Tab --}}
        <div class="kr-rise mb-8">
            <div class="flex flex-col items-center justify-center w-full border-b border-gray-200 relative pb-4">
                <div class="flex items-center gap-4 sm:gap-12 -mb-[1px]">
                    <a href="{{ route('keranjang.index') }}"
                        class="pb-4 text-sm font-medium transition-all flex items-center gap-2 border-b-2 border-transparent text-gray-400 hover:text-gray-700">
                        Keranjang Beli
                    </a>
                    <a href="{{ route('sewa.keranjang.index') }}"
                        class="pb-4 text-sm font-medium transition-all flex items-center gap-2 border-b-2 border-[#c99a32] text-[#c99a32]">
                        Keranjang Sewa
                    </a>
                </div>
                <div class="absolute right-0 bottom-3 hidden md:block">
                    <a href="{{ route('koleksi.index') }}"
                        class="group flex items-center gap-2.5 text-xs font-bold text-gray-400 hover:text-[#a37b2a] transition-colors duration-300">
                        <span
                            class="flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 bg-white text-gray-400 group-hover:border-[#c99a32] group-hover:bg-[#fbf6e9] group-hover:text-[#c99a32] group-hover:-translate-y-0.5 group-hover:shadow-sm transition-all duration-300">
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </span>
                        <span class="tracking-wide uppercase">Lanjut Belanja</span>
                    </a>
                </div>
            </div>
        </div>

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-semibold text-sm">
                {{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 font-semibold text-sm">
                {{ session('success') }}</div>
        @endif

        @if (empty($keranjangSewa))
            {{-- Empty State --}}
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
                    Mulai Sewa <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3">
                        </path>
                    </svg>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2">

                    {{-- Toolbar Pilih Semua & Hapus Pilihan --}}
                    <div class="kr-rise mb-4 flex items-center justify-between gap-3 rounded-2xl border bg-white px-4 py-3 shadow-sm"
                        style="border-color: #f0ece2; --d: .04s;">
                        <label class="flex cursor-pointer select-none items-center gap-3">
                            <input type="checkbox" id="select-all" class="kr-check">
                            <span class="text-sm font-semibold" style="color: #15233d;">Pilih Semua (<span
                                    id="total-items">{{ count($keranjangSewa) }}</span> Barang)</span>
                        </label>
                        <form action="{{ route('sewa.keranjang.hapusPilihan') }}" method="POST" id="form-hapus-pilihan">
                            @csrf
                            <button type="submit" id="btn-hapus-pilihan" disabled
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700 disabled:cursor-not-allowed disabled:text-gray-300 disabled:hover:bg-transparent">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus Pilihan <span id="jml-terpilih"></span>
                            </button>
                        </form>
                    </div>

                    {{-- Daftar Item --}}
                    <div class="space-y-4">
                        @foreach ($keranjangSewa as $id => $item)
                            <div class="kr-rise" style="--d: {{ min($loop->index, 6) * 0.06 + 0.1 }}s;">
                                <article data-item class="kr-item rounded-2xl border bg-white p-4 shadow-sm md:p-5"
                                    style="border-color: #f0ece2;">
                                    <div class="flex gap-3 md:gap-4">
                                        {{-- Checkbox --}}
                                        <div class="flex items-start pt-1">
                                            <input type="checkbox" class="kr-check item-checkbox"
                                                value="{{ $id }}" aria-label="Pilih {{ $item['nama'] }}">
                                        </div>

                                        {{-- Gambar --}}
                                        <div
                                            class="h-24 w-20 sm:h-28 sm:w-24 shrink-0 overflow-hidden rounded-xl bg-gray-100 md:h-36 md:w-28">
                                            @php
                                                $fotoItem = $item['foto_produk'];
                                                if (is_array($fotoItem)) {
                                                    $fotoItem = $fotoItem[0] ?? null;
                                                }
                                            @endphp
                                            @if ($fotoItem)
                                                <img src="{{ asset('storage/' . $fotoItem) }}" alt="{{ $item['nama'] }}"
                                                    class="h-full w-full object-cover">
                                            @else
                                                <div
                                                    class="w-full h-full flex items-center justify-center text-gray-300 text-xs">
                                                    Foto</div>
                                            @endif
                                        </div>

                                        {{-- Info & Harga --}}
                                        <div class="flex min-w-0 flex-1 flex-col">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <h3 class="line-clamp-2 text-sm font-bold leading-snug md:text-base"
                                                        style="color: #15233d;">{{ $item['nama'] }}</h3>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        @if (!empty($item['ukuran']))
                                                            Ukuran: <span
                                                                class="font-semibold text-gray-800">{{ $item['ukuran'] }}</span>
                                                        @endif
                                                        @if (!empty($item['warna']))
                                                            <span class="mx-1 text-gray-300">·</span>Warna: <span
                                                                class="font-semibold text-gray-800">{{ $item['warna'] }}</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-gray-500">Tanggal:
                                                        {{ \Carbon\Carbon::parse($item['tanggal_ambil'])->format('d M') }}
                                                        s/d
                                                        {{ \Carbon\Carbon::parse($item['tanggal_kembali'])->format('d M Y') }}
                                                        ({{ $item['durasi'] }} hari)</p>
                                                </div>

                                                {{-- Tombol Hapus Tunggal --}}
                                                <form action="{{ route('sewa.keranjang.hapus', $id) }}" method="POST"
                                                    class="shrink-0 form-hapus-item">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" aria-label="Hapus {{ $item['nama'] }}"
                                                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>

                                            <div class="mt-auto flex items-end justify-between gap-3 pt-3">
                                                <div>
                                                    <p
                                                        class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                                                        Total Sewa</p>
                                                    <p class="text-base sm:text-lg font-bold tabular-nums md:text-xl"
                                                        style="color: #c99a32;">Rp
                                                        {{ number_format($item['total_sewa'], 0, ',', '.') }}</p>
                                                    <p
                                                        class="text-xs text-rose-500 font-bold mt-1 bg-rose-50 inline-block px-2 py-0.5 rounded-md">
                                                        DP: Rp {{ number_format($item['dp'], 0, ',', '.') }}</p>
                                                    @if (($item['diskon'] ?? 0) > 0)
                                                        <p class="mt-1 text-[11px] font-semibold text-emerald-600">Sudah
                                                            termasuk diskon sewa
                                                            {{ rtrim(rtrim(number_format($item['diskon'], 2, '.', ''), '0'), '.') }}%
                                                        </p>
                                                    @endif
                                                    @if (isset($item['tersedia']) && $item['tersedia'] <= 0)
                                                        <p class="mt-1 text-[11px] font-semibold text-red-600">Stok sewa
                                                            pilihan ini habis — hapus atau pilih ulang di halaman produk.
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Ringkasan Checkout (Desktop) --}}
                <div class="kr-rise lg:col-span-1" style="--d: .18s;">
                    <div class="rounded-2xl border bg-white p-6 shadow-sm lg:sticky lg:top-24"
                        style="border-color: #f0ece2;">
                        <h2 class="font-display mb-6 text-xl font-bold" style="color: #15233d;">Ringkasan Sewa</h2>

                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm text-gray-500">Subtotal ({{ count($keranjangSewa) }} Produk)</span>
                            <span class="text-sm font-bold tabular-nums" style="color: #15233d;">Rp
                                {{ number_format($totalKeseluruhan, 0, ',', '.') }}</span>
                        </div>

                        <div class="my-5 border-t border-dashed" style="border-color: #e5e0d3;"></div>

                        <div class="mb-6 flex items-end justify-between">
                            <span class="text-base font-bold" style="color: #15233d;">Total Pembayaran</span>
                            <span class="text-2xl font-bold tabular-nums" style="color: #c99a32;">Rp
                                {{ number_format($totalKeseluruhan, 0, ',', '.') }}</span>
                        </div>

                        <div class="mb-6 flex items-start gap-2.5 rounded-xl px-3.5 py-3"
                            style="background-color: #faf6ea;">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" style="color: #c99a32;" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <p class="text-xs leading-relaxed text-gray-600">Pastikan jadwal dan ukuran sudah sesuai
                                sebelum melanjutkan.</p>
                        </div>

                        <a href="{{ route('sewa.checkout') }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0"
                            style="background-color: #c99a32;">
                            Checkout Sewa
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

    {{-- Bar Melayang Bawah (Khusus Mobile) --}}
    @if (count($keranjangSewa) > 0)
        <div class="fixed inset-x-0 bottom-0 z-40 border-t bg-white/95 px-4 pt-3 shadow-[0_-8px_24px_-12px_rgba(21,35,61,.25)] backdrop-blur lg:hidden"
            style="border-color: #ebe7df; padding-bottom: max(.75rem, env(safe-area-inset-bottom));">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Total Pembayaran</p>
                    <p class="text-xl font-bold tabular-nums" style="color: #c99a32;">Rp
                        {{ number_format($totalKeseluruhan, 0, ',', '.') }}</p>
                </div>
                <a href="{{ route('sewa.checkout') }}"
                    class="inline-flex shrink-0 items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-md active:scale-[.98]"
                    style="background-color: #c99a32;">
                    Checkout <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3">
                        </path>
                    </svg>
                </a>
            </div>
        </div>
    @endif
    </section>

    {{-- SCRIPT ANTI-KEDIP & CHECKBOX --}}
    @push('scripts')
        <script>
            // 1. Script Anti-Kedip (Scroll)
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    sessionStorage.setItem('sewaScrollPosition', window.scrollY);
                });
            });
            window.addEventListener('DOMContentLoaded', function() {
                const scrollPos = sessionStorage.getItem('sewaScrollPosition');
                if (scrollPos) {
                    document.documentElement.style.scrollBehavior = 'auto';
                    window.scrollTo(0, parseInt(scrollPos));
                    setTimeout(() => document.documentElement.style.scrollBehavior = 'smooth', 10);
                    sessionStorage.removeItem('sewaScrollPosition');
                }
            });

            // 2. Script Checkbox "Pilih Semua"
            (function() {
                const all = document.getElementById('select-all');
                const boxes = Array.from(document.querySelectorAll('.item-checkbox'));
                const btn = document.getElementById('btn-hapus-pilihan');
                const jml = document.getElementById('jml-terpilih');

                function sinkron() {
                    const n = boxes.filter(b => b.checked).length;
                    if (all) {
                        all.checked = n > 0 && n === boxes.length;
                        all.indeterminate = n > 0 && n < boxes.length;
                    }
                    if (btn) btn.disabled = n === 0;
                    if (jml) jml.textContent = n > 0 ? '(' + n + ')' : '';
                    boxes.forEach(b => {
                        const card = b.closest('[data-item]');
                        if (card) card.classList.toggle('is-selected', b.checked);
                    });
                }

                if (all) all.addEventListener('change', function() {
                    boxes.forEach(b => b.checked = all.checked);
                    sinkron();
                });
                boxes.forEach(b => b.addEventListener('change', sinkron));
                sinkron();

                // 3. Konfirmasi Hapus Pilihan
                const formHapus = document.getElementById('form-hapus-pilihan');
                if (formHapus) {
                    formHapus.addEventListener('submit', function(e) {
                        const terpilih = boxes.filter(b => b.checked);
                        if (terpilih.length === 0) {
                            e.preventDefault();
                            return;
                        }
                        if (formHapus.dataset.ok !== '1') {
                            e.preventDefault();
                            konfirmasi('Yakin ingin menghapus ' + terpilih.length + ' item terpilih?').then(function (ya) {
                                if (ya) { formHapus.dataset.ok = '1'; formHapus.requestSubmit(); }
                            });
                            return;
                        }
                        formHapus.dataset.ok = '';

                        // Hapus input hidden lama jika ada
                        formHapus.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

                        // Tambahkan input hidden baru berdasarkan yang dicentang
                        terpilih.forEach(cb => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = cb.value;
                            formHapus.appendChild(input);
                        });
                    });
                }

                // Script Animasi Hapus Tunggal
                document.querySelectorAll('.form-hapus-item').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const itemWrapper = this.closest('.kr-rise');
                        if (itemWrapper) {
                            itemWrapper.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                            itemWrapper.style.opacity = '0';
                            itemWrapper.style.transform = 'scale(0.9) translateY(10px)';

                            setTimeout(() => {
                                form.submit();
                            }, 400);
                        } else {
                            form.submit();
                        }
                    });
                });

                // Hilangkan confirm pada Hapus Pilihan Massal (Keranjang Sewa)
                const formHapus = document.getElementById('form-hapus-pilihan');
                if (formHapus) {
                    formHapus.addEventListener('submit', function(e) {
                        const terpilih = boxes.filter(b => b.checked);
                        if (terpilih.length === 0) {
                            e.preventDefault();
                            return;
                        }

                        terpilih.forEach(cb => {
                            const wrapper = cb.closest('.kr-rise');
                            if (wrapper) {
                                wrapper.style.transition = 'all 0.4s ease';
                                wrapper.style.opacity = '0';
                                wrapper.style.transform = 'scale(0.9)';
                            }
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = cb.value;
                            formHapus.appendChild(input);
                        });

                        e.preventDefault();
                        setTimeout(() => formHapus.submit(), 400);
                    });
                }
            })();
        </script>
    @endpush
@endsection
