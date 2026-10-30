<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
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
        $query = Produk::with('kategori');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
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
        $fotoPaths = $this->uploadFotoSlots($request);

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
        $fotoBaru = $this->uploadFotoSlots($request, hapusSlotKosong: false);
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
                $hasil[$i] = $request->file("foto.$i")->store('produk', 'public');
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