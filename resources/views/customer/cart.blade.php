@extends('layouts.public')

@section('title', 'Keranjang Belanja - ALNAY.ID')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
    .font-display { font-family: 'Playfair Display', serif; }
    body { font-family: 'Inter', sans-serif; }
    .kr-rise { animation: krRise .55s cubic-bezier(.22, 1, .36, 1) both; animation-delay: var(--d, 0s); }
    @keyframes krRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    .kr-check { appearance: none; -webkit-appearance: none; flex-shrink: 0; width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 6px; background: #fff center / 12px no-repeat; cursor: pointer; transition: all .2s ease; }
    .kr-check:hover { border-color: #c99a32; }
    .kr-check:focus-visible { outline: 2px solid #c99a32; outline-offset: 2px; }
    .kr-check:checked { background-color: #c99a32; border-color: #c99a32; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7.5'/%3E%3C/svg%3E"); }
    .kr-check:indeterminate { background-color: #c99a32; border-color: #c99a32; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round'%3E%3Cpath d='M6 12h12'/%3E%3C/svg%3E"); }
    .kr-item { transition: box-shadow .3s ease, border-color .3s ease, background-color .3s ease; }
    .kr-item:hover { box-shadow: 0 14px 30px -18px rgba(21, 35, 61, .28); border-color: #e6d3a7; }
    .kr-item.is-selected { border-color: #c99a32; background-color: #fffdf7; box-shadow: 0 0 0 3px rgba(201, 154, 50, .12); }
    .kr-item img { transition: transform .6s cubic-bezier(.22, 1, .36, 1); }
    .kr-item:hover img { transform: scale(1.05); }
    .kr-qty input { -moz-appearance: textfield; appearance: textfield; }
    .kr-qty input::-webkit-outer-spin-button, .kr-qty input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .kr-qty button { transition: background-color .2s ease, color .2s ease; }
    .kr-qty button:hover:not(:disabled) { background-color: #faf3e0; color: #a37b2a; }
    .kr-qty button:disabled { opacity: .35; cursor: not-allowed; }
    @media (prefers-reduced-motion: reduce) { .kr-rise { animation: none; } .kr-item, .kr-item img { transition: none; } }

    /* Animasi Transisi Halaman (Smooth Toggle) */
@view-transition {
    navigation: auto;
}

::view-transition-old(root) {
    animation: 300ms cubic-bezier(0.4, 0, 0.2, 1) both fade-out;
}

::view-transition-new(root) {
    animation: 300ms cubic-bezier(0.4, 0, 0.2, 1) both fade-in;
}

@keyframes fade-out {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.98); }
}

@keyframes fade-in {
    from { opacity: 0; transform: scale(1.02); }
    to { opacity: 1; transform: scale(1); }
}
</style>

@if(session('success'))
<div id="notif-success" role="status" class="max-w-7xl mx-auto mt-6 px-4 transition-all duration-500 ease-in-out" style="opacity: 1; transform: translateY(0);">
    <div class="flex items-center gap-3 rounded-xl border px-4 py-3" style="background-color: #f0fdf4; border-color: #86efac;">
        <svg class="w-5 h-5 shrink-0" style="color: #16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        <p class="text-sm font-semibold" style="color: #15803d;">{{ session('success') }}</p>
        <button type="button" onclick="closeNotif()" aria-label="Tutup notifikasi" class="ml-auto rounded-md p-1 text-green-600 transition hover:bg-green-100 hover:text-green-800"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
    </div>
</div>
<script>setTimeout(function() { closeNotif(); }, 3000); function closeNotif() { const notif = document.getElementById('notif-success'); if (notif) { notif.style.opacity = '0'; notif.style.transform = 'translateY(-10px)'; setTimeout(function() { notif.remove(); }, 500); } }</script>
@endif

@if(session('error'))
<div role="alert" class="max-w-7xl mx-auto mt-6 px-4">
    <div class="flex items-center gap-3 rounded-xl border px-4 py-3" style="background-color: #fef2f2; border-color: #fecaca;">
        <svg class="w-5 h-5 shrink-0" style="color: #dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        <p class="text-sm font-semibold" style="color: #b91c1c;">{{ session('error') }}</p>
    </div>
</div>
@endif

<section class="max-w-7xl mx-auto px-4 pt-10 pb-32 lg:pb-14">
    
    {{-- HEADER & TAB MINIMALIS (Posisi Tengah & Tombol Lanjut Belanja) --}}
    <div class="kr-rise mb-8">
        
        {{-- Container Tab di Tengah --}}
        <div class="flex items-center justify-between w-full border-b border-gray-200 relative pb-4">
            
            {{-- Spacer Kiri (Agar tab tetap di tengah persis) --}}
            <div class="w-40 hidden md:block"></div>

            {{-- Tab Tengah --}}
            <div class="flex items-center gap-8 sm:gap-12 -mb-[1px] mx-auto md:mx-0">
                {{-- Tab Keranjang Beli --}}
                <a href="{{ route('keranjang.index') }}" 
                class="pb-4 text-sm font-medium transition-all flex items-center gap-2 border-b-2 
                {{ request()->routeIs('keranjang.index') 
                    ? 'border-[#c99a32] text-[#c99a32]' 
                    : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                    Keranjang Beli
                </a>

                {{-- Tab Keranjang Sewa --}}
                <a href="{{ route('sewa.keranjang.index') }}" 
                class="pb-4 text-sm font-medium transition-all flex items-center gap-2 border-b-2 active:scale-95
                {{ request()->routeIs('sewa.keranjang.index') 
                    ? 'border-[#c99a32] text-[#c99a32]' 
                    : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                    Keranjang Sewa
                </a>
            </div>

            {{-- TOMBOL LANJUT BELANJA (Kanan Atas) --}}
            <div class="hidden md:flex w-40 justify-end">
                <a href="{{ route('koleksi.index') }}"
                   class="group flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-[#a37b2a] transition-colors duration-300">
                    <span class="flex items-center justify-center w-7 h-7 rounded-full border border-gray-200 bg-white text-gray-400 
                                 group-hover:border-[#c99a32] group-hover:bg-[#fbf6e9] group-hover:text-[#c99a32] 
                                 group-hover:-translate-y-0.5 group-hover:shadow-sm transition-all duration-300">
                        <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </span>
                    <span class="tracking-wide uppercase">Lanjut Belanja</span>
                </a>
            </div>
        </div>
        
        {{-- Info Jumlah Produk (Jika ada) --}}
        @if(count($cartItems) > 0)
            <div class="text-center mt-4">
                <p class="text-xs text-gray-400 font-medium">{{ count($cartItems) }} produk siap dibayar.</p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="{{ count($cartItems) == 0 ? 'lg:col-span-3' : 'lg:col-span-2' }}">
            @if(count($cartItems) == 0)
                <div class="kr-rise rounded-3xl border border-dashed bg-white px-6 py-14 md:py-16 text-center max-w-2xl mx-auto shadow-sm" style="border-color: #d9cfb8; --d: .05s;">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full" style="background-color: #f8f1df; color: #c99a32;">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <h3 class="font-display text-2xl font-bold mb-2" style="color: #15233d;">Keranjang belanja masih kosong</h3>
                    <p class="mx-auto mb-8 max-w-sm text-sm leading-relaxed text-gray-500">Yuk, cari busana adat favoritmu dan mulai beli sekarang juga.</p>
                    <a href="{{ route('koleksi.index') }}" class="inline-flex items-center gap-2 rounded-xl px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 transition hover:-translate-y-1 hover:shadow-amber-500/50 active:translate-y-0" style="background-color: #c99a32;">
                        Mulai Belanja 
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                        </svg>
                    </a>
                </div>
            @else
                <div class="kr-rise mb-4 flex items-center justify-between gap-3 rounded-2xl border bg-white px-4 py-3 shadow-sm" style="border-color: #f0ece2; --d: .04s;">
                    <label class="flex cursor-pointer select-none items-center gap-3"><input type="checkbox" id="select-all" class="kr-check"><span class="text-sm font-semibold" style="color: #15233d;">Pilih Semua (<span id="total-items">{{ count($cartItems) }}</span> Barang)</span></label>
                    <form action="/keranjang/hapus-pilihan" method="POST" id="form-hapus-pilihan">
                        @csrf
                        <button type="submit" id="btn-hapus-pilihan" disabled class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700 disabled:cursor-not-allowed disabled:text-gray-300 disabled:hover:bg-transparent"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg> Hapus Pilihan <span id="jml-terpilih"></span></button>
                    </form>
                </div>

                <div class="space-y-4">
                    @foreach($cartItems as $id => $item)
                        @php
                            $idAsli = (string) $id;
                            $idSafe = htmlspecialchars($idAsli, ENT_QUOTES, 'UTF-8');
                        @endphp
                        <div class="kr-rise" style="--d: {{ min($loop->index, 6) * 0.06 + 0.1 }}s;">
                            <article data-item class="kr-item rounded-2xl border bg-white p-4 shadow-sm md:p-5" style="border-color: #f0ece2;">
                                <div class="flex gap-4">
                                    <div class="flex items-start pt-1"><input type="checkbox" class="kr-check item-checkbox" value="{{ $idSafe }}" aria-label="Pilih {{ $item['nama'] }}"></div>
                                    <div class="h-28 w-24 shrink-0 overflow-hidden rounded-xl bg-gray-100 md:h-36 md:w-28">
                                        @php
                                            $fotoItem = $item['foto'];
                                            if (is_array($fotoItem)) {
                                                $fotoItem = $fotoItem[0] ?? null;
                                            }
                                        @endphp
                                        @if($fotoItem)
                                            <img src="{{ asset('storage/' . $fotoItem) }}" alt="{{ $item['nama'] }}" loading="lazy" class="h-full w-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">Foto</div>
                                        @endif
                                    </div>
                                    <div class="flex min-w-0 flex-1 flex-col">
                                        <div class="flex items-start justify-between gap-3">
                                            <h3 class="line-clamp-2 text-sm font-bold leading-snug md:text-base" style="color: #15233d;">{{ $item['nama'] }}</h3>
                                            <form action="/keranjang/hapus/{{ $idSafe }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus item ini?')" class="shrink-0">
                                                @csrf @method('DELETE')
                                                <button type="submit" aria-label="Hapus {{ $item['nama'] }}" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                            </form>
                                        </div>
                                        @php
                                            $specCart = collect([
                                                !empty($item['ukuran']) ? 'Ukuran ' . $item['ukuran'] : null,
                                                !empty($item['warna']) ? 'Warna ' . $item['warna'] : null,
                                                ($item['mode'] ?? 'beli') === 'sewa' ? 'Sewa' : null,
                                            ])->filter()->implode(' · ');
                                        @endphp
                                        @if ($specCart)
                                            <p class="mt-1 text-xs text-gray-500">{{ $specCart }}</p>
                                        @endif
                                        @if (isset($item['tersedia']) && $item['tersedia'] < $item['qty'])
                                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $item['tersedia'] > 0 ? 'Stok tinggal ' . $item['tersedia'] . ' unit — kurangi jumlah.' : 'Stok pilihan ini habis — hapus dari keranjang.' }}</p>
                                        @endif
                                        <div class="mt-auto flex items-end justify-between gap-3 pt-3">
                                            <div>
                                                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Harga</p>
                                                @if (($item['diskon'] ?? 0) > 0)
                                                    <p class="text-xs text-gray-400"><span class="line-through tabular-nums">Rp {{ number_format($item['harga_asli'], 0, ',', '.') }}</span>
                                                        <span class="ml-1 rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-700">-{{ rtrim(rtrim(number_format($item['diskon'], 2, '.', ''), '0'), '.') }}%</span></p>
                                                @endif
                                                <p class="text-lg font-bold tabular-nums md:text-xl" style="color: #c99a32;">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                                            </div>
                                            <form action="/keranjang/update/{{ $idSafe }}" method="POST" id="form-qty-{{ md5($idSafe) }}">
                                                @csrf @method('PATCH')
                                                <div class="kr-qty flex items-center overflow-hidden rounded-lg border bg-white" style="border-color: #d1d5db;">
                                                    <button type="button" data-qty-step="-1" data-id="{{ md5($idSafe) }}" aria-label="Kurangi jumlah" @disabled($item['qty'] <= 1) class="flex h-9 w-9 items-center justify-center text-lg font-semibold text-gray-600">−</button>
                                                    <input type="number" name="qty" id="qty-{{ md5($idSafe) }}" value="{{ $item['qty'] }}" min="1" max="10" readonly aria-label="Jumlah" class="h-9 w-10 border-x text-center text-sm font-semibold focus:outline-none" style="border-color: #d1d5db;">
                                                    <button type="button" data-qty-step="1" data-id="{{ md5($idSafe) }}" aria-label="Tambah jumlah" @disabled($item['qty'] >= 10) class="flex h-9 w-9 items-center justify-center text-lg font-semibold text-gray-600">+</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if(count($cartItems) > 0)
        <div class="kr-rise lg:col-span-1" style="--d: .18s;">
            <div class="rounded-2xl border bg-white p-6 shadow-sm lg:sticky lg:top-24" style="border-color: #f0ece2;">
                <h2 class="font-display mb-6 text-xl font-bold" style="color: #15233d;">Ringkasan Pembelian</h2>
                <div class="mb-3 flex items-center justify-between"><span class="text-sm text-gray-500">Subtotal ({{ count($cartItems) }} Produk)</span><span class="text-sm font-bold tabular-nums" style="color: #15233d;">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                
                <div class="my-5 border-t border-dashed" style="border-color: #e5e0d3;"></div>
                <div class="mb-6 flex items-end justify-between">
                    <span class="text-base font-bold" style="color: #15233d;">Total Harga Barang</span>
                    <span class="text-2xl font-bold tabular-nums" style="color: #c99a32;">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="mb-6 flex items-start gap-2.5 rounded-xl px-3.5 py-3" style="background-color: #faf6ea;"><svg class="mt-0.5 h-4 w-4 shrink-0" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg><p class="text-xs leading-relaxed text-gray-600">Tanpa biaya deposit jaminan sewa (barang menjadi hak milik).</p></div>
                <a href="{{ route('checkout.index') }}" class="flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0" style="background-color: #c99a32;">Lanjut ke Pembayaran <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg></a>
                {{-- Tombol Lanjut Berbelanja Lama Dihapus --}}
            </div>
        </div>
        @endif
    </div>
</section>

@if(count($cartItems) > 0)
<div class="fixed inset-x-0 bottom-0 z-40 border-t bg-white/95 px-4 pt-3 shadow-[0_-8px_24px_-12px_rgba(21,35,61,.25)] backdrop-blur lg:hidden" style="border-color: #ebe7df; padding-bottom: max(.75rem, env(safe-area-inset-bottom));">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
        <div><p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Grand Total</p><p class="text-lg font-bold tabular-nums" style="color: #c99a32;">Rp {{ number_format($total, 0, ',', '.') }}</p></div>
        <a href="{{ route('checkout.index') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-md active:scale-[.98]" style="background-color: #c99a32;">Bayar <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg></a>
    </div>
</div>
@endif

<script>
    (function () {
        const all = document.getElementById('select-all');
        const boxes = Array.from(document.querySelectorAll('.item-checkbox'));
        const btn = document.getElementById('btn-hapus-pilihan');
        const jml = document.getElementById('jml-terpilih');

        function sinkron() {
            const n = boxes.filter(function (b) { return b.checked; }).length;
            if (all) { all.checked = n > 0 && n === boxes.length; all.indeterminate = n > 0 && n < boxes.length; }
            if (btn) btn.disabled = n === 0;
            if (jml) jml.textContent = n > 0 ? '(' + n + ')' : '';
            boxes.forEach(function (b) { const kartu = b.closest('[data-item]'); if (kartu) kartu.classList.toggle('is-selected', b.checked); });
        }

        if (all) { all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); sinkron(); }); }
        boxes.forEach(function (b) { b.addEventListener('change', sinkron); });
        sinkron();

        document.querySelectorAll('[data-qty-step]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                const id = tombol.dataset.id;
                const input = document.getElementById('qty-' + id);
                const form = document.getElementById('form-qty-' + id);
                const baru = parseInt(input.value, 10) + parseInt(tombol.dataset.qtyStep, 10);
                if (isNaN(baru) || baru < 1 || baru > 10) return;
                input.value = baru;
                form.querySelectorAll('button').forEach(function (b) { b.disabled = true; });
                form.classList.add('opacity-60');
                form.submit();
            });
        });

        const formHapus = document.getElementById('form-hapus-pilihan');
        if (formHapus) {
            formHapus.addEventListener('submit', function (e) {
                formHapus.querySelectorAll('input[name="ids[]"][type="hidden"]').forEach(function (el) { el.remove(); });
                const terpilih = boxes.filter(function (b) { return b.checked; });
                if (terpilih.length === 0) { e.preventDefault(); return; }
                if (!confirm('Yakin ingin menghapus ' + terpilih.length + ' item terpilih?')) { e.preventDefault(); return; }
                terpilih.forEach(function (cb) { const input = document.createElement('input'); input.type = 'hidden'; input.name = 'ids[]'; input.value = cb.value; formHapus.appendChild(input); });
            });
        }
    })();
</script>

@endsection