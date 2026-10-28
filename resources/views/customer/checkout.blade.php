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
                                <p class="text-xs text-gray-500">Ongkir akan dihitung otomatis.</p>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Input Alamat (Muncul jika pilih Kirim) --}}
                <div id="alamat-container" class="mt-4 hidden">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#c99a32]/20 focus:border-[#c99a32] outline-none transition-all" placeholder="Masukkan alamat lengkap pengiriman"></textarea>
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
                    @foreach($cartItems as $item)
                        <div class="flex justify-between text-sm">
                            <div class="text-gray-600">
                                <span class="font-semibold text-gray-800">{{ $item['nama'] }}</span><br>
                                <span class="text-xs">{{ $item['qty'] }}x Rp {{ number_format($item['harga'], 0, ',', '.') }}</span>
                            </div>
                            <span class="font-bold text-gray-800">Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-4 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-500">Total Pembayaran</span>
                        <span class="text-2xl font-bold" style="color: #c99a32;">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl text-center text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0" 
                        style="background-color: #c99a32;">
                    Buat Pesanan & Bayar
                </button>
            </div>
        </div>
    </form>
</section>

<script>
    // Tampilkan input alamat jika pilih "kirim"
    document.querySelectorAll('input[name="metode_penerimaan"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const alamatContainer = document.getElementById('alamat-container');
            if (this.value === 'kirim') {
                alamatContainer.classList.remove('hidden');
            } else {
                alamatContainer.classList.add('hidden');
            }
        });
    });
</script>
@endsection