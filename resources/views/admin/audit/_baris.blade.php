@php
    $aksiMeta = match ($log->aksi) {
        'created' => ['Dibuat', 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        'updated' => ['Diubah', 'bg-sky-100 text-sky-700 border-sky-200'],
        'deleted' => ['Dihapus', 'bg-rose-100 text-rose-700 border-rose-200'],
        'login' => ['Masuk', 'bg-amber-100 text-amber-700 border-amber-200'],
        'logout' => ['Keluar', 'bg-gray-100 text-gray-600 border-gray-200'],
        'login_gagal' => ['Gagal masuk', 'bg-rose-100 text-rose-700 border-rose-200'],
        default => [\Illuminate\Support\Str::headline($log->aksi), 'bg-violet-100 text-violet-700 border-violet-200'],
    };

    $peranMeta = match ($log->peran) {
        'owner' => ['Owner', 'bg-amber-50 text-amber-700 border-amber-200'],
        'admin' => ['Admin', 'bg-sky-50 text-sky-700 border-sky-200'],
        'pelanggan' => ['Pelanggan', 'bg-gray-100 text-gray-600 border-gray-200'],
        default => ['Tamu', 'bg-slate-100 text-slate-500 border-slate-200'],
    };

    $waktu = $log->created_at ? $log->created_at->copy()->locale('id') : null;
    $inisial = strtoupper(mb_substr(trim((string) $log->pelaku) ?: '?', 0, 1));
    $rincian = is_array($log->perubahan) ? $log->perubahan : [];
@endphp
<tr data-log-id="{{ $log->id }}" data-tanggal="{{ $waktu?->isoFormat('dddd, D MMMM Y') }}" class="align-top transition-colors duration-1000 hover:bg-amber-50/30">
    <td class="w-10 px-4 py-4">
        <input type="checkbox" value="{{ $log->id }}" aria-label="Pilih log #{{ $log->id }}"
               class="cek-log h-4 w-4 cursor-pointer rounded border-gray-300 accent-amber-600">
    </td>
    <td class="whitespace-nowrap px-4 py-4">
        <p class="text-xs font-semibold text-gray-800">{{ $waktu?->translatedFormat('d M Y') }}</p>
        <p class="text-[11px] tabular-nums text-gray-400">{{ $waktu?->format('H:i:s') }}</p>
    </td>
    <td class="px-4 py-4">
        <div class="flex items-center gap-2.5">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-xs font-black text-amber-700">{{ $inisial }}</span>
            <div class="min-w-0">
                <p class="max-w-[10rem] truncate text-xs font-bold text-gray-900" title="{{ $log->pelaku }}">{{ $log->pelaku ?: '-' }}</p>
                <span class="mt-0.5 inline-flex items-center rounded-full border px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $peranMeta[1] }}">{{ $peranMeta[0] }}</span>
            </div>
        </div>
    </td>
    <td class="px-4 py-4">
        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $aksiMeta[1] }}">{{ $aksiMeta[0] }}</span>
    </td>
    <td class="whitespace-nowrap px-4 py-4 text-xs font-semibold text-gray-700">{{ $log->modul ?: '-' }}</td>
    <td class="px-4 py-4">
        <p class="max-w-md break-words text-xs text-gray-700">{{ $log->deskripsi }}</p>

        @if (count($rincian))
            <details class="mt-1.5">
                <summary class="cursor-pointer select-none text-[11px] font-bold text-amber-700 hover:text-amber-800">Lihat rincian ({{ count($rincian) }})</summary>
                <div class="mt-2 max-w-md space-y-1">
                    @foreach ($rincian as $p)
                        <div class="rounded-lg bg-gray-50 px-2.5 py-1.5 text-[11px] leading-relaxed">
                            <span class="font-bold text-gray-500">{{ $p['field'] ?? '-' }}</span>
                            @if (array_key_exists('lama', $p))
                                <span class="break-all text-rose-600 line-through">{{ $p['lama'] ?? '—' }}</span>
                                <span class="text-gray-400">&rarr;</span>
                                <span class="break-all font-medium text-emerald-700">{{ $p['baru'] ?? '—' }}</span>
                            @else
                                <span class="break-all text-gray-800">{{ $p['baru'] ?? '—' }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    </td>
    <td class="px-4 py-4">
        <p class="whitespace-nowrap text-xs tabular-nums text-gray-600" title="{{ $log->user_agent }}">{{ $log->ip_address ?: '-' }}</p>
        <p class="max-w-[10rem] truncate text-[10px] text-gray-400" title="{{ $log->method }} /{{ $log->url }}">{{ $log->method }} /{{ $log->url }}</p>
    </td>
</tr>