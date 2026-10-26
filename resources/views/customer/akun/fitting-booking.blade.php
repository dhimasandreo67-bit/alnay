@extends('layouts.akun')
@section('title', 'Booking Fitting — ' . config('alnay.brand.nama'))

@section('content')
<div class="bg-cream min-h-screen py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4">
        
        <div class="mb-6">
            <h1 class="font-display text-2xl font-medium text-ink/85">Pilih Jadwal Fitting</h1>
            <p class="text-xs text-ink/50 mt-1">Tentukan waktu kedatangan Anda ke studio ALNAY untuk pengukuran dan penyesuaian busana.</p>
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 rounded-md bg-rose-50 border border-rose-200 text-[11px] text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-panel border border-line/40 rounded-xl p-5 mb-6">
            <div class="flex items-center gap-3 border-b border-line/30 pb-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-gold/10 flex items-center justify-center text-gold">
                    <x-icon name="calendar" :size="20" />
                </div>
                <div>
                    <p class="text-[10px] text-ink/50 uppercase tracking-wider">Untuk Pesanan</p>
                    <p class="text-sm font-bold text-ink">{{ $transaksi->kode_transaksi }}</p>
                </div>
            </div>

            <form action="{{ route('fitting.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="transaksi_id" value="{{ $transaksi->id }}">

                <div>
                    <label class="block text-xs font-bold text-ink/80 mb-3">Pilih Tanggal & Jam</label>
                    
                    @forelse($slots as $tanggal => $slotList)
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-ink/85 mb-2">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($slotList as $slot)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="slot_id" value="{{ $slot->id }}" class="peer sr-only" required>
                                        <div class="px-3 py-2 rounded-lg border border-line/60 text-center transition-all duration-300 peer-checked:bg-gold peer-checked:text-white peer-checked:border-gold hover:border-gold/50">
                                            <p class="text-sm font-bold">{{ \Carbon\Carbon::parse($slot->jam_mulai)->format('H:i') }} WIB</p>
                                            <p class="text-[10px] opacity-80 mt-0.5">Sisa {{ $slot->kapasitas - $slot->reservasi_count }} slot</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-rose-500 italic">Belum ada jadwal yang tersedia saat ini. Silakan hubungi Admin.</p>
                    @endforelse
                </div>

                <div>
                    <label class="block text-xs font-bold text-ink/80 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Contoh: Ingin minta disiapkan aksesoris tambahan." class="w-full px-3 py-2.5 rounded-md border border-line/60 bg-white text-[13px] text-ink/85 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all resize-none"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full h-[46px] rounded-lg bg-gold text-white text-[13px] font-semibold tracking-wide hover:bg-gold/90 hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300">
                        Konfirmasi Jadwal Fitting
                    </button>
                </div>
            </form>
        </div>

        <a href="{{ route('akun.pesanan.index') }}" class="block text-center text-xs text-ink/50 hover:text-gold transition-colors">
            Kembali ke Pesanan Saya
        </a>
    </div>
</div>
@endsection