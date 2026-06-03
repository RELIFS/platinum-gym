# Platinum Gym Padang

Website Company Profile dan Sistem Informasi Layanan Platinum Gym Padang berbasis Laravel 12.

## Deskripsi Proyek

Platinum Gym Padang adalah aplikasi web untuk membantu pengelolaan informasi layanan gym, registrasi member, autentikasi pengguna, dan dasar pengembangan sistem operasional gym.

Dokumentasi proyek disusun agar aplikasi mudah dipasang, diuji, dipelihara, dikembangkan, dan digunakan sebagai dasar kolaborasi tim.

## Status Saat Ini

| Area | Status |
|---|---|
| Auth, role, permission, Google OAuth | Selesai fase foundation |
| Public website company profile | Selesai fase public + polish responsive |
| Clean architecture foundation | Selesai tahap awal berbasis `app/Features` |
| Dashboard member/admin/owner | Placeholder untuk validasi auth dan role |
| Payment, booking, QR, laporan, AI backend | Belum dikerjakan |

## Tujuan Proyek

- Menyediakan company profile digital untuk Platinum Gym Padang.
- Menyediakan fondasi autentikasi untuk member, admin, dan owner.
- Menyediakan halaman public untuk layanan, jadwal kelas, produk, galeri, lokasi, dan BMI.
- Menyiapkan struktur aplikasi untuk fitur membership, booking, pembayaran, check-in, dan laporan.
- Mendokumentasikan proses konstruksi dan evolusi perangkat lunak secara bertahap.

## Masalah Yang Diselesaikan

- Informasi layanan gym belum terdokumentasi dalam satu sistem web.
- Registrasi member perlu dibuat lebih terstruktur.
- Hak akses pengguna perlu dipisahkan berdasarkan role.
- Perubahan dependency, fitur, dan refactoring perlu dicatat agar evolusi proyek mudah ditelusuri.

## Target Pengguna

- Pengunjung website yang ingin melihat informasi Platinum Gym.
- Member yang melakukan registrasi dan login.
- Admin yang mengelola data operasional gym.
- Owner yang memantau laporan dan perkembangan bisnis.
- Developer yang mengembangkan dan memelihara aplikasi.

## Fitur Utama

### Fitur Sudah Tersedia

- Registrasi member dengan data profil awal.
- Login dan logout pengguna.
- Login/register Google untuk member.
- Verifikasi email setelah registrasi.
- Pengiriman ulang email verifikasi.
- Proteksi dashboard menggunakan middleware `auth` dan `verified`.
- Role `member`, `admin`, dan `owner` menggunakan Spatie Laravel Permission.
- Redirect dashboard berdasarkan role.
- Policy dasar untuk membatasi akses data milik member sendiri.
- Website public Blade untuk Beranda, Tentang Kami, Layanan, Kelas, Produk, Galeri, Lokasi, dan BMI.
- Filter jadwal kelas berbasis query string.
- Filter dan pencarian produk berbasis query string.
- Chatbot public statis dengan quick replies dan eskalasi WhatsApp.
- Google Maps iframe embed tanpa API key pada halaman Lokasi.
- Seeder kontak public, promo, testimoni, dan galeri.
- Tampilan autentikasi bertema Platinum Gym.
- Toggle tema dark/light mengikuti preferensi perangkat dan pilihan pengguna.
- Struktur feature-based awal untuk Auth, PublicWebsite, dan Shared support.
- Testing fitur autentikasi menggunakan Pest.
- Testing fitur public website menggunakan Pest.

### Fitur Rencana Pengembangan

- Dashboard member, admin, dan owner.
- Manajemen paket membership.
- Booking kelas.
- Pembayaran membership dan layanan.
- Check-in gym.
- Laporan owner.
- Upload media konten website.
- Audit log aktivitas sistem.

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL/MariaDB
- Laravel Breeze
- Spatie Laravel Permission
- Spatie Laravel MediaLibrary
- Spatie Laravel Activitylog
- Laravel Socialite
- Pest PHP
- Blade
- Tailwind CSS
- Alpine.js
- Vite
- Composer
- NPM
- Git dan GitHub

## Instalasi Singkat

```bash
git clone <url-repository>
cd platinum-gym
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm.cmd run build
php artisan serve
```

Catatan Windows PowerShell: jika `npm` atau `npx` diblokir karena execution policy, gunakan `npm.cmd` dan `npx.cmd`.

Dokumentasi instalasi lengkap tersedia di `docs/installation.md`.

## Menjalankan Test

```bash
php artisan test --no-ansi
```

Project menggunakan Pest PHP. Pest berjalan di atas ekosistem PHPUnit, sehingga tetap kompatibel dengan testing Laravel.

Baseline validasi terakhir pada fase public/auth polish: `76 passed / 356 assertions` dan `npm.cmd run build` berhasil.

## Build Asset Frontend

```bash
npm.cmd run build
```

Untuk development frontend:

```bash
npm.cmd run dev
```

## Struktur Dokumentasi

```text
README.md
CHANGELOG.md
dependency-package.md

docs/
|-- installation.md
|-- features.md
|-- dependency.md
|-- refactoring.md
|-- public-responsive-audit.md
`-- github-actions.md
```

## Screenshot Proyek

Screenshot aplikasi akan ditambahkan setelah halaman siap digunakan dan telah diverifikasi.

Rencana screenshot minimal:

- Halaman beranda public.
- Halaman layanan dan kelas.
- Halaman login.
- Halaman registrasi member.
- Halaman verifikasi email.
- Dashboard member.
- Dashboard admin atau owner.

## Documentation

| Dokumen | Deskripsi |
|---|---|
| `docs/installation.md` | Panduan instalasi lokal dan troubleshooting |
| `docs/features.md` | Dokumentasi fitur aplikasi |
| `docs/dependency.md` | Dokumentasi dependency backend dan frontend |
| `docs/refactoring.md` | Catatan refactoring dan perbaikan struktur kode |
| `docs/public-responsive-audit.md` | Audit responsive dan smoke test public website |
| `docs/github-actions.md` | Rencana workflow CI/CD |
| `CHANGELOG.md` | Riwayat perubahan proyek |

Catatan: folder `platinumgym-figma/` adalah workspace referensi lokal/private dan sudah masuk `.gitignore`, sehingga tidak ikut repository production Laravel.

## Tim Pengembang

| Nama | NIM | Peran Proyek |
|---|---|---|
| Muhammad Luthfi | 2411083023 | Project Manager |
| Rossi Firmanda | 2411081039 | System Analyst |
| Sulthaan Dzakii Alfitri | 2411082032 | Lead Programmer |
| Muhammad Raffi | 2411082039 | AI Specialist |
| Faiz Altamis Akhyar | 2311083016 | Quality Assurance |

## Repository

Repository ini digunakan sebagai pusat kolaborasi kode, dokumentasi teknis, dan riwayat perubahan proyek.
