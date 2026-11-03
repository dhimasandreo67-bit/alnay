1. Yang Perlu Diinstal Dulu

Sebelum mulai, pastikan komputer sudah punya:

| Software | Versi | Cek dengan |
|----------|-------|------------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 20+ | `node -v` |
| npm | 10+ | `npm -v` |
| Git | 2.x | `git --version` |
| XAMPP / MySQL | (apa saja) | buka phpMyAdmin |

**Kalau ada yang belum terpasang**, install dulu. Laravel 13 butuh PHP minimal 8.2.

2. Clone Repo<br>

Buka terminal (PowerShell / CMD / Bash), pindah ke folder kerja, lalu:<br>
git clone https://github.com/dhimasandreo67-bit/alnay.git<br>
cd alnay<br>
<br>
3. Install Dependency<br>
composer install<br>
npm install<br>
<br>
4. Setup File .env<br>
APP_NAME=ALNAY.ID<br>
APP_URL=http://127.0.0.1:8001<br>
<br>
DB_CONNECTION=mysql<br>
DB_HOST=127.0.0.1<br>
DB_PORT=3306<br>
DB_DATABASE=alnay<br>
DB_USERNAME=root<br>
DB_PASSWORD=<br>
<br>
5.Buat Database di MySQL<br>
Nama database: alnay (atau terserah, catat namanya)<br>
Collation: utf8mb4_unicode_ciySQL<br>
<br>
6.Jalankan Project<br>
php artisan serve --port=8001<br>
<br>
7.Alur Kerja Git <br>
git checkout develop<br>
git pull origin develop<br>
git checkout -b fitur/nama-tugas-anda<br><br>
(contoh)<br>
fitur/auth-login<br>

fitur/katalog-produk<br>
fitur/admin-dashboard<br><br>
kalau sudah<br>
git add .<br>
git commit -m "feat: deskripsi singkat"<br>
git push -u origin fitur/nama-tugas-anda<br><br>

GOOGLE_CLIENT_ID=388401030455-n39vooe3p3up0p79m2m28dmlv3s312su.apps.googleusercontent.com<br>
GOOGLE_CLIENT_SECRET=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<br>
GOOGLE_REDIRECT_URI=http://127.0.0.1:8001/auth/google/callback<br><br>

# Komerce (bukan RajaOngkir)<br>
RAJAONGKIR_API_KEY=XNpBewL1cd95f5152dba5971hYdR5ZnI<br>
RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api<br>
RAJAONGKIR_ORIGIN=73924<br>
RAJAONGKIR_ORIGIN_LABEL="Kemiling Raya, Bandar Lampung"<br>


