@extends('layouts.public')

@section('title', 'Checkout - ALNAY.ID')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
    .font-display { font-family: 'Playfair Display', serif; }
    body { font-family: 'Inter', sans-serif; }
</style>

<section class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="font-display text-3xl md:text-4xl font-bold mb-8" style="color: #15233d;">
        Checkout Pesanan
    </h1>

    <form action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf
        
        {{-- KIRI: Form Data --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Card: Metode Penerimaan --}}
            <div class="bg-white rounded-lg border p-6" style="border-color: #f3f4f6;">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Metode Penerimaan</h2>
                
                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer mb-2 hover:border-gold transition-colors">
                    <input type="radio" name="metode_penerimaan" value="ambil" checked class="w-4 h-4 text-gold focus:ring-gold">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Ambil di Studio</p>
                        <p class="text-xs text-gray-500">Jl. Terusan Rajabasa No.11, Bandar Lampung</p>
                    </div>
                </label>
                
                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:border-gold transition-colors">
                    <input type="radio" name="metode_penerimaan" value="kirim" class="w-4 h-4 text-gold focus:ring-gold">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-800">Kirim ke Alamat</p>
                        <p class="text-xs text-gray-500">Ongkir akan dihitung otomatis.</p>
                    </div>
                </label>

                {{-- Input Alamat (Muncul jika pilih Kirim) --}}
                <div id="alamat-container" class="mt-4 hidden">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gold/20 focus:border-gold outline-none" placeholder="Masukkan alamat lengkap pengiriman"></textarea>
                </div>
            </div>

            {{-- Card: Jadwal (Jika ada barang sewa) --}}
            <div class="bg-white rounded-lg border p-6" style="border-color: #f3f4f6;">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Tanggal Sewa (Jika Ada)</h2>
                <p class="text-xs text-gray-500 mb-2">Pilih tanggal pengambilan jika pesanan Anda berisi barang sewa.</p>
                <input type="date" name="tanggal_sewa" min="{{ now()->toDateString() }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gold/20 focus:border-gold outline-none">
            </div>

            {{-- Card: Metode Pembayaran --}}
            <div class="bg-white rounded-lg border p-6" style="border-color: #f3f4f6;">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Metode Pembayaran</h2>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:border-gold transition-colors">
                        <input type="radio" name="metode_bayar" value="qris" checked class="w-4 h-4 text-gold focus:ring-gold">
                        <span class="text-sm font-semibold text-gray-800">QRIS (Semua Bank & Dompet Digital)</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:border-gold transition-colors">
                        <input type="radio" name="metode_bayar" value="va" class="w-4 h-4 text-gold focus:ring-gold">
                        <span class="text-sm font-semibold text-gray-800">Virtual Account Bank</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:border-gold transition-colors">
                        <input type="radio" name="metode_bayar" value="ewallet" class="w-4 h-4 text-gold focus:ring-gold">
                        <span class="text-sm font-semibold text-gray-800">E-Wallet (GoPay, OVO, ShopeePay)</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- KANAN: Ringkasan --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg border p-6 sticky top-4" style="border-color: #f3f4f6;">
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

                <div class="border-t pt-4 mb-4" style="border-color: #e5e7eb;">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-500">Total Pembayaran</span>
                        <span class="text-xl font-bold" style="color: #c99a32;">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-lg text-center text-sm font-bold text-white transition-colors hover:opacity-90" 
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