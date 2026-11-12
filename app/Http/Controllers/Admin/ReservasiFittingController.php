<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservasiFitting;
use Illuminate\Http\Request;

class ReservasiFittingController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query dasar: ambil reservasi beserta relasi user dan slot
        $query = ReservasiFitting::with(['user', 'slot'])->latest();

        // 2. Filter Pencarian (Kode Booking atau Nama User)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('kode_booking', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // 3. Filter Tanggal (Jika admin ingin melihat jadwal di tanggal tertentu)
        if ($request->filled('tanggal')) {
            $query->whereHas('slot', function($q) use ($request) {
                $q->where('tanggal', $request->tanggal);
            });
        }

        $reservasis = $query->paginate(15)->withQueryString();

        return view('admin.fitting.index', compact('reservasis'));
    }

    // Fungsi untuk mengubah status (Hadir / Batal)
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,hadir,batal']);
        
        $reservasi = ReservasiFitting::findOrFail($id);
        $reservasi->update(['status' => $request->status]);

        // Respon JSON untuk AJAX
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Status diperbarui.']);
        }

        return back()->with('success', 'Status reservasi fitting berhasil diperbarui.');
    }

    // Fungsi untuk menghapus reservasi (Sudah mendukung AJAX)
    public function destroy(Request $request, $id)
    {
        $reservasi = ReservasiFitting::findOrFail($id);
        
        // Proteksi: Cegah hapus data yang statusnya sudah HADIR
        if ($reservasi->status === 'hadir') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Reservasi HADIR tidak boleh dihapus.']);
            }
            return back()->with('error', 'Reservasi yang sudah HADIR tidak boleh dihapus.');
        }

        $reservasi->delete();

        // Respon JSON untuk AJAX
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Reservasi dihapus.']);
        }

        return back()->with('success', 'Reservasi fitting berhasil dihapus permanen.');
    }

        public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);
        }

        $deletedCount = ReservasiFitting::whereIn('id', $ids)
            ->where('status', '!=', 'hadir')
            ->delete();

        return response()->json([
            'success' => true, 
            'message' => $deletedCount . ' reservasi berhasil dihapus.'
        ]);
    }
}