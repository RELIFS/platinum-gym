# Changelog

Semua perubahan penting pada proyek Platinum Gym Padang dicatat pada dokumen ini.

Format mengikuti prinsip changelog sederhana: `Added`, `Changed`, `Fixed`, `Dependency`, `Refactor`, dan `Testing`.

## [0.1.0] - Auth Foundation

### Added

- Menambahkan autentikasi dasar menggunakan Laravel Breeze berbasis Blade.
- Menambahkan registrasi member dengan field nama, tanggal lahir, jenis kelamin, nomor WhatsApp, email, password, dan persetujuan syarat layanan.
- Menambahkan normalisasi nomor WhatsApp Indonesia ke format `08xxxxxxxxxx`.
- Menambahkan pembuatan data member otomatis setelah registrasi.
- Menambahkan kode member otomatis dengan format `PG-YYYYMMDD-0001`.
- Menambahkan role `member` menggunakan Spatie Laravel Permission.
- Menambahkan verifikasi email setelah registrasi.
- Menambahkan halaman pemberitahuan verifikasi email.
- Menambahkan pengiriman ulang email verifikasi.
- Menambahkan proteksi dashboard dengan middleware `auth` dan `verified`.
- Menambahkan tampilan autentikasi bertema Platinum Gym.
- Menambahkan asset brand lokal berupa logo, favicon, dan apple touch icon.
- Menambahkan toggle tema dark/light pada layout autentikasi.

### Changed

- Mengganti tampilan default autentikasi Breeze menjadi layout custom Platinum Gym.
- Menggunakan asset lokal untuk brand aplikasi agar tidak bergantung pada sumber eksternal.
- Mengatur Tailwind agar mendukung dark mode berbasis class.

### Dependency

- Menggunakan Laravel Breeze untuk starter autentikasi.
- Menggunakan Spatie Laravel Permission untuk role pengguna.
- Menggunakan Laravel Socialite sebagai dependency rencana login/register Google.
- Menggunakan Spatie Laravel MediaLibrary sebagai dependency rencana upload dan manajemen media.
- Menggunakan Spatie Laravel Activitylog sebagai dependency rencana audit log.
- Menggunakan Pest PHP untuk automated testing.

### Refactor

- Memisahkan metadata brand ke partial `resources/views/partials/head-brand.blade.php`.
- Memusatkan layout autentikasi pada `resources/views/layouts/guest.blade.php`.
- Menyesuaikan component logo aplikasi agar memakai asset lokal.

### Testing

- Menambahkan test registrasi member.
- Menambahkan test validasi field registrasi member.
- Menambahkan test normalisasi nomor WhatsApp.
- Menambahkan test duplicate nomor WhatsApp.
- Menambahkan test verifikasi email.
- Menambahkan test proteksi dashboard untuk user belum verified.
- Menambahkan test akses dashboard untuk user verified.

## [Unreleased]

### Planned

- Dashboard berbeda untuk member, admin, dan owner.
- Login/register Google menggunakan Laravel Socialite.
- Manajemen paket membership.
- Booking kelas.
- Pembayaran layanan.
- Check-in gym.
- Laporan owner.
- Penyempurnaan dokumentasi refactoring.
- Implementasi dokumentasi dan workflow GitHub Actions.
