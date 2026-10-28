<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Services\MidtransQris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QrisController extends Controller
{
    public function __construct(private MidtransQris $qris)
    {
    }

    private function milikSaya($id): Transaksi
    {
        return Transaksi::with('pembayaran')
            ->where('user_id', Auth::id())
            ->findOrFail($id);
    }

    /** POST — ambil QR aktif (atau buat baru bila diminta / sudah kedaluwarsa). */
    public function charge(Request $request, $id)
    {
        $transaksi = $this->milikSaya($id);

        if ($this->qris->sudahLunas($transaksi)) {
            return response()->json(['paid' => true]);
        }

        try {
            return response()->json($this->qris->qr($transaksi, $request->boolean('baru')));
        } catch (\Throwable $e) {
            Log::error('QRIS charge gagal', ['transaksi' => $transaksi->id, 'pesan' => $e->getMessage()]);

            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    /** GET — dipanggil berkala oleh halaman untuk mengetahui sudah bayar atau belum. */
    public function status($id)
    {
        $transaksi = $this->milikSaya($id);

        return response()->json(['status' => $this->qris->sync($transaksi)]);
    }
}
