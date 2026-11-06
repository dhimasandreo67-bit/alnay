{{--
    Form alamat pengiriman untuk modal Tambah / Edit pengguna.
    Isinya sama dengan form alamat di Checkout & "Alamat Saya" pelanggan (data wilayah dan
    pencarian kelurahan dari RajaOngkir), jadi alamat yang diinput admin langsung bisa dipakai
    pelanggan untuk menghitung ongkir.

    Parameter: $p = awalan id ('tp' untuk tambah, 'ep' untuk edit), $catatan = teks petunjuk.
--}}
@php
    $kelas = 'mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm';
    $kelasSelect = $kelas . ' disabled:opacity-50 disabled:cursor-not-allowed';
@endphp

<div class="space-y-4 rounded-2xl border border-gray-100 bg-gray-50/60 p-4">
    <div>
        <p class="text-xs font-bold text-gray-700">Alamat Pengiriman</p>
        <p id="{{ $p }}-catatan" class="mt-0.5 text-[11px] leading-relaxed text-gray-400">{{ $catatan }}</p>
    </div>

    <div>
        <p class="text-xs font-bold text-gray-500">Tandai sebagai</p>
        <div class="mt-1.5 flex gap-3">
            @foreach (['Rumah', 'Kantor'] as $lbl)
                <label class="cursor-pointer">
                    <input type="radio" name="label" value="{{ $lbl }}" class="peer sr-only" {{ $lbl === 'Rumah' ? 'checked' : '' }}>
                    <span class="inline-block rounded-lg border border-gray-200 bg-white px-4 py-1.5 text-xs font-semibold text-gray-600 transition peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700">{{ $lbl }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label for="{{ $p }}-alamat" class="text-xs font-bold text-gray-500">Alamat lengkap</label>
        <textarea id="{{ $p }}-alamat" name="alamat" rows="2" maxlength="500" placeholder="Nama jalan, nomor rumah, RT/RW, patokan" class="{{ $kelas }} resize-none"></textarea>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label for="{{ $p }}-provinsi" class="text-xs font-bold text-gray-500">Provinsi</label>
            <select id="{{ $p }}-provinsi" name="provinsi" class="{{ $kelasSelect }}">
                <option value="">Pilih Provinsi</option>
            </select>
        </div>
        <div>
            <label for="{{ $p }}-kota" class="text-xs font-bold text-gray-500">Kota/Kabupaten</label>
            <select id="{{ $p }}-kota" name="kota_kabupaten" disabled class="{{ $kelasSelect }}">
                <option value="">Pilih Kota/Kabupaten</option>
            </select>
        </div>
        <div>
            <label for="{{ $p }}-kecamatan" class="text-xs font-bold text-gray-500">Kecamatan</label>
            <select id="{{ $p }}-kecamatan" name="kecamatan" disabled class="{{ $kelasSelect }}">
                <option value="">Pilih Kecamatan</option>
            </select>
        </div>
    </div>

    <div class="relative">
        <label for="{{ $p }}-tujuan-q" class="text-xs font-bold text-gray-500">Kelurahan tujuan (untuk ongkir)</label>
        <input type="text" id="{{ $p }}-tujuan-q" autocomplete="off" placeholder="Ketik minimal 3 huruf, contoh: kemiling" class="{{ $kelas }}">
        <div id="{{ $p }}-tujuan-hasil" class="absolute left-0 right-0 z-30 mt-1 hidden max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg"></div>
        <p id="{{ $p }}-tujuan-info" class="mt-1 text-[11px] text-gray-400"></p>
    </div>

    <div>
        <label for="{{ $p }}-kodepos" class="text-xs font-bold text-gray-500">Kode pos</label>
        <input type="text" id="{{ $p }}-kodepos" name="kode_pos" maxlength="10" class="{{ $kelas }}">
    </div>

    <input type="hidden" name="tujuan_id" id="{{ $p }}-tujuan-id" value="">
    <input type="hidden" name="tujuan_label" id="{{ $p }}-tujuan-label" value="">
</div>