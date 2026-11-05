<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengiriman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AlamatController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label' => ['nullable', 'string', 'max:50'],
            'nama_penerima' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota_kabupaten' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'alamat' => ['required', 'string', 'max:500'],
            'ongkir_tujuan_id' => ['required', 'integer'],
            'ongkir_tujuan_label' => ['required', 'string', 'max:255'],
            'is_utama' => ['nullable', 'boolean'],
        ], [
            'ongkir_tujuan_id.required' => 'Pilih kecamatan atau kelurahan dari hasil pencarian.',
            'ongkir_tujuan_label.required' => 'Pilih kecamatan atau kelurahan dari hasil pencarian.',
        ]);

        if ($validator->fails()) {
            return $this->kembali()->withErrors($validator, 'alamat')->withInput();
        }

        $data = $validator->validated();
        $data['label'] = $data['label'] ?? 'Rumah';
        $userId = auth()->id();

        DB::transaction(function () use ($data, $userId) {
            $sudahAdaAlamat = AlamatPengiriman::where('user_id', $userId)->exists();

            // Alamat pertama otomatis jadi utama
            $jadiUtama = $sudahAdaAlamat ? (bool) ($data['is_utama'] ?? false) : true;

            if ($jadiUtama) {
                AlamatPengiriman::where('user_id', $userId)->update(['is_utama' => false]);
            }

            AlamatPengiriman::create(array_merge($data, [
                'user_id' => $userId,
                'is_utama' => $jadiUtama,
            ]));
        });

        return $this->kembali()->with('success_alamat', 'Alamat berhasil ditambahkan.');
    }

    public function jadikanUtama($id)
    {
        $alamat = $this->milikSaya($id);

        DB::transaction(function () use ($alamat) {
            AlamatPengiriman::where('user_id', $alamat->user_id)->update(['is_utama' => false]);
            $alamat->update(['is_utama' => true]);
        });

        return $this->kembali()->with('success_alamat', 'Alamat utama diperbarui.');
    }

    public function destroy($id)
    {
        $alamat = $this->milikSaya($id);
        $userId = $alamat->user_id;
        $wasUtama = $alamat->is_utama;

        $alamat->delete();

        // Kalau alamat utama dihapus, pilih alamat terbaru sebagai pengganti
        if ($wasUtama) {
            AlamatPengiriman::where('user_id', $userId)->latest()->first()?->update(['is_utama' => true]);
        }

        return $this->kembali()->with('success_alamat', 'Alamat dihapus.');
    }

    private function milikSaya($id): AlamatPengiriman
    {
        return AlamatPengiriman::where('user_id', auth()->id())->findOrFail($id);
    }

    private function kembali()
    {
        return redirect(route('akun.dashboard') . '#alamat');
    }
}