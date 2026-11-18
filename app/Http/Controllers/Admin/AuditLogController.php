<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Halaman Audit Log: jejak siapa melakukan apa di sistem, dengan pembaruan hampir real-time
 * (halaman menarik baris baru dari feed() secara berkala).
 *
 * Catatan performa (supaya tetap ringan saat log mencapai jutaan baris):
 *  - Paginasi memakai cursor (WHERE id < ?), bukan OFFSET dan bukan COUNT(*) tiap halaman.
 *  - Hitungan berat (total, per umur) TIDAK dihitung saat halaman dibuka, hanya saat modal "Bersihkan" dibuka (hitung()).
 *  - Ringkasan hari ini & daftar modul di-cache singkat, jadi banyak tab/admin tidak melipatgandakan query.
 *  - Penghapusan massal dilakukan bertahap (per 5.000 baris) supaya tidak mengunci tabel / timeout.
 */
class AuditLogController extends Controller
{
    protected const FILTER = ['search', 'aksi', 'modul', 'peran', 'dari', 'sampai'];

    /** Aksi baku yang punya label sendiri; selain ini dikelompokkan sebagai "Aksi lain". */
    protected const AKSI_BAKU = ['created', 'updated', 'deleted', 'login', 'logout', 'login_gagal'];

    /** Hanya kolom yang benar-benar dipakai tampilan tabel. */
    protected const KOLOM_TAMPIL = [
        'id', 'created_at', 'pelaku', 'peran', 'aksi', 'modul',
        'deskripsi', 'perubahan', 'ip_address', 'user_agent', 'method', 'url',
    ];

    /** Pilihan umur log untuk fitur "Bersihkan Log" (hari => label). */
    protected const BATAS_HARI = [
        7 => '7 hari',
        30 => '30 hari',
        90 => '3 bulan',
        180 => '6 bulan',
        365 => '1 tahun',
    ];

