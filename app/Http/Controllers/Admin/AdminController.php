<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Daftar akun admin & owner yang punya akses ke panel admin,
     * beserta hak akses menu masing-masing.
     */
    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'owner'])
            ->orderByRaw("role = 'owner' desc")
            ->orderBy('name')
            ->get();

        return view('admin.kelola-admin.index', [
            'admins'         => $admins,
            'menuGrouped'    => AdminMenu::grouped(),
            'canAssignOwner' => Auth::user()?->isOwner() ?? false,
            'totalOwner'     => User::where('role', 'owner')->count(),
            'totalAdmin'     => User::where('role', 'admin')->count(),
            'totalAktif'     => User::whereIn('role', ['admin', 'owner'])->where('status_aktif', true)->count(),
        ]);
    }

    /**
     * Simpan akun admin/owner baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|max:255|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'role'                  => ['required', Rule::in($this->allowedRoleOptions())],
            'admin_permissions'     => 'nullable|array',
            'admin_permissions.*'   => Rule::in(AdminMenu::keys()),
            'status_aktif'          => 'nullable|boolean',
        ]);

        User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => $validated['role'],
            // Owner tidak butuh daftar permission, karena selalu full access.
            'admin_permissions' => $validated['role'] === 'owner' ? [] : ($validated['admin_permissions'] ?? []),
            'status_aktif'      => $request->boolean('status_aktif', true),
        ]);

        return redirect()
            ->route('admin.kelola-admin.index')
            ->with('success', 'Akun admin berhasil ditambahkan.');
    }

    /**
     * Update akun admin/owner: data diri, role, hak akses menu, status aktif,
     * dan password (opsional, dikosongkan kalau tidak ingin ganti).
     */
    public function update(Request $request, $id)
    {
        $admin = User::whereIn('role', ['admin', 'owner'])->findOrFail($id);

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'password'              => 'nullable|string|min:8|confirmed',
            'role'                  => ['required', Rule::in($this->allowedRoleOptions())],
            'admin_permissions'     => 'nullable|array',
            'admin_permissions.*'   => Rule::in(AdminMenu::keys()),
            'status_aktif'          => 'nullable|boolean',
        ]);

        $statusAktifBaru = $request->boolean('status_aktif', true);

        // Jangan sampai owner terakhir diturunkan jadi admin atau dinonaktifkan,
        // nanti tidak ada lagi yang bisa kelola akses admin lainnya.
        if ($admin->role === 'owner' && ($validated['role'] !== 'owner' || !$statusAktifBaru)) {
            $ownerLainMasihAda = User::where('role', 'owner')
                ->where('id', '!=', $admin->id)
                ->where('status_aktif', true)
                ->exists();

            if (!$ownerLainMasihAda) {
                return back()->withInput()->withErrors([
                    'role' => 'Tidak bisa mengubah atau menonaktifkan owner terakhir. Pastikan minimal ada satu akun owner lain yang aktif.',
                ]);
            }
        }

        $admin->name              = $validated['name'];
        $admin->email             = $validated['email'];
        $admin->role              = $validated['role'];
        $admin->admin_permissions = $validated['role'] === 'owner' ? [] : ($validated['admin_permissions'] ?? []);
        $admin->status_aktif      = $statusAktifBaru;

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        return redirect()
            ->route('admin.kelola-admin.index')
            ->with('success', 'Akun admin berhasil diperbarui.');
    }

    /**
     * Hapus akun admin/owner.
     */
    public function destroy($id)
    {
        $admin = User::whereIn('role', ['admin', 'owner'])->findOrFail($id);

        if ($admin->id === Auth::id()) {
            return redirect()
                ->route('admin.kelola-admin.index')
                ->with('error', 'Tidak bisa menghapus akun yang sedang kamu pakai sendiri.');
        }

        if ($admin->role === 'owner' && User::where('role', 'owner')->count() <= 1) {
            return redirect()
                ->route('admin.kelola-admin.index')
                ->with('error', 'Tidak bisa menghapus owner terakhir.');
        }

        $admin->delete();

        return redirect()
            ->route('admin.kelola-admin.index')
            ->with('success', 'Akun admin berhasil dihapus.');
    }

    /**
     * Role yang boleh dipilih di form: hanya akun owner yang boleh
     * membuat/menetapkan akun lain jadi owner. Admin biasa cuma bisa
     * membuat/mengedit sesama 'admin'.
     */
    protected function allowedRoleOptions(): array
    {
        return (Auth::user()?->isOwner() ?? false) ? ['admin', 'owner'] : ['admin'];
    }
}