<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Sewa;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TransaksiController extends Controller
{
    /**
     * CATATAN: kolom tabel `produk` sudah dicek dan dipastikan benar
     * (harga_jual, harga_sewa, dp, stok, bisa_disewa, dapat_dijual, diskon_persen).
     * Kolom DetailTransaksi (transaksi_id, produk_id, qty, harga, subtotal),
     * Sewa (transaksi_id, tanggal_pinjam, tanggal_kembali_rencana, status),
     * dan Pembayaran (transaksi_id, jenis_bayar, metode, jumlah, status_midtrans)
     * MASIH ASUMSI. Kalau nanti muncul error "Unknown column" lagi saat submit
     * form kasir, jalankan `php artisan db:table detail_transaksi` (atau nama
     * tabel aslinya) lalu kirim hasilnya biar disesuaikan lagi.
     */

    public function index(Request $request)
    {
        // 1. Ambil data dengan relasi
        $query = Transaksi::with(['user', 'sewa', 'detail.produk'])->latest();

        // 2. Filter Pencarian (Kode Transaksi atau Nama User)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('kode_transaksi', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhere('nama_tamu', 'like', '%' . $search . '%');
            });
        }

        // 3. Filter Status Pembayaran
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status_pembayaran', $request->status);
        }

        $transaksis = $query->paginate(10)->withQueryString();

        // 4. Hitung Statistik untuk Card
        $totalPendapatan = Transaksi::whereIn('status_pembayaran', ['dp', 'lunas', 'dp_dibayar'])->sum('total');
        $pesananBaru = Transaksi::where('status_pembayaran', 'pending')->count();
        $pesananDiproses = Transaksi::whereIn('status_pembayaran', ['dp', 'dp_dibayar'])->count();

        return view('admin.transaksi.index', compact('transaksis', 'totalPendapatan', 'pesananBaru', 'pesananDiproses'));
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['user', 'detail.produk', 'sewa', 'pembayaran', 'pengiriman'])
            ->findOrFail($id);

        return view('admin.transaksi.show', compact('transaksi'));
    }

    /**
     * Halaman kasir: form untuk membuat transaksi offline.
     */
    public function create()
    {
        $produks = Produk::select(
                'id', 'nama', 'harga_jual', 'harga_sewa', 'dp',
                'stok', 'bisa_disewa', 'dapat_dijual', 'diskon_persen'
            )
            ->where(function ($q) {
                $q->where('bisa_disewa', 1)->orWhere('dapat_dijual', 1);
            })
            ->orderBy('nama')
            ->get();

        $users = User::select('id', 'name', 'no_hp')
            ->orderBy('name')
            ->get();

        return view('admin.transaksi.create', compact('produks', 'users'));
    }

    /**
     * Simpan transaksi yang dibuat lewat kasir.
     * Transaksi kasir otomatis ditandai channel = 'kasir' dan muncul
     * di halaman Semua Transaksi seperti transaksi online lainnya.
     */
    public function store(Request $request)
    {
        $produkTable = (new Produk())->getTable();

        $validated = $request->validate([
            'jenis' => 'required|in:beli,sewa',
            'customer_type' => 'required|in:terdaftar,tamu',
            'user_id' => 'required_if:customer_type,terdaftar|nullable|exists:users,id',
            'nama_tamu' => 'required_if:customer_type,tamu|nullable|string|max:255',
            'no_hp_tamu' => 'required_if:customer_type,tamu|nullable|string|max:30',

            'items' => 'required|array|min:1',
            'items.*.produk_id' => ['required', Rule::exists($produkTable, 'id')],
            'items.*.qty' => 'required|integer|min:1',

            'tanggal_pinjam' => 'required_if:jenis,sewa|nullable|date',
            'tanggal_kembali_rencana' => 'required_if:jenis,sewa|nullable|date|after_or_equal:tanggal_pinjam',

            'status_pembayaran' => 'required|in:lunas,dp',
            'jumlah_dp' => 'required_if:status_pembayaran,dp|nullable|numeric|min:1',
            'metode_bayar' => 'required|in:tunai,qris,debit,transfer',
            'catatan' => 'nullable|string|max:255',
        ]);

        // Helper: hitung harga final 1 unit produk sesuai jenis transaksi (beli/sewa) + diskon
        $hargaUnit = function (Produk $produk, string $jenis): float {
            $hargaDasar = $jenis === 'sewa' ? $produk->harga_sewa : $produk->harga_jual;
            $hargaDasar = (float) ($hargaDasar ?? 0);
            $diskon = (float) ($produk->diskon_persen ?? 0);

            return $hargaDasar - ($hargaDasar * $diskon / 100);
        };

        $transaksi = DB::transaction(function () use ($validated, $hargaUnit) {
            $produkIds = collect($validated['items'])->pluck('produk_id');
            $produkMap = Produk::whereIn('id', $produkIds)->get()->keyBy('id');

            // Pastikan setiap produk memang boleh dijual/disewa sesuai jenis yang dipilih
            foreach ($produkMap as $produk) {
                if ($validated['jenis'] === 'sewa' && ! $produk->bisa_disewa) {
                    abort(422, "Produk \"{$produk->nama}\" tidak tersedia untuk disewa.");
                }
                if ($validated['jenis'] === 'beli' && ! $produk->dapat_dijual) {
                    abort(422, "Produk \"{$produk->nama}\" tidak tersedia untuk dijual.");
                }
            }

            $total = 0;
            foreach ($validated['items'] as $item) {
                $produk = $produkMap[$item['produk_id']];
                $total += $hargaUnit($produk, $validated['jenis']) * $item['qty'];
            }

            $transaksi = Transaksi::create([
                'kode_transaksi' => 'KSR-' . now()->format('ymd') . '-' . strtoupper(Str::random(4)),
                'tanggal' => now()->toDateString(),
                'user_id' => $validated['customer_type'] === 'terdaftar' ? $validated['user_id'] : null,
                'nama_tamu' => $validated['customer_type'] === 'tamu' ? $validated['nama_tamu'] : null,
                'no_hp_tamu' => $validated['customer_type'] === 'tamu' ? $validated['no_hp_tamu'] : null,
                'jenis' => $validated['jenis'],
                'total' => $total,
                // Transaksi kasir langsung dibayar di tempat, jadi status dasarnya
                // 'lunas' atau 'dp_dibayar' (bukan 'pending' seperti pesanan online).
                'status_pembayaran' => $validated['status_pembayaran'] === 'lunas' ? 'lunas' : 'dp_dibayar',
                'channel' => 'kasir',
            ]);

            foreach ($validated['items'] as $item) {
                $produk = $produkMap[$item['produk_id']];
                $harga = $hargaUnit($produk, $validated['jenis']);

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['produk_id'],
                    'qty' => $item['qty'],
                    'harga' => $harga,
                    'subtotal' => $harga * $item['qty'],
                ]);

                // Kurangi stok karena transaksi kasir langsung terjadi di tempat
                $produk->decrement('stok', $item['qty']);
            }

            if ($validated['jenis'] === 'sewa') {
                Sewa::create([
                    'transaksi_id' => $transaksi->id,
                    'tanggal_pinjam' => $validated['tanggal_pinjam'],
                    'tanggal_kembali_rencana' => $validated['tanggal_kembali_rencana'],
                    'status' => 'aktif',
                ]);
            }

            Pembayaran::create([
                'transaksi_id' => $transaksi->id,
                'jenis_bayar' => $validated['status_pembayaran'],
                'metode' => $validated['metode_bayar'],
                'jumlah' => $validated['status_pembayaran'] === 'lunas' ? $total : $validated['jumlah_dp'],
                'status_midtrans' => 'success',
            ]);

            return $transaksi;
        });

        return redirect()
            ->route('admin.transaksi.show', $transaksi->id)
            ->with('success', 'Transaksi kasir berhasil dibuat: ' . $transaksi->kode_transaksi);
    }

    // Fungsi untuk melunasi transaksi secara manual (oleh Admin)
    public function lunasi($id)
    {
        $transaksi = Transaksi::findOrFail($id);

        // Ubah status transaksi
        $transaksi->update(['status_pembayaran' => 'lunas']);

        // Ubah status di tabel pembayaran (jika ada tagihan DP/Pending)
        $transaksi->pembayaran()->update(['status_midtrans' => 'success']);

        return redirect()->back()->with('success', 'Status pembayaran berhasil diubah menjadi LUNAS.');
    }

    // Fungsi untuk menyelesaikan masa sewa (baju sudah dikembalikan)
    public function selesaikanSewa($id)
    {
        $transaksi = Transaksi::with('sewa')->findOrFail($id);

        if ($transaksi->sewa) {
            $transaksi->sewa->update([
                'status' => 'selesai',
                'tanggal_kembali_aktual' => now()->toDateString(),
            ]);
        }

        return redirect()->back()->with('success', 'Masa sewa telah berhasil diselesaikan.');
    }
}