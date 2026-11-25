<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Kelola Pengguna (akun pelanggan).
 *
 * Akun staf (owner/admin) TIDAK ikut dikelola di sini, itu tugas halaman Kelola Admin.
 * Staf dikenali dari kolom `role` (nilai di ROLE_STAF) atau kolom boolean is_admin / is_owner
 * kalau ada. Sesuaikan ROLE_STAF kalau nama role di proyekmu berbeda.
 *
 * Aturan edit: hanya akun yang dibuat manual oleh admin (kolom `dibuat_admin` = true)
 * yang boleh diedit. Akun hasil daftar/OTP/Google tidak bisa diedit admin.
 */
class PenggunaController extends Controller
{
    protected const ROLE_STAF = ['owner', 'admin', 'superadmin', 'super_admin'];

    /** Hari tanpa aktivitas yang dianggap "lama tidak aktif" (sesuai view: setahun). */
    protected const BATAS_LAMA_TIDAK_AKTIF = 360;

    /** Pilihan filter ?inaktif= yang diterima. */
    protected const PILIHAN_INAKTIF = [30, 60, 90, 180, 360];

    public function index(Request $request)
    {
        $status = in_array($request->input('status'), ['aktif', 'nonaktif'], true) ? $request->input('status') : '';

        // Tanpa ?inaktif, semua pelanggan ditampilkan. Parameter ini hanya dipakai kalau
        // admin menekan kartu "Tidak Aktif ≥ 360 Hari" untuk menyaring akun yang lama tidak aktif.
        $inaktif = (int) $request->input('inaktif');
        $inaktif = in_array($inaktif, self::PILIHAN_INAKTIF, true) ? $inaktif : 0;

        $query = $this->pelanggan();

        if ($request->filled('search')) {
            $cari = $request->input('search');
            $query->where(function ($q) use ($cari) {
                $q->where('name', 'like', "%{$cari}%")
                  ->orWhere('email', 'like', "%{$cari}%")
                  ->orWhere('no_hp', 'like', "%{$cari}%");
            });
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        if ($inaktif > 0) {
            $query->whereRaw('COALESCE(last_active_at, created_at) < ?', [now()->subDays($inaktif)])
                  ->orderByRaw('COALESCE(last_active_at, created_at) asc');
        } else {
            $query->latest();
        }

        $users = $query->paginate(15)->withQueryString();

        // Ringkasan transaksi per pengguna di halaman ini (tanpa bergantung pada relasi di model)
        $stat = Transaksi::whereIn('user_id', $users->pluck('id'))
            ->selectRaw("user_id, COUNT(*) as jumlah, MAX(created_at) as terakhir,
                         COALESCE(SUM(CASE WHEN status_pembayaran IN ('lunas','dp','dp_dibayar') THEN total ELSE 0 END), 0) as belanja")
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $detail = $users->getCollection()->mapWithKeys(fn ($u) => [$u->id => $this->bangunDetail($u, $stat->get($u->id))]);

        // Data form Edit: hanya untuk akun yang dibuat admin (akun lain tidak punya tombol Edit)
        $dataEdit = $users->getCollection()
            ->filter(fn ($u) => (bool) $u->dibuat_admin)
            ->mapWithKeys(fn ($u) => [$u->id => $this->bangunDataEdit($u)]);

        // Kartu statistik (tidak terpengaruh filter)
        $total = $this->pelanggan()->count();
        $aktif = $this->pelanggan()->where('is_active', true)->count();
        $lama = $this->pelanggan()->where('is_active', true)
            ->whereRaw('COALESCE(last_active_at, created_at) < ?', [now()->subDays(self::BATAS_LAMA_TIDAK_AKTIF)])
            ->count();

        return view('admin.pengguna.index', [
            'users' => $users,
            'stat' => $stat,
            'detail' => $detail,
            'dataEdit' => $dataEdit,
            'status' => $status,
            'inaktif' => $inaktif,
            'ringkasan' => [
                'total' => $total,
                'aktif' => $aktif,
                'nonaktif' => $total - $aktif,
                'lama' => $lama,
            ],
            'batasLama' => self::BATAS_LAMA_TIDAK_AKTIF,
        ]);
    }

    /**
     * Tambah akun manual oleh admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'no_hp' => 'nullable|string|max:30|unique:users,no_hp',
            'password' => 'nullable|string|min:8|max:100',
            'verifikasi_email' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            // Sama seperti validasi di ProfilController (customer), biar data yang
            // diisi admin di sini bentuknya persis sama dengan yang diisi pelanggan
            // sendiri lewat halaman Profil.
            'alamat' => 'nullable|string',
            'provinsi' => 'nullable|string|max:100',
            'kota_kabupaten' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar.',
        ], [
            'name' => 'nama',
            'email' => 'email',
            'no_hp' => 'nomor HP',
            'password' => __('kata sandi'),   // ← dari: 'kata sandi'
        ]);

        $data = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'no_hp' => isset($validated['no_hp']) ? preg_replace('/\s+/', '', $validated['no_hp']) : null,
            'is_active' => $request->boolean('is_active'),
            'alamat' => $validated['alamat'] ?? null,
            'provinsi' => $validated['provinsi'] ?? null,
            'kota_kabupaten' => $validated['kota_kabupaten'] ?? null,
            'kecamatan' => $validated['kecamatan'] ?? null,
            'kode_pos' => $validated['kode_pos'] ?? null,
            // Penanda: akun ini dibuat manual oleh admin, jadi boleh diedit admin.
            'dibuat_admin' => true,
        ];

        // Pelanggan masuk lewat OTP/Google, jadi kata sandi boleh kosong (diisi acak).
        if (Schema::hasColumn('users', 'password')) {
            $data['password'] = Hash::make($validated['password'] ?? Str::random(32));
        }

        if ($request->boolean('verifikasi_email') && Schema::hasColumn('users', 'email_verified_at')) {
            $data['email_verified_at'] = now();
        }

        // Peran default 'pelanggan' — sama seperti yang dipakai DaftarController &
        // OtpController@verifyDaftar saat orang daftar sendiri. Dicek dulu ke
        // peranPelanggan() supaya tetap mengikuti nilai role pelanggan lain kalau
        // proyek ini pakai penamaan role yang berbeda dari 'pelanggan'.
        if (Schema::hasColumn('users', 'role')) {
            $data['role'] = $this->peranPelanggan() ?? 'pelanggan';
        }

        // `status_aktif` juga diisi mengikuti `is_active`. Alur daftar sendiri
        // (DaftarController, OtpController@verifyDaftar) selalu set status_aktif
        // = true saat akun dibuat; tanpa baris ini, akun yang ditambah manual oleh
        // admin akan beda datanya dari akun yang daftar sendiri walau sama-sama
        // "aktif" di mata admin — dan itu yang bikin akun manual terasa tidak
        // tersambung dengan sisi login/OTP meski sebenarnya tetap bisa login
        // (OtpController cuma mengecek baris di tabel `users`, bukan kolom ini).
        if (Schema::hasColumn('users', 'status_aktif')) {
            $data['status_aktif'] = $data['is_active'];
        }

        if (! $data['is_active']) {
            $data['dinonaktifkan_pada'] = now();
        }

        $user = new User();
        $user->forceFill($data)->save();

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', "Akun {$user->name} berhasil ditambahkan.");
    }

    /**
     * Ubah data akun pelanggan dari modal Edit. Status aktif/nonaktif TIDAK diubah di sini
     * (tetap lewat status()), dan kata sandi hanya berubah kalau kolomnya diisi.
     *
     * Hanya akun buatan admin (dibuat_admin = true) yang boleh diedit.
     */
    public function update(Request $request, $id)
    {
        $user = $this->pelanggan()->findOrFail($id);

        if (! $user->dibuat_admin) {
            return back()->with('error', "Akun {$user->name} dibuat sendiri oleh pengguna (daftar/login), sehingga tidak bisa diedit oleh admin.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'max:30', Rule::unique('users', 'no_hp')->ignore($user->id)],
            'password' => 'nullable|string|min:8|max:100',
            'alamat' => 'nullable|string',
            'provinsi' => 'nullable|string|max:100',
            'kota_kabupaten' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
        ], [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'no_hp.unique' => 'Nomor HP ini sudah dipakai akun lain.',
        ], [
            'name' => 'nama',
            'email' => 'email',
            'no_hp' => 'nomor HP',
            'password' => __('kata sandi'),
        ]);

        $emailBaru = strtolower(trim($validated['email']));

        $data = [
            'name' => trim($validated['name']),
            'email' => $emailBaru,
            'no_hp' => isset($validated['no_hp']) ? preg_replace('/\s+/', '', $validated['no_hp']) : null,
            'alamat' => $validated['alamat'] ?? null,
            'provinsi' => $validated['provinsi'] ?? null,
            'kota_kabupaten' => $validated['kota_kabupaten'] ?? null,
            'kecamatan' => $validated['kecamatan'] ?? null,
            'kode_pos' => $validated['kode_pos'] ?? null,
        ];

        // Email diganti = alamat baru belum terbukti milik pelanggan, jadi status verifikasinya direset.
        // Hapus blok ini kalau admin ingin email baru tetap dianggap terverifikasi.
        if ($emailBaru !== strtolower((string) $user->email) && Schema::hasColumn('users', 'email_verified_at')) {
            $data['email_verified_at'] = null;
        }

        // Kata sandi hanya diganti kalau diisi (cast `hashed` di model yang melakukan hash).
        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->forceFill($data)->save();

        // Kembali ke halaman daftar (filter & halaman pagination tetap kalau asalnya dari daftar).
        // Kalau request tidak datang dari halaman daftar, jatuh ke daftar pengguna biasa.
        $daftar = route('admin.pengguna.index');
        $asal = url()->previous();
        $kembali = rtrim((string) parse_url($asal, PHP_URL_PATH), '/') === rtrim((string) parse_url($daftar, PHP_URL_PATH), '/')
            ? $asal
            : $daftar;

        return redirect($kembali)->with('success', "Data akun {$user->name} berhasil diperbarui.");
    }

    /**
     * Aktifkan / nonaktifkan satu akun. Parameter `aktif` = 1 atau 0.
     */
    public function status(Request $request, $id)
    {
        $user = $this->pelanggan()->findOrFail($id);
        $aktif = $request->boolean('aktif');

        $this->setStatus(collect([$user->id]), $aktif);

        return back()->with('success', $aktif
            ? "Akun {$user->name} diaktifkan kembali."
            : "Akun {$user->name} dinonaktifkan. Pengguna yang sedang login akan otomatis keluar saat membuka halaman berikutnya.");
    }

    /**
     * Hapus satu akun. Akun yang punya transaksi tidak boleh dihapus (riwayat keuangan harus utuh).
     */
    public function destroy($id)
    {
        $user = $this->pelanggan()->findOrFail($id);

        $jumlah = Transaksi::where('user_id', $user->id)->count();
        if ($jumlah > 0) {
            return back()->with('error', "Akun {$user->name} punya {$jumlah} transaksi sehingga tidak bisa dihapus. Nonaktifkan saja agar riwayatnya tetap aman.");
        }

        try {
            DB::transaction(function () use ($user) {
                $this->akhiriSesi([$user->id]);
                $user->delete();
            });
        } catch (QueryException $e) {
            report($e);

            return back()->with('error', "Akun {$user->name} tidak bisa dihapus karena masih terhubung dengan data lain (mis. keranjang, rating, atau reservasi fitting). Nonaktifkan saja.");
        }

        return back()->with('success', "Akun {$user->name} berhasil dihapus.");
    }

    /**
     * Aksi massal dari baris yang dicentang: nonaktifkan, aktifkan, atau hapus.
     */
    public function massal(Request $request)
    {
        $validated = $request->validate([
            'aksi' => 'required|in:nonaktifkan,aktifkan,hapus',
            'ids' => 'required|array|min:1|max:200',
            'ids.*' => 'integer',
        ], [
            'ids.required' => 'Pilih minimal satu akun.',
        ]);

        $users = $this->pelanggan()->whereIn('id', $validated['ids'])->get();
        if ($users->isEmpty()) {
            return back()->with('error', 'Akun yang dipilih tidak ditemukan.');
        }

        if ($validated['aksi'] !== 'hapus') {
            $aktif = $validated['aksi'] === 'aktifkan';
            $this->setStatus($users->pluck('id'), $aktif);

            return back()->with('success', $users->count() . ' akun ' . ($aktif ? 'diaktifkan kembali.' : 'dinonaktifkan.'));
        }

        // Hapus: lewati akun yang punya transaksi atau masih terhubung ke data lain
        $punyaTransaksi = Transaksi::whereIn('user_id', $users->pluck('id'))->distinct()->pluck('user_id')->all();
        $terhapus = 0;
        $dilewati = 0;

        foreach ($users as $user) {
            if (in_array($user->id, $punyaTransaksi, true)) {
                $dilewati++;
                continue;
            }

            try {
                DB::transaction(function () use ($user) {
                    $this->akhiriSesi([$user->id]);
                    $user->delete();
                });
                $terhapus++;
            } catch (QueryException $e) {
                report($e);
                $dilewati++;
            }
        }

        if ($terhapus === 0) {
            return back()->with('error', "Tidak ada akun yang dihapus. {$dilewati} akun dilewati karena punya transaksi atau masih terhubung dengan data lain. Nonaktifkan saja.");
        }

        $pesan = "{$terhapus} akun dihapus.";
        if ($dilewati > 0) {
            $pesan .= " {$dilewati} akun dilewati karena punya transaksi atau masih terhubung dengan data lain.";
        }

        return back()->with('success', $pesan);
    }

    // ------------------------------------------------------------------ helper

    /**
     * Query dasar: hanya akun pelanggan (bukan owner/admin).
     */
    protected function pelanggan()
    {
        $q = User::query();

        if (Schema::hasColumn('users', 'role')) {
            $q->where(function ($w) {
                $w->whereNull('role')->orWhereNotIn('role', self::ROLE_STAF);
            });
        }
        foreach (['is_admin', 'is_owner'] as $kolom) {
            if (Schema::hasColumn('users', $kolom)) {
                $q->where(function ($w) use ($kolom) {
                    $w->whereNull($kolom)->orWhere($kolom, false);
                });
            }
        }

        return $q;
    }

    /**
     * Nilai `role` yang dipakai pelanggan lain (supaya akun manual konsisten). Null kalau belum ada.
     */
    protected function peranPelanggan(): ?string
    {
        return $this->pelanggan()->whereNotNull('role')->value('role');
    }

    protected function setStatus($ids, bool $aktif): void
    {
        $ids = collect($ids)->all();

        $data = [
            'is_active' => $aktif,
            'dinonaktifkan_pada' => $aktif ? null : now(),
            'updated_at' => now(),
        ];
        // Sesi & "ingat saya" sengaja TIDAK dihapus di sini: pengguna yang sedang login akan
        // dikeluarkan otomatis oleh listener BlokirAkunNonaktif pada request berikutnya, lalu
        // melihat halaman keterangan akun dinonaktifkan (bukan sekadar terlempar ke tamu).

        DB::table('users')->whereIn('id', $ids)->update($data);

        // DB::table() tidak memicu event Eloquent, jadi perubahan status dicatat ke audit log secara manual.
        foreach (User::whereIn('id', $ids)->get() as $u) {
            Audit::catat('status', ($aktif ? 'mengaktifkan' : 'menonaktifkan') . " akun \"{$u->name}\"", $u, [
                ['field' => 'Status akun', 'lama' => $aktif ? 'Nonaktif' : 'Aktif', 'baru' => $aktif ? 'Aktif' : 'Nonaktif'],
            ]);
        }
    }

    /**
     * Hapus sesi login (driver `database`) supaya akun yang dinonaktifkan langsung keluar.
     * Kalau driver sesi lain, middleware EnsureUserActive yang menolaknya di request berikutnya.
     */
    protected function akhiriSesi(array $userIds): void
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::table('sessions')->whereIn('user_id', $userIds)->delete();
        }
    }

