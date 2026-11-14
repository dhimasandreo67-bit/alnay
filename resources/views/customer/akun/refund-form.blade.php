@extends('layouts.akun')

@section('title', 'Ajukan Refund — ' . config('alnay.brand.nama'))

@section('content')
@php
    $contohAlasan = [
        'Ukuran tidak sesuai',
        'Warna berbeda dari foto',
        'Barang rusak / cacat',
        'Barang tidak lengkap',
        'Tidak sesuai deskripsi',
    ];
    $totalItem = $transaksi->detail->sum('jumlah');
    $banyak = $transaksi->detail->count();
    $subtotalBarang = $transaksi->detail->sum(fn ($d) => (int) $d->jumlah * (int) $d->harga_satuan);
    $selisih = (int) $transaksi->total - $subtotalBarang;
    $pengiriman = $transaksi->pengiriman;
    $tglTerima = $pengiriman?->tanggal_diterima;
    $ongkir = (int) ($pengiriman?->biaya_kirim ?? 0);
    $lain = $selisih - $ongkir; // biaya lain / potongan (voucher, dll.)
    $infoKirim = collect([
        'Kurir'         => $pengiriman ? collect([$pengiriman->kurir ? strtoupper($pengiriman->kurir) : null, $pengiriman->layanan_ongkir])->filter()->implode(' · ') : null,
        'No. resi'      => $pengiriman?->no_resi,
        'Tanggal kirim' => $pengiriman?->tanggal_kirim?->format('d M Y'),
    ])->filter();
    $alamatKirim = $pengiriman ? collect([
        $pengiriman->alamat_tujuan, $pengiriman->kecamatan_tujuan, $pengiriman->kota_tujuan,
        $pengiriman->provinsi_tujuan, $pengiriman->kode_pos_tujuan,
    ])->filter()->implode(', ') : '';
