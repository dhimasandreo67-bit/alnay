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

2. Clone Repo

Buka terminal (PowerShell / CMD / Bash), pindah ke folder kerja, lalu:
git clone https://github.com/dhimasandreo67-bit/alnay.git
cd alnay

3. Install Dependency
composer install
npm install

4. Setup File .env
APP_NAME=ALNAY.ID
APP_URL=http://127.0.0.1:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alnay
DB_USERNAME=root
DB_PASSWORD=

5.Buat Database di MySQL
Nama database: alnay (atau terserah, catat namanya)
Collation: utf8mb4_unicode_ciySQL

6.Jalankan Project
php artisan serve --port=8001

7.Alur Kerja Git 
git checkout develop
git pull origin develop
git checkout -b fitur/nama-tugas-anda
(contoh)
fitur/auth-login
fitur/katalog-produk
fitur/admin-dashboard
kalau sudah
git add .
git commit -m "feat: deskripsi singkat"
git push -u origin fitur/nama-tugas-anda
