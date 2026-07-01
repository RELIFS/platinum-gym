# Platinum Gym Padang

Website Company Profile dan Sistem Informasi Layanan Platinum Gym Padang berbasis Laravel 12.

## Deskripsi Proyek

Platinum Gym Padang adalah aplikasi web untuk membantu pengelolaan informasi layanan gym, registrasi member, pembayaran, booking kelas, check-in, dan pekerjaan operasional admin.

Dokumentasi proyek disusun agar aplikasi mudah dipasang, diuji, dipelihara, dikembangkan, dan digunakan sebagai dasar kolaborasi tim.

## Status Saat Ini

| Area | Status |
|---|---|
| Auth, role, permission, Google OAuth | Operasional dengan UI auth Platinum Gym, kode verifikasi email, undangan akun member dari admin, Google onboarding, dan role redirect |
| Public website company profile | Operasional dengan halaman informasi, katalog produk stok aktual, real image assets, dan Gymmi public |
| Clean architecture foundation | Aktif berbasis [`app/Features`](app/Features), Action, Query, FormRequest, ViewModel, dan component Blade |
| Member portal | Operasional: profil editable dengan bukti mahasiswa, checkout membership/paket sesi, booking/cancel, transaksi, QR, notifikasi, sidebar minimal, server-side pagination/filter, dan Gymmi global |
| Admin portal | Production custom Blade dengan route `/admin`, CRUD master data, payment cash/approve/reject, booking create/confirm/cancel, QR-camera preview-confirm check-in, notifikasi aktivitas member, settings whitelist, audit filter, report CSV, dan masked secrets |
| Owner portal | Operasional read-only untuk dashboard bisnis, laporan web, export CSV, dan invoice transaksi berbasis data pembayaran terkonfirmasi |
| Payment, email, QR, Gymmi AI | Operasional memakai Midtrans Sandbox, Resend, QR visual/check-in, dan Gymmi hybrid RAG Gemini dengan fallback lokal natural |
| Invoice PDF/Excel, struk, upload bukti bayar, refund/correction workflow | Invoice PDF/Excel dan struk sudah tersedia; upload bukti bayar dan refund/correction workflow menjadi rencana berikutnya |

## Tujuan Proyek

- Menyediakan company profile digital untuk Platinum Gym Padang.
- Menyediakan autentikasi dan otorisasi untuk member, admin, dan owner.
- Menyediakan halaman public untuk layanan, jadwal kelas, produk, galeri, lokasi, dan BMI.
- Menyediakan flow membership, booking, pembayaran, notifikasi, check-in, CRUD master data, laporan operasional admin, dan monitoring bisnis owner.
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
- Verifikasi email memakai kode 6 digit dan signed-link fallback.
- Undangan akun member dari admin untuk mengatur password awal secara mandiri.
- Pengiriman ulang email verifikasi.
- Proteksi dashboard menggunakan middleware `auth` dan `verified`.
- Role `member`, `admin`, dan `owner` menggunakan Spatie Laravel Permission.
- Redirect dashboard berdasarkan role.
- Policy dan guard untuk membatasi akses data milik member sendiri.
- Website public Blade untuk Beranda, Tentang Kami, Layanan, Kelas, Produk, Galeri, Lokasi, dan BMI.
- Filter jadwal kelas berbasis query string.
- Katalog produk dengan foto/fallback, harga, stok aktual dari database, filter, pencarian, dan arahan pembelian langsung di lokasi.
- Gymmi public/member hybrid RAG dengan endpoint Gemini, dataset terkompilasi, database live aman, fallback lokal natural, quick replies, dan guardrail data.
- Google Maps iframe embed tanpa API key pada halaman Lokasi.
- Seeder kontak public, promo, testimoni, galeri, produk, dan foto produk optimized.
- Tampilan autentikasi bertema Platinum Gym dengan panel visual foto gym pada desktop dan form panel responsive.
- Portal member untuk dashboard, edit profil member dengan upload bukti mahasiswa, keamanan akun, checkout membership/paket sesi dengan eligibility profil lengkap, booking/cancel kelas, riwayat booking, transaksi/detail/pay, QR visual, notifikasi, server-side pagination/filter, dan chatbot global Gymmi.
- Admin portal custom Blade untuk dashboard, CRUD anggota/paket/kelas/jadwal/produk/konten/trainer, payment cash/approve/reject, booking create/confirm/cancel, check-in QR-camera preview-confirm, notifikasi, laporan CSV/Excel/PDF, audit log filter, pengaturan whitelist, tabel server-side pagination, invoice/struk, dan profil admin dengan upload foto.
- Owner portal read-only untuk ringkasan bisnis, tren pendapatan, laporan keuangan/member/booking-kelas, export CSV/Excel/PDF, tampilan invoice web, struk, dan upload foto profil owner.
- Toggle tema dark/light mengikuti preferensi perangkat dan pilihan pengguna.
- Struktur feature-based untuk Auth, PublicWebsite, MemberPortal, Admin, OwnerPortal, Reports, Invoices, Payments, Bookings, CheckIns, Gymmi, dan Shared support.
- Testing fitur autentikasi, public website, member portal, admin portal, owner portal, invoice, pembayaran, booking, check-in, authorization, dan Gymmi menggunakan Pest.