    /**
     * Data awal untuk modal Edit (dibaca JS lewat atribut data-user di tombol Edit).
     * Kuncinya mengikuti yang dipakai isiFormEdit()/pasangAlamat() di view.
     * Alamat diambil dari kolom alamat di tabel users (sama dengan yang disimpan update()).
     */
    protected function bangunDataEdit($u): array
    {
        return [
            'name' => $u->name,
            'email' => $u->email,
            'no_hp' => $u->no_hp,
            'alamat' => $u->alamat,
            'kode_pos' => $u->kode_pos,
            'provinsi' => $u->provinsi,
            'kota_kabupaten' => $u->kota_kabupaten,
            'kecamatan' => $u->kecamatan,
            'tujuan_id' => null,
            'tujuan_label' => null,
            'label' => 'Rumah',
            'jumlah_alamat' => filled($u->alamat) ? 1 : 0,
            'ubah_alamat' => 0,
        ];
    }

    /**
     * Susun data untuk modal detail: ringkasan + seluruh kolom akun (kecuali yang sensitif).
     */
    protected function bangunDetail($u, $stat): array
    {
        $tz = config('app.timezone');
        $fmt = fn ($v) => $v ? Carbon::parse($v)->timezone($tz)->locale('id')->translatedFormat('d M Y, H:i') : '-';

        $label = [
            'id' => 'ID', 'name' => 'Nama', 'email' => 'Email', 'no_hp' => 'No. HP', 'role' => 'Peran',
            'email_verified_at' => 'Email terverifikasi', 'created_at' => 'Bergabung', 'updated_at' => 'Diperbarui',
            'last_active_at' => 'Terakhir aktif', 'is_active' => 'Status akun', 'dinonaktifkan_pada' => 'Dinonaktifkan pada',
            'google_id' => 'Akun Google', 'dibuat_admin' => 'Dibuat oleh admin',
        ];

        $semua = [];
        foreach ($u->toArray() as $kunci => $nilai) {
            if (preg_match('/password|token|secret|otp|two_factor/i', $kunci)) {
                continue;
            }

            if ($kunci === 'is_active') {
                $tampil = $nilai ? 'Aktif' : 'Nonaktif';
            } elseif ($kunci === 'google_id') {
                $tampil = $nilai ? 'Terhubung' : '-';
            } elseif (is_bool($nilai) || str_starts_with($kunci, 'is_') || str_starts_with($kunci, 'has_')) {
                // is_bool() menangkap semua kolom boolean (termasuk status_aktif,
                // yang namanya tidak diawali is_/has_), jadi tampilannya tetap
                // "Ya"/"Tidak" dan bukan angka mentah 1/0.
                $tampil = $nilai ? 'Ya' : 'Tidak';
            } elseif (str_ends_with($kunci, '_at') && is_string($nilai)) {
                $tampil = $fmt($nilai);
            } elseif (is_array($nilai)) {
                $tampil = json_encode($nilai, JSON_UNESCAPED_UNICODE);
            } else {
                $tampil = ($nilai === null || $nilai === '') ? '-' : (string) $nilai;
            }

            $semua[] = ['label' => $label[$kunci] ?? Str::headline($kunci), 'nilai' => $tampil];
        }

        $terakhirAktif = $u->last_active_at ?? $u->created_at;

        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'no_hp' => $u->no_hp,
            'aktif' => (bool) $u->is_active,
            'bergabung' => $fmt($u->created_at),
            'terakhir_aktif' => $u->last_active_at ? $fmt($u->last_active_at) : 'Belum tercatat',
            'terakhir_aktif_relatif' => $terakhirAktif ? Carbon::parse($terakhirAktif)->locale('id')->diffForHumans() : '-',
            'transaksi' => (int) ($stat->jumlah ?? 0),
            'belanja' => 'Rp ' . number_format((float) ($stat->belanja ?? 0), 0, ',', '.'),
            'transaksi_terakhir' => ($stat && $stat->terakhir) ? $fmt($stat->terakhir) : '-',
            'semua' => $semua,
        ];
    }
}