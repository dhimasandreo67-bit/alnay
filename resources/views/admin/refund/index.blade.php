@extends('admin.layouts.admin')
@section('title', 'Refund & Return')

@section('content')
    @php
        $totalSemua = ($totalPending ?? 0) + ($totalDisetujui ?? 0) + ($totalDitolak ?? 0);

        $kartu = [
            'pending' => [
                'label' => 'Menunggu', 'angka' => $totalPending ?? 0, 'ket' => 'Perlu ditindaklanjuti',
                'wrap' => 'border-amber-200/70 from-amber-50 hover:shadow-amber-500/10',
                'ikon' => 'from-amber-500 to-amber-400 shadow-amber-500/25',
                'teks' => 'text-amber-950', 'sub' => 'text-amber-700/80',
                'path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            'disetujui' => [
                'label' => 'Disetujui', 'angka' => $totalDisetujui ?? 0, 'ket' => 'Sudah disetujui',
                'wrap' => 'border-emerald-200/70 from-emerald-50 hover:shadow-emerald-500/10',
                'ikon' => 'from-emerald-500 to-teal-500 shadow-emerald-500/25',
                'teks' => 'text-emerald-950', 'sub' => 'text-emerald-700/80',
                'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            'ditolak' => [
                'label' => 'Ditolak', 'angka' => $totalDitolak ?? 0, 'ket' => 'Pengajuan ditolak',
                'wrap' => 'border-rose-200/70 from-rose-50 hover:shadow-rose-500/10',
                'ikon' => 'from-rose-500 to-pink-500 shadow-rose-500/25',
                'teks' => 'text-rose-950', 'sub' => 'text-rose-700/80',
                'path' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
        ];

        $tabs = [
            'semua'     => ['Semua', $totalSemua, 'amber'],
            'pending'   => ['Pending', $totalPending ?? 0, 'amber'],
            'disetujui' => ['Disetujui', $totalDisetujui ?? 0, 'emerald'],
            'ditolak'   => ['Ditolak', $totalDitolak ?? 0, 'rose'],
        ];
        $tabAktif = [
            'amber'   => 'border-amber-600 bg-amber-600 text-white shadow-md shadow-amber-500/25',
            'emerald' => 'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-500/25',
            'rose'    => 'border-rose-600 bg-rose-600 text-white shadow-md shadow-rose-500/25',
        ];
        $tabHover = [
            'amber'   => 'hover:border-amber-400 hover:text-amber-700',
            'emerald' => 'hover:border-emerald-400 hover:text-emerald-700',
            'rose'    => 'hover:border-rose-400 hover:text-rose-700',
        ];

        $badgeStatus = [
            'disetujui' => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500'],
            'ditolak'   => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200', 'bg-rose-500'],
            'pending'   => ['Pending', 'bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500'],
        ];

        $pesanKosong = [
            'semua'     => 'Belum ada pengajuan refund.',
            'pending'   => 'Tidak ada pengajuan yang menunggu.',
            'disetujui' => 'Belum ada pengajuan yang disetujui.',
            'ditolak'   => 'Belum ada pengajuan yang ditolak.',
        ];
    @endphp

    <style>[x-cloak] { display: none !important; }</style>

    <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto"
         x-data="{
            modal: false, proses: false, form: null,
            jenis: 'ditolak', kode: '', nama: '', resi: '',
            tanya(e, jenis, kode, nama) {
                this.form = e.target;
                this.jenis = jenis; this.kode = kode; this.nama = nama;
                this.resi = (e.target.querySelector('[name=no_resi_penjemputan]') || {}).value || '';
                this.proses = false;
                this.modal = true;
                this.$nextTick(() => this.$refs.batal && this.$refs.batal.focus());
            },
            lanjut() {
                if (this.proses) return;
                this.proses = true;
                
                // SIMPAN POSISI SCROLL SEBELUM SUBMIT
                sessionStorage.setItem('scrollPositionRefundAdmin', window.scrollY);
                
                this.form.submit();
            },
         }"
         @keydown.escape.window="if (!proses) modal = false">

        {{-- Flash messages --}}
        @foreach (['success' => 'emerald', 'error' => 'rose'] as $tipe => $warna)
            @if (session($tipe))
                <div x-data="{ buka: true }" x-show="buka" x-transition
                     class="flex items-start gap-3 rounded-2xl border p-4 shadow-sm
                            {{ $warna === 'emerald' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}"
                     role="alert">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $warna === 'emerald' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if ($tipe === 'success')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                            @endif
                        </svg>
                    </div>
                    <p class="flex-1 self-center text-sm font-semibold">{{ session($tipe) }}</p>
                    <button type="button" @click="buka = false" class="shrink-0 rounded-lg p-1 opacity-60 transition hover:opacity-100" aria-label="Tutup">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
        @endforeach

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Refund &amp; Return</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola pengajuan pengembalian dana atau barang dari pelanggan.</p>
        </div>

        {{-- Statistik (klik untuk memfilter) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ($kartu as $kunci => $k)
                <a href="{{ route('admin.refund.index', ['status' => $kunci]) }}"
                   class="group relative overflow-hidden rounded-2xl border bg-gradient-to-br {{ $k['wrap'] }} via-white to-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg
                          {{ $filter === $kunci ? 'ring-2 ring-offset-2 ring-amber-400/70' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider {{ $k['sub'] }}">{{ $k['label'] }}</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr {{ $k['ikon'] }} text-white shadow-md transition-transform duration-300 group-hover:scale-110">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $k['path'] }}" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold tabular-nums tracking-tight {{ $k['teks'] }}">{{ $k['angka'] }}</span>
                        <span class="text-sm font-semibold text-gray-400">pengajuan</span>
                    </div>
                    <p class="mt-1.5 text-xs font-medium {{ $k['sub'] }}">{{ $k['ket'] }}</p>
                </a>
            @endforeach
        </div>

        {{-- Filter tab --}}
        <div class="flex items-center gap-2 overflow-x-auto rounded-2xl border border-gray-100 bg-white p-3 shadow-sm" role="tablist" aria-label="Filter status refund">
            @foreach ($tabs as $kunci => [$label, $jumlah, $warna])
                @php $aktif = $filter === $kunci; @endphp
                <a href="{{ route('admin.refund.index', ['status' => $kunci]) }}" role="tab" aria-selected="{{ $aktif ? 'true' : 'false' }}"
                   class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl border px-4 py-2 text-xs font-bold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400
                          {{ $aktif ? $tabAktif[$warna] : 'border-gray-200 bg-white text-gray-600 ' . $tabHover[$warna] }}">
                    {{ $label }}
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold tabular-nums {{ $aktif ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $jumlah }}</span>
                </a>
            @endforeach
        </div>

        {{-- Tabel --}}
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div>
                <table class="w-full divide-y divide-gray-100 text-left text-sm">
                    <thead class="bg-gray-50/80">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                            <th class="px-4 py-3">Transaksi</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Alasan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100/80">
                        @forelse ($refundList as $r)
                            @php
                                $t = $r->transaksi;
                                $u = $t->user ?? null;
                                $jenis = $t->jenis ?? null;
                                $produkNama = $t?->detail?->first()?->produk?->nama ?? '-';

                                $namaPelanggan = $u->name ?? '-';
                                $inisial = strtoupper(mb_substr(trim($namaPelanggan) ?: '?', 0, 1));
                                $fotoUser = $u->foto_profil ?? null;
                                $fotoUserUrl = $fotoUser
                                    ? (\Illuminate\Support\Str::startsWith($fotoUser, ['http://', 'https://']) ? $fotoUser : asset('storage/' . $fotoUser))
                                    : null;

                                $hp = preg_replace('/\D/', '', (string) ($u->no_hp ?? ''));
                                if ($hp !== '' && str_starts_with($hp, '0')) { $hp = '62' . substr($hp, 1); }

                                $bs = $badgeStatus[$r->status] ?? $badgeStatus['pending'];
                                $diajukan = $r->created_at?->copy()->locale('id')->translatedFormat('d M Y, H:i');
                            @endphp

                            <tr class="transition-colors hover:bg-amber-50/30">
                                {{-- Transaksi --}}
                                <td class="px-4 py-3.5 align-top">
                                    <p class="font-bold text-gray-900 whitespace-nowrap">{{ $t->kode_transaksi ?? '-' }}</p>
                                    <p class="mt-0.5 max-w-[170px] truncate text-xs text-gray-500" title="{{ $produkNama }}">{{ $produkNama }}</p>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                        @if ($jenis)
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide
                                                {{ $jenis === 'sewa' ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-sky-200 bg-sky-50 text-sky-700' }}">{{ $jenis }}</span>
                                        @endif
                                        @if ($diajukan)
                                            <span class="text-[10px] text-gray-400">{{ $diajukan }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Pelanggan --}}
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-center gap-3">
                                        <div class="relative h-10 w-10 shrink-0">
                                            <span class="absolute inset-0 flex items-center justify-center rounded-full border border-amber-100 bg-amber-50 text-sm font-black text-amber-700">{{ $inisial }}</span>
                                            @if ($fotoUserUrl)
                                                <img src="{{ $fotoUserUrl }}" alt="{{ $namaPelanggan }}" loading="lazy" referrerpolicy="no-referrer"
                                                     onerror="this.remove()"
                                                     class="absolute inset-0 h-full w-full rounded-full border border-gray-200 object-cover">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="max-w-[140px] truncate font-bold text-gray-900" title="{{ $namaPelanggan }}">{{ $namaPelanggan }}</p>
                                            @if ($hp !== '')
                                                <a href="https://wa.me/{{ $hp }}" target="_blank" rel="noopener"
                                                   class="text-xs text-gray-500 transition hover:text-emerald-600 hover:underline">{{ $u->no_hp }}</a>
                                            @else
                                                <p class="text-xs text-gray-400">-</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Alasan --}}
                                <td class="px-4 py-3.5 align-top">
                                    <p class="line-clamp-3 text-xs leading-relaxed text-gray-600" title="{{ $r->alasan }}">{{ $r->alasan ?: '-' }}</p>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3.5 align-top text-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $bs[1] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $bs[2] }}"></span>{{ $bs[0] }}
                                    </span>
                                    @if ($jenis === 'beli' && $r->no_resi_penjemputan)
                                        <p class="mt-1.5 font-mono text-[10px] text-gray-500" title="No. resi penjemputan">{{ $r->no_resi_penjemputan }}</p>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3.5 align-top">
                                    @if ($r->status === 'pending')
                                        <div class="ml-auto flex w-[150px] flex-col gap-1.5">

                                            {{-- Setujui --}}
                                            <form action="{{ route('admin.refund.update-status', $r->id) }}" method="POST"
                                                  @submit.prevent="tanya($event, 'disetujui', @js($t->kode_transaksi ?? '-'), @js($namaPelanggan))" class="flex flex-col gap-2">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="disetujui">

                                                @if ($jenis === 'beli')
                                                    <input type="text" name="no_resi_penjemputan" placeholder="No. resi" required
                                                           class="w-full rounded-lg border border-gray-200 bg-gray-50/60 px-3 py-2 text-xs transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                                @endif

                                                <button type="submit"
                                                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.98]">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                    Setujui
                                                </button>
                                            </form>

                                            {{-- Tolak --}}
                                            <form action="{{ route('admin.refund.update-status', $r->id) }}" method="POST"
                                                  @submit.prevent="tanya($event, 'ditolak', @js($t->kode_transaksi ?? '-'), @js($namaPelanggan))">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit"
                                                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50 active:scale-[0.98]">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <p class="text-right text-xs text-gray-300">—</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16">
                                    <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                                        <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-600">{{ $pesanKosong[$filter] ?? $pesanKosong['semua'] }}</p>
                                        @if ($filter !== 'semua')
                                            <a href="{{ route('admin.refund.index', ['status' => 'semua']) }}" class="mt-2 text-xs font-bold text-amber-600 hover:underline">Lihat semua pengajuan</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        {{-- ===== Modal konfirmasi ===== --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" x-show="modal"
                 x-transition.opacity.duration.200ms @click="if (!proses) modal = false"></div>

            <div x-show="modal"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white p-6 shadow-2xl">

                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl"
                         :class="jenis === 'disetujui' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                        <svg x-show="jenis === 'disetujui'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        <svg x-show="jenis !== 'disetujui'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" /></svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-bold text-gray-900" x-text="jenis === 'disetujui' ? 'Setujui pengajuan refund?' : 'Tolak pengajuan refund?'"></h3>
                        <p class="mt-1 text-sm leading-relaxed text-gray-500">
                            <span x-show="jenis === 'disetujui'">Pengajuan ini akan disetujui dan pelanggan akan mengetahui statusnya.</span>
                            <span x-show="jenis !== 'disetujui'">Pengajuan ini akan ditolak. Tindakan ini tidak dapat dibatalkan.</span>
                        </p>
                    </div>
                </div>

                <dl class="mt-5 space-y-2 rounded-2xl bg-gray-50 p-4 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500">Transaksi</dt>
                        <dd class="font-bold text-gray-900" x-text="kode"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-gray-500">Pelanggan</dt>
                        <dd class="truncate font-semibold text-gray-900" x-text="nama"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4" x-show="jenis === 'disetujui' && resi" x-cloak>
                        <dt class="text-gray-500">No. resi</dt>
                        <dd class="font-mono text-xs font-semibold text-gray-900" x-text="resi"></dd>
                    </div>
                </dl>

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" x-ref="batal" @click="modal = false" :disabled="proses"
                            class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-300 disabled:opacity-50">
                        Batal
                    </button>
                    <button type="button" @click="lanjut()" :disabled="proses"
                            class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-70"
                            :class="jenis === 'disetujui' ? 'bg-emerald-600 hover:bg-emerald-700 focus-visible:ring-emerald-500' : 'bg-rose-600 hover:bg-rose-700 focus-visible:ring-rose-500'">
                        <svg x-show="proses" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        <span x-text="proses ? 'Memproses...' : (jenis === 'disetujui' ? 'Ya, setujui' : 'Ya, tolak')"></span>
                    </button>
                </div>
            </div>
        </div>

        @if ($refundList->hasPages())
            <div>{{ $refundList->withQueryString()->links() }}</div>
        @endif

    </div>

    {{-- SCRIPT PEMULIH SCROLL --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollPos = sessionStorage.getItem('scrollPositionRefundAdmin');
            if (scrollPos !== null) {
                sessionStorage.removeItem('scrollPositionRefundAdmin');
                
                // Paksa scroll kembali ke posisi semula, lakukan beberapa kali untuk mengalahkan layout shift
                let upaya = 0;
                const paksaScroll = setInterval(function () {
                    window.scrollTo(0, parseInt(scrollPos));
                    upaya++;
                    if (upaya >= 10) clearInterval(paksaScroll);
                }, 100);
            }
        });
    </script>
@endsection