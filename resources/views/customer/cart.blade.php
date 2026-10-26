@extends('layouts.public')

@section('title', 'Keranjang Belanja - ALNAY.ID')

@section('content')

{{-- ============================================================
     CUSTOM STYLES
     ============================================================ --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');
    
    .font-display {
        font-family: 'Playfair Display', serif;
    }
    
    body {
        font-family: 'Inter', sans-serif;
    }
    
    /* Custom Checkbox Styling */
    .custom-checkbox {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .custom-checkbox:checked {
        background-color: #c99a32;
        border-color: #c99a32;
    }
    
    .custom-checkbox:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 14px;
        font-weight: bold;
    }
    
    /* Quantity Input Styling */
    .qty-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d1d5db;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .qty-btn:hover {
        background-color: #f3f4f6;
    }
    
    .qty-input {
        width: 40px;
        text-align: center;
        border: none;
        border-left: 1px solid #d1d5db;
        border-right: 1px solid #d1d5db;
        font-weight: 600;
    }
    
    .qty-input:focus {
        outline: none;
    }
</style>


{{-- ============================================================
     KERANJANG BELANJA
     ============================================================ --}}
    {{-- Notifikasi Sukses dengan Auto-Dismiss --}}
@if(session('success'))
<div id="notif-success" class="mb-6 p-4 rounded-lg border transition-all duration-500 ease-in-out" 
     style="background-color: #f0fdf4; border-color: #86efac; opacity: 1; transform: translateY(0);">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5" style="color: #16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <p class="text-sm font-semibold" style="color: #16a34a;">{{ session('success') }}</p>
        <button onclick="closeNotif()" class="ml-auto text-green-600 hover:text-green-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<script>
    // Auto-dismiss notifikasi setelah 3 detik
    setTimeout(function() {
        closeNotif();
    }, 3000);

    function closeNotif() {
        const notif = document.getElementById('notif-success');
        if (notif) {
            notif.style.opacity = '0';
            notif.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                notif.remove();
            }, 500); // Tunggu animasi selesai sebelum remove
        }
    }
</script>
@endif
<section class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="font-display text-3xl md:text-4xl font-bold mb-8" style="color: #15233d;">
        Keranjang Belanja
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- LEFT: Daftar Item Keranjang --}}
        <div class="lg:col-span-2">
            
           {{-- Header: Pilih Semua & Hapus --}}
<div class="flex items-center justify-between mb-4 p-4 bg-white rounded-lg border" style="border-color: #f3f4f6;">
    <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" id="select-all" class="custom-checkbox">
        <span class="text-sm font-semibold" style="color: #15233d;">
            Pilih Semua (<span id="total-items">{{ count($cartItems) }}</span> Barang)
        </span>
    </label>
    
    {{-- Form Hapus Pilihan --}}
    <form action="{{ route('keranjang.removeSelected') }}" method="POST" id="form-hapus-pilihan">
        @csrf
        @method('POST')
        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700 transition-colors">
             Hapus Pilihan
        </button>
    </form>
</div>

            {{-- List Items --}}
            <div class="space-y-4">
                @foreach($cartItems as $id => $item)
<div class="bg-white rounded-lg border p-4 md:p-6" style="border-color: #f3f4f6;">
    <div class="flex gap-4">
        {{-- Checkbox --}}
        <div class="flex items-start">
            <input type="checkbox" 
                   class="custom-checkbox item-checkbox" 
                   name="ids[]" 
                   value="{{ $id }}"
                   data-id="{{ $id }}">
        </div>

        {{-- Gambar Produk --}}
        <div class="w-24 h-24 md:w-32 md:h-32 flex-shrink-0">
            <img src="{{ asset($item['foto']) }}" alt="{{ $item['nama'] }}" 
                 class="w-full h-full object-cover rounded-lg">
        </div>

        {{-- Detail Produk --}}
        <div class="flex-1">
            <h3 class="text-sm md:text-base font-bold mb-2" style="color: #15233d;">
                {{ $item['nama'] }}
            </h3>
            
            <div class="flex items-center justify-between mt-4">
                {{-- Harga --}}
                <p class="text-lg md:text-xl font-bold" style="color: #c99a32;">
                    Rp {{ number_format($item['harga'], 0, ',', '.') }}
                </p>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    {{-- Form Hapus Item --}}
                    <form action="{{ route('keranjang.remove', $id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>

                    {{-- Form Update Quantity --}}
                    <form action="{{ route('keranjang.updateQty', $id) }}" method="POST" id="form-qty-{{ $id }}">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-center border rounded-lg" style="border-color: #d1d5db;">
                            <button type="button" onclick="updateQty({{ $id }}, -1)" class="qty-btn rounded-l-lg">-</button>
                            <input type="number" name="qty" id="qty-{{ $id }}" value="{{ $item['qty'] }}" 
                                   class="qty-input" min="1" max="10" readonly>
                            <button type="button" onclick="updateQty({{ $id }}, 1)" class="qty-btn rounded-r-lg">+</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
            </div>
        </div>

       {{-- RIGHT: Ringkasan Pembelian --}}
