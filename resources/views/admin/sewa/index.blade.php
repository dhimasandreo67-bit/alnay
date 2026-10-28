@extends('admin.layouts.admin')

@section('title', 'Jadwal Sewa Busana')

@section('pageTitle', 'Jadwal Sewa Busana')
@section('pageSubtitle', 'Pantau sirkulasi peminjaman gaun pengantin & jas beskap, status verifikasi jaminan.')

@section('content')
<div class="space-y-6">

    {{-- Kartu Ringkasan (Statistik) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-line shadow-sm">
            <p class="text-xs text-gray-500 font-semibold mb-1">TOTAL SEDANG DIPINJAM</p>
            <h3 class="text-3xl font-bold text-ink">{{ $totalDipinjam }} <span class="text-sm font-normal">Gaun</span></h3>
            <p class="text-xs text-emerald-600 mt-2">Aktif disewa saat ini</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-line shadow-sm">
            <p class="text-xs text-rose-500 font-semibold mb-1">JATUH TEMPO / TERLAMBAT</p>
            <h3 class="text-3xl font-bold text-rose-600">{{ $totalTerlambat }} <span class="text-sm font-normal">Pesanan Kritis</span></h3>
            <p class="text-xs text-rose-500 mt-2">Perlu follow-up WhatsApp segera</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-line shadow-sm">
            <p class="text-xs text-gray-500 font-semibold mb-1">SELESAI KEMBALI BULAN INI</p>
            <h3 class="text-3xl font-bold text-ink">{{ $totalSelesai }} <span class="text-sm font-normal">Gaun Lolos QC</span></h3>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-line shadow-sm">
            <p class="text-xs text-gray-500 font-semibold mb-1">JAMINAN TERTAHAN (DEPOSIT)</p>
            <h3 class="text-3xl font-bold text-ink">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</h3>
            <p class="text-xs text-gray-500 mt-2">{{ \App\Models\JaminanSewa::where('status_pengembalian_deposit', 'belum')->count() }} Jaminan aktif</p>
        </div>
    </div>

        {{-- Tab dan Filter --}}
    <div class="bg-white p-4 rounded-t-2xl border border-line shadow-sm flex items-center gap-2 overflow-x-auto">
        {{-- Tab Semua Sewa --}}
        <a href="{{ route('admin.sewa.index', ['status' => 'semua']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition flex items-center gap-2 {{ ($filter ?? 'semua') === 'semua' ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-50' }}">
            Semua Sewa 
            <span class="px-2 py-0.5 rounded-full text-xs {{ ($filter ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ \App\Models\Sewa::count() }}
            </span>
        </a>

        {{-- Tab Dipinjam --}}
        <a href="{{ route('admin.sewa.index', ['status' => 'dipinjam']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition flex items-center gap-2 {{ ($filter ?? '') === 'dipinjam' ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-50' }}">
            Dipinjam 
            <span class="px-2 py-0.5 rounded-full text-xs {{ ($filter ?? '') === 'dipinjam' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ $totalDipinjam }}
            </span>
        </a>

        {{-- Tab Terlambat --}}
        <a href="{{ route('admin.sewa.index', ['status' => 'terlambat']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition flex items-center gap-2 {{ ($filter ?? '') === 'terlambat' ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-50' }}">
            Terlambat 
            <span class="px-2 py-0.5 rounded-full text-xs {{ ($filter ?? '') === 'terlambat' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-600' }}">
                {{ $totalTerlambat }}
            </span>
        </a>

        {{-- Tab Dikembalikan --}}
        <a href="{{ route('admin.sewa.index', ['status' => 'dikembalikan']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition flex items-center gap-2 {{ ($filter ?? '') === 'dikembalikan' ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-50' }}">
            Dikembalikan 
            <span class="px-2 py-0.5 rounded-full text-xs {{ ($filter ?? '') === 'dikembalikan' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ $totalSelesai }}
            </span>
        </a>
    </div>

    {{-- Tabel Daftar Sewa --}}
    <div class="bg-white rounded-b-2xl border border-line shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b border-line text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-4">Kode Sewa & Busana Adat</th>
                    <th class="px-6 py-4">Pelanggan & Kontak</th>
                    <th class="px-6 py-4">Jadwal Pinjam & Durasi</th>
                    <th class="px-6 py-4">Status Sewa</th>
                    <th class="px-6 py-4">Status Verifikasi Jaminan</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($daftarSewa as $sewa)
                <tr class="hover:bg-cream/30 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-xs text-gray-400 mb-1">#SEWA-{{ $sewa->id }}</p>
                        <p class="font-bold text-ink">{{ $sewa->transaksi->detail->first()->produk->nama ?? 'Produk' }}</p>
                        <p class="text-xs text-gray-500">SKU: {{ $sewa->transaksi->detail->first()->produk->sku ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-ink">{{ $sewa->transaksi->user->name ?? 'Pelanggan' }}</p>
                        <p class="text-xs text-gray-500">{{ $sewa->transaksi->user->no_hp ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-medium">
                            {{ \Carbon\Carbon::parse($sewa->tanggal_pinjam)->format('d M') }} - 
                            {{ \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->format('d M Y') }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Durasi: {{ \Carbon\Carbon::parse($sewa->tanggal_pinjam)->diffInDays(\Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)) }} Hari
                        </p>
                    </td>
                    <td class="px-6 py-4">
                        @if($sewa->status === 'aktif' && \Carbon\Carbon::parse($sewa->tanggal_kembali_rencana)->isPast())
                            <span class="px-3 py-1 bg-rose-100 text-rose-600 rounded-md text-xs font-bold">TERLAMBAT</span>
                        @elseif($sewa->status === 'aktif')
                            <span class="px-3 py-1 bg-amber-100 text-amber-600 rounded-md text-xs font-bold">DIPINJAM</span>
                        @else
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-600 rounded-md text-xs font-bold">DIKEMBALIKAN</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($sewa->jaminan)
                            @if($sewa->jaminan->status_verifikasi === 'verified')
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md text-xs font-medium">Terverifikasi</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md text-xs font-medium">Pending</span>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.sewa.show', $sewa->id) }}" class="inline-flex items-center gap-1 bg-ink text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-gray-800 transition">
                            DETAIL &rarr;
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada data sewa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection