@extends('layouts.public')

@section('title', 'Beri Rating — ' . config('alnay.brand.nama'))

@section('content')
<div class="max-w-lg mx-auto py-12 px-4 font-sans">
    <div class="bg-white rounded-2xl shadow-sm border border-line p-6">
        <h1 class="text-xl font-bold text-ink mb-1">Beri Rating & Ulasan</h1>
        <p class="text-sm text-gray-500 mb-6">Produk: <strong>{{ $detail->produk->nama ?? '-' }}</strong></p>

        <form action="{{ route('rating.store', $detail->id) }}" method="POST" x-data="{ rating: 0 }">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-600 mb-2">Rating Bintang</label>
                <div class="flex gap-1 mb-2">
                    <template x-for="i in 5" :key="i">
                        <button type="button" @click="rating = i" class="focus:outline-none">
                            <svg class="w-10 h-10 transition-colors duration-200" 
                                :class="i <= rating ? 'text-amber-400' : 'text-gray-300'"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                    </template>
                    <input type="hidden" name="rating" x-model="rating">
                </div>
                <p class="text-xs text-gray-400 mt-1" x-show="rating > 0">Rating: <span x-text="rating"></span> / 5</p>
                <p class="text-xs text-rose-500 mt-1" x-show="rating === 0">Silakan pilih bintang terlebih dahulu.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-600 mb-2">Ulasan (Opsional)</label>
                <textarea name="ulasan" rows="4" 
                          class="w-full px-3 py-2 border border-line rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold outline-none resize-none" 
                          placeholder="Ceritakan pengalaman Anda menggunakan busana ini..."></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-gold text-white font-bold rounded-lg hover:bg-amber-600 transition-colors">
                Kirim Ulasan
            </button>
        </form>
    </div>
</div>
@endsection