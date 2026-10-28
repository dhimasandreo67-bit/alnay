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
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Kategori;

class TransaksiController extends Controller
{
    /**
     * CATATAN: semua skema tabel di bawah ini sudah dikonfirmasi lewat
     * `php artisan db:table ...`, bukan tebakan lagi:
     * - transaksi: kode_transaksi, tanggal, user_id, jenis, total,
     *   status_pembayaran, channel, nama_tamu, no_hp_tamu, catatan
     * - detail_transaksi: transaksi_id, produk_id, jumlah, harga_satuan,
     *   ukuran, warna (dua terakhir dari migration add_detail_kasir_columns)
     *   (TIDAK ADA kolom subtotal)
     * - sewa: transaksi_id, tanggal_pinjam, tanggal_kembali_rencana, status
     * - pembayaran: transaksi_id, jenis_bayar, jumlah, metode,
     *   status_midtrans, tanggal_bayar
     *
     * Dipakai forceFill() (bukan create()) di semua insert supaya kolom
     * channel/nama_tamu/no_hp_tamu tetap tersimpan meskipun belum ada di
     * $fillable model Transaksi.
     */

    public function index(Request $request)
    {
        $tab = in_array($request->input('tab'), ['beli', 'sewa'], true) ? $request->input('tab') : 'semua';
        $channel = in_array($request->input('channel'), ['online', 'kasir'], true) ? $request->input('channel') : '';

        // 1. Query dasar: pencarian + status + channel (belum termasuk tab,
        //    supaya angka di tiap tab tetap sesuai filter lainnya)
        $filtered = Transaksi::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $filtered->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhere('nama_tamu', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $filtered->where('status_pembayaran', $request->status);
        }

        // Transaksi online lama bisa saja channel-nya NULL, jadi dianggap online
        if ($channel === 'kasir') {
            $filtered->where('channel', 'kasir');
        } elseif ($channel === 'online') {
            $filtered->where(function ($q) {
                $q->whereNull('channel')->orWhere('channel', 'online');
            });
        }

        // 2. Jumlah per tab
        $tabCounts = [
            'semua' => (clone $filtered)->count(),
            'beli'  => (clone $filtered)->where('jenis', 'beli')->count(),
            'sewa'  => (clone $filtered)->where('jenis', 'sewa')->count(),
        ];

        // 3. Data tabel (1 transaksi = 1 baris, item ditampilkan lewat expand)
        $query = (clone $filtered)->with(['user', 'sewa', 'detail.produk'])->latest();
        if ($tab !== 'semua') {
            $query->where('jenis', $tab);
        }

        $transaksis = $query->paginate(10)->withQueryString();

        // 4. Statistik untuk card (tidak terpengaruh filter)
        $totalPendapatan = Transaksi::whereIn('status_pembayaran', ['dp', 'lunas', 'dp_dibayar'])->sum('total');
        $pesananBaru = Transaksi::where('status_pembayaran', 'pending')->count();
        $pesananDiproses = Transaksi::whereIn('status_pembayaran', ['dp', 'dp_dibayar'])->count();

        return view('admin.transaksi.index', compact(
            'transaksis', 'totalPendapatan', 'pesananBaru', 'pesananDiproses',
            'tab', 'channel', 'tabCounts'
        ));
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
        $produks = Produk::with('kategori:id,nama_kategori')
            ->select(
                'id', 'kategori_id', 'sku', 'nama', 'foto_produk', 'bahan', 'motif',
                'warna', 'ukuran', 'berat', 'harga_jual', 'harga_sewa', 'dp',
                'stok', 'status', 'bisa_disewa', 'dapat_dijual', 'diskon_persen',
                'denda_per_hari_telat'
            )
            ->where(function ($q) {
                $q->where('bisa_disewa', 1)->orWhere('dapat_dijual', 1);
            })
            ->orderBy('nama')
            ->get();

        $kategoris = Kategori::where('is_active', true)
            ->orderBy('nama_kategori')
            ->get(['id', 'nama_kategori']);

        // Hanya akun pelanggan yang boleh dipilih di kasir; staf (owner/admin)
        // dikecualikan lewat scope User::pelanggan() (sama seperti yang dipakai
        // halaman Kelola Pengguna).
        $users = User::pelanggan()
            ->select('id', 'name', 'no_hp')
            ->orderBy('name')
            ->get();

        return view('admin.transaksi.create', compact('produks', 'kategoris', 'users'));
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
            'items.*.ukuran' => 'nullable|string|max:20',
            'items.*.warna' => 'nullable|string|max:100',
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

        $gagal = fn (string $pesan) => throw ValidationException::withMessages(['items' => $pesan]);

        $transaksi = DB::transaction(function () use ($validated, $hargaUnit, $gagal) {
            $produkIds = collect($validated['items'])->pluck('produk_id')->unique();

            // lockForUpdate: dua kasir tidak bisa menjual stok yang sama bersamaan
            $produkMap = Produk::whereIn('id', $produkIds)->lockForUpdate()->get()->keyBy('id');

            // Pastikan setiap produk memang boleh dijual/disewa sesuai jenis yang dipilih
            foreach ($produkMap as $produk) {
                if ($validated['jenis'] === 'sewa' && ! $produk->bisa_disewa) {
                    $gagal("Produk \"{$produk->nama}\" tidak tersedia untuk disewa.");
                }
                if ($validated['jenis'] === 'beli' && ! $produk->dapat_dijual) {
                    $gagal("Produk \"{$produk->nama}\" tidak tersedia untuk dijual.");
                }
            }

            // Validasi ukuran/warna per item + kumpulkan kebutuhan stok per produk & ukuran
            $kebutuhan = []; // [produk_id => ['total' => n, 'ukuran' => ['M' => n]]]
            foreach ($validated['items'] as $item) {
                $produk = $produkMap[$item['produk_id']];
                $varian = collect($produk->ukuran ?? [])->values();
                $ukuran = $item['ukuran'] ?? null;
                $warna = $item['warna'] ?? null;

                if ($varian->isNotEmpty()) {
                    if (! $ukuran) {
                        $gagal("Pilih ukuran untuk produk \"{$produk->nama}\".");
                    }
                    if (! $varian->contains(fn ($v) => ($v['ukuran'] ?? null) === $ukuran)) {
                        $gagal("Ukuran {$ukuran} tidak ada pada produk \"{$produk->nama}\".");
                    }
                    $kebutuhan[$produk->id]['ukuran'][$ukuran] = ($kebutuhan[$produk->id]['ukuran'][$ukuran] ?? 0) + $item['qty'];
                }

                $daftarWarna = (array) ($produk->warna ?? []);
                if ($warna && ! empty($daftarWarna) && ! in_array($warna, $daftarWarna, true)) {
                    $gagal("Warna {$warna} tidak tersedia pada produk \"{$produk->nama}\".");
                }

                $kebutuhan[$produk->id]['total'] = ($kebutuhan[$produk->id]['total'] ?? 0) + $item['qty'];
            }

            // Cek stok (total, dan per ukuran bila produk punya variasi)
            foreach ($kebutuhan as $produkId => $k) {
                $produk = $produkMap[$produkId];
                $varian = collect($produk->ukuran ?? [])->keyBy('ukuran');

                foreach ($k['ukuran'] ?? [] as $uk => $qty) {
                    $stokUkuran = (int) ($varian[$uk]['stok'] ?? 0);
                    if ($qty > $stokUkuran) {
                        $gagal("Stok \"{$produk->nama}\" ukuran {$uk} tinggal {$stokUkuran}, diminta {$qty}.");
                    }
                }
                if ($k['total'] > (int) $produk->stok) {
                    $gagal("Stok \"{$produk->nama}\" tinggal {$produk->stok}, diminta {$k['total']}.");
                }
            }

            $total = 0;
            foreach ($validated['items'] as $item) {
                $produk = $produkMap[$item['produk_id']];
                $total += $hargaUnit($produk, $validated['jenis']) * $item['qty'];
            }

            if ($validated['status_pembayaran'] === 'dp' && (float) $validated['jumlah_dp'] >= $total) {
                throw ValidationException::withMessages([
                    'jumlah_dp' => 'Jumlah DP harus lebih kecil dari total. Pilih status Lunas jika dibayar penuh.',
                ]);
            }

            // Pakai forceFill() (bukan create()) supaya kolom channel/nama_tamu/no_hp_tamu/catatan
            // tetap tersimpan walaupun belum ditambahkan ke $fillable di model Transaksi.
            $transaksi = new Transaksi();
            $transaksi->forceFill([
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
                'catatan' => $validated['catatan'] ?? null,
            ])->save();

            foreach ($validated['items'] as $item) {
                $produk = $produkMap[$item['produk_id']];

                // Kolom tabel detail_transaksi: transaksi_id, produk_id, jumlah,
                // harga_satuan, ukuran, warna (tidak ada kolom subtotal).
                (new DetailTransaksi())->forceFill([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah' => $item['qty'],
                    'harga_satuan' => $hargaUnit($produk, $validated['jenis']),
                    'ukuran' => $item['ukuran'] ?: null,
                    'warna' => $item['warna'] ?: null,
                ])->save();
            }

            // Kurangi stok sekali per produk (total + stok per variasi ukuran)
            foreach ($kebutuhan as $produkId => $k) {
                $produk = $produkMap[$produkId];

                if (! empty($k['ukuran'])) {
                    $varian = collect($produk->ukuran)->map(function ($v) use ($k) {
                        $v['stok'] = (int) $v['stok'] - (int) ($k['ukuran'][$v['ukuran']] ?? 0);
                        return $v;
                    })->values()->all();

                    $produk->ukuran = $varian;
                    $produk->stok = collect($varian)->sum('stok');
                } else {
                    $produk->stok = (int) $produk->stok - $k['total'];
                }

                $produk->save();
            }

            if ($validated['jenis'] === 'sewa') {
                (new Sewa())->forceFill([
                    'transaksi_id' => $transaksi->id,
                    'tanggal_pinjam' => $validated['tanggal_pinjam'],
                    'tanggal_kembali_rencana' => $validated['tanggal_kembali_rencana'],
                    'status' => 'aktif',
                ])->save();
            }

            (new Pembayaran())->forceFill([
                'transaksi_id' => $transaksi->id,
                'jenis_bayar' => $validated['status_pembayaran'],
                'metode' => $validated['metode_bayar'],
                'jumlah' => $validated['status_pembayaran'] === 'lunas' ? $total : $validated['jumlah_dp'],
                'status_midtrans' => 'success',
                'tanggal_bayar' => now(),
            ])->save();

            return $transaksi;
        });

        return redirect()
            ->route('admin.transaksi.show', $transaksi->id)
            ->with('success', 'Transaksi kasir berhasil dibuat: ' . $transaksi->kode_transaksi);
    }

