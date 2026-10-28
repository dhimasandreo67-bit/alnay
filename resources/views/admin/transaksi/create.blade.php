@extends('admin.layouts.admin')
@section('title', 'Kasir - Buat Transaksi Baru')

@section('content')
<div class="p-6 space-y-6 max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Kasir - Transaksi Baru</h1>
            <p class="text-xs text-gray-500 mt-1">Catat transaksi offline. Setelah disimpan, data langsung muncul di Semua Transaksi dengan label <strong>Kasir</strong>.</p>
        </div>
        <a href="{{ route('admin.transaksi.index') }}" class="text-xs font-bold text-gray-500 hover:text-amber-600">
            &larr; Kembali ke Semua Transaksi
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-bold mb-1">Periksa lagi isian form:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.transaksi.store') }}" id="form-kasir" class="space-y-6">
        @csrf

        {{-- Jenis Transaksi --}}
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-3">Jenis Transaksi</p>
            <div class="flex gap-3">
                <label class="flex-1">
                    <input type="radio" name="jenis" value="beli" class="peer sr-only" checked>
                    <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700">
                        Pembelian
                    </div>
                </label>
                <label class="flex-1">
                    <input type="radio" name="jenis" value="sewa" class="peer sr-only" onchange="toggleSewa()" id="radio-sewa">
                    <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700">
                        Penyewaan
                    </div>
                </label>
            </div>

            <div id="box-tanggal-sewa" class="hidden grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="text-xs font-bold text-gray-500">Tanggal Ambil</label>
                    <input type="date" name="tanggal_pinjam" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500">Tanggal Kembali (Rencana)</label>
                    <input type="date" name="tanggal_kembali_rencana" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        {{-- Data Pelanggan --}}
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-3">Data Pelanggan</p>
            <div class="flex gap-3 mb-4">
                <label class="flex-1">
                    <input type="radio" name="customer_type" value="terdaftar" class="peer sr-only" checked onchange="toggleCustomer()">
                    <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700">
                        Pelanggan Terdaftar
                    </div>
                </label>
                <label class="flex-1">
                    <input type="radio" name="customer_type" value="tamu" class="peer sr-only" onchange="toggleCustomer()">
                    <div class="cursor-pointer rounded-xl border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700">
                        Tamu (Walk-in)
                    </div>
                </label>
            </div>

            <div id="box-user-terdaftar">
                <label class="text-xs font-bold text-gray-500">Cari Pelanggan</label>
                <select name="user_id" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}{{ $u->no_hp ? ' - ' . $u->no_hp : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div id="box-user-tamu" class="hidden grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500">Nama Tamu</label>
                    <input type="text" name="nama_tamu" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm" placeholder="Nama pembeli">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500">No. WhatsApp</label>
                    <input type="text" name="no_hp_tamu" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm" placeholder="08xxxxxxxxxx">
                </div>
            </div>
        </div>

        {{-- Daftar Item --}}
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Item Produk</p>
                <button type="button" onclick="tambahBaris()" class="text-xs font-bold text-amber-700 hover:text-amber-800">+ Tambah Item</button>
            </div>

            <div id="daftar-item" class="space-y-3"></div>

            <div class="flex justify-end mt-4 pt-4 border-t border-gray-100">
                <div class="text-right">
                    <p class="text-[10px] uppercase tracking-wider text-gray-400">Total</p>
                    <p id="total-display" class="text-xl font-black text-gray-900">Rp 0</p>
                </div>
            </div>
        </div>

        {{-- Pembayaran --}}
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-3">Pembayaran</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500">Status</label>
                    <select name="status_pembayaran" id="status_pembayaran" onchange="toggleDp()" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        <option value="lunas">Lunas (Bayar Penuh)</option>
                        <option value="dp">DP (Bayar Sebagian)</option>
                    </select>
                </div>
                <div id="box-jumlah-dp" class="hidden">
                    <label class="text-xs font-bold text-gray-500">Jumlah DP</label>
                    <input type="number" name="jumlah_dp" min="1" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500">Metode Bayar</label>
                    <select name="metode_bayar" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        <option value="tunai">Tunai</option>
                        <option value="qris">QRIS</option>
                        <option value="debit">Kartu Debit</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="text-xs font-bold text-gray-500">Catatan (opsional)</label>
                <textarea name="catatan" rows="2" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm"></textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-gray-900 text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-gray-800 transition-all">
                Simpan Transaksi
            </button>
        </div>
    </form>
</div>

{{-- Data produk untuk JS (ASUMSI kolom: id, nama, harga, stok) --}}
<script>
    const PRODUK_LIST = @json($produks->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama, 'harga' => (float) $p->harga, 'stok' => $p->stok]));

    function rupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function buatOptionProduk(selectedId) {
        return PRODUK_LIST.map(p =>
            `<option value="${p.id}" data-harga="${p.harga}" ${p.id == selectedId ? 'selected' : ''}>${p.nama} - ${rupiah(p.harga)} (stok ${p.stok})</option>`
        ).join('');
    }

    function tambahBaris() {
        const wrap = document.getElementById('daftar-item');
        const idx = wrap.children.length;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 baris-item';
        row.innerHTML = `
            <select name="items[${idx}][produk_id]" class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm select-produk" onchange="hitungTotal()">
                <option value="">-- Pilih Produk --</option>
                ${buatOptionProduk(null)}
            </select>
            <input type="number" name="items[${idx}][qty]" value="1" min="1" class="w-20 border border-gray-200 rounded-xl px-3 py-2 text-sm input-qty" onchange="hitungTotal()">
            <span class="w-32 text-right text-sm font-bold text-gray-700 subtotal-display">Rp 0</span>
            <button type="button" onclick="hapusBaris(this)" class="text-rose-500 hover:text-rose-700 text-xs font-bold">Hapus</button>
        `;
        wrap.appendChild(row);
    }

    function hapusBaris(btn) {
        btn.closest('.baris-item').remove();
        hitungTotal();
    }

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.baris-item').forEach(row => {
            const select = row.querySelector('.select-produk');
            const qty = parseInt(row.querySelector('.input-qty').value) || 0;
            const harga = parseFloat(select.selectedOptions[0]?.dataset.harga || 0);
            const subtotal = harga * qty;
            row.querySelector('.subtotal-display').textContent = rupiah(subtotal);
            total += subtotal;
        });
        document.getElementById('total-display').textContent = rupiah(total);
    }

    function toggleSewa() {
        const isSewa = document.getElementById('radio-sewa').checked;
        document.getElementById('box-tanggal-sewa').classList.toggle('hidden', !isSewa);
    }

    function toggleCustomer() {
        const terdaftar = document.querySelector('input[name="customer_type"]:checked').value === 'terdaftar';
        document.getElementById('box-user-terdaftar').classList.toggle('hidden', !terdaftar);
        document.getElementById('box-user-tamu').classList.toggle('hidden', terdaftar);
    }

    function toggleDp() {
        const isDp = document.getElementById('status_pembayaran').value === 'dp';
        document.getElementById('box-jumlah-dp').classList.toggle('hidden', !isDp);
    }

    // Mulai dengan 1 baris item kosong
    tambahBaris();
    toggleCustomer();
    toggleDp();
</script>
@endsection