<div class="lg:col-span-1">
    <div class="bg-white rounded-lg border p-6 sticky top-4" style="border-color: #f3f4f6;">
        
        {{-- Judul --}}
        <h2 class="font-display text-xl font-bold mb-6" style="color: #15233d;">
            Ringkasan Pembelian
        </h2>

        {{-- Subtotal --}}
        <div class="flex justify-between items-center mb-3">
            <span class="text-sm" style="color: #6b7280;">Subtotal ({{ count($cartItems) }} Produk)</span>
            <span class="text-sm font-bold" style="color: #15233d;">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>

        {{-- Ongkir --}}
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-1">
                <span class="text-sm" style="color: #6b7280;">Pengiriman Kurir Aman</span>
                <span class="text-xs" style="color: #9ca3af;">ⓘ</span>
            </div>
            <span class="text-sm font-bold" style="color: #15233d;">Rp {{ number_format($ongkir, 0, ',', '.') }}</span>
        </div>

        {{-- Divider --}}
        <div class="border-t my-4" style="border-color: #e5e7eb;"></div>

        {{-- Grand Total --}}
        <div class="flex justify-between items-center mb-6">
            <span class="text-base font-bold" style="color: #15233d;">Grand Total</span>
            <span class="text-2xl font-bold" style="color: #c99a32;">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>

        {{-- Info Deposit --}}
        <div class="bg-gray-50 rounded-lg p-3 mb-6">
            <p class="text-xs" style="color: #6b7280;">
                *Tanpa biaya deposit jaminan sewa (Barang menjadi hak milik)
            </p>
        </div>

        {{-- Tombol Checkout --}}
        <a href="#" class="block w-full py-3 rounded-lg text-center text-sm font-bold text-white transition-colors hover:opacity-90 mb-3" 
           style="background-color: #c99a32;">
            Lanjut ke Pembayaran →
        </a>

        {{-- Link Kembali --}}
        <a href="{{ route('koleksi.index') }}" class="block text-center text-sm font-semibold transition-colors hover:underline" 
           style="color: #6b7280;">
            ← Lanjut Berbelanja
        </a>

        {{-- Divider --}}
        <div class="border-t my-6" style="border-color: #e5e7eb;"></div>

        {{-- Info Trust --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-xs font-semibold" style="color: #15233d;">100% Produk Asli</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" style="color: #c99a32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span class="text-xs font-semibold" style="color: #15233d;">Pengemasan Rapi dan Aman</span>
            </div>
        </div>
        
    </div>
</div>

    </div>
</section>


{{-- ============================================================
     JAVASCRIPT
     ============================================================ --}}
<script>
    // Select All Checkbox
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Update Quantity
    function updateQty(itemId, change) {
        const input = document.getElementById(`qty-${itemId}`);
        let newValue = parseInt(input.value) + change;
        
        if (newValue >= 1 && newValue <= 10) {
            input.value = newValue;
            // Submit form untuk update ke server
            document.getElementById(`form-qty-${itemId}`).submit();
        }
    }

    // Hapus Pilihan - tambahkan checkbox yang terpilih ke form sebelum submit
    document.getElementById('form-hapus-pilihan').addEventListener('submit', function(e) {
        const selected = document.querySelectorAll('.item-checkbox:checked');
        if (selected.length === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 item untuk dihapus');
            return;
        }
        
        if (!confirm(`Yakin ingin menghapus ${selected.length} item terpilih?`)) {
            e.preventDefault();
            return;
        }

        // Tambahkan input hidden untuk setiap item yang dipilih
        selected.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            this.appendChild(input);
        });
    });
</script>

@endsection