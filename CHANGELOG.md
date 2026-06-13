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

### Added

- Menambahkan public website Blade untuk Beranda, Tentang Kami, Layanan, Kelas, Produk, Galeri, Lokasi, dan BMI.
- Menambahkan `PublicWebsiteController` untuk query data public dari database dengan whitelist setting public.
- Menambahkan layout public dengan header sticky, navigasi mobile, footer, CTA, SEO dasar, dan tema dark/gold.
- Menambahkan filter jadwal kelas berbasis query string `hari` dan `jenis`.
- Menambahkan filter kategori dan pencarian produk berbasis query string `kategori` dan `q`.
- Menambahkan foto produk optimized WebP, field `image_path`/`image_alt`, stok aktual, dan copy pembelian langsung di lokasi pada katalog produk public.
- Menambahkan visual auth desktop berbasis foto gym asli dengan form panel responsive untuk login/register dan halaman auth terkait.
- Menambahkan kalkulator BMI client-side menggunakan Alpine tanpa penyimpanan data.
- Menambahkan Gymmi public statis dengan quick replies, typing state, fallback, dan eskalasi WhatsApp.
- Menambahkan Google Maps iframe embed tanpa API key melalui setting public `maps_embed_url`.
- Menambahkan `PromoSeeder`, `TestimonialSeeder`, dan `GallerySeeder` untuk konten public.
- Menambahkan kontak public final ke `SettingSeeder`.
- Menambahkan asset brand lokal, favicon, web manifest, gallery images, dan Open Graph image public.
- Menambahkan `PublicWebsiteTest` untuk route public, CTA, data seeder, filter, kontak, dan proteksi setting sensitif.
- Menambahkan `PublicImagePerformanceTest` untuk memastikan asset public tetap berada dalam budget performa.
- Menambahkan coverage test untuk scope produk katalog/stok/lokasi dan budget performa gambar produk.
- Menambahkan workflow GitHub Actions CI lokal untuk Composer, Pint, Vite build, dan Pest test.
- Menambahkan screenshot evidence public home desktop dan katalog produk mobile pada workspace konteks private.
- Menambahkan admin portal v1 read-only berbasis Blade/Tailwind untuk dashboard, check-in, booking, notifikasi, anggota, paket, kelas, pembayaran, produk, galeri, testimoni, promo, trainer, laporan, audit log, pengaturan, dan profil admin.
- Menambahkan admin workbench dengan status strip, KPI ringkas, quick links, grouped sidebar, dan label `Read-only v1`.
- Menambahkan tabel admin read-only dengan local search, status filter, count, empty/no-result state, dan mobile card fallback.
- Menambahkan `AdminPortalController`, `AdminDashboardQuery`, `AdminLayout`, partial tabel admin, dan `AdminPortalTest`.

### Refactor

- Menambahkan struktur `app/Features` sebagai foundation clean architecture pragmatis.
- Memindahkan workflow register member, complete profile, dan Google OAuth ke Action class.
- Memindahkan validasi register dan complete profile ke FormRequest.
- Memindahkan query/list/filter public website ke Query class.
- Memusatkan normalisasi nomor Indonesia pada `NormalizeIndonesianPhone`.
- Memindahkan logic chatbot public/member ke `resources/js/public-chatbot.js` dan mempertahankan export lama untuk kompatibilitas.
- Menambahkan komponen dashboard dan UI Blade reusable untuk app shell internal.

### Changed

- Mengganti route `/` dari Laravel default welcome menjadi halaman beranda public Platinum Gym Padang.
- Merebrand auth/app shell agar memakai direct official logo, theme toggle, dan layout Platinum Gym yang konsisten.
- Mengubah identitas chatbot public dan member menjadi Gymmi.
- Mengganti legal pages agar memakai public layout.
- Menghapus view/test default Laravel yang tidak dipakai.
- Menghapus scaffold kosong, komponen Blade default yang tidak dipakai, asset brand lama yang tidak direferensikan, dan dokumen tambahan root yang sudah dipindahkan ke arsip konteks.

### Fixed / Polish

- Memperbaiki responsive public website pada mobile kecil, landscape, tablet, desktop, dan wide desktop.
- Memperbaiki tap target, focus-visible state, mobile nav scroll containment, dynamic text wrapping, dan chatbot focus behavior.
- Memadatkan hero beranda mobile agar tidak terlalu menonjolkan Muaythai dan tetap memakai visual gym/strength umum sebagai visual utama.
- Memperbaiki fallback link WhatsApp chatbot agar tetap tersedia sebagai `href` statis saat JavaScript belum berjalan.
- Memperbaiki katalog paket member agar paket sesi yang sudah habis/kedaluwarsa milik member tidak muncul kembali sebagai paket tersedia.
- Memoles layout Gymmi agar user bubble berada di kanan tanpa avatar `AN`, bot memakai initial `GY`, FAQ tampil sebagai chip kanan, typing guard aktif, dan label a11y lebih jelas.
- Memperbaiki action `QR Member` pada Gymmi member agar menuju `/member/qr`.

### Testing

- `php artisan test --no-ansi` lulus dengan 144 test dan 835 assertion.
- `npm.cmd run build` berhasil membuat asset Vite production.
- `vendor\bin\pint --test` lulus.
- `composer validate --no-check-publish --no-ansi` valid.

### Planned

- Owner dashboard dan workflow CRUD admin penuh.
- Manajemen paket membership.
- Booking kelas.
- Pembayaran layanan.
- Check-in gym.
- Laporan owner.
- Penyempurnaan dokumentasi refactoring.
- Status badge dan screenshot sukses GitHub Actions setelah workflow berjalan di GitHub.
