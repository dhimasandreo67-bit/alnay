@php
    $status = strtolower($fitting->status);

    // Tentukan tema warna & ikon berdasarkan status
    if ($status === 'batal') {
        $cardBg = 'bg-rose-50/40';
        $cardBorder = 'border-rose-100';
        $iconBg = 'bg-rose-100';
        $iconBorder = 'border-rose-500';
        $iconColor = 'text-rose-600';
        $btnClass = 'border-rose-200 text-rose-700 hover:bg-rose-50';
        $statusBadge = '<span class="px-2 py-0.5 rounded bg-rose-100 text-rose-700 text-[10px] font-bold uppercase ml-2">Dibatalkan</span>';
        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>';
    } elseif ($status === 'hadir' || $status === 'disetujui') {
        $cardBg = 'bg-emerald-50/40';
        $cardBorder = 'border-emerald-100';
        $iconBg = 'bg-emerald-100';
        $iconBorder = 'border-emerald-500';
        $iconColor = 'text-emerald-600';
        $btnClass = 'border-emerald-200 text-emerald-700 hover:bg-emerald-50';
        $statusBadge = '<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase ml-2">Hadir</span>';
        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>';
    } else {
        // Default untuk Pending
        $cardBg = 'bg-amber-50/40';
        $cardBorder = 'border-amber-100';
        $iconBg = 'bg-amber-100';
        $iconBorder = 'border-amber-500';
        $iconColor = 'text-amber-600';
        $btnClass = 'border-amber-200 text-amber-700 hover:bg-amber-50';
        $statusBadge = '<span class="px-2 py-0.5 rounded bg-amber-100 text-amber-700 text-[10px] font-bold uppercase ml-2">Menunggu</span>';
        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
    }
@endphp

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border {{ $cardBorder }} {{ $cardBg }}">
    <div class="flex items-center gap-4">
        {{-- Ikon Bulat Dinamis --}}
        <div class="w-12 h-12 rounded-full {{ $iconBg }} border-2 {{ $iconBorder }} flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $iconSvg !!}
            </svg>
        </div>
        
        <div>
            <div class="flex items-center">
                <p class="text-sm font-bold text-ink/90">{{ $fitting->kode_booking }}</p>
                {!! $statusBadge !!}
            </div>
            <p class="text-xs text-ink/60 mt-0.5">
                {{ $fitting->slot ? \Carbon\Carbon::parse($fitting->slot->tanggal)->translatedFormat('l, d M Y') : '—' }}
                • {{ $fitting->slot?->jam_mulai ? $fitting->slot->jam_mulai . ' WIB' : '—' }}
            </p>
        </div>
    </div>
    
    <a href="{{ route('fitting.show', $fitting->id) }}"
       class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-white border {{ $btnClass }} text-xs font-bold rounded-xl shrink-0 transition-colors">
        Lihat Detail <x-icon name="chevron-right" :size="14" />
    </a>
</div>