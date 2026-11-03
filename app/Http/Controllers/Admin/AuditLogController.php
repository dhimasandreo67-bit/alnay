<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Halaman Audit Log: jejak siapa melakukan apa di sistem, dengan pembaruan hampir real-time
 * (halaman menarik baris baru dari feed() tiap beberapa detik).
 */
class AuditLogController extends Controller
{
    protected const FILTER = ['search', 'aksi', 'modul', 'peran', 'dari', 'sampai'];

    /** Aksi baku yang punya label sendiri; selain ini dikelompokkan sebagai "Aksi lain". */
    protected const AKSI_BAKU = ['created', 'updated', 'deleted', 'login', 'logout', 'login_gagal'];

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

        $logs = $this->query($request)->orderByDesc('id')->paginate(25)->withQueryString();

        // Jumlah log per batas umur + total, dihitung dalam satu query untuk pratinjau di modal "Bersihkan Log".
        $hitung = AuditLog::query()->selectRaw('COUNT(*) as semua');
        foreach (array_keys(self::BATAS_HARI) as $h) {
            $hitung->selectRaw("COALESCE(SUM(created_at < ?), 0) as h{$h}", [now()->subDays($h)->toDateTimeString()]);
        }
        $hitung = $hitung->first();

        $hitungUmur = [];
        foreach (array_keys(self::BATAS_HARI) as $h) {
            $hitungUmur[$h] = (int) ($hitung->{"h{$h}"} ?? 0);
        }

        return view('admin.audit.index', [
            'logs' => $logs,
            'modulList' => AuditLog::query()->whereNotNull('modul')->distinct()->orderBy('modul')->pluck('modul'),
            'ringkasan' => $this->ringkasan(),
            'idTerbaru' => (int) AuditLog::max('id'),
            'adaFilter' => $this->adaFilter($request),
            'totalSemua' => (int) ($hitung->semua ?? 0),
            'hitungUmur' => $hitungUmur,
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

        $jumlah = $q->delete();

        $this->catatPembersihan($ket, $jumlah);

        return redirect()
            ->route('admin.audit.index')
            ->with('success', $jumlah > 0 ? "{$jumlah} log berhasil dibersihkan." : 'Tidak ada log yang cocok untuk dihapus.');
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

    protected function query(Request $request)
    {
        $q = AuditLog::query();

        if ($request->filled('search')) {
            $cari = $request->input('search');
            $q->where(function ($w) use ($cari) {
                $w->where('deskripsi', 'like', "%{$cari}%")
                  ->orWhere('pelaku', 'like', "%{$cari}%")
                  ->orWhere('subjek_label', 'like', "%{$cari}%")
                  ->orWhere('ip_address', 'like', "%{$cari}%");
            });
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
     * Angka ringkasan hari ini (satu query).
     */
    protected function ringkasan(): array
    {
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
    }
}