### Fitur Rencana Pengembangan

- Upload bukti pembayaran manual bila diperlukan.
- Refund/correction workflow untuk pembayaran operasional.
- Export queue untuk dataset besar jika laporan mulai berat.
- Upload media konten website.

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL/MariaDB
- Laravel Breeze
- Spatie Laravel Permission
- Spatie Laravel MediaLibrary
- Spatie Laravel Activitylog
- Laravel Socialite
- Resend
- Midtrans Sandbox
- Simple QRCode
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

Dokumentasi instalasi lengkap tersedia di [`docs/installation.md`](docs/installation.md).

## Akses Admin Lokal

Admin saat ini login melalui halaman umum:

```text
/login
```

Jika akun memiliki role `admin`, sistem mengarahkan user ke:

```text
/admin
```

Akun seeded untuk local/development:

```text
admin@platinumgympadang.com / password
```

Password seeded hanya untuk local/development. Ganti atau nonaktifkan akun tersebut sebelum production.

## Menjalankan Test

```bash
php artisan test --no-ansi
```

Project menggunakan Pest PHP. Pest berjalan di atas ekosistem PHPUnit, sehingga tetap kompatibel dengan testing Laravel.

Baseline validasi terakhir pada 23 Juni 2026: `php artisan test --no-ansi` lulus dengan `579 passed / 3813 assertions`, `vendor\bin\pint --test` lulus, `npm.cmd run build` lulus, dan `git diff --check` lulus dengan peringatan line-ending Git. Jika melakukan perubahan baru, jalankan ulang semua command tersebut sebelum push.

Catatan: konfigurasi [`phpunit.xml`](phpunit.xml) memakai SQLite in-memory untuk testing. Pastikan PHP CLI memiliki extension `pdo_sqlite` aktif sebelum menjalankan full test suite lokal.

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

docs/
|-- installation.md
|-- features.md
|-- dependency.md
|-- refactoring.md
|-- github-actions.md
`-- screenshot/
    |-- README.md
    |-- public/
    |-- auth/
    |-- member/
    |-- admin/
    |-- owner/
    `-- detail/
```

## Screenshot Proyek

Screenshot fitur per halaman disimpan di [`docs/screenshot/`](docs/screenshot/). Index galeri tersedia di [`docs/screenshot/README.md`](docs/screenshot/README.md) dan mencakup public website, auth, member portal, admin portal, owner portal, serta beberapa halaman detail representatif.

Screenshot dibuat pada viewport desktop 1440px dengan data lokal aman. Area login-protected dimasking untuk email akun, dan screenshot tidak boleh menampilkan `.env`, password, API key, OAuth token, raw QR token, atau raw payment payload.

## Documentation

| Dokumen | Deskripsi |
|---|---|
| [`docs/installation.md`](docs/installation.md) | Panduan instalasi lokal dan troubleshooting |
| [`docs/features.md`](docs/features.md) | Dokumentasi fitur aplikasi |
| [`docs/dependency.md`](docs/dependency.md) | Dokumentasi dependency backend dan frontend |
| [`docs/refactoring.md`](docs/refactoring.md) | Catatan refactoring dan perbaikan struktur kode |
| [`docs/github-actions.md`](docs/github-actions.md) | Dokumentasi workflow CI/CD |
| [`docs/screenshot/README.md`](docs/screenshot/README.md) | Index screenshot fitur per halaman |
| [`CHANGELOG.md`](CHANGELOG.md) | Riwayat perubahan proyek |

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
