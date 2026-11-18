<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\NotifikasiPelanggan;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Lonceng notifikasi PELANGGAN di header.
 * (Notifikasi admin ada di Admin\NotifikasiController dan tidak disentuh.)
 */
class NotifikasiController extends Controller
{
    /** Buka notifikasi: tandai sudah dibaca, lalu arahkan ke detail pesanan. */
    public function buka(int $id): RedirectResponse
    {
        $transaksi = $this->milik($id);

        NotifikasiPelanggan::tandaiDibaca(auth()->id(), $transaksi->id);

        return redirect()->route('akun.pesanan.detail', $transaksi->id);
    }

    /** Tandai semua notifikasi yang tampil sebagai sudah dibaca. */
    public function bacaSemua(): JsonResponse
    {
        $ids = NotifikasiPelanggan::dasar(auth()->id())->pluck('transaksi.id')->all();

        NotifikasiPelanggan::tandaiDibaca(auth()->id(), $ids);

        return $this->ringkas();
    }

    /** Hapus satu notifikasi dari lonceng. */
    public function hapus(int $id): JsonResponse
    {
        $transaksi = $this->milik($id);

        NotifikasiPelanggan::sembunyikan(auth()->id(), $transaksi->id);

        return $this->ringkas();
    }

    /** Hapus semua notifikasi dari lonceng. */
    public function hapusSemua(): JsonResponse
    {
        $ids = NotifikasiPelanggan::dasar(auth()->id())->pluck('transaksi.id')->all();

        NotifikasiPelanggan::sembunyikan(auth()->id(), $ids);

        return $this->ringkas();
    }

    /** Hanya transaksi milik pelanggan yang sedang login. */
    private function milik(int $id): Transaksi
    {
        return Transaksi::where('user_id', auth()->id())->findOrFail($id);
    }

    private function ringkas(): JsonResponse
    {
        $r = NotifikasiPelanggan::ringkasan(auth()->id());

        return response()->json([
            'unread' => $r['belum_dibaca'],
            'total'  => $r['total'],
        ]);
    }
}