    /**
     * Hapus transaksi beserta item, pembayaran, pengiriman, dan data sewanya.
     * Opsional: kembalikan stok produk (checkbox "restock" di modal konfirmasi).
     */
    public function destroy(Request $request, $id)
    {
        $transaksi = Transaksi::with(['detail', 'sewa'])->findOrFail($id);
        $kode = $transaksi->kode_transaksi;
        $kembalikanStok = $request->boolean('restock');

        try {
            DB::transaction(function () use ($transaksi, $kembalikanStok) {
                if ($kembalikanStok) {
                    $this->kembalikanStok($transaksi);
                }

                $transaksi->pembayaran()->delete();
                $transaksi->pengiriman()->delete();
                $transaksi->sewa()->delete();
                $transaksi->detail()->delete();
                $transaksi->delete();
            });
        } catch (QueryException $e) {
            // Biasanya karena masih ada data lain yang menunjuk ke transaksi ini
            // (refund, jaminan sewa, pengembalian, denda, dll) dan tidak di-cascade.
            report($e);

            return back()->with('error', "Transaksi {$kode} tidak bisa dihapus karena masih terhubung dengan data lain (mis. refund, jaminan, atau pengembalian).");
        }

        return back()->with('success', "Transaksi {$kode} berhasil dihapus." . ($kembalikanStok ? ' Stok produk sudah dikembalikan.' : ''));
    }

    /**
     * Tambahkan lagi jumlah item transaksi ke stok produk (total + per variasi ukuran).
     */
    protected function kembalikanStok(Transaksi $transaksi): void
    {
        $produkMap = Produk::whereIn('id', $transaksi->detail->pluck('produk_id')->filter()->unique())
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($transaksi->detail as $d) {
            $produk = $produkMap[$d->produk_id] ?? null;
            if (! $produk) {
                continue; // produk sudah dihapus
            }

            $varian = collect($produk->ukuran ?? [])->values();
            $punyaVarian = $d->ukuran && $varian->contains(fn ($v) => ($v['ukuran'] ?? null) === $d->ukuran);

            if ($punyaVarian) {
                $varian = $varian->map(function ($v) use ($d) {
                    if (($v['ukuran'] ?? null) === $d->ukuran) {
                        $v['stok'] = (int) $v['stok'] + (int) $d->jumlah;
                    }
                    return $v;
                })->values();

                $produk->ukuran = $varian->all();
                $produk->stok = (int) $varian->sum('stok');
            } else {
                $produk->stok = (int) $produk->stok + (int) $d->jumlah;
            }
        }

        foreach ($produkMap as $produk) {
            $produk->save();
        }
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