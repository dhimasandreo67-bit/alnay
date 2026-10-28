@extends('layouts.akun')

@section('title', 'Detail Pesanan — ' . config('alnay.brand.nama'))

@section('content')

<style>
    @keyframes pd-up   { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
    @keyframes pd-pop  { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.15); } 100% { opacity: 1; transform: none; } }
    .pd-up    { animation: pd-up .7s cubic-bezier(.22, .8, .3, 1) both; animation-delay: var(--d, 0s); }
    .pd-pop   { animation: pd-pop .5s cubic-bezier(.3, 1.4, .5, 1) both; animation-delay: var(--d, 0s); }
    .pd-shine { position: relative; overflow: hidden; }
    .pd-shine::after { content: ''; position: absolute; inset: 0; background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .38) 50%, transparent 70%); transform: translateX(-120%); transition: transform .8s ease; pointer-events: none; }
    .pd-shine:hover::after { transform: translateX(120%); }
</style>

<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-3xl mx-auto px-4">

        {{-- Tombol Kembali & Judul --}}
        <div class="pd-up flex items-center gap-3 mb-6" style="--d: .05s;">
            <a href="{{ route('akun.pesanan.index') }}" class="w-8 h-8 rounded-full bg-white border border-line/50 flex items-center justify-center text-ink/50 hover:text-gold hover:border-gold/50 hover:-translate-x-0.5 transition-all duration-300">
                <x-icon name="chevron-right" :size="14" class="rotate-180" />
            </a>
            <div>
                <h1 class="font-display text-2xl font-medium text-ink/85">Detail Pesanan</h1>
                <p class="text-[11px] text-ink/50">{{ $transaksi->kode_transaksi }}</p>
            </div>
        </div>

        {{-- STATUS CARD --}}
        <div class="pd-up bg-panel border border-line/40 rounded-xl p-5 mb-4" style="--d: .1s;">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-[10px] text-ink/50 uppercase tracking-wider mb-1">Status Pembayaran</p>
                    <p class="text-[13px] font-semibold text-ink/85 uppercase">{{ str_replace('_', ' ', $transaksi->status_pembayaran) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-ink/50 uppercase tracking-wider mb-1">Tanggal Transaksi</p>
                    <p class="text-[13px] font-semibold text-ink/85">{{ $transaksi->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- PRODUK --}}
        <div class="pd-up bg-panel border border-line/40 rounded-xl p-5 mb-4" style="--d: .15s;">
            <h2 class="text-[11px] font-semibold text-ink/80 mb-3 uppercase tracking-wider">Informasi Produk</h2>
            
            @foreach($transaksi->detail as $d)
                <div class="flex gap-3 items-center bg-cream/40 p-3 rounded-lg border border-line/30 mb-2">
                    <div class="w-14 h-14 rounded-md bg-white border border-line/50 overflow-hidden shrink-0">
                        @php
                            $foto = $d->produk->foto_produk ?? null;
                            if (is_array($foto)) {
                                $foto = $foto[0] ?? null;
                            }
                        @endphp

                        @if($foto)
                            <img src="{{ asset('storage/' . $foto) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-ink/20">
                                <x-icon name="sparkle" :size="18" />
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-[13px] font-semibold text-ink/85">{{ $d->produk->nama ?? 'Produk' }}</p>
                        <p class="text-[11px] text-ink/55">Jumlah: {{ $d->jumlah }} x Rp{{ number_format($d->harga_satuan, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-[13px] font-semibold text-ink/85 tabular-nums">
                        Rp{{ number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- JADWAL SEWA --}}
        @if($transaksi->sewa)
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5 mb-4" style="--d: .2s;">
                <h2 class="text-[11px] font-semibold text-ink/80 mb-3 uppercase tracking-wider">Jadwal Penyewaan</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-3 border-b border-line/30">
                        <span class="text-[12px] text-ink/60">Tanggal Ambil</span>
                        <span class="text-[13px] font-semibold text-ink/85">{{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_pinjam)->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-line/30">
                        <span class="text-[12px] text-ink/60">Tanggal Kembali</span>
                        <span class="text-[13px] font-semibold text-ink/85">{{ \Carbon\Carbon::parse($transaksi->sewa->tanggal_kembali_rencana)->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-[12px] text-ink/60">Status Sewa</span>
                        <span class="px-2.5 py-1 rounded-md bg-gold/10 text-gold text-[10px] font-bold uppercase border border-gold/20">
                            {{ $transaksi->sewa->status }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        {{-- RIWAYAT KONDISI BARANG (QC) --}}
        @if($transaksi->sewa && $transaksi->sewa->pengecekan->count() > 0)
            <div class="pd-up bg-panel border border-line/40 rounded-xl p-5 mb-4" style="--d: .22s;">
                <h2 class="text-[11px] font-semibold text-ink/80 mb-3 uppercase tracking-wider">Riwayat Kondisi Barang</h2>
                
                <div class="relative border-l-2 border-gold/30 ml-2 space-y-6">
                    @foreach($transaksi->sewa->pengecekan->sortByDesc('created_at') as $qc)
                        <div class="relative pl-4">
                            <div class="absolute -left-[5px] top-1 w-2 h-2 rounded-full {{ $qc->tahap === 'sebelum_cuci' ? 'bg-rose-500' : 'bg-emerald-500' }} ring-2 ring-white"></div>
                            <p class="text-xs font-bold text-ink">
                                {{ $qc->tahap === 'sebelum_cuci' ? 'Pengecekan Saat Dikembalikan' : 'Pengecekan Sebelum Diserahkan' }}
                            </p>
                            <p class="text-[10px] text-gray-500">{{ $qc->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                            <p class="text-[11px] text-gray-600 mt-1 italic">"{{ $qc->catatan }}"</p>
                            @if($qc->foto_bukti)
                                <a href="{{ asset('storage/' . $qc->foto_bukti) }}" target="_blank" class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold text-gold hover:underline">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat Foto Bukti
                                </a>
                            @endif
                            <p class="text-[9px] text-gray-400 mt-1">Petugas: {{ $qc->petugas }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- RINCIAN PEMBAYARAN --}}
        <div class="pd-up bg-panel border border-line/40 rounded-xl p-5 mb-4" style="--d: .25s;">
            <h2 class="text-[11px] font-semibold text-ink/80 mb-3 uppercase tracking-wider">Riwayat Pembayaran</h2>
            
            <div class="space-y-2">
                @forelse($transaksi->pembayaran as $p)
                    <div class="flex justify-between items-center bg-cream/40 p-3 rounded-lg border border-line/30">
                        <div>
                            <p class="text-[12px] font-semibold text-ink/85 uppercase">{{ $p->jenis_bayar }}</p>
                            <p class="text-[10px] text-ink/50">{{ $p->created_at->format('d M Y') }} · {{ $p->metode }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[13px] font-semibold text-ink/85 tabular-nums">Rp{{ number_format($p->jumlah, 0, ',', '.') }}</p>
                            <p class="text-[9px] font-bold uppercase {{ $p->status_midtrans === 'success' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $p->status_midtrans }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-[11px] text-ink/50 italic">Belum ada riwayat pembayaran.</p>
                @endforelse
            </div>

            <div class="flex justify-between items-center border-t border-line/40 pt-4 mt-4">
                <span class="text-[12px] font-semibold text-ink/80">Total Tagihan</span>
                <span class="font-display text-lg font-semibold text-gold tabular-nums">Rp{{ number_format($transaksi->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- FORM RATING --}}
        @if(in_array($transaksi->status_pembayaran, ['lunas', 'settlement', 'success']))
            @php
                $detailPertama = $transaksi->detail->first();
                $sudahRating = $detailPertama ? \App\Models\Rating::where('detail_transaksi_id', $detailPertama->id)->exists() : true;
            @endphp

            @if(!$sudahRating && $detailPertama)
                <div class="pd-up bg-panel border border-line/40 rounded-xl p-5 mb-4" style="--d: .35s;">
                    <h2 class="text-[11px] font-semibold text-ink/80 mb-3 uppercase tracking-wider">Beri Ulasan</h2>
                    <form action="{{ route('rating.store', $detailPertama->id) }}" method="POST" x-data="{ rating: 5 }">
                        @csrf
                        
                        <div class="flex gap-2 mb-3">
                            <template x-for="i in 5" :key="i">
                                <button type="button" @click="rating = i" 
                                        :class="i <= rating ? 'text-amber-400' : 'text-gray-300'"
                                        class="text-3xl transition-transform hover:scale-110">
                                    ★
                                </button>
                            </template>
                            <input type="hidden" name="rating" x-model="rating">
                        </div>
                        <p class="text-[10px] text-ink/50 mb-2">Rating: <span x-text="rating"></span> / 5</p>
                        
                        <textarea name="ulasan" rows="2" placeholder="Ceritakan pengalaman Anda menyewa di ALNAY..." class="w-full px-3 py-2 rounded-lg border border-line/50 bg-white text-xs mb-3 focus:outline-none focus:border-gold"></textarea>
                        <button type="submit" class="px-4 py-2 bg-gold text-white text-xs font-bold rounded-lg hover:bg-gold/90">Kirim Ulasan</button>
                    </form>
                </div>
            @elseif($sudahRating)
                <div class="p-3 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200 mb-4 text-center">
                    Anda sudah memberikan ulasan untuk pesanan ini. Terima kasih!
                </div>
            @endif
        @endif

        {{-- TOMBOL AKSI --}}
        <div class="pd-up flex gap-2" style="--d: .3s;">
            <a href="{{ route('akun.pesanan.index') }}" class="flex-1 text-center py-2.5 rounded-lg border border-line/50 text-ink/65 text-[11px] font-semibold hover:bg-white transition-all duration-300">
                Kembali
            </a>
            @if($transaksi->status_pembayaran === 'pending' || $transaksi->status_pembayaran === 'dp')
                <a href="{{ route('sewa.konfirmasi', $transaksi->id) }}" class="flex-1 text-center py-2.5 rounded-lg bg-gold text-white text-[11px] font-semibold hover:bg-gold/90 transition-all duration-300 pd-shine">
                    Lanjutkan Pembayaran
                </a>
            @endif
        </div>

    </div>
</div>

@endsection