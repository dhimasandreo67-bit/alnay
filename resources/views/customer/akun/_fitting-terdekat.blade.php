<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-emerald-100 bg-emerald-50/40">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-emerald-100 border-2 border-emerald-500 flex items-center justify-center shrink-0">
            <x-icon name="check" :size="20" class="text-emerald-600" />
        </div>
        <div>
            <p class="text-sm font-bold text-ink/90">{{ $fitting->kode_booking }}</p>
            <p class="text-xs text-ink/60 mt-0.5">
                {{ $fitting->slot ? \Carbon\Carbon::parse($fitting->slot->tanggal)->translatedFormat('l, d M Y') : '—' }}
                • {{ $fitting->slot?->jam_mulai ? $fitting->slot->jam_mulai . ' WIB' : '—' }}
            </p>
        </div>
    </div>
    <a href="{{ route('fitting.show', $fitting->id) }}"
       class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-white border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-50 shrink-0">
        Lihat Detail <x-icon name="chevron-right" :size="14" />
    </a>
</div>