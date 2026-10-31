<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class KategoriController extends Controller
{
    // 1. Tampilkan daftar kategori (DENGAN FILTER)
    public function index(Request $request)
    {
        // Siapkan query dasar (menggunakan relasi 'produks' sesuai model Anda)
        $query = Kategori::withCount('produks');

        // Filter Pencarian (Nama atau Slug)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_kategori', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        // Filter Status (Aktif / Nonaktif)
        if ($request->filled('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->input('status'));
        }

        // Eksekusi query
        $categories = $query->get();
        
        // Hitung total produk untuk Card Statistik
        $totalProduk = Produk::count();

        return view('admin.kategori.index', compact('categories', 'totalProduk'));
    }

    // 2. Tampilkan form tambah kategori
    public function create()
    {
        return redirect()->route('admin.kategori.index');
    }

    // 3. Simpan kategori baru ke database
        public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:kategori,slug',
            'kode_kategori' => 'required|string|max:10|unique:kategori,kode_kategori',
            'foto' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            try {
                $fotoPath = ImageOptimizer::simpanSebagaiWebp($request->file('foto'), 'kategori');
            } catch (\RuntimeException $e) {
                return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
            }
        }

        Kategori::create([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => $validated['slug'],
            'kode_kategori' => $validated['kode_kategori'],
            'foto' => $fotoPath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    // 4. Tampilkan form edit kategori
    public function edit($id)
    {
        return redirect()->route('admin.kategori.index');
    }

    // 5. Update kategori di database
        public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:kategori,slug,' . $kategori->id,
            'kode_kategori' => 'required|string|max:10|unique:kategori,kode_kategori,' . $kategori->id,
            'foto' => 'nullable|image|max:2048', // <--- TAMBAHKAN
            'is_active' => 'boolean',
        ]);

        $fotoPath = $kategori->foto;
        if ($request->hasFile('foto')) {
            try {
                $fotoPathBaru = ImageOptimizer::simpanSebagaiWebp($request->file('foto'), 'kategori');
            } catch (\RuntimeException $e) {
                return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
            }

            // Hapus foto lama hanya setelah foto baru berhasil dibuat
            if ($kategori->foto) {
                \Storage::disk('public')->delete($kategori->foto);
            }
            $fotoPath = $fotoPathBaru;
        }

        $kategori->update([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => $validated['slug'],
            'kode_kategori' => $validated['kode_kategori'],
            'foto' => $fotoPath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    // 6. Hapus kategori dari database

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Pilih minimal satu kategori untuk dihapus.',
        ])['ids'];

        $kategoris = Kategori::withCount('produks')->whereIn('id', $ids)->get();

        $dihapus = 0;
        $dilewati = 0;

        foreach ($kategoris as $k) {
            if ($k->produks_count > 0) {
                $dilewati++;
                continue;
            }

            if ($k->foto) {
                Storage::disk('public')->delete($k->foto);
            }

            $k->delete();
            $dihapus++;
        }

        // Semua yang dipilih masih punya produk -> tidak ada yang terhapus
        if ($dihapus === 0 && $dilewati > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk terkait. Pindahkan atau hapus produknya terlebih dahulu.');
        }

        $pesan = "$dihapus kategori berhasil dihapus.";
        if ($dilewati > 0) {
            $pesan .= " $dilewati kategori dilewati karena masih punya produk terkait.";
        }

        return redirect()->route('admin.kategori.index')->with('success', $pesan);
    }
}