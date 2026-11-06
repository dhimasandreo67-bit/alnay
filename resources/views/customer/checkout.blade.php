@extends('layouts.public')

@section('title', 'Checkout - ALNAY.ID')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
    .font-display { font-family: 'Playfair Display', serif; }
    body { font-family: 'Inter', sans-serif; }

    .ck-rise { animation: ckRise .55s cubic-bezier(.22, 1, .36, 1) both; animation-delay: var(--d, 0s); }
    @keyframes ckRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

    .opsi-radio:checked + .opsi-card .radio-dot {
        background: var(--color-gold, #c99a32);
        border-color: var(--color-gold, #c99a32);
        box-shadow: inset 0 0 0 2px #fff;
    }
    .opsi-radio:focus-visible + .opsi-card { outline: 2px solid var(--color-gold, #c99a32); outline-offset: 2px; }
    .opsi-radio:disabled + .opsi-card { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

    @media (prefers-reduced-motion: reduce) {
        .ck-rise { animation: none; }
    }
</style>

<section class="max-w-7xl mx-auto px-4 py-10">
    <div class="ck-rise mb-8">
        <h1 class="font-display text-3xl md:text-4xl font-bold" style="color: #15233d;">Checkout Pesanan</h1>
        <p class="mt-1.5 text-sm text-gray-500">Lengkapi data pengiriman dan pilih metode pembayaran Anda.</p>
    </div>

    @if (session('error'))
        <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
            @foreach ($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        @csrf

        {{-- KIRI: Form Data --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Card: Metode Penerimaan --}}
            <div class="ck-rise bg-white rounded-2xl border border-gray-100 p-6 shadow-sm" style="--d: .05s;">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Metode Penerimaan</h2>

                <div class="space-y-2">
                    <label class="cursor-pointer block">
                        <input type="radio" name="metode_penerimaan" value="ambil" checked class="opsi-radio peer sr-only">
                        <div class="opsi-card flex items-center gap-3 p-4 rounded-xl border border-gray-200 peer-checked:border-[#c99a32] peer-checked:bg-[#c99a32]/5 hover:border-[#c99a32]/60 transition-all duration-300">
                            <span class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">Ambil di Toko</p>
                                <p class="text-xs text-gray-500">Jl. Terusan Rajabasa No.11, Bandar Lampung</p>
                            </div>
                        </div>
                    </label>

                    <label class="cursor-pointer block">
                        <input type="radio" name="metode_penerimaan" value="kirim" class="opsi-radio peer sr-only">
                        <div class="opsi-card flex items-center gap-3 p-4 rounded-xl border border-gray-200 peer-checked:border-[#c99a32] peer-checked:bg-[#c99a32]/5 hover:border-[#c99a32]/60 transition-all duration-300">
                            <span class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">Kirim ke Alamat</p>
                                <p class="text-xs text-gray-500">Ongkir dihitung otomatis sesuai tujuan dan berat pesanan.</p>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Pengiriman (muncul jika pilih Kirim) --}}
                <div id="alamat-container" class="mt-4 hidden space-y-4">

                    {{-- Alamat tersimpan dari menu "Alamat Saya" --}}
                    @if ($alamatList->isNotEmpty())
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-600">Alamat Pengiriman</p>
                                <a href="{{ route('akun.dashboard') }}#alamat" class="text-xs font-semibold hover:underline" style="color: #c99a32;">Kelola alamat</a>
                            </div>

                            @foreach ($alamatList as $a)
                                <label class="cursor-pointer block">
                                    <input type="radio" name="alamat_id" value="{{ $a->id }}"
                                        @checked((string) old('alamat_id', $alamatList->first()->id) === (string) $a->id)
                                        class="opsi-radio peer sr-only">
                                    <div class="opsi-card flex items-start gap-3 p-4 rounded-xl border border-gray-200 peer-checked:border-[#c99a32] peer-checked:bg-[#c99a32]/5 hover:border-[#c99a32]/60 transition-all duration-300">
                                        <span class="radio-dot mt-0.5 w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-800">
                                                {{ $a->label ?: 'Alamat' }}
                                                @if ($a->is_utama)
                                                    <span class="ml-1 rounded-full bg-[#c99a32]/10 px-2 py-0.5 text-[10px] font-bold" style="color: #c99a32;">Utama</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-600 mt-0.5">{{ $a->nama_penerima }} &middot; {{ $a->no_hp }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $a->alamat }}</p>
                                            <p class="text-xs text-gray-400">{{ collect([$a->kecamatan, $a->kota_kabupaten, $a->provinsi, $a->kode_pos])->filter()->implode(', ') }}</p>
                                        </div>
                                    </div>
                                </label>
                            @endforeach

                            <label class="cursor-pointer block">
                                <input type="radio" name="alamat_id" value="baru"
                                    @checked(old('alamat_id') === 'baru') class="opsi-radio peer sr-only">
                                <div class="opsi-card flex items-center gap-3 p-4 rounded-xl border border-dashed border-gray-300 peer-checked:border-[#c99a32] peer-checked:bg-[#c99a32]/5 hover:border-[#c99a32]/60 transition-all duration-300">
                                    <span class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                                    <p class="text-sm font-semibold text-gray-800">Gunakan alamat lain</p>
                                </div>
                            </label>
                        </div>
                    @else
                        <p class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-800">
                            Anda belum punya alamat tersimpan. Isi di bawah, atau tambahkan di
                            <a href="{{ route('akun.dashboard') }}#alamat" class="font-bold underline">Alamat Saya</a>
                            agar pesanan berikutnya tidak perlu input ulang.
                        </p>
                    @endif

                        {{-- TAMBAHKAN BLOK INI --}}
    <div data-manual class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Penerima</label>
            <input type="text" name="nama_penerima" value="{{ old('nama_penerima', auth()->user()->name ?? '') }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">No. WhatsApp Aktif</label>
            <input type="text" name="no_wa" value="{{ old('no_wa', auth()->user()->no_hp ?? '') }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all">
        </div>
    </div>
    {{-- SAMPAI SINI --}}

                    <div data-manual class="relative">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kelurahan / Kecamatan Tujuan</label>
                        <input type="text" id="q-tujuan" autocomplete="off"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all"
                            placeholder="Ketik minimal 3 huruf, contoh: kemiling">
                        <div id="hasil-tujuan"
                            class="hidden absolute z-30 left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"></div>
                        <p id="info-tujuan" class="mt-1 text-xs text-gray-400"></p>
                    </div>

                    <div>
                        <label data-manual class="block text-xs font-semibold text-gray-600 mb-1">Alamat Lengkap</label>
                        <textarea data-manual name="alamat" rows="2" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all"
                            placeholder="Nama jalan, nomor rumah, RT/RW, patokan">{{ old('alamat', auth()->user()->alamat ?? '') }}</textarea>
                    </div>

                    <div>
                        <p class="block text-xs font-semibold text-gray-600 mb-2">Layanan Pengiriman</p>
                        <div id="opsi-ongkir" class="space-y-2">
                            <p class="text-xs text-gray-400">Pilih tujuan terlebih dahulu.</p>
                        </div>
                    </div>

                    <input type="hidden" name="tujuan_id" id="in-tujuan-id" value="">
                    <input type="hidden" name="tujuan_label" id="in-tujuan-label" value="">
                    <input type="hidden" name="kurir" id="in-kurir" value="">
                    <input type="hidden" name="layanan" id="in-layanan" value="">
                </div>
            </div>

            {{-- Card: Metode Pembayaran --}}
            <div class="ck-rise bg-white rounded-2xl border border-gray-100 p-6 shadow-sm" style="--d: .1s;">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Metode Pembayaran</h2>
                <div class="space-y-2">
                    <label class="cursor-pointer block">
                        <input type="radio" name="metode_bayar" value="qris" checked class="opsi-radio peer sr-only">
                        <div class="opsi-card flex items-center gap-3 p-4 rounded-xl border border-gray-200 peer-checked:border-[#c99a32] peer-checked:bg-[#c99a32]/5 hover:border-[#c99a32]/60 transition-all duration-300">
                            <span class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">QRIS (Semua Bank & Dompet Digital)</p>
                                <p class="text-xs text-gray-500">BCA, Mandiri, OVO, GoPay, ShopeePay, Dana</p>
                            </div>
                        </div>
                    </label>

                    <label class="cursor-pointer block">
                        <input type="radio" name="metode_bayar" value="va" class="opsi-radio peer sr-only">
                        <div class="opsi-card flex items-center gap-3 p-4 rounded-xl border border-gray-200 peer-checked:border-[#c99a32] peer-checked:bg-[#c99a32]/5 hover:border-[#c99a32]/60 transition-all duration-300">
                            <span class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">Virtual Account Bank</p>
                                <p class="text-xs text-gray-500">BCA, Mandiri, BNI, BRI, Permata</p>
                            </div>
                        </div>
                    </label>

                    <label class="cursor-pointer block">
                        <input type="radio" name="metode_bayar" value="ewallet" class="opsi-radio peer sr-only">
                        <div class="opsi-card flex items-center gap-3 p-4 rounded-xl border border-gray-200 peer-checked:border-[#c99a32] peer-checked:bg-[#c99a32]/5 hover:border-[#c99a32]/60 transition-all duration-300">
                            <span class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all"></span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">E-Wallet (GoPay, OVO, ShopeePay)</p>
                                <p class="text-xs text-gray-500">Pilih dompet digital favorit Anda</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- KANAN: Ringkasan --}}
        <div class="lg:col-span-1">
            <div class="ck-rise bg-white rounded-2xl border border-gray-100 p-6 shadow-sm sticky top-24" style="--d: .15s;">
                <h2 class="font-display text-xl font-bold mb-4" style="color: #15233d;">Ringkasan Pesanan</h2>

                <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-2">
                    @foreach ($cartItems as $item)
                        <div class="flex justify-between text-sm">
                            <div class="text-gray-600">
                                <span class="font-semibold text-gray-800">{{ $item['nama'] }}</span><br>
                                @php
                                    $specCk = collect([!empty($item['ukuran']) ? 'Ukuran ' . $item['ukuran'] : null, !empty($item['warna']) ? 'Warna ' . $item['warna'] : null])->filter()->implode(' · ');
                                @endphp
                                @if ($specCk)<span class="text-xs text-gray-500">{{ $specCk }}</span><br>@endif
                                <span class="text-xs">{{ $item['qty'] }}x
                                    @if (($item['diskon'] ?? 0) > 0)<span class="line-through text-gray-400">Rp {{ number_format($item['harga_asli'], 0, ',', '.') }}</span>@endif
                                    Rp {{ number_format($item['harga'], 0, ',', '.') }}</span>
                            </div>
                            <span class="font-bold text-gray-800">Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-4 mb-6 space-y-2">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Ongkos Kirim</span>
                        <span id="row-ongkir" class="font-semibold text-gray-800">Gratis</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                        <span class="text-sm text-gray-500">Total Pembayaran</span>
                        <span id="txt-total" class="text-2xl font-bold" style="color: #c99a32;">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <button type="submit" id="btn-submit"
                    class="w-full py-3.5 rounded-xl text-center text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none"
                    style="background-color: #c99a32;">
                    Buat Pesanan & Bayar
                </button>
            </div>
        </div>
    </form>
</section>

@php
    $alamatJs = $alamatList->map(function ($a) {
        return [
            'id'           => $a->id,
            'tujuan_id'    => $a->ongkir_tujuan_id,
            'tujuan_label' => $a->ongkir_tujuan_label,
        ];
    })->values();
@endphp

<script>
(function () {
    const SUBTOTAL   = {{ (int) $total }};
    const URL_TUJUAN = "{{ route('ongkir.tujuan') }}";
    const URL_HITUNG = "{{ route('ongkir.hitung') }}";
    const TOKEN      = document.querySelector('input[name="_token"]').value;

    const $ = id => document.getElementById(id);
    const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

    let ongkir = 0;
    let timer  = null;

    const isKirim = () => document.querySelector('input[name="metode_penerimaan"]:checked').value === 'kirim';

    function refresh() {
        const kirim = isKirim();
        const pilih = $('in-layanan').value !== '';
        const biaya = kirim && pilih ? ongkir : 0;

        $('row-ongkir').textContent = !kirim ? 'Gratis' : (pilih ? rupiah(biaya) : 'Belum dipilih');
        $('txt-total').textContent  = rupiah(SUBTOTAL + biaya);
        $('btn-submit').disabled    = kirim && !pilih;
    }

    function resetLayanan() {
        $('in-kurir').value   = '';
        $('in-layanan').value = '';
        ongkir = 0;
        refresh();
    }

    function resetTujuan() {
        $('in-tujuan-id').value    = '';
        $('in-tujuan-label').value = '';
        $('info-tujuan').textContent = '';
        $('opsi-ongkir').innerHTML = '<p class="text-xs text-gray-400">Pilih tujuan terlebih dahulu.</p>';
        resetLayanan();
    }

    function pesanOpsi(teks) {
        const p = document.createElement('p');
        p.className = 'text-xs text-gray-400';
        p.textContent = teks;
        $('opsi-ongkir').replaceChildren(p);
    }

    // Tampilkan / sembunyikan blok pengiriman
    document.querySelectorAll('input[name="metode_penerimaan"]').forEach(radio => {
        radio.addEventListener('change', () => {
            $('alamat-container').classList.toggle('hidden', !isKirim());
            terapkanAlamat();
            refresh();
        });
    });

    // Cari tujuan saat mengetik (debounce 350 ms)
    $('q-tujuan').addEventListener('input', function () {
        resetTujuan();
        clearTimeout(timer);

        const q = this.value.trim();
        if (q.length < 3) {
            $('hasil-tujuan').classList.add('hidden');
            return;
        }

        timer = setTimeout(async () => {
            try {
                const res  = await fetch(URL_TUJUAN + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                renderHasil(Array.isArray(data) ? data : []);
            } catch (e) {
                $('info-tujuan').textContent = 'Gagal mencari tujuan. Coba lagi.';
            }
        }, 350);
    });

    function renderHasil(list) {
        const box = $('hasil-tujuan');
        box.replaceChildren();

        if (!list.length) {
            const p = document.createElement('p');
            p.className = 'px-3 py-2 text-xs text-gray-400';
            p.textContent = 'Tujuan tidak ditemukan.';
            box.appendChild(p);
        }

        list.forEach(t => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'block w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-[#c99a32]/10 border-b border-gray-50 last:border-0';
            b.textContent = t.label;
            b.addEventListener('click', () => pilihTujuan(t));
            box.appendChild(b);
        });

        box.classList.remove('hidden');
    }

    function pilihTujuan(t) {
        $('in-tujuan-id').value    = t.id;
        $('in-tujuan-label').value = t.label;
        $('q-tujuan').value        = t.label;
        $('hasil-tujuan').classList.add('hidden');
        $('info-tujuan').textContent = 'Tujuan dipilih.';
        hitungOngkir(t.id);
    }

    async function hitungOngkir(tujuanId) {
        resetLayanan();
        pesanOpsi('Menghitung ongkir...');

        try {
            const res = await fetch(URL_HITUNG, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': TOKEN,
                },
                body: JSON.stringify({ tujuan_id: tujuanId }),
            });
            const data = await res.json();

            if (!res.ok) {
                pesanOpsi(data.message || 'Gagal menghitung ongkir.');
                return;
            }

            renderOpsi(data.opsi || []);
        } catch (e) {
            pesanOpsi('Gagal menghitung ongkir. Coba lagi.');
        }
    }

    function renderOpsi(list) {
        const wrap = $('opsi-ongkir');
        wrap.replaceChildren();

        list.forEach(o => {
            const label = document.createElement('label');
            label.className = 'cursor-pointer block';

            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'opsi_ongkir';
            input.className = 'opsi-radio peer sr-only';
            input.addEventListener('change', () => {
                $('in-kurir').value   = o.kurir;
                $('in-layanan').value = o.layanan;
                ongkir = o.biaya;
                refresh();
            });

            const card = document.createElement('div');
            card.className = 'opsi-card flex items-center gap-3 p-3 rounded-xl border border-gray-200 peer-checked:border-[#c99a32] peer-checked:bg-[#c99a32]/5 hover:border-[#c99a32]/60 transition-all duration-300';

            const dot = document.createElement('span');
            dot.className = 'radio-dot w-4 h-4 rounded-full border-2 border-gray-300 shrink-0 transition-all';

            const info = document.createElement('div');
            info.className = 'flex-1 min-w-0';
            const nama = document.createElement('p');
            nama.className = 'text-sm font-semibold text-gray-800';
            nama.textContent = o.kurir.toUpperCase() + ' ' + o.layanan;
            const est = document.createElement('p');
            est.className = 'text-xs text-gray-500 truncate';
            est.textContent = (o.deskripsi || '') + ' · est. ' + o.etd;
            info.append(nama, est);

            const harga = document.createElement('span');
            harga.className = 'text-sm font-bold text-gray-800 shrink-0';
            harga.textContent = rupiah(o.biaya);

            card.append(dot, info, harga);
            label.append(input, card);
            wrap.appendChild(label);
        });
    }

    // ===== Alamat tersimpan (dari "Alamat Saya") =====
    const ALAMAT = @json($alamatJs);

    let alamatTerhitung = null;

    const alamatDipilih = () => {
        const r = document.querySelector('input[name="alamat_id"]:checked');
        return r ? r.value : '';
    };

    // Alamat tersimpan: form manual disembunyikan & dinonaktifkan (tidak ikut validasi/terkirim),
    // tujuan ongkir diisi otomatis lalu ongkir langsung dihitung.
    // Form manual hanya aktif bila kirim ke alamat lain / belum punya alamat tersimpan.
    function terapkanAlamat() {
        const kirim = isKirim();
        const id = alamatDipilih();
        const pakaiTersimpan = kirim && id !== '' && id !== 'baru';
        const tampilManual = kirim && !pakaiTersimpan;

        document.querySelectorAll('[data-manual]').forEach(el => {
            el.classList.toggle('hidden', !tampilManual);
            const fields = el.matches('input, textarea') ? [el] : el.querySelectorAll('input, textarea');
            fields.forEach(f => { f.disabled = !tampilManual; });
        });

        if (pakaiTersimpan) {
            const a = ALAMAT.find(x => String(x.id) === String(id));
            if (a && alamatTerhitung !== id) {
                alamatTerhitung = id;
                $('in-tujuan-id').value    = a.tujuan_id;
                $('in-tujuan-label').value = a.tujuan_label;
                $('info-tujuan').textContent = '';
                hitungOngkir(a.tujuan_id);
            }
        } else if (kirim && alamatTerhitung !== null) {
            alamatTerhitung = null;
            $('q-tujuan').value = '';
            resetTujuan();
        }
    }

    document.querySelectorAll('input[name="alamat_id"]').forEach(r => r.addEventListener('change', terapkanAlamat));
    terapkanAlamat();

    // Tutup daftar hasil saat klik di luar
    document.addEventListener('click', e => {
        if (!$('hasil-tujuan').contains(e.target) && e.target !== $('q-tujuan')) {
            $('hasil-tujuan').classList.add('hidden');
        }
    });
        refresh();
})();
</script>
@endsection