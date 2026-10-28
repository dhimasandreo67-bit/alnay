<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Halaman Audit Log: jejak siapa melakukan apa di sistem, dengan pembaruan hampir real-time
 * (halaman menarik baris baru dari feed() tiap beberapa detik).
 */
class AuditLogController extends Controller
{
    protected const FILTER = ['search', 'aksi', 'modul', 'peran', 'dari', 'sampai'];

    /** Aksi baku yang punya label sendiri; selain ini dikelompokkan sebagai "Aksi lain". */
    protected const AKSI_BAKU = ['created', 'updated', 'deleted', 'login', 'logout', 'login_gagal'];

    public function index(Request $request)
    {
        $this->izin();

        $logs = $this->query($request)->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'modulList' => AuditLog::query()->whereNotNull('modul')->distinct()->orderBy('modul')->pluck('modul'),
            'ringkasan' => $this->ringkasan(),
            'idTerbaru' => (int) AuditLog::max('id'),
            'adaFilter' => collect(self::FILTER)->contains(fn ($k) => $request->filled($k)),
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

    // ------------------------------------------------------------------ helper

    /**
     * Hanya owner, atau admin yang diberi hak akses menu "audit" di Kelola Admin.
     */
    protected function izin(): void
    {
        $user = auth('admin')->user();

        abort_unless($user && $user->canAccessAdminMenu('audit'), 403, 'Anda tidak punya akses ke halaman Audit Log.');
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