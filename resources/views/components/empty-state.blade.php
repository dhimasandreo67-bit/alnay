@props(['title' => 'Area konten', 'message' => 'Bagian ini akan diisi oleh tim pengembang.'])

<div class="border-2 border-dashed border-line rounded-2xl p-12 md:p-20 text-center bg-panel/50">
    <div class="w-12 h-12 rounded-full bg-cream flex items-center justify-center text-gold mx-auto mb-4">
        <x-icon name="sparkle" :size="20" />
    </div>
    <h3 class="font-display text-lg text-ink mb-2">{{ $title }}</h3>
    <p class="text-sm text-muted max-w-md mx-auto">{{ $message }}</p>
</div>