@endphp

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('akun.pesanan.detail', $transaksi->id) }}" aria-label="Kembali"
               class="w-9 h-9 shrink-0 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 transition-all">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <div>
                <h1 class="font-display text-2xl font-medium text-ink/90 leading-tight">Ajukan Refund</h1>
                <p class="text-[11px] text-ink/50 font-mono">{{ $transaksi->kode_transaksi }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-700 space-y-1">
                @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        {{-- Sebelum mengajukan --}}
        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-xl p-4 mb-4 text-xs text-amber-900 leading-relaxed">
            <p class="font-bold mb-1">Sebelum mengajukan</p>
            <ul class="list-disc pl-4 space-y-1">
                <li>Pengajuan maksimal <strong>{{ \App\Models\Refund::BATAS_HARI }} hari</strong> setelah barang diterima.</li>
                <li>Siapkan <strong>foto bukti</strong> yang jelas (1–3 foto).</li>
                <li>Barang harus <strong>dikirim kembali</strong>; dana ditransfer setelah barang dicek admin.</li>
            </ul>
        </div>

        {{-- Batas waktu pengajuan --}}
        <div class="mb-4">@include('customer.akun._refund-batas', ['t' => $transaksi])</div>

        {{-- Info pesanan --}}
        <div class="bg-panel border border-line/40 rounded-2xl p-5 mb-4">
            <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60 mb-3">Info pesanan</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-[12px]">
                <div>
                    <dt class="text-ink/45">Kode pesanan</dt>
                    <dd class="font-semibold text-ink/85 font-mono">{{ $transaksi->kode_transaksi }}</dd>
                </div>
                <div>
                    <dt class="text-ink/45">Tanggal pesanan</dt>
                    <dd class="font-semibold text-ink/85">{{ $transaksi->created_at?->format('d M Y, H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-ink/45">Barang diterima</dt>
                    <dd class="font-semibold text-ink/85">{{ $tglTerima instanceof \Carbon\CarbonInterface ? $tglTerima->format('d M Y') : 'Belum tercatat' }}</dd>
                </div>
                <div>
                    <dt class="text-ink/45">Status pembayaran</dt>
                    <dd class="font-semibold text-ink/85">{{ ucfirst(str_replace('_', ' ', (string) ($transaksi->status_pembayaran ?? '-'))) }}</dd>
                </div>
                @foreach ($infoKirim as $label => $nilai)
                    <div>
                        <dt class="text-ink/45">{{ $label }}</dt>
                        <dd class="font-semibold text-ink/85">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>
            @if ($alamatKirim !== '')
                <div class="mt-3 pt-3 border-t border-line/30 text-[12px]">
                    <p class="text-ink/45">Dikirim ke</p>
                    <p class="font-semibold text-ink/85">{{ $pengiriman->nama_penerima ?: '' }}</p>
                    <p class="text-ink/65 leading-relaxed">{{ $alamatKirim }}</p>
                </div>
            @endif
        </div>

        {{-- Ringkasan produk --}}
        <div class="bg-panel border border-line/40 rounded-2xl p-5 mb-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-[11px] font-semibold uppercase tracking-wider text-ink/60">Barang yang direfund</h2>
                <span class="text-[10px] text-ink/50">{{ $totalItem }} item</span>
            </div>

            <div class="space-y-3">
                @foreach ($transaksi->detail as $d)
                    <div class="{{ ! $loop->last ? 'pb-3 border-b border-line/30' : '' }}">
                        @include('customer.akun._refund-item', ['d' => $d])
                    </div>
                @endforeach
            </div>

            <dl class="mt-4 pt-3 border-t border-line/40 space-y-1.5 text-[12px]">
                @if ($banyak > 1 || $selisih !== 0)
                    <div class="flex items-center justify-between">
                        <dt class="text-ink/55">Subtotal barang ({{ $banyak }} produk)</dt>
                        <dd class="text-ink/80 tabular-nums">Rp{{ number_format($subtotalBarang, 0, ',', '.') }}</dd>
                    </div>
                @endif
                @if ($ongkir > 0)
                    <div class="flex items-center justify-between">
                        <dt class="text-ink/55">Ongkos kirim</dt>
                        <dd class="text-ink/80 tabular-nums">Rp{{ number_format($ongkir, 0, ',', '.') }}</dd>
                    </div>
                @endif
                @if ($lain !== 0)
                    <div class="flex items-center justify-between">
                        <dt class="text-ink/55">{{ $lain < 0 ? 'Potongan / diskon' : 'Biaya lain' }}</dt>
                        <dd class="text-ink/80 tabular-nums">{{ $lain < 0 ? '−' : '' }}Rp{{ number_format(abs($lain), 0, ',', '.') }}</dd>
                    </div>
                @endif
                <div class="flex items-center justify-between">
                    <dt class="text-ink/55">Total pesanan</dt>
                    <dd class="font-semibold text-ink/85 tabular-nums">Rp{{ number_format($transaksi->total, 0, ',', '.') }}</dd>
                </div>
            </dl>
            <p class="mt-2 text-[11px] text-ink/50 leading-relaxed">
                Nominal refund akhir ditentukan admin setelah pengajuan ditinjau dan barang dicek,
                sehingga bisa berbeda dari total pesanan.
            </p>
        </div>

        <form action="{{ route('akun.refund.store', $transaksi->id) }}" method="POST" enctype="multipart/form-data"
              id="form-refund"
              class="bg-panel border border-line/40 rounded-2xl p-5 md:p-6 space-y-5 mb-4">
            @csrf

            {{-- Pesanan berisi lebih dari 1 produk: pilih produk yang bermasalah --}}
            @if ($banyak > 1)
                <div>
                    <p class="text-xs font-bold text-ink/70 mb-1">Produk yang bermasalah <span class="text-rose-500">*</span></p>
                    <p class="text-[11px] text-ink/50 mb-2 leading-relaxed">
                        Pesanan ini berisi {{ $banyak }} produk. Pilih produk yang ingin direfund; pilihan Anda dicatat di alasan
                        agar admin tahu persis barang mana yang dimaksud.
                    </p>
                    <div id="pilih-produk" class="space-y-2">
                        @foreach ($transaksi->detail as $d)
                            @php
                                $labelProduk = ($d->produk->nama ?? 'Produk')
                                    . ($d->ukuran || $d->warna ? ' (' . collect([$d->ukuran, $d->warna])->filter()->implode(', ') . ')' : '')
                                    . ' ×' . $d->jumlah;
                            @endphp
                            <label class="flex items-start gap-2.5 px-3 py-2.5 rounded-xl border border-line/50 bg-white text-[12px] text-ink/75 cursor-pointer hover:border-gold/60 transition-colors">
                                <input type="checkbox" value="{{ $labelProduk }}" data-subtotal="{{ (int) $d->jumlah * (int) $d->harga_satuan }}" class="mt-0.5 rounded border-line/60 text-gold focus:ring-gold">
                                <span class="flex-1 min-w-0">{{ $labelProduk }}</span>
                                <span class="shrink-0 tabular-nums text-ink/50">Rp{{ number_format((int) $d->jumlah * (int) $d->harga_satuan, 0, ',', '.') }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p id="pilih-produk-error" class="hidden mt-1.5 text-[11px] text-rose-600">Pilih minimal satu produk yang bermasalah.</p>
                </div>
            @endif

            {{-- Alasan --}}
            <div>
                <label for="alasan" class="block text-xs font-bold text-ink/70 mb-1.5">Alasan refund <span class="text-rose-500">*</span></label>
                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach ($contohAlasan as $c)
                        <button type="button" data-alasan="{{ $c }}"
                                class="px-2.5 py-1 rounded-full border border-line/50 bg-white text-[11px] text-ink/65 hover:border-gold/60 hover:text-gold transition-colors">{{ $c }}</button>
                    @endforeach
                </div>
                <textarea id="alasan" name="alasan" rows="4" required minlength="10" maxlength="500"
                          placeholder="Jelaskan masalahnya: bagian mana yang bermasalah dan kapan Anda menyadarinya..."
                          class="w-full px-4 py-3 border border-line/50 rounded-xl text-sm bg-white focus:outline-none focus:border-gold">{{ old('alasan') }}</textarea>
                <p class="mt-1 text-[11px] text-ink/45 text-right"><span id="alasan-count">0</span>/500 · minimal 10 karakter</p>
            </div>

            {{-- Foto bukti --}}
            <div>
                <label for="bukti-foto" class="block text-xs font-bold text-ink/70 mb-1.5">Foto bukti <span class="text-rose-500">*</span>
                    <span class="font-normal text-ink/45">(1–3 foto, maks 3 MB per foto)</span></label>
                <input type="file" name="bukti_foto[]" accept="image/jpeg,image/png,image/webp" multiple required id="bukti-foto"
                       class="block w-full text-xs text-ink/70 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-gold file:text-white file:font-semibold file:cursor-pointer">
                <p id="bukti-info" class="mt-1 text-[11px] text-ink/50"></p>
                <div id="bukti-preview" class="mt-2 flex flex-wrap gap-2"></div>
                <ul class="mt-2 text-[11px] text-ink/50 leading-relaxed list-disc pl-4 space-y-0.5">
                    <li>Foto bagian yang rusak / tidak sesuai dari dekat.</li>
                    <li>Sertakan foto keseluruhan barang dan label ukuran.</li>
                    @if ($banyak > 1)
                        <li>Maksimal 3 foto: prioritaskan produk yang bermasalah, satu foto per produk bila memungkinkan.</li>
                    @endif
                    <li>Pastikan pencahayaan cukup agar detail terlihat jelas.</li>
                </ul>
            </div>

            {{-- Rekening --}}
            <div>
                <p class="text-xs font-bold text-ink/70 mb-1.5">Rekening / e-wallet tujuan dana <span class="text-rose-500">*</span></p>
                <div class="grid sm:grid-cols-2 gap-3">
                    <input type="text" name="bank_tujuan" value="{{ old('bank_tujuan') }}" required maxlength="50" placeholder="Bank / e-wallet (mis. BCA, DANA)"
                           class="px-4 py-2.5 border border-line/50 rounded-xl text-sm bg-white focus:outline-none focus:border-gold">
                    <input type="text" name="no_rekening" value="{{ old('no_rekening') }}" required inputmode="numeric" maxlength="30" placeholder="Nomor rekening / HP"
                           class="px-4 py-2.5 border border-line/50 rounded-xl text-sm bg-white focus:outline-none focus:border-gold">
                    <input type="text" name="atas_nama" value="{{ old('atas_nama') }}" required maxlength="100" placeholder="Atas nama"
                           class="sm:col-span-2 px-4 py-2.5 border border-line/50 rounded-xl text-sm bg-white focus:outline-none focus:border-gold">
                </div>
                <p class="mt-1.5 text-[11px] text-ink/50">Periksa kembali nomor dan nama pemilik. Kesalahan data rekening dapat menunda transfer dana.</p>
            </div>

            {{-- Estimasi nilai barang --}}
            <div class="rounded-xl border border-line/40 bg-white p-4 text-[12px]">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-ink/60">{{ $banyak > 1 ? 'Nilai barang yang dipilih' : 'Nilai barang' }}</span>
                    <span id="estimasi-nilai" class="font-bold text-ink/90 tabular-nums" data-semua="{{ $subtotalBarang }}">
                        Rp{{ number_format($banyak > 1 ? 0 : $subtotalBarang, 0, ',', '.') }}
                    </span>
                </div>
                <p class="mt-1.5 text-[11px] text-ink/50 leading-relaxed">
                    Ini hanya perkiraan nilai barang. Nominal refund akhir ditentukan admin setelah barang dicek
                    dan bisa berbeda (misalnya terkait ongkir atau kondisi barang).
                </p>
            </div>

            {{-- Alur setelah pengajuan --}}
            <div class="bg-cream/70 border border-line/40 rounded-xl p-4 text-[11px] text-ink/65 leading-relaxed">
                <p class="font-bold text-ink/75 mb-1.5">Setelah Anda mengirim pengajuan</p>
                <ol class="list-decimal pl-4 space-y-1">
                    <li>Admin meninjau pengajuan (maksimal 1x24 jam).</li>
                    <li>Jika disetujui, Anda diminta mengirim barang kembali.</li>
                    <li>Admin mengecek barang, lalu dana ditransfer ke rekening di atas.</li>
                </ol>
            </div>

            <label class="flex items-start gap-2.5 text-[12px] text-ink/70 leading-relaxed cursor-pointer">
                <input type="checkbox" required class="mt-0.5 rounded border-line/60 text-gold focus:ring-gold">
                <span>Saya memahami ketentuan refund dan bersedia mengirim barang kembali untuk dicek.</span>
            </label>

            <div class="flex gap-3">
                <a href="{{ route('akun.pesanan.detail', $transaksi->id) }}"
                   class="flex-1 text-center py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit"
                        class="flex-1 py-3 bg-rose-600 text-white rounded-xl font-bold text-sm hover:bg-rose-700 transition-colors">Kirim Pengajuan</button>
            </div>
        </form>

        @include('customer.akun._refund-panduan', ['compact' => true])
    </div>
</div>

<script>
    (function () {
        const alasan = document.getElementById('alasan');
        const form = document.getElementById('form-refund');
        const pilih = document.getElementById('pilih-produk');
        const PREFIX = '[Produk bermasalah: ';
        const hitung = document.getElementById('alasan-count');
        const updateHitung = () => { hitung.textContent = alasan.value.length; };
        if (pilih) {
            const m = alasan.value.match(/^\[Produk bermasalah: ([^\]]*)\]/);
            if (m) {
                const terpilih = m[1].split('; ');
                pilih.querySelectorAll('input').forEach(i => { i.checked = terpilih.includes(i.value); });
                alasan.value = alasan.value.replace(/^\[Produk bermasalah:[^\]]*\]\s*/, '');
            }
        }
        alasan.addEventListener('input', updateHitung);
        updateHitung();

        document.querySelectorAll('[data-alasan]').forEach(btn => {
            btn.addEventListener('click', () => {
                const teks = btn.dataset.alasan;
                alasan.value = alasan.value.trim() ? alasan.value.trim() + '. ' + teks : teks + ' — ';
                alasan.focus();
                updateHitung();
            });
        });

        // Catat produk terpilih di awal alasan (aman bila form dikirim ulang setelah error validasi)
        form.addEventListener('submit', function (e) {
            if (!pilih) return;
            const dipilih = [...pilih.querySelectorAll('input:checked')].map(i => i.value);
            const err = document.getElementById('pilih-produk-error');
            err.classList.toggle('hidden', dipilih.length > 0);
            if (!dipilih.length) {
                e.preventDefault();
                pilih.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            let isi = alasan.value.replace(/^\[Produk bermasalah:[^\]]*\]\s*/, '').trim();
            const awal = PREFIX + dipilih.join('; ') + ']\n';
            if ((awal + isi).length > 500) {
                e.preventDefault();
                err.textContent = 'Alasan terlalu panjang bersama daftar produk (maks 500 karakter). Persingkat alasan Anda.';
                err.classList.remove('hidden');
                return;
            }
            alasan.value = awal + isi;
        });

        // Estimasi nilai barang yang dipilih
        const estimasi = document.getElementById('estimasi-nilai');
        const rupiah = n => 'Rp' + Math.round(n).toLocaleString('id-ID');
        if (pilih && estimasi) {
            const hitungEstimasi = () => {
                const total = [...pilih.querySelectorAll('input:checked')]
                    .reduce((a, i) => a + Number(i.dataset.subtotal || 0), 0);
                estimasi.textContent = rupiah(total);
            };
            pilih.addEventListener('change', hitungEstimasi);
            hitungEstimasi();
        }

        const input = document.getElementById('bukti-foto');
        const info = document.getElementById('bukti-info');
        const preview = document.getElementById('bukti-preview');
        const MAKS_BYTE = 3 * 1024 * 1024;

        input?.addEventListener('change', function () {
            preview.innerHTML = '';
            if (this.files.length > 3) {
                info.textContent = 'Maksimal 3 foto. Pilih ulang.';
                this.value = '';
                return;
            }
            if ([...this.files].some(f => f.size > MAKS_BYTE)) {
                info.textContent = 'Ada foto yang lebih dari 3 MB. Pilih ulang.';
                this.value = '';
                return;
            }
            info.textContent = this.files.length ? this.files.length + ' foto dipilih' : '';
            [...this.files].forEach(f => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                img.alt = 'Pratinjau bukti';
                img.className = 'w-20 h-20 rounded-lg object-cover border border-line/50';
                img.onload = () => URL.revokeObjectURL(img.src);
                preview.appendChild(img);
            });
        });
    })();
</script>
@endsection