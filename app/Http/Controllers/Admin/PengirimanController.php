<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use Illuminate\Http\Request;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengiriman::with(['transaksi.user', 'transaksi.detail.produk']);

        // Filter Status
        $filter = $request->get('status', 'semua');
        if ($filter === 'menunggu') {
            $query->where('status_kirim', 'menunggu');
        } elseif ($filter === 'dikirim') {
            $query->where('status_kirim', 'dikirim');
        } elseif ($filter === 'diterima') {
            $query->where('status_kirim', 'diterima');
        }

        // Filter Pencarian (Kode Transaksi atau Nama User)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('transaksi', function($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $pengirimanList = $query->latest()->paginate(10)->withQueryString();

        // Statistik
        $totalMenunggu = Pengiriman::where('status_kirim', 'menunggu')->count();
        $totalDikirim = Pengiriman::where('status_kirim', 'dikirim')->count();
        $totalDiterima = Pengiriman::where('status_kirim', 'diterima')->count();

        return view('admin.pengiriman.index', compact(
            'pengirimanList', 'filter', 'totalMenunggu', 'totalDikirim', 'totalDiterima'
        ));
    }

        public function updateStatus(Request $request, $id)
    {
        $pengiriman = Pengiriman::findOrFail($id);
        
        $data = ['status_kirim' => $request->status_kirim];
        
        // Jika status berubah menjadi 'dikirim', wajib isi kurir & resi
        if ($request->status_kirim === 'dikirim') {
            $request->validate([
                'kurir' => 'required|string|max:50',
                'layanan_ongkir' => 'required|string|max:30',
                'no_resi' => 'required|string|max:100',
            ]);
            $data['kurir'] = $request->kurir;
            $data['layanan_ongkir'] = $request->layanan_ongkir;
            $data['no_resi'] = $request->no_resi;
        }
                
        $pengiriman->update($data);

        return back()->with('success', 'Status pengiriman berhasil diperbarui!');
    }

        public function batalkanTerima($id)
    {
        $pengiriman = Pengiriman::findOrFail($id);
        
        if ($pengiriman->status_kirim === 'diterima') {
            $pengiriman->update(['status_kirim' => 'dikirim']);
            return back()->with('success', 'Konfirmasi penerimaan berhasil dibatalkan. Status kembali ke "Dikirim".');
        }

        return back()->with('error', 'Status pengiriman bukan "Diterima".');
    }

        /**
     * Update status pengiriman & resi dari halaman Detail Transaksi.
     */
        public function update(Request $request, \App\Models\Transaksi $transaksi)
    {
        $pengiriman = $transaksi->pengiriman;
        
        if (!$pengiriman) {
            return back()->with('error', 'Transaksi ini tidak memiliki data pengiriman.');
        }

        $isSewa = $transaksi->jenis === 'sewa';

        // Validasi: no_resi wajib HANYA jika transaksi Beli dan status dikirim/diterima
        $request->validate([
            'status_kirim' => 'required|in:menunggu,dikemas,dikirim,diterima',
            'no_resi' => [
                'nullable',
                'string',
                'max:60',
                // Wajib diisi jika ini BUKAN sewa (berarti beli), dan statusnya dikirim
                $isSewa ? 'nullable' : 'required_if:status_kirim,dikirim,diterima'
            ],
        ], [
            'no_resi.required_if' => 'Isi nomor resi sebelum menandai pesanan dikirim.',
        ]);

        // Validasi: Barang tidak boleh dikirim sebelum lunas
        $lunas = in_array(strtolower((string) $transaksi->status_pembayaran), ['lunas', 'settlement', 'success']);
        if (!$lunas && $request->status_kirim !== 'menunggu') {
            return back()->with('error', 'Pembayaran belum lunas. Pengiriman baru bisa diproses setelah pembayaran diterima.');
        }

        $data = [
            'status_kirim' => $request->status_kirim,
        ];

        // Hanya simpan no_resi jika BUKAN sewa
        if (!$isSewa) {
            $data['no_resi'] = $request->no_resi;
        }

        // Atur tanggal kirim & terima otomatis
        if (in_array($request->status_kirim, ['dikirim', 'diterima'])) {
            $data['tanggal_kirim'] = $pengiriman->tanggal_kirim ?? now();
        } else {
            $data['tanggal_kirim'] = null;
        }

        if ($request->status_kirim === 'diterima') {
            $data['tanggal_diterima'] = $pengiriman->tanggal_diterima ?? now();
        } else {
            $data['tanggal_diterima'] = null;
        }

        $pengiriman->update($data);

        return back()->with('success', 'Data pengiriman berhasil diperbarui!');
    }
}