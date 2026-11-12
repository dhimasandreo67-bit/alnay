@php
    $status = strtolower($fitting->status);

    // Tidak ada tahap "menunggu persetujuan": booking langsung Terjadwal. 'disetujui' (data lama) dianggap sama.
    if ($status === 'batal') {
        $tema = [
            'bg' => 'bg-rose-50/40', 'border' => 'border-rose-100',
            'iconBg' => 'bg-rose-100', 'iconBorder' => 'border-rose-500', 'iconColor' => 'text-rose-600',
            'btn' => 'border-rose-200 text-rose-700 hover:bg-rose-50',
            'badge' => 'bg-rose-100 text-rose-700', 'label' => 'Dibatalkan',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>',
        ];
    } elseif ($status === 'hadir') {
        $tema = [
            'bg' => 'bg-emerald-50/40', 'border' => 'border-emerald-100',
            'iconBg' => 'bg-emerald-100', 'iconBorder' => 'border-emerald-500', 'iconColor' => 'text-emerald-600',
            'btn' => 'border-emerald-200 text-emerald-700 hover:bg-emerald-50',
            'badge' => 'bg-emerald-100 text-emerald-700', 'label' => 'Selesai',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>',
        ];
    } else {
        $tema = [
            'bg' => 'bg-sky-50/40', 'border' => 'border-sky-100',
            'iconBg' => 'bg-sky-100', 'iconBorder' => 'border-sky-500', 'iconColor' => 'text-sky-600',
            'btn' => 'border-sky-200 text-sky-700 hover:bg-sky-50',
            'badge' => 'bg-sky-100 text-sky-700', 'label' => 'Terjadwal',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        ];
    }

    $tgl = $fitting->slot ? \Carbon\Carbon::parse($fitting->slot->tanggal) : null;
    $jam = $fitting->slot?->jam_mulai ? \Carbon\Carbon::parse($fitting->slot->jam_mulai)->format('H:i') : null;
@endphp

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border {{ $tema['border'] }} {{ $tema['bg'] }}">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full {{ $tema['iconBg'] }} border-2 {{ $tema['iconBorder'] }} flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 {{ $tema['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $tema['icon'] !!}
            </svg>
        </div>

        <div>
            <div class="flex items-center">
                <p class="text-sm font-bold text-ink/90">{{ $fitting->kode_booking }}</p>
                <span class="px-2 py-0.5 rounded {{ $tema['badge'] }} text-[10px] font-bold uppercase ml-2">{{ $tema['label'] }}</span>
            </div>
            <p class="text-xs text-ink/60 mt-0.5">
                {{ $tgl ? $tgl->translatedFormat('l, d M Y') : '—' }}
                • {{ $jam ? $jam . ' WIB' : '—' }}
            </p>
        </div>
    </div>

    {{-- BAGIAN TOMBOL YANG DIGABUNGKAN --}}
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('fitting.show', $fitting->id) }}"
           class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-white border {{ $tema['btn'] }} text-xs font-bold rounded-xl transition-colors">
            Lihat Detail <x-icon name="chevron-right" :size="14" />
        </a>

        @if (in_array($fitting->status, ['pending', 'batal']))
            <form action="{{ route('fitting.destroy', $fitting->id) }}"
                  method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus reservasi fitting ini? Data akan hilang permanen.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-rose-200 text-rose-500 hover:bg-rose-50 transition-colors"
                        title="Hapus Reservasi">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        @endif
    </div>
</div>