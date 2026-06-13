# Installation Documentation

Dokumen ini menjelaskan langkah instalasi proyek Platinum Gym Padang pada lingkungan lokal.

## Persyaratan Sistem

- PHP 8.2 atau lebih baru.
- Composer.
- Node.js dan NPM.
- MySQL atau MariaDB.
- Git.
- Web browser modern.
- Terminal atau PowerShell.

## Clone Repository

```bash
git clone <url-repository>
cd platinum-gym
```

Ganti `<url-repository>` dengan URL repository GitHub proyek.

## Install Dependency Backend

```bash
composer install
```

Perintah ini membaca `composer.json` dan memasang package PHP yang terkunci pada `composer.lock`.

## Install Dependency Frontend

```bash
npm install
```

Perintah ini membaca `package.json` dan memasang package frontend yang terkunci pada `package-lock.json`.

## Setup Environment

Salin file environment contoh:

```bash
cp .env.example .env
```

Pada Windows PowerShell, jika `cp` tidak tersedia, gunakan:

```powershell
Copy-Item .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

## Setup Database

Buat database MySQL atau MariaDB, misalnya:

```text
platinum_gym
```

Sesuaikan konfigurasi `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=platinum_gym
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

## Konfigurasi Integrasi Opsional

`.env.example` sudah menyediakan placeholder non-secret untuk Google OAuth, Midtrans, Gemini, mail, queue, dan session secure cookie. Isi hanya nilai yang dibutuhkan pada `.env` lokal atau production; jangan commit secret ke Git.

Untuk Google OAuth lokal, pastikan nilai berikut konsisten dengan Google Cloud Console:

```env
APP_URL=http://127.0.0.1:8000
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

## Build Asset Frontend

Untuk production build:

```bash
npm.cmd run build
```

Untuk development mode:

```bash
npm.cmd run dev
```

Catatan Windows PowerShell: jika `npm` diblokir karena execution policy, gunakan `npm.cmd`.

## Menjalankan Aplikasi

```bash
php artisan serve
```

Aplikasi akan berjalan pada:

```text
http://127.0.0.1:8000
```

## Menjalankan Test

```bash
php artisan test --no-ansi
```

Proyek menggunakan Pest PHP sebagai framework testing. Pest berjalan di atas PHPUnit dan tetap memakai ekosistem testing Laravel.

## Akun dan Role

Role yang disiapkan pada proyek:

- `member`
- `admin`
- `owner`

Saat registrasi member berhasil, sistem membuat user, membuat data member, memberikan role `member`, mengirim email verifikasi, lalu mengarahkan user ke halaman verifikasi email.

Akun seeded untuk local/development setelah `php artisan migrate --seed`:

| Role | Email | Password | Catatan |
|---|---|---|---|
| Admin | `admin@platinumgympadang.com` | `password` | Login lewat `/login`, lalu redirect ke `/admin` |
| Owner | `owner@platinumgympadang.com` | `password` | Login lewat `/login`, lalu redirect ke `/owner` |

Akun seeded hanya untuk local/development. Ganti password atau nonaktifkan akun tersebut sebelum production.

## Akses Admin Lokal

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Buka halaman login:

```text
/login
```

Masuk memakai akun admin seeded. Jika role valid, sistem mengarahkan user ke:

```text
/admin
```

Halaman khusus `/admin/login` belum dibuat pada v1 ini dan masuk roadmap production.

## Troubleshooting

### APP_KEY belum dibuat

Gejala:

```text
No application encryption key has been specified.
```

Solusi:

```bash
php artisan key:generate
```

### Database belum tersedia

Gejala:

```text
SQLSTATE[HY000] [1049] Unknown database
```

Solusi:

- Buat database sesuai nilai `DB_DATABASE`.
- Cek `DB_USERNAME` dan `DB_PASSWORD` pada `.env`.
- Jalankan ulang migration.

```bash
php artisan migrate --seed
```

### NPM diblokir PowerShell

Gejala:

```text
npm.ps1 cannot be loaded because running scripts is disabled on this system
```

Solusi:

```bash
npm.cmd install
npm.cmd run build
```

### Asset masih mengarah ke Vite dev server

Gejala:

- Halaman mencoba memuat asset dari `http://[::1]:5173`.
- File CSS atau JS tidak muncul saat mode production.

Solusi:

- Hentikan Vite dev server jika tidak dipakai.
- Hapus file `public/hot` jika masih ada.
- Jalankan build ulang.

```bash
npm.cmd run build
```

### Cache konfigurasi bermasalah

Solusi:

```bash
php artisan optimize:clear
```

### Permission storage bermasalah pada Linux/macOS

Solusi:

```bash
chmod -R 775 storage bootstrap/cache
```

Pada Windows, pastikan folder `storage` dan `bootstrap/cache` dapat ditulis oleh aplikasi.

## Verifikasi Instalasi

Instalasi dianggap berhasil jika:

- Halaman utama dapat dibuka.
- Halaman public `/`, `/tentang-kami`, `/layanan`, `/kelas`, `/produk`, `/galeri`, `/lokasi`, dan `/bmi` dapat dibuka.
- Halaman register dapat dibuka.
- User member dapat registrasi.
- Halaman pemberitahuan verifikasi email muncul setelah registrasi.
- Admin local dapat login lewat `/login` dan masuk ke `/admin`.
- Test berjalan tanpa gagal.
- Asset frontend berhasil dibuild.