    public function index(Request $request)
    {
        $this->izin();

        $logs = $this->query($request)
            ->select(self::KOLOM_TAMPIL)
            ->orderByDesc('id')
            ->cursorPaginate(25)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'modulList' => Cache::remember('audit:modul', 600, fn () => AuditLog::query()
                ->whereNotNull('modul')->distinct()->orderBy('modul')->pluck('modul')),
            'ringkasan' => $this->ringkasan(),
            'idTerbaru' => (int) AuditLog::max('id'),
            'adaFilter' => $this->adaFilter($request),
            'batasHari' => self::BATAS_HARI,
        ]);
    }

    /**
     * Baris baru sejak id tertentu (sesuai filter yang sedang dipakai halaman), dalam bentuk HTML siap sisip.
     */
    public function feed(Request $request): JsonResponse
    {
        $this->izin();

        $setelah = (int) $request->input('after', 0);

        $logs = $this->query($request)
            ->select(self::KOLOM_TAMPIL)
            ->where('id', '>', $setelah)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json([
            'terbaru' => (int) max($setelah, (int) $logs->max('id')),
            'html' => $logs->map(fn ($log) => view('admin.audit._baris', ['log' => $log])->render())->implode(''),
            'ringkasan' => $this->ringkasan(),
        ]);
    }

    /**
     * Jumlah log untuk pratinjau modal "Bersihkan Log". Dihitung hanya saat modal dibuka
     * (bukan di setiap pemuatan halaman) dan bagian umum di-cache 1 menit.
     */
    public function hitung(Request $request): JsonResponse
    {
        $this->izin();

        $umum = Cache::remember('audit:hitung', 60, function () {
            $q = AuditLog::query()->selectRaw('COUNT(*) as semua');

            foreach (array_keys(self::BATAS_HARI) as $h) {
                $q->selectRaw("COALESCE(SUM(created_at < ?), 0) as h{$h}", [now()->subDays($h)->toDateTimeString()]);
            }

            $r = $q->first();
            $umur = [];

            foreach (array_keys(self::BATAS_HARI) as $h) {
                $umur[$h] = (int) ($r->{"h{$h}"} ?? 0);
            }

            return ['semua' => (int) ($r->semua ?? 0), 'umur' => $umur];
        });

        return response()->json($umum + [
            'filter' => $this->adaFilter($request) ? (int) $this->query($request)->count() : 0,
        ]);
    }

    /**
     * Hapus log yang dicentang di tabel.
     */
    public function hapusTerpilih(Request $request)
    {
        $this->izinHapus();

        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:1000'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Pilih minimal satu log untuk dihapus.',
        ])['ids'];

        $jumlah = AuditLog::whereIn('id', $ids)->delete();

        $this->catatPembersihan('terpilih', $jumlah);
        $this->lupakanCache();

        return redirect()
            ->route('admin.audit.index', $request->only(self::FILTER))
            ->with('success', "{$jumlah} log berhasil dihapus.");
    }

    /**
     * Bersihkan log sekaligus: berdasarkan umur, sesuai filter aktif, atau seluruhnya.
     */
    public function bersihkan(Request $request)
    {
        $this->izinHapus();

        $data = $request->validate([
            'mode' => ['required', Rule::in(['umur', 'filter', 'semua'])],
            'hari' => ['nullable', 'required_if:mode,umur', 'integer', Rule::in(array_keys(self::BATAS_HARI))],
            'konfirmasi' => ['nullable', 'string'],
        ], [
            'mode.required' => 'Pilih cakupan log yang ingin dibersihkan.',
            'hari.required_if' => 'Pilih batas umur log yang ingin dihapus.',
            'hari.in' => 'Batas umur log tidak valid.',
        ]);

        $ket = $data['mode'];

        if ($data['mode'] === 'umur') {
            $hari = (int) $data['hari'];
            $q = AuditLog::query()->where('created_at', '<', now()->subDays($hari));
            $ket = "umur > {$hari} hari";
        } elseif ($data['mode'] === 'filter') {
            if (! $this->adaFilter($request)) {
                return redirect()->route('admin.audit.index')
                    ->with('error', 'Tidak ada filter aktif, jadi tidak ada yang dihapus.');
            }

            $q = $this->query($request);
        } else {
            if (strtoupper(trim((string) ($data['konfirmasi'] ?? ''))) !== 'HAPUS') {
                return redirect()->route('admin.audit.index')
                    ->with('error', 'Ketik HAPUS untuk mengonfirmasi penghapusan seluruh log.');
            }

            $q = AuditLog::query();
        }

        [$jumlah, $selesai] = $this->hapusBertahap($q);

        $this->catatPembersihan($ket . ($selesai ? '' : ' (sebagian)'), $jumlah);
        $this->lupakanCache();

        $pesan = match (true) {
            $jumlah === 0 => 'Tidak ada log yang cocok untuk dihapus.',
            $selesai => "{$jumlah} log berhasil dibersihkan.",
            default => "{$jumlah} log dihapus, tapi masih ada sisa. Jalankan Bersihkan Log sekali lagi.",
        };

        return redirect()
            ->route('admin.audit.index')
            ->with('success', $pesan);
    }

    // ------------------------------------------------------------------ helper

    /**
     * Hanya owner, atau admin yang diberi hak akses menu "audit" di Kelola Admin.
     */
    protected function izin(): void
    {
        $user = auth('admin')->user();

        abort_unless($user && $user->canAccessAdminMenu('audit'), 403, 'Anda tidak punya akses ke halaman Audit Log.');
    }

    /**
     * Menghapus log lebih sensitif daripada melihatnya. Saat ini aturannya sama dengan izin() (owner atau
     * admin yang diberi menu "audit"). Kalau penghapusan mau dibatasi khusus owner, ubah di sini saja.
     */
    protected function izinHapus(): void
    {
        $this->izin();
    }

    protected function adaFilter(Request $request): bool
    {
        return collect(self::FILTER)->contains(fn ($k) => $request->filled($k));
    }

    /**
     * Hapus per potongan supaya satu DELETE raksasa tidak mengunci tabel atau kena timeout.
     * Berhenti kalau sudah melewati batas waktu; $selesai false berarti masih ada sisa.
     *
     * @return array{0: int, 1: bool}  [jumlah terhapus, apakah sudah tuntas]
     */
    protected function hapusBertahap(Builder $q, int $ukuran = 5000, int $detik = 25): array
    {
        @set_time_limit($detik + 15);

        $mulai = microtime(true);
        $total = 0;

        do {
            $n = (clone $q)->limit($ukuran)->delete();
            $total += $n;
        } while ($n === $ukuran && (microtime(true) - $mulai) < $detik);

        return [$total, $n < $ukuran];
    }

    protected function lupakanCache(): void
    {
        Cache::forget('audit:hitung');
        Cache::forget('audit:modul');
    }

    /**
     * Jejak pembersihan disimpan di log aplikasi (storage/logs), bukan di tabel audit yang baru saja dibersihkan.
     */
    protected function catatPembersihan(string $cakupan, int $jumlah): void
    {
        $user = auth('admin')->user();

        Log::info('Audit log dibersihkan', [
            'oleh_id' => $user?->getKey(),
            'oleh' => $user->name ?? null,
            'cakupan' => $cakupan,
            'jumlah' => $jumlah,
            'ip' => request()->ip(),
        ]);
    }

    protected function query(Request $request): Builder
    {
        $q = AuditLog::query();

        if ($request->filled('search')) {
            $cari = trim((string) $request->input('search'));
            $like = addcslashes($cari, '%_\\');

            // Nama pelaku & label data sudah ada di dalam kolom deskripsi, jadi cukup satu kolom.
            // Kalau yang diketik berbentuk alamat IP, pakai pencarian awalan agar index ip_address terpakai.
            if (preg_match('/^[\da-f:.]+$/i', $cari) && preg_match('/[.:]/', $cari)) {
                $q->where('ip_address', 'like', "{$like}%");
            } else {
                $q->where('deskripsi', 'like', "%{$like}%");
            }
        }

        if ($request->filled('aksi')) {
            $aksi = $request->input('aksi');

            if ($aksi === 'lainnya') {
                $q->whereNotIn('aksi', self::AKSI_BAKU);
            } else {
                $q->where('aksi', $aksi);
            }
        }

        if ($request->filled('modul')) {
            $q->where('modul', $request->input('modul'));
        }

        if ($request->filled('peran')) {
            $q->where('peran', $request->input('peran'));
        }

        if ($dari = $this->tanggal($request->input('dari'))) {
            $q->where('created_at', '>=', $dari->startOfDay());
        }

        if ($sampai = $this->tanggal($request->input('sampai'))) {
            $q->where('created_at', '<=', $sampai->endOfDay());
        }

        return $q;
    }

    protected function tanggal(?string $nilai): ?Carbon
    {
        if (! $nilai) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $nilai, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Angka ringkasan hari ini (satu query, di-cache 10 detik dan dipakai bersama semua admin/tab).
     */
    protected function ringkasan(): array
    {
        return Cache::remember('audit:ringkasan:' . now()->format('Ymd'), 10, function () {
            $r = AuditLog::query()
                ->where('created_at', '>=', now()->startOfDay())
                ->selectRaw("COUNT(*) as total,
                             COUNT(DISTINCT user_id) as pelaku,
                             COALESCE(SUM(aksi = 'deleted'), 0) as dihapus,
                             COALESCE(SUM(aksi = 'login_gagal'), 0) as gagal")
                ->first();

            return [
                'total' => (int) ($r->total ?? 0),
                'pelaku' => (int) ($r->pelaku ?? 0),
                'dihapus' => (int) ($r->dihapus ?? 0),
                'gagal' => (int) ($r->gagal ?? 0),
            ];
        });
    }
}