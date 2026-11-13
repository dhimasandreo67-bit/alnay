@extends('admin.layouts.admin')
@section('title', 'Kelola Refund')

@section('content')
    @php
        $st = $refund->status;
        $t = $refund->transaksi;
        $u = $t->user;
        $items = $t->detail;
        $foto = $refund->fotoBuktiUrl();
        $buktiTransfer = $refund->buktiTransferUrl();
        $tfPdf = $buktiTransfer && str_ends_with(strtolower((string) $refund->bukti_transfer), '.pdf');
        $jamMenunggu = $refund->status === 'diajukan' ? (int) abs($refund->created_at->diffInHours(now())) : null;

        $badge = [
            'diajukan'        => 'bg-amber-50 text-amber-700 border-amber-200',
            'disetujui'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'barang_dikirim'  => 'bg-sky-50 text-sky-700 border-sky-200',
            'barang_diterima' => 'bg-sky-50 text-sky-700 border-sky-200',
            'selesai'         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ditolak'         => 'bg-rose-50 text-rose-700 border-rose-200',
            'dibatalkan'      => 'bg-gray-50 text-gray-600 border-gray-200',
        ];
        $input = 'w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500';

        // Nomor WA pelanggan
        $hp = preg_replace('/\D/', '', (string) ($u->no_hp ?? ''));
        if ($hp !== '' && str_starts_with($hp, '0')) { $hp = '62' . substr($hp, 1); }

        // Foto produk (kolom produk.foto_produk: JSON array atau string)
        $fotoProduk = function ($produk) {
            $f = $produk->foto_produk ?? null;
            if (is_string($f)) {
                $dec = json_decode($f, true);
                $f = is_array($dec) ? $dec : [$f];
            }
            $f = is_array($f) ? collect($f)->filter()->first() : null;
            return $f ? (preg_match('#^https?://#', $f) ? $f : asset('storage/' . ltrim($f, '/'))) : null;
        };

        // Label item — harus sama dengan format di form pelanggan agar bisa dicocokkan
        $labelItem = fn ($d) => ($d->produk->nama ?? 'Produk')
            . ($d->ukuran || $d->warna ? ' (' . collect([$d->ukuran, $d->warna])->filter()->implode(', ') . ')' : '')
            . ' ×' . $d->jumlah;

        // Pisahkan daftar "produk bermasalah" dari teks alasan
        $alasanBersih = (string) $refund->alasan;
        $bermasalah = [];
        if (preg_match('/^\[Produk bermasalah:\s*(.*?)\]\s*/su', $alasanBersih, $mm)) {
            $bermasalah = array_values(array_filter(array_map('trim', explode(';', $mm[1]))));
            $alasanBersih = trim(substr($alasanBersih, strlen($mm[0])));
        }
        $adaTanda = count($bermasalah) > 0;
        $itemBermasalah = fn ($d) => $adaTanda && in_array($labelItem($d), $bermasalah, true);

        // Hitungan nominal
        $subtotal = fn ($d) => (int) $d->jumlah * (int) $d->harga_satuan;
        $nilaiBarang = $items->sum($subtotal);
        $nilaiBermasalah = $items->filter($itemBermasalah)->sum($subtotal);
        $selisih = (int) $t->total - $nilaiBarang;

        // Jarak pengajuan dari tanggal barang diterima
        $tglTerima = $t->pengiriman?->tanggal_diterima;
        $hariSetelah = $tglTerima instanceof \Carbon\CarbonInterface
            ? (int) abs($tglTerima->copy()->startOfDay()->diffInDays($refund->created_at->copy()->startOfDay()))
            : null;
        $batas = \App\Models\Refund::BATAS_HARI;
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.refund.index') }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:text-amber-600" aria-label="Kembali">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="flex-1">
                <h1 class="text-xl font-bold text-gray-900">Refund {{ $t->kode_transaksi }}</h1>
                <p class="text-xs text-gray-500">Diajukan {{ $refund->created_at->format('d M Y, H:i') }}
                    @if ($jamMenunggu !== null)
                        · <span class="font-bold {{ $jamMenunggu >= 24 ? 'text-rose-600' : 'text-amber-600' }}">menunggu {{ $jamMenunggu }} jam{{ $jamMenunggu >= 24 ? ' (lewat 1x24 jam)' : '' }}</span>
                    @endif
                </p>
            </div>
            <span class="rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-wider {{ $badge[$st] ?? $badge['diajukan'] }}">{{ $refund->label_status }}</span>
        </div>

        @foreach (['success' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'error' => 'border-rose-200 bg-rose-50 text-rose-800'] as $tipe => $kelas)
            @if (session($tipe))
                <div class="rounded-2xl border px-4 py-3 text-sm font-medium {{ $kelas }}">{{ session($tipe) }}</div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="space-y-1 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- ===== Kiri: data pengajuan ===== --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Ringkasan pengajuan --}}
                <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Ringkasan pengajuan</h2>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-[11px] text-gray-400">Diajukan</dt>
                            <dd class="font-semibold text-gray-800">{{ $refund->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] text-gray-400">Barang diterima</dt>
                            <dd class="font-semibold text-gray-800">{{ $tglTerima instanceof \Carbon\CarbonInterface ? $tglTerima->format('d M Y') : 'Belum tercatat' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] text-gray-400">Jarak pengajuan</dt>
                            <dd>
                                @if ($hariSetelah !== null)
                                    <span class="rounded-md border px-2 py-0.5 text-xs font-bold {{ $hariSetelah <= $batas ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                                        {{ $hariSetelah }} hari · {{ $hariSetelah <= $batas ? 'dalam batas' : 'lewat batas ' . $batas . ' hari' }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] text-gray-400">Nominal {{ $st === 'diajukan' ? 'usulan' : 'refund' }}</dt>
                            <dd class="font-semibold tabular-nums text-gray-800">Rp{{ number_format($refund->jumlah, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] text-gray-400">Total transaksi</dt>
                            <dd class="font-semibold tabular-nums text-gray-800">Rp{{ number_format($t->total, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] text-gray-400">Pembayaran</dt>
                            <dd class="font-semibold text-gray-800">{{ ucfirst(str_replace('_', ' ', (string) ($t->status_pembayaran ?? '-'))) }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Pelanggan --}}
                <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Pelanggan</h2>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900">{{ $u->name ?? '-' }}</p>
                            @if ($u?->email)<p class="text-xs text-gray-500">{{ $u->email }}</p>@endif
                            @if ($hp !== '')
                                <p class="text-sm text-gray-500">{{ $u->no_hp }}</p>
                            @else
                                <p class="text-xs text-gray-400">No. HP belum diisi</p>
                            @endif
                        </div>
                        @if ($hp !== '')
                            <a href="https://wa.me/{{ $hp }}?text={{ urlencode('Halo ' . ($u->name ?? '') . ', kami dari ' . config('alnay.brand.nama') . ' terkait pengajuan refund pesanan ' . $t->kode_transaksi . '.') }}"
                               target="_blank" rel="noopener"
                               class="rounded-xl border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50">Hubungi via WhatsApp</a>
                        @endif
                    </div>
                </section>

                {{-- Barang --}}
                <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Barang dalam pesanan ({{ $items->count() }} produk)</h2>
                        @if ($adaTanda)
                            <span class="text-[10px] font-semibold text-rose-600">{{ count($bermasalah) }} dipilih pelanggan</span>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @foreach ($items as $d)
                            @php $img = $fotoProduk($d->produk); $flag = $itemBermasalah($d); @endphp
                            <div class="flex gap-3 rounded-xl border p-3 {{ $flag ? 'border-rose-200 bg-rose-50/40' : 'border-gray-100' }}">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 text-[10px] text-gray-300">
                                    @if ($img)
                                        <img src="{{ $img }}" alt="{{ $d->produk->nama ?? 'Produk' }}" loading="lazy" class="h-full w-full object-cover" onerror="this.remove()">
                                    @else
                                        Foto
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-bold leading-snug text-gray-900">{{ $d->produk->nama ?? 'Produk' }}</p>
                                        @if ($flag)
                                            <span class="shrink-0 rounded-md border border-rose-200 bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700">Bermasalah</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-1.5 text-[10px] text-gray-500">
                                        @if ($d->ukuran)<span class="rounded-md border border-gray-200 bg-gray-50 px-2 py-0.5">Ukuran {{ $d->ukuran }}</span>@endif
                                        @if ($d->warna)<span class="rounded-md border border-gray-200 bg-gray-50 px-2 py-0.5">Warna {{ $d->warna }}</span>@endif
                                        <span class="rounded-md border border-gray-200 bg-gray-50 px-2 py-0.5">{{ $d->jumlah }} item</span>
                                    </div>
                                    <p class="mt-1.5 text-xs tabular-nums text-gray-500">
                                        {{ $d->jumlah }} × Rp{{ number_format((int) $d->harga_satuan, 0, ',', '.') }}
                                        · <span class="font-semibold text-gray-800">Rp{{ number_format($subtotal($d), 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <dl class="mt-4 space-y-1.5 border-t border-gray-100 pt-3 text-xs">
                        <div class="flex justify-between"><dt class="text-gray-500">Subtotal barang</dt><dd class="tabular-nums text-gray-700">Rp{{ number_format($nilaiBarang, 0, ',', '.') }}</dd></div>
                        @if ($selisih !== 0)
                            <div class="flex justify-between"><dt class="text-gray-500">Ongkir / biaya lain</dt><dd class="tabular-nums text-gray-700">{{ $selisih < 0 ? '−' : '' }}Rp{{ number_format(abs($selisih), 0, ',', '.') }}</dd></div>
                        @endif
                        <div class="flex justify-between text-sm"><dt class="font-semibold text-gray-700">Total transaksi</dt><dd class="font-bold tabular-nums text-gray-900">Rp{{ number_format($t->total, 0, ',', '.') }}</dd></div>
                        @if ($t->pengiriman?->status_kirim)
                            <div class="flex justify-between"><dt class="text-gray-500">Status pengiriman</dt><dd class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $t->pengiriman->status_kirim)) }}</dd></div>
                        @endif
                    </dl>
                </section>

                {{-- Alasan & bukti --}}
                <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Alasan & bukti pelanggan</h2>

                    @if ($adaTanda)
                        <div class="mb-3 flex flex-wrap gap-1.5">
                            @foreach ($bermasalah as $b)
                                <span class="rounded-md border border-rose-200 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700">{{ $b }}</span>
                            @endforeach
                        </div>
                    @endif

                    <p class="whitespace-pre-line text-sm text-gray-700">{{ $alasanBersih !== '' ? $alasanBersih : '-' }}</p>

                    <p class="mb-2 mt-4 text-[11px] font-semibold text-gray-400">Foto bukti ({{ count($foto) }})</p>
                    @if ($foto)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($foto as $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="block h-24 w-24 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                    <img src="{{ $url }}" alt="Bukti {{ $loop->iteration }}" loading="lazy" class="h-full w-full object-cover"
                                         onerror="this.parentNode.innerHTML='<span class=&quot;flex h-full w-full items-center justify-center p-2 text-center text-[10px] text-rose-500&quot;>Gagal dimuat — klik untuk buka</span>'">
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400">Pelanggan tidak melampirkan foto.</p>
                    @endif
                </section>

                {{-- Rekening --}}
                <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Transfer dana ke</h2>
                    <div class="grid gap-3 text-sm sm:grid-cols-3">
                        <div><p class="text-[11px] text-gray-400">Bank / e-wallet</p><p class="font-semibold text-gray-800">{{ $refund->bank_tujuan ?: '-' }}</p></div>
                        <div><p class="text-[11px] text-gray-400">No. rekening / HP</p><p class="font-mono font-semibold text-gray-800">{{ $refund->no_rekening ?: '-' }}</p></div>
                        <div><p class="text-[11px] text-gray-400">Atas nama</p><p class="font-semibold text-gray-800">{{ $refund->atas_nama ?: '-' }}</p></div>
                    </div>
                </section>

                {{-- Catatan admin (setelah diproses) --}}
                @if ($refund->catatan_negosiasi || $refund->no_resi_penjemputan || $refund->pemroses)
                    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Catatan admin</h2>
                        <dl class="space-y-2 text-sm">
                            @if ($refund->pemroses)<div class="flex justify-between gap-3"><dt class="text-gray-500">Diproses oleh</dt><dd class="font-semibold text-gray-800">{{ $refund->pemroses->name }}</dd></div>@endif
                            @if ($refund->no_resi_penjemputan)<div class="flex justify-between gap-3"><dt class="text-gray-500">No. resi penjemputan</dt><dd class="font-mono font-semibold text-gray-800">{{ $refund->no_resi_penjemputan }}</dd></div>@endif
                            @if ($refund->catatan_negosiasi)<div><dt class="mb-0.5 text-gray-500">Instruksi untuk pelanggan</dt><dd class="whitespace-pre-line text-gray-800">{{ $refund->catatan_negosiasi }}</dd></div>@endif
                        </dl>
                    </section>
                @endif

                @if ($riwayat->isNotEmpty())
                    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h2 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Riwayat status</h2>
                        <div class="relative ml-2 space-y-4 border-l-2 border-amber-200">
                            @foreach ($riwayat as $r)
                                <div class="relative pl-4">
                                    <span class="absolute -left-[5px] top-1 h-2 w-2 rounded-full bg-amber-500 ring-2 ring-white"></span>
                                    <p class="text-sm font-bold text-gray-800">{{ \App\Models\Refund::LABEL[$r->status_baru] ?? $r->status_baru }}</p>
                                    <p class="text-[11px] text-gray-400">{{ ($r->waktu_ubah ?? $r->created_at)->format('d M Y, H:i') }} · {{ $r->pengubah->name ?? 'Pelanggan' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- ===== Kanan: tindakan ===== --}}
            <div class="space-y-6">

                @if ($st === 'diajukan')
                    <section class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 text-sm font-bold text-emerald-800">Setujui refund</h2>
                        <form action="{{ route('admin.refund.setujui', $refund->id) }}" method="POST" class="space-y-3">
                            @csrf @method('PATCH')
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">Nominal refund (Rp)</label>
                                <input type="number" id="input-jumlah" name="jumlah" min="1" max="{{ (int) $t->total }}" value="{{ old('jumlah', (int) $refund->jumlah) }}" required class="{{ $input }}">
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @if ($adaTanda && $nilaiBermasalah > 0)
                                        <button type="button" data-isi="{{ $nilaiBermasalah }}" class="rounded-lg border border-rose-200 px-2 py-1 text-[10px] font-bold text-rose-700 hover:bg-rose-50">Produk bermasalah · Rp{{ number_format($nilaiBermasalah, 0, ',', '.') }}</button>
                                    @endif
                                    <button type="button" data-isi="{{ $nilaiBarang }}" class="rounded-lg border border-gray-200 px-2 py-1 text-[10px] font-bold text-gray-600 hover:bg-gray-50">Semua barang · Rp{{ number_format($nilaiBarang, 0, ',', '.') }}</button>
                                    <button type="button" data-isi="{{ (int) $t->total }}" class="rounded-lg border border-gray-200 px-2 py-1 text-[10px] font-bold text-gray-600 hover:bg-gray-50">Total transaksi · Rp{{ number_format($t->total, 0, ',', '.') }}</button>
                                </div>
                                <p class="mt-1.5 text-[11px] text-gray-400">Maks total transaksi. Pilih salah satu tombol atau isi manual.</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">No. resi penjemputan <span class="font-normal text-gray-400">(opsional)</span></label>
                                <input type="text" name="no_resi_penjemputan" maxlength="100" value="{{ old('no_resi_penjemputan') }}" placeholder="Kosongkan bila pelanggan kirim sendiri" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">Catatan / instruksi untuk pelanggan</label>
                                <textarea name="catatan_negosiasi" rows="3" maxlength="1000" placeholder="Alamat pengiriman balik, potongan, dll." class="{{ $input }}">{{ old('catatan_negosiasi') }}</textarea>
                            </div>
                            <button class="w-full rounded-xl bg-emerald-600 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Setujui</button>
                        </form>
                    </section>

                    <section class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 text-sm font-bold text-rose-800">Tolak refund</h2>
                        <form action="{{ route('admin.refund.tolak', $refund->id) }}" method="POST" class="space-y-3">
                            @csrf @method('PATCH')
                            <textarea name="alasan_penolakan" rows="3" required minlength="5" maxlength="500" placeholder="Alasan penolakan (dilihat pelanggan)" class="{{ $input }}">{{ old('alasan_penolakan') }}</textarea>
                            <button class="w-full rounded-xl border border-rose-300 py-2.5 text-sm font-bold text-rose-700 hover:bg-rose-50" onclick="return confirm('Tolak pengajuan refund ini?')">Tolak</button>
                        </form>
                    </section>

                @elseif (in_array($st, ['disetujui', 'barang_dikirim'], true))
                    <section class="rounded-2xl border border-sky-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-1 text-sm font-bold text-sky-800">Terima barang kembali</h2>
                        <p class="mb-3 text-xs text-gray-500">
                            {{ $st === 'barang_dikirim' ? 'Pelanggan menandai barang sudah dikirim.' : 'Menunggu pelanggan mengirim barang.' }}
                            Catat setelah barang benar-benar sampai dan dicek.
                        </p>
                        <p class="mb-3 text-xs text-gray-600">Nominal disetujui: <b>Rp{{ number_format($refund->jumlah, 0, ',', '.') }}</b>
                            @if ($refund->no_resi_penjemputan)<br>Resi: <span class="font-mono">{{ $refund->no_resi_penjemputan }}</span>@endif</p>
                        <form action="{{ route('admin.refund.terima-barang', $refund->id) }}" method="POST" class="space-y-3">
                            @csrf @method('PATCH')
                            <div>
                                <p class="mb-1.5 text-xs font-bold text-gray-600">Kembalikan stok untuk produk yang layak dijual kembali</p>
                                <div class="space-y-1.5">
                                    @foreach ($items as $d)
                                        <label class="flex items-start gap-2 rounded-lg border border-gray-100 px-2.5 py-2 text-xs text-gray-700">
                                            <input type="checkbox" name="restock_detail[]" value="{{ $d->id }}" class="mt-0.5 accent-amber-600">
                                            <span class="flex-1">{{ $labelItem($d) }}
                                                @if ($itemBermasalah($d))<span class="ml-1 font-bold text-rose-600">· bermasalah</span>@endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-1.5 text-[11px] text-gray-400">Kosongkan semua bila stok tidak dikembalikan.</p>
                            </div>
                            <button class="w-full rounded-xl bg-sky-600 py-2.5 text-sm font-bold text-white hover:bg-sky-700">Barang diterima</button>
                        </form>
                    </section>

                @elseif ($st === 'barang_diterima')
                    <section class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-1 text-sm font-bold text-amber-800">Transfer dana</h2>
                        <div class="mb-3 rounded-xl bg-amber-50 p-3 text-xs text-amber-900">
                            <p class="text-lg font-black">Rp{{ number_format($refund->jumlah, 0, ',', '.') }}</p>
                            {{ $refund->bank_tujuan }} · <span class="font-mono">{{ $refund->no_rekening }}</span><br>a.n. {{ $refund->atas_nama }}
                        </div>
                        <form action="{{ route('admin.refund.selesai', $refund->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">Bukti transfer <span class="text-rose-500">*</span></label>
                                <input type="file" name="bukti_transfer" required accept="image/jpeg,image/png,image/webp,application/pdf" class="block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-amber-600 file:px-3 file:py-2 file:font-semibold file:text-white">
                            </div>
                            <button class="w-full rounded-xl bg-amber-600 py-2.5 text-sm font-bold text-white hover:bg-amber-700" onclick="return confirm('Pastikan dana sudah ditransfer. Tandai refund selesai?')">Tandai selesai</button>
                        </form>
                    </section>

                @elseif ($st === 'selesai')
                    <section class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-5 text-sm text-emerald-900 shadow-sm">
                        <p class="font-bold">Refund selesai</p>
                        <p class="text-xs">Rp{{ number_format($refund->jumlah, 0, ',', '.') }} · {{ $refund->tanggal_selesai?->format('d M Y, H:i') }}</p>
                        @if ($buktiTransfer)
                            <p class="mt-3 text-[11px] font-bold">Bukti transfer (terlihat oleh pelanggan)</p>
                            @unless ($tfPdf)
                                <a href="{{ $buktiTransfer }}" target="_blank" rel="noopener" class="mt-1 block w-fit">
                                    <img src="{{ $buktiTransfer }}" alt="Bukti transfer" loading="lazy" class="max-h-56 max-w-full rounded-lg border border-emerald-200 bg-white object-contain"
                                         onerror="this.replaceWith(document.createTextNode('Gambar gagal dimuat.'))">
                                </a>
                            @endunless
                            <a href="{{ $buktiTransfer }}" target="_blank" rel="noopener" class="mt-2 inline-block text-xs font-bold underline">{{ $tfPdf ? 'Buka PDF' : 'Lihat ukuran penuh' }}</a>
                        @endif
                        @if ($refund->stok_dikembalikan)<p class="mt-2 text-xs">Stok produk sudah dikembalikan.</p>@endif
                    </section>

                @elseif ($st === 'ditolak')
                    <section class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5 text-sm text-rose-900 shadow-sm">
                        <p class="font-bold">Ditolak</p>
                        <p class="mt-1 whitespace-pre-line text-xs">{{ $refund->alasan_penolakan }}</p>
                    </section>
                @else
                    <section class="rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm text-gray-600 shadow-sm">Dibatalkan oleh pelanggan.</section>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-isi]').forEach(btn => {
            btn.addEventListener('click', () => {
                const el = document.getElementById('input-jumlah');
                if (el) { el.value = btn.dataset.isi; el.focus(); }
            });
        });
    </script>
@endsection