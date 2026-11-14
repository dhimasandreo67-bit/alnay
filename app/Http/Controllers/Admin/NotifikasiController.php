<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\NotifikasiAdmin;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    private const TAB_KATEGORI = ['pesanan', 'fitting', 'refund', 'pelanggan'];

    /** Riwayat semua notifikasi + filter. */
    public function index(Request $request)
    {
        $user = $request->user();
        $tab  = $request->query('tab', 'semua');

        $query = $user->notifications();

        if ($tab === 'belum') {
            $query->whereNull('read_at');
        } elseif (in_array($tab, self::TAB_KATEGORI, true)) {
            $query->where('data->kategori', $tab);
        } else {
            $tab = 'semua';
        }

        $items = $query->latest()->paginate(20)->withQueryString();
        $items->getCollection()->transform(fn ($n) => NotifikasiAdmin::ringkas($n));

        // Jumlah belum dibaca per tab
        $belum = ['semua' => $user->unreadNotifications()->count()];
        foreach (self::TAB_KATEGORI as $k) {
            $belum[$k] = $user->unreadNotifications()->where('data->kategori', $k)->count();
        }

        return view('admin.notifikasi.index', compact('items', 'tab', 'belum'));
    }

    /** JSON untuk lonceng (dipanggil berkala oleh topbar). */
    public function feed(Request $request)
    {
        return response()->json(NotifikasiAdmin::ringkasan($request->user()));
    }

    /** Klik notifikasi: tandai dibaca lalu buka halaman tujuannya. */
    public function buka(Request $request, string $id)
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $n->markAsRead();

        $url = (string) ($n->data['url'] ?? '');
        // Hanya izinkan path internal (cegah open redirect)
        if (! str_starts_with($url, '/') || str_starts_with($url, '//')) {
            $url = route('admin.notifikasi.index', [], false);
        }

        return redirect($url);
    }

    public function bacaSemua(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['unread' => 0]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function hapusDibaca(Request $request)
    {
        $jumlah = $request->user()->notifications()->whereNotNull('read_at')->delete();

        return back()->with('success', $jumlah . ' notifikasi yang sudah dibaca dihapus.');
    }
}