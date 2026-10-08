# MySchool

![Tests](https://github.com/MuhamadAryadilah/myschool/actions/workflows/tests.yml/badge.svg)

Aplikasi web sekolah berbasis Laravel: pendaftaran siswa baru online, tagihan dan pembayaran dengan unggah bukti transfer, serta pengelolaan berita dan ekstrakurikuler.

## Fitur

**Calon orang tua dan orang tua**
- Formulir pendaftaran siswa baru (`/pendaftaran`) dengan batas usia otomatis dan alur setelah mendaftar
- Dashboard tagihan anak, cara bayar (rekening dan QRIS), dan unggah bukti transfer (JPG, PNG, atau PDF, maksimal 5 MB)
- Riwayat pembayaran dan unduh kuitansi untuk tagihan lunas

**Admin**
- Kelola pendaftaran, siswa, orang tua, tagihan, dan pembayaran
- Verifikasi bukti bayar (terima atau tolak dengan alasan) dan notifikasi email saat ada bukti baru
- Kelola berita dan ekstrakurikuler
- Pengaturan pendaftaran: batas usia, tanggal acuan usia, dan langkah-langkah alur pendaftaran

**Keamanan**
- Nominal pembayaran selalu diambil dari tagihan di server, bukan dari kiriman form
- Bukti bayar disimpan di storage privat dan hanya bisa dilihat admin dan orang tua pemilik tagihan

## Teknologi

- PHP dan Laravel (versi tepatnya ada di `composer.json`)
- MySQL
- Vite, Tailwind CSS, dan Alpine.js
- Pest untuk pengujian

## Menjalankan di komputer lokal

Kebutuhan: PHP, Composer, Node.js, dan MySQL (contoh: Laravel Herd).

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Atur koneksi database (`DB_*`) di `.env`, lalu:

```bash
php artisan migrate
php artisan storage:link
npm run dev
```

Untuk membuat akun admin pertama, jalankan `php artisan tinker` lalu buat user dengan `role` bernilai `admin`.

## Konfigurasi pembayaran

Rekening bank, nama pemilik rekening, dan gambar QRIS diatur di `config/payment.php`. Gambar QRIS diletakkan di folder `public` dan namanya diisi di pengaturan tersebut.

## Pengujian

```bash
php artisan test
```

## Deploy ke produksi

1. Atur `.env` di server:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-sekolah
```

2. Pastikan PHP mengizinkan unggah file minimal 8 MB (`upload_max_filesize` dan `post_max_size`).
3. Jalankan:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Catatan

- Jangan pernah meng-commit file `.env`, cadangan database (`*.sql`), atau bukti bayar.
- Repo ini berisi data dan pengaturan sekolah, jadi sebaiknya tetap private.