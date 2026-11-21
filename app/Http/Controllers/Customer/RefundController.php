<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\RiwayatStatus;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RefundController extends Controller
{
    /** Halaman "Refund Saya": pengajuan berjalan + pesanan yang masih bisa direfund. */
    public function index()
    {
        $refundList = Refund::with('transaksi.detail.produk')
            ->where('diajukan_oleh', auth()->id())
            ->latest()
            ->get();

        $bisaDirefund = Transaksi::with(['detail.produk', 'pengiriman', 'refund'])
            ->where('user_id', auth()->id())
            ->where('jenis', 'beli')
            ->latest()
            ->get()
            ->filter(fn (Transaksi $t) => $t->cekBisaRefund()[0])
            ->values();

        return view('customer.akun.refund', compact('refundList', 'bisaDirefund'));
    }

    public function create($id)
    {
        $transaksi = $this->transaksiSaya($id)->load(['detail.produk', 'pengiriman', 'refund']);

        [$boleh, $alasan] = $transaksi->cekBisaRefund();
        if (! $boleh) {
            return redirect()->route('akun.pesanan.detail', $transaksi->id)->with('error', $alasan);
        }

        return view('customer.akun.refund-form', compact('transaksi'));
    }

    public function store(Request $request, $id)
    {
        $transaksi = $this->transaksiSaya($id);

        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
            'bukti_foto' => ['required', 'array', 'min:1', 'max:3'],
            'bukti_foto.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'bank_tujuan' => ['required', 'string', 'max:50'],
            'no_rekening' => ['required', 'string', 'regex:/^[0-9\- ]{5,30}$/'],
            'atas_nama' => ['required', 'string', 'max:100'],
        ], [
            'alasan.min' => 'Jelaskan alasan refund minimal 10 karakter.',
            'bukti_foto.required' => 'Unggah minimal 1 foto bukti (mis. kondisi barang).',
            'bukti_foto.max' => 'Maksimal 3 foto.',
            'bukti_foto.*.image' => 'Bukti harus berupa gambar.',
            'bukti_foto.*.max' => 'Ukuran tiap foto maksimal 3 MB.',
            'no_rekening.regex' => 'Nomor rekening/e-wallet hanya boleh berisi angka.',
        ]);

        $path = [];

        try {
            $refund = DB::transaction(function () use ($request, $data, $transaksi, &$path) {
                // Kunci transaksi: klik ganda tidak boleh membuat dua pengajuan.
                $transaksi = Transaksi::with(['detail', 'pengiriman', 'refund'])->lockForUpdate()->findOrFail($transaksi->id);

                [$boleh, $alasan] = $transaksi->cekBisaRefund();
                if (! $boleh) {
                    abort(422, $alasan);
                }

                foreach ($request->file('bukti_foto') as $foto) {
                    $path[] = $foto->store('refund/bukti', 'public');
                }

                $refund = (new Refund)->forceFill([
                    'transaksi_id' => $transaksi->id,
                    'diajukan_oleh' => auth()->id(),
                    'alasan' => $data['alasan'],
                    'bukti_foto' => $path,
                    'bank_tujuan' => $data['bank_tujuan'],
                    'no_rekening' => preg_replace('/[\s-]+/', '', $data['no_rekening']),
                    'atas_nama' => $data['atas_nama'],
                    'jumlah' => $transaksi->nilaiBarang(), // usulan awal; admin bisa menyesuaikan saat menyetujui
                    'status' => 'diajukan',
                ]);
                $refund->save();

                RiwayatStatus::create([
                    'tabel_terkait' => 'refund',
                    'record_id' => $refund->id,
                    'status_lama' => null,
                    'status_baru' => 'diajukan',
                    'diubah_oleh' => auth()->id(),
                    'waktu_ubah' => now(),
                ]);

                return $refund;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path); // jangan tinggalkan foto yatim

            if ($e instanceof HttpException) {
                return redirect()->route('akun.pesanan.detail', $transaksi->id)->with('error', $e->getMessage());
            }

            throw $e;
        }

        return redirect()->route('akun.refund.show', $refund->id)
            ->with('success', 'Pengajuan refund terkirim. Tim kami akan meninjau maksimal 1x24 jam.');
    }

    public function show($id)
    {
        $refund = Refund::with(['transaksi.detail.produk', 'transaksi.pengiriman'])
            ->where('diajukan_oleh', auth()->id())
            ->findOrFail($id);

        $riwayat = $refund->riwayat()->orderBy('waktu_ubah', 'asc')->get();

        return view('customer.akun.refund-detail', compact('refund', 'riwayat'));
    }

    public function kirim(Request $request, $id)
    {
        $refund = $this->refundSaya($id);

        $perluResi = $refund->metode_pengembalian === 'kirim_sendiri';
        $atribut = ['tanggal_dikirim' => now()];
        $path = null;

        if ($perluResi) {
            $data = $request->validate([
                'kurir_retur' => ['required', 'string', 'max:50'],
                'no_resi_retur' => ['required', 'string', 'max:100'],
                'biaya_ongkir_retur' => ['required', 'numeric', 'min:0', 'max:99999999'],
                'bukti_pengiriman' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            ], [
                'biaya_ongkir_retur.required' => 'Isi biaya ongkir yang Anda bayar (sesuai struk).',
                'bukti_pengiriman.required' => 'Unggah foto struk/resi agar toko bisa mengganti ongkir.',
                'kurir_retur.required' => 'Isi nama kurir (mis. JNE, J&T, SiCepat).',
                'no_resi_retur.required' => 'Isi nomor resi pengiriman.',
            ]);

            $atribut['kurir_retur'] = $data['kurir_retur'];
            $atribut['no_resi_retur'] = trim($data['no_resi_retur']);
            $atribut['biaya_ongkir_retur'] = $data['biaya_ongkir_retur'];

            $path = $request->file('bukti_pengiriman')->store('refund/pengiriman', 'public');
            $atribut['bukti_pengiriman'] = $path;
        }

        if (! $refund->pindahKe('barang_dikirim', $atribut, auth()->id())) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            return back()->with('error', 'Status refund tidak bisa diubah ke "barang dikirim" saat ini.');
        }

        return back()->with('success', 'Terima kasih. Kami akan memberi tahu setelah barang diterima dan dicek.');
    }

    /** Pelanggan membatalkan pengajuan selama belum ditinjau. */
    public function batal($id)
    {
        $refund = $this->refundSaya($id);

        if (! $refund->pindahKe('dibatalkan', [], auth()->id())) {
            return back()->with('error', 'Pengajuan ini sudah tidak bisa dibatalkan (barang sudah dikirim atau pengajuan sudah selesai).');
        }

        return back()->with('success', 'Pengajuan refund dibatalkan.');
    }

    private function transaksiSaya($id): Transaksi
    {
        return Transaksi::where('user_id', auth()->id())->findOrFail($id);
    }

    private function refundSaya($id): Refund
    {
        return Refund::where('diajukan_oleh', auth()->id())->findOrFail($id);
    }
}
