<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            $fotoPath = $request->file('foto')->store('kategori', 'public');
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
            // Hapus foto lama
            if ($kategori->foto) {
                \Storage::disk('public')->delete($kategori->foto);
            }
            $fotoPath = $request->file('foto')->store('kategori', 'public');
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
    public function destroy($id)
    {
        $kategori = Kategori::withCount('produks')->findOrFail($id);
        
        // Proteksi relasi: jangan izinkan hapus kategori yang masih punya produk.
        if ($kategori->produks_count > 0) {
            return redirect()->back()->with('error', "Kategori \"{$kategori->nama_kategori}\" tidak bisa dihapus karena masih memiliki {$kategori->produks_count} produk terkait.");
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}