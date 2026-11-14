@extends('layouts.admin')

@section('title', 'Notifikasi')

@section('content')
@php
    // Warna ikon per "nada" (ditulis lengkap agar terbaca Tailwind)
    $nada = [
        'amber'   => 'bg-amber-50 text-amber-600',
        'sky'     => 'bg-sky-50 text-sky-600',
        'rose'    => 'bg-rose-50 text-rose-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'violet'  => 'bg-violet-50 text-violet-600',
    ];
    $ikon = \App\Support\NotifikasiAdmin::IKON;

    $tabs = [
        'semua'     => 'Semua',
        'belum'     => 'Belum dibaca',
        'pesanan'   => 'Pesanan & Sewa',
        'fitting'   => 'Fitting',
        'refund'    => 'Refund',
        'pelanggan' => 'Pelanggan',
    ];
@endphp

<div class="mx-auto max-w-4xl space-y-5">

    {{-- Aksi --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">
            Pesanan, pembayaran, fitting, refund, dan aktivitas pelanggan yang perlu Anda ketahui.
        </p>

        <div class="flex items-center gap-2">
            @if (($belum['semua'] ?? 0) > 0)
                <form method="POST" action="{{ route('admin.notifikasi.baca-semua') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                        Tandai semua dibaca
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('admin.notifikasi.hapus-dibaca') }}"
                  onsubmit="return confirm('Hapus semua notifikasi yang sudah dibaca?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                    Hapus yang sudah dibaca
                </button>
            </form>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $kunci => $label)
            @php $aktif = $tab === $kunci; @endphp
            <a href="{{ route('admin.notifikasi.index', $kunci === 'semua' ? [] : ['tab' => $kunci]) }}"
               class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-bold transition
                      {{ $aktif
                            ? 'border-amber-500 bg-amber-500 text-white shadow-sm shadow-amber-500/30'
                            : 'border-gray-200 bg-white text-gray-600 hover:border-amber-300 hover:text-amber-700' }}">
                {{ $label }}
                @if (($belum[$kunci] ?? 0) > 0)
                    <span class="rounded-full px-1.5 py-px text-[10px] leading-none {{ $aktif ? 'bg-white/25 text-white' : 'bg-red-100 text-red-600' }}">
                        {{ $belum[$kunci] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        @forelse ($items as $n)
            <a href="{{ $n['url'] }}"
               class="flex items-start gap-4 border-b border-gray-50 px-5 py-4 transition last:border-b-0 hover:bg-amber-50/50
                      {{ $n['dibaca'] ? 'bg-white' : 'bg-amber-50/30' }}">
                <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $nada[$n['nada']] ?? $nada['amber'] }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikon[$n['ikon']] ?? $ikon['bell'] }}"/>
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm text-gray-900 {{ $n['dibaca'] ? 'font-semibold' : 'font-bold' }}">{{ $n['judul'] }}</p>
                        @if ($n['prioritas'] === 'tinggi' && ! $n['dibaca'])
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-600">Perlu tindakan</span>
                        @endif
                        @if (! $n['dibaca'])
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        @endif
                    </div>
                    <p class="mt-0.5 break-words text-sm text-gray-500">{{ $n['pesan'] }}</p>
                    <p class="mt-1 text-[11px] font-medium text-gray-400" title="{{ $n['waktu_lengkap'] }}">{{ $n['waktu'] }}</p>
                </div>

                <svg class="mt-3 h-4 w-4 shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="font-semibold text-gray-700">Tidak ada notifikasi</p>
                <p class="mt-1 text-sm text-gray-400">
                    {{ $tab === 'belum' ? 'Semua notifikasi sudah dibaca.' : 'Notifikasi akan muncul di sini saat ada aktivitas baru.' }}
                </p>
            </div>
        @endforelse
    </div>

    @if ($items->hasPages())
        <div>{{ $items->links() }}</div>
    @endif
</div>
@endsection