<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Konversi gambar yang diupload menjadi WebP supaya ukuran file lebih kecil
 * dan halaman lebih cepat dimuat, tanpa perlu package tambahan (pakai GD bawaan PHP).
 *
 * Contoh pakai di controller:
 *
 *     if ($request->hasFile('foto')) {
 *         $produk->foto = ImageOptimizer::simpanSebagaiWebp($request->file('foto'), 'produk');
 *     }
 */
class ImageOptimizer
{
    /**
     * Konversi & simpan file upload sebagai .webp di storage disk 'public'.
     *
     * @param  UploadedFile  $file       File yang diupload (dari $request->file(...))
     * @param  string        $folder     Sub-folder di storage/app/public, mis. 'produk' atau 'kategori'
     * @param  int           $kualitas   0-100, makin kecil makin ringan tapi makin turun kualitasnya (default 80, umumnya sudah bagus)
     * @param  int|null      $lebarMaks  Lebar maksimum piksel; gambar yang lebih lebar akan diperkecil proporsional. null = tidak di-resize.
     * @return string        Path relatif untuk disimpan di kolom database, mis. "produk/9f2c....webp"
     */
    public static function simpanSebagaiWebp(UploadedFile $file, string $folder, int $kualitas = 80, ?int $lebarMaks = 1600): string
    {
        if (!function_exists('imagewebp')) {
            throw new \RuntimeException(
                'Ekstensi GD di server ini belum mendukung WebP. Cek dengan phpinfo() -> cari "WebP Support".'
            );
        }

        $folder = trim($folder, '/');
        $namaFile = Str::uuid() . '.webp';
        $pathRelatif = $folder . '/' . $namaFile;
        $pathAbsolut = storage_path('app/public/' . $pathRelatif);

        if (!is_dir(dirname($pathAbsolut))) {
            mkdir(dirname($pathAbsolut), 0755, true);
        }

        $sumber = self::resizeJikaPerlu(self::buatResourceDariFile($file), $lebarMaks);

        imagewebp($sumber, $pathAbsolut, $kualitas);
        imagedestroy($sumber);

        return $pathRelatif;
    }

    /**
     * Konversi file yang SUDAH ADA di storage disk 'public' (bukan upload baru) menjadi WebP.
     * Dipakai untuk membereskan foto lama yang masih .jpg/.png.
     *
     * - Kalau file sudah .webp, atau filenya tidak ditemukan, atau gagal dibaca: return null (tidak ada perubahan).
     * - Kalau berhasil: file baru dibuat, path relatif barunya dikembalikan. File lama TIDAK dihapus
     *   di sini — silakan hapus sendiri di pemanggil setelah yakin path baru berhasil disimpan ke database.
     *
     * @param  string    $pathRelatif  Path relatif lama, mis. "produk/abc123.jpg"
     * @param  string    $folder       Folder tujuan untuk file baru, mis. "produk" atau "kategori"
     * @return string|null             Path relatif baru (.webp), atau null kalau tidak perlu/tidak bisa dikonversi
     */
    public static function konversiFileTersimpan(string $pathRelatif, string $folder, int $kualitas = 80, ?int $lebarMaks = 1600): ?string
    {
        if (!function_exists('imagewebp') || Str::endsWith(strtolower($pathRelatif), '.webp')) {
            return null;
        }

        $pathAbsolutLama = storage_path('app/public/' . ltrim($pathRelatif, '/'));

        if (!is_file($pathAbsolutLama)) {
            return null;
        }

        try {
            $gambar = self::buatResourceDariPath($pathAbsolutLama);
        } catch (\RuntimeException) {
            return null; // bukan gambar yang didukung, biarkan file lama apa adanya
        }

        $sumber = self::resizeJikaPerlu($gambar, $lebarMaks);

        $folder = trim($folder, '/');
        $namaBaru = Str::uuid() . '.webp';
        $pathRelatifBaru = $folder . '/' . $namaBaru;
        $pathAbsolutBaru = storage_path('app/public/' . $pathRelatifBaru);

        if (!is_dir(dirname($pathAbsolutBaru))) {
            mkdir(dirname($pathAbsolutBaru), 0755, true);
        }

        imagewebp($sumber, $pathAbsolutBaru, $kualitas);
        imagedestroy($sumber);

        return $pathRelatifBaru;
    }

    /**
     * Hapus file di storage disk 'public', dipakai saat mengganti/membereskan foto lama.
     */
    public static function hapus(?string $pathRelatif): void
    {
        if (!$pathRelatif) {
            return;
        }

        $pathAbsolut = storage_path('app/public/' . ltrim($pathRelatif, '/'));

        if (is_file($pathAbsolut)) {
            unlink($pathAbsolut);
        }
    }

    /**
     * Perkecil resource gambar GD proporsional kalau lebarnya melebihi $lebarMaks.
     * Mengembalikan resource yang sama kalau tidak perlu di-resize.
     */
    private static function resizeJikaPerlu($sumber, ?int $lebarMaks)
    {
        $lebarAsli = imagesx($sumber);
        $tinggiAsli = imagesy($sumber);

        if (!$lebarMaks || $lebarAsli <= $lebarMaks) {
            return $sumber;
        }

        $tinggiBaru = (int) round($tinggiAsli * ($lebarMaks / $lebarAsli));
        $tujuan = imagecreatetruecolor($lebarMaks, $tinggiBaru);

        // Jaga transparansi (penting untuk PNG/WebP berlatar transparan, mis. logo).
        imagealphablending($tujuan, false);
        imagesavealpha($tujuan, true);
        $transparan = imagecolorallocatealpha($tujuan, 0, 0, 0, 127);
        imagefill($tujuan, 0, 0, $transparan);

        imagecopyresampled($tujuan, $sumber, 0, 0, 0, 0, $lebarMaks, $tinggiBaru, $lebarAsli, $tinggiAsli);
        imagedestroy($sumber);

        return $tujuan;
    }

    private static function buatResourceDariFile(UploadedFile $file)
    {
        return self::buatResourceDariPath($file->getRealPath());
    }

    /**
     * Buat resource GD dari sebuah path, membaca tipe gambar dari ISI file
     * (bukan dari ekstensi nama file atau mime yang "dikata" browser, yang bisa salah/dipalsukan).
     * Kalau filenya ternyata bukan gambar yang didukung GD (mis. HEIC/AVIF foto iPhone
     * yang cuma diganti nama jadi .jpg), lempar pesan yang jelas supaya bisa ditangkap di controller.
     */
    private static function buatResourceDariPath(string $path)
    {
        $tipe = @exif_imagetype($path);

        // @ supaya warning internal GD tidak ikut dijadikan exception oleh Laravel;
        // kegagalan tetap kita deteksi sendiri lewat ($gambar === false) di bawah.
        $gambar = match ($tipe) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_BMP  => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : false,
            default => false,
        };

        if ($gambar === false) {
            throw new \RuntimeException(
                'File ini bukan gambar yang didukung (yang bisa dipakai: JPG, PNG, GIF, atau WebP). '
                . 'Kalau ini foto dari iPhone (format HEIC) atau format AVIF, konversi dulu ke JPG/PNG sebelum diupload.'
            );
        }

        // JPEG dari HP menyimpan arah putar di EXIF; GD membuang EXIF saat disimpan,
        // jadi putar dulu supaya hasil WebP tidak miring.
        if ($tipe === IMAGETYPE_JPEG) {
            $gambar = self::perbaikiOrientasi($gambar, $path);
        }

        // PNG-8/GIF (palet) tidak bisa disimpan sebagai WebP -> ubah ke truecolor,
        // dan pertahankan transparansi.
        if (!imageistruecolor($gambar)) {
            imagepalettetopruecolor($gambar);
        }
        imagealphablending($gambar, false);
        imagesavealpha($gambar, true);

        return $gambar;
    }

    private static function perbaikiOrientasi($gambar, string $path)
    {
        if (!function_exists('exif_read_data')) {
            return $gambar;
        }

        $exif = @exif_read_data($path);
        $sudut = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($sudut === 0) {
            return $gambar;
        }

        $putar = imagerotate($gambar, $sudut, 0);
        if ($putar === false) {
            return $gambar;
        }

        imagedestroy($gambar);

        return $putar;
    }
}