<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriController extends Controller
{
    // 1. Tampilkan daftar kategori (beserta jumlah produk tiap kategori)
    public function index()
    {
        $categories = Kategori::withCount('produk')
            ->orderBy('id', 'asc')
            ->paginate(10);

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
            'nama' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:kategori,slug',
            'is_active' => 'boolean',
        ]);

        Kategori::create([
            'nama_kategori' => $validated['nama'],
            'slug'          => $validated['slug'] ?: Str::slug($validated['nama']),
            'is_active'     => $request->has('is_active'),
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // 4. Tampilkan form edit kategori
    public function edit($id)
    {
        return redirect()->route('admin.kategori.index');
    }

    // 5. Update kategori di database
    public function update(Request $request, $id)
    {
        $category = Kategori::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:kategori,slug,' . $category->id,
            'is_active' => 'boolean',
        ]);

        $category->update([
            'nama_kategori' => $validated['nama'],
            'slug'          => $validated['slug'] ?: Str::slug($validated['nama']),
            'is_active'     => $request->has('is_active'),
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    // 6. Hapus kategori dari database
    public function destroy($id)
    {
        $category = Kategori::withCount('produk')->findOrFail($id);

        // Proteksi relasi: jangan izinkan hapus kategori yang masih punya produk.
        if ($category->produk_count > 0) {
            return redirect()
                ->route('admin.kategori.index')
                ->with('error', "Kategori \"{$category->nama_kategori}\" tidak bisa dihapus karena masih memiliki {$category->produk_count} produk terkait. Pindahkan atau hapus produk tersebut terlebih dahulu.");
        }

        $category->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}