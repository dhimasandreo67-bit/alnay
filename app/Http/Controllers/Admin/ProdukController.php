<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Services\ImageOptimizer;
use App\Support\XlsxSederhana;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    /**
     * Tampilkan daftar produk (Kelola Produk).
     */
    public function index(Request $request)
    {
        $query = $this->terapkanFilter(Produk::with('kategori'), $request);

        $produkList = $query->orderByDesc('id')->get();

        return view('admin.produk.index', [
            'produkList'      => $produkList,
            'categories'      => Kategori::where('is_active', true)->orderBy('nama_kategori')->get(),
            'opsiStatus'      => Produk::opsiStatus(),
            'totalKoleksi'    => Produk::count(),
            'totalKategori'   => Kategori::where('is_active', true)->count(),
            'siapCount'       => Produk::where('status', 'tersedia')->count(),
            'disewaCount'     => Produk::where('status', 'disewa')->count(),
            'perawatanCount'  => Produk::whereIn('status', ['dicuci', 'rusak'])->count(),
            'filters'         => $request->only(['q', 'kategori_id', 'status', 'jenis']),
        ]);
    }

    /**
     * Tampilkan form tambah produk baru.
     */
    public function create()
    {
        $categories = Kategori::where('is_active', true)->orderBy('nama_kategori')->get();

        return view('admin.produk.create', [
            'categories'   => $categories,
            'opsiStatus'   => Produk::opsiStatus(),
            'opsiUkuran'   => Produk::opsiUkuran(),
            'paletWarna'   => Produk::paletWarna(),
        ]);
    }

    /**
     * Tampilkan form edit produk.
     */
    public function edit($id)
    {
        $produk = Produk::findOrFail($id);

        return view('admin.produk.edit', [
            'produk'       => $produk,
            'categories'   => Kategori::where('is_active', true)->orderBy('nama_kategori')->get(),
            'opsiStatus'   => Produk::opsiStatus(),
            'opsiUkuran'   => Produk::opsiUkuran(),
            'paletWarna'   => Produk::paletWarna(),
        ]);
    }

    /**
     * Simpan produk baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'                   => 'nullable|string|max:50|unique:produk,sku',
            'nama'                  => 'required|string|max:255',
            'deskripsi'             => 'nullable|string',
            'kategori_id'           => 'required|exists:kategori,id',
            'bahan'                 => 'nullable|string|max:255',
            'motif'                 => 'nullable|string|max:255',
            'berat'                 => 'nullable|numeric|min:0',
            'warna'                 => 'nullable|array',
            'warna.*'               => 'string|max:100',
            'ukuran'                => 'nullable|array',
            'ukuran.*.ukuran'       => 'required_with:ukuran|string|max:20',
            'ukuran.*.stok'         => 'required_with:ukuran|integer|min:0',
            'ukuran.*.warna'        => 'nullable|array',
            'ukuran.*.warna.*.nama' => 'required|string|max:100',
            'ukuran.*.warna.*.stok' => 'required|integer|min:0',
            'bisa_disewa'           => 'nullable|boolean',
            'dapat_dijual'          => 'nullable|boolean',
            'status'                => 'required|in:' . implode(',', array_keys(Produk::opsiStatus())),
            'harga_sewa'            => 'nullable|numeric|min:0',
            'dp'                    => 'nullable|numeric|min:0',
            'harga_jual'            => 'nullable|numeric|min:0',
            'stok'                  => 'nullable|integer|min:0',
            'denda_per_hari_telat'  => 'nullable|numeric|min:0',
            'diskon_persen'         => 'nullable|numeric|min:0|max:100',
            'foto'                  => 'nullable|array|max:4',
            'foto.*'                => 'nullable|image|max:5120', // 5MB
        ], [
            'ukuran.*.warna.*.nama.required' => 'Pilih nama warna pada setiap baris rincian warna.',
            'ukuran.*.warna.*.stok.required' => 'Isi stok pada setiap baris rincian warna.',
        ]);

        $bisaDisewa  = $request->boolean('bisa_disewa');
        $dapatDijual = $request->boolean('dapat_dijual');

        if (!$bisaDisewa && !$dapatDijual) {
            return back()
                ->withInput()
                ->withErrors(['jenis_transaksi' => 'Pilih minimal satu jenis transaksi: Bisa disewa atau Bisa dijual.']);
        }

        $ukuranVariasi = $this->rapikanUkuran($validated['ukuran'] ?? []);

        if ($ukuranGanda = $this->ukuranGanda($ukuranVariasi)) {
            return back()
                ->withInput()
                ->withErrors(['ukuran' => "Ukuran {$ukuranGanda} dipilih lebih dari sekali. Gabungkan jadi satu baris."]);
        }

        if (empty($ukuranVariasi) && !$request->filled('stok')) {
            return back()
                ->withInput()
                ->withErrors(['stok' => 'Isi stok, atau tambahkan minimal satu variasi ukuran dengan stoknya.']);
        }

        // Kalau ada variasi ukuran, total stok dihitung otomatis dari jumlah tiap variasi.
        $totalStok = !empty($ukuranVariasi)
            ? collect($ukuranVariasi)->sum(fn ($v) => (int) $v['stok'])
            : (int) $validated['stok'];

        $kategori = Kategori::findOrFail($validated['kategori_id']);

        // Upload foto (4 slot berurutan: 0=sampul, 1=slot2, 2=slot3, 3=detail)
        try {
            $fotoPaths = $this->uploadFotoSlots($request);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
        }

        Produk::create([
            'kategori_id'          => $kategori->id,
            'sku'                  => $validated['sku'] ?: $this->generateSku($kategori),
            'nama'                 => $validated['nama'],
            'deskripsi'            => $validated['deskripsi'] ?? null,
            'foto_produk'          => $fotoPaths,
            'bahan'                => $validated['bahan'] ?? null,
            'motif'                => $validated['motif'] ?? null,
            'warna'                => $validated['warna'] ?? [],
            'berat'                => $validated['berat'] ?? null,
            'ukuran'               => $ukuranVariasi,
            'harga_jual'           => $dapatDijual ? ($validated['harga_jual'] ?? null) : null,
            'harga_sewa'           => $bisaDisewa ? ($validated['harga_sewa'] ?? null) : null,
            'dp'                   => $bisaDisewa ? ($validated['dp'] ?? 0) : 0,
            'stok'                 => $totalStok,
            'status'               => $validated['status'],
            'dapat_dijual'         => $dapatDijual,
            'bisa_disewa'          => $bisaDisewa,
            'denda_per_hari_telat' => $validated['denda_per_hari_telat'] ?? 0,
            'diskon_persen'        => $validated['diskon_persen'] ?? 0,
            'rating_rata_rata'     => 0,
            'jumlah_rating'        => 0,
        ]);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Update produk yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validated = $request->validate([
            'sku'                   => 'nullable|string|max:50|unique:produk,sku,' . $produk->id,
            'nama'                  => 'required|string|max:255',
            'deskripsi'             => 'nullable|string',
            'kategori_id'           => 'required|exists:kategori,id',
            'bahan'                 => 'nullable|string|max:255',
            'motif'                 => 'nullable|string|max:255',
            'berat'                 => 'nullable|numeric|min:0',
            'warna'                 => 'nullable|array',
            'warna.*'               => 'string|max:100',
            'ukuran'                => 'nullable|array',
            'ukuran.*.ukuran'       => 'required_with:ukuran|string|max:20',
            'ukuran.*.stok'         => 'required_with:ukuran|integer|min:0',
            'ukuran.*.warna'        => 'nullable|array',
            'ukuran.*.warna.*.nama' => 'required|string|max:100',
            'ukuran.*.warna.*.stok' => 'required|integer|min:0',
            'bisa_disewa'           => 'nullable|boolean',
            'dapat_dijual'          => 'nullable|boolean',
            'status'                => 'required|in:' . implode(',', array_keys(Produk::opsiStatus())),
            'harga_sewa'            => 'nullable|numeric|min:0',
            'dp'                    => 'nullable|numeric|min:0',
            'harga_jual'            => 'nullable|numeric|min:0',
            'stok'                  => 'nullable|integer|min:0',
            'denda_per_hari_telat'  => 'nullable|numeric|min:0',
            'diskon_persen'         => 'nullable|numeric|min:0|max:100',
            'foto'                  => 'nullable|array|max:4',
            'foto.*'                => 'nullable|image|max:5120',
            'foto_existing'         => 'nullable|array|max:4',
            'foto_existing.*'       => 'nullable|string',
        ], [
            'ukuran.*.warna.*.nama.required' => 'Pilih nama warna pada setiap baris rincian warna.',
            'ukuran.*.warna.*.stok.required' => 'Isi stok pada setiap baris rincian warna.',
        ]);

        $bisaDisewa  = $request->boolean('bisa_disewa');
        $dapatDijual = $request->boolean('dapat_dijual');

        if (!$bisaDisewa && !$dapatDijual) {
            return back()
                ->withInput()
                ->withErrors(['jenis_transaksi' => 'Pilih minimal satu jenis transaksi: Bisa disewa atau Bisa dijual.']);
        }

        $ukuranVariasi = $this->rapikanUkuran($validated['ukuran'] ?? []);

        if ($ukuranGanda = $this->ukuranGanda($ukuranVariasi)) {
            return back()
                ->withInput()
                ->withErrors(['ukuran' => "Ukuran {$ukuranGanda} dipilih lebih dari sekali. Gabungkan jadi satu baris."]);
        }

        if (empty($ukuranVariasi) && !$request->filled('stok')) {
            return back()
                ->withInput()
                ->withErrors(['stok' => 'Isi stok, atau tambahkan minimal satu variasi ukuran dengan stoknya.']);
        }

        $totalStok = !empty($ukuranVariasi)
            ? collect($ukuranVariasi)->sum(fn ($v) => (int) $v['stok'])
            : (int) $validated['stok'];

        $kategori = Kategori::findOrFail($validated['kategori_id']);

        // Gabungkan foto baru (per slot) dengan foto lama yang tidak diganti
        try {
            $fotoBaru = $this->uploadFotoSlots($request, hapusSlotKosong: false);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
        }
        $fotoLama = array_map(
            fn ($p) => $this->normalizeFotoPath($p),
            $request->input('foto_existing', [])
        );
        $fotoFinal = [];

        for ($i = 0; $i < 4; $i++) {
            if (!empty($fotoBaru[$i])) {
                $fotoFinal[] = $fotoBaru[$i];
            } elseif (!empty($fotoLama[$i])) {
                $fotoFinal[] = $fotoLama[$i];
            }
        }

        // Hapus file lama di slot yang diganti dengan foto baru
        foreach ($fotoBaru as $i => $path) {
            $oldPath = $fotoLama[$i] ?? null;
            if ($path && $oldPath && $oldPath !== $path) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $produk->update([
            'kategori_id'          => $kategori->id,
            'sku'                  => $validated['sku'] ?: $produk->sku,
            'nama'                 => $validated['nama'],
            'deskripsi'            => $validated['deskripsi'] ?? null,
            'foto_produk'          => $fotoFinal,
            'bahan'                => $validated['bahan'] ?? null,
            'motif'                => $validated['motif'] ?? null,
            'warna'                => $validated['warna'] ?? [],
            'berat'                => $validated['berat'] ?? null,
            'ukuran'               => $ukuranVariasi,
            'harga_jual'           => $dapatDijual ? ($validated['harga_jual'] ?? null) : null,
            'harga_sewa'           => $bisaDisewa ? ($validated['harga_sewa'] ?? null) : null,
            'dp'                   => $bisaDisewa ? ($validated['dp'] ?? 0) : 0,
            'stok'                 => $totalStok,
            'status'               => $validated['status'],
            'dapat_dijual'         => $dapatDijual,
            'bisa_disewa'          => $bisaDisewa,
            'denda_per_hari_telat' => $validated['denda_per_hari_telat'] ?? 0,
            'diskon_persen'        => $validated['diskon_persen'] ?? 0,
        ]);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus produk.
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        foreach ((array) $produk->foto_produk as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        $produk->delete();

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Hapus banyak produk sekaligus (checklist di halaman Kelola Produk).
     * Foto di storage dihapus hanya untuk produk yang berhasil dihapus.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Pilih minimal satu produk untuk dihapus.',
        ])['ids'];

        $dihapus = 0;
        $gagal   = 0;

        foreach (Produk::whereIn('id', $ids)->get() as $produk) {
            $foto = (array) $produk->foto_produk;

            try {
                $produk->delete();
            } catch (QueryException $e) {
                // Masih terikat data lain (mis. transaksi/sewa lewat foreign key)
                $gagal++;
                continue;
            }

            foreach ($foto as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }
            $dihapus++;
        }

        if ($dihapus === 0 && $gagal > 0) {
            return redirect()
                ->route('admin.produk.index')
                ->with('error', 'Produk tidak dapat dihapus karena masih terkait data transaksi atau sewa.');
        }

        $pesan = "$dihapus produk berhasil dihapus.";
        if ($gagal > 0) {
            $pesan .= " $gagal produk dilewati karena masih terkait transaksi atau sewa.";
        }

        return redirect()->route('admin.produk.index')->with('success', $pesan);
    }

    /**
     * Unduh data produk lengkap dalam format Excel (.xlsx).
     * Mengikuti filter yang sedang aktif di halaman (q, kategori_id, status, jenis).
     */
    public function export(Request $request)
    {
        $produkList = $this->terapkanFilter(Produk::with('kategori'), $request)
            ->orderBy('id')
            ->get();

        $opsiStatus = Produk::opsiStatus();

        $judul = [
            'No', 'Kode SKU', 'Nama Produk', 'Kategori', 'Deskripsi', 'Bahan', 'Motif',
            'Warna', 'Ukuran & Stok', 'Berat', 'Jenis Transaksi', 'Harga Sewa', 'DP',
            'Harga Jual', 'Diskon (%)', 'Denda/Hari Telat', 'Total Stok', 'Status',
            'Rating', 'Jumlah Rating', 'Tanggal Dibuat',
        ];

        $xlsx = (new XlsxSederhana('Data Produk'))
            ->lebarKolom([6, 16, 32, 20, 40, 18, 18, 24, 40, 10, 16, 14, 12, 14, 11, 16, 11, 16, 9, 13, 18])
            ->aktifkanFilterHeader()
            ->tambahBaris(array_map(fn ($h) => [$h, XlsxSederhana::S_HEADER], $judul));

        foreach ($produkList as $i => $p) {
            $zebra = $i % 2 === 1;
            $teks  = fn ($v) => [$v, $zebra ? XlsxSederhana::S_ZEBRA : 0];
            $angka = fn ($v) => [
                ($v === null || $v === '') ? '' : (float) $v,
                $zebra ? XlsxSederhana::S_NUM_ZEBRA : XlsxSederhana::S_NUM,
            ];

            $jenis = match (true) {
                (bool) $p->bisa_disewa && (bool) $p->dapat_dijual => 'Sewa & Jual',
                (bool) $p->bisa_disewa                            => 'Sewa',
                (bool) $p->dapat_dijual                           => 'Jual',
                default                                           => '-',
            };

            $xlsx->tambahBaris([
                $angka($i + 1),
                $teks($p->sku),
                $teks($p->nama),
                $teks($p->kategori->nama_kategori ?? '-'),
                $teks($p->deskripsi),
                $teks($p->bahan),
                $teks($p->motif),
                $teks(implode(', ', $this->keArray($p->warna))),
                $teks($this->ringkasUkuran($p->ukuran)),
                $angka($p->berat),
                $teks($jenis),
                $angka($p->harga_sewa),
                $angka($p->dp),
                $angka($p->harga_jual),
                $angka($p->diskon_persen),
                $angka($p->denda_per_hari_telat),
                $angka($p->stok),
                $teks($opsiStatus[$p->status] ?? $p->status),
                $angka($p->rating_rata_rata),
                $angka($p->jumlah_rating),
                $teks(optional($p->created_at)->format('d/m/Y H:i')),
            ]);
        }

        $path = tempnam(sys_get_temp_dir(), 'produk_');

        try {
            $xlsx->simpan($path);
        } catch (\RuntimeException $e) {
            @unlink($path);
            return back()->with('error', $e->getMessage());
        }

        return response()->download(
            $path,
            'data-produk-' . now()->format('Ymd-His') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /**
     * Terapkan filter pencarian yang sama untuk daftar produk dan unduhan Excel.
     */
    protected function terapkanFilter($query, Request $request)
    {
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('motif', 'like', "%{$search}%")
                  ->orWhere('bahan', 'like', "%{$search}%")
                  ->orWhereHas('kategori', fn ($k) => $k->where('nama_kategori', 'like', "%{$search}%"));
            });
        }

        if ($kategoriId = $request->get('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($jenis = $request->get('jenis')) {
            if ($jenis === 'sewa') {
                $query->where('bisa_disewa', true);
            } elseif ($jenis === 'jual') {
                $query->where('dapat_dijual', true);
            }
        }

        return $query;
    }

    /**
     * Pastikan nilai (array atau string JSON) menjadi array.
     */
    protected function keArray($nilai): array
    {
        if (is_string($nilai)) {
            $nilai = json_decode($nilai, true);
        }

        return is_array($nilai) ? $nilai : [];
    }

    /**
     * Ringkas variasi ukuran jadi satu teks, mis. "S: 3 (Merah 2, Biru 1); M: 2".
     */
    protected function ringkasUkuran($ukuran): string
    {
        $bagian = [];

        foreach ($this->keArray($ukuran) as $u) {
            $teks = ($u['ukuran'] ?? '-') . ': ' . ($u['stok'] ?? 0);

            if (! empty($u['warna'])) {
                $warna = collect($u['warna'])
                    ->map(fn ($w) => ($w['nama'] ?? '-') . ' ' . ($w['stok'] ?? 0))
                    ->implode(', ');
                $teks .= " ($warna)";
            }

            $bagian[] = $teks;
        }

        return implode('; ', $bagian);
    }

    /**
     * Rapikan input variasi ukuran menjadi format simpan:
     *   [{ukuran, stok, warna?: [{nama, stok}]}]
     *
     * - Kalau sebuah ukuran punya rincian warna, stok ukuran = jumlah stok warnanya.
     * - Warna yang sama di satu ukuran digabung (stoknya dijumlahkan).
     * - Ukuran tanpa rincian warna tetap berupa satu angka stok (format lama, tetap kompatibel).
     *
     * @param  array<int, array<string, mixed>>  $ukuran
     * @return array<int, array<string, mixed>>
     */
    protected function rapikanUkuran(array $ukuran): array
    {
        $hasil = [];

        foreach ($ukuran as $row) {
            $perWarna = [];

            foreach (($row['warna'] ?? []) as $w) {
                $nama = trim((string) ($w['nama'] ?? ''));
                if ($nama === '') {
                    continue;
                }
                $perWarna[$nama] = ($perWarna[$nama] ?? 0) + (int) ($w['stok'] ?? 0);
            }

            $item = [
                'ukuran' => trim((string) $row['ukuran']),
                'stok'   => $perWarna ? array_sum($perWarna) : (int) ($row['stok'] ?? 0),
            ];

            if ($perWarna) {
                $item['warna'] = [];
                foreach ($perWarna as $nama => $stok) {
                    $item['warna'][] = ['nama' => (string) $nama, 'stok' => (int) $stok];
                }
            }

            $hasil[] = $item;
        }

        return $hasil;
    }

    /**
     * Kembalikan nama ukuran yang muncul lebih dari sekali (null kalau tidak ada).
     */
    protected function ukuranGanda(array $ukuranVariasi): ?string
    {
        return collect($ukuranVariasi)->pluck('ukuran')->duplicates()->first();
    }

    /**
     * Upload file foto per slot (index 0-3: sampul, slot2, slot3, detail).
     * Mengembalikan array dengan key = index slot, hanya berisi slot yang ada file-nya.
     *
     * @return array<int, string>
     */
    protected function uploadFotoSlots(Request $request, bool $hapusSlotKosong = true): array
    {
        $hasil = [];

        for ($i = 0; $i < 4; $i++) {
            if ($request->hasFile("foto.$i")) {
                try {
                    $hasil[$i] = ImageOptimizer::simpanSebagaiWebp($request->file("foto.$i"), 'produk');
                } catch (\RuntimeException $e) {
                    throw new \RuntimeException("Foto slot " . ($i + 1) . ": " . $e->getMessage());
                }
            } elseif (!$hapusSlotKosong) {
                $hasil[$i] = null;
            }
        }

        return $hapusSlotKosong ? array_values($hasil) : $hasil;
    }

    /**
     * Ubah URL penuh (data legacy) kembali menjadi path relatif storage.
     */
    protected function normalizeFotoPath(?string $value): ?string
    {
        if (!$value) return null;
        if (($pos = strpos($value, '/storage/')) !== false) {
            return substr($value, $pos + strlen('/storage/'));
        }
        return $value;
    }

    /**
     * Buat kode SKU otomatis: {KODE_KATEGORI}-{urutan 3 digit}.
     */
    protected function generateSku(Kategori $kategori): string
    {
        $prefix = Str::upper($kategori->kode_kategori ?: Str::slug($kategori->nama_kategori, ''));
        $urutan = Produk::where('kategori_id', $kategori->id)->count() + 1;

        $sku = sprintf('%s-%03d', $prefix, $urutan);

        // Hindari bentrok jika pernah ada produk yang dihapus/urutan loncat
        while (Produk::where('sku', $sku)->exists()) {
            $urutan++;
            $sku = sprintf('%s-%03d', $prefix, $urutan);
        }

        return $sku;
    }
}