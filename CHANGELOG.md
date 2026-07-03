# Changelog

Semua perubahan penting pada proyek Platinum Gym Padang dicatat pada dokumen ini.

Format mengikuti prinsip changelog sederhana: `Added`, `Changed`, `Fixed`, `Dependency`, `Refactor`, dan `Testing`.

## [Unreleased] - Production Hardening, Gymmi, Member, Admin, Dan Public Polish

### Added

- Menambahkan kode verifikasi email 6 digit, undangan akun member dari admin, dan template email operasional branded untuk auth, pembayaran, dan booking.
- Menambahkan upload bukti mahasiswa pada profil member sebagai pengganti input NIM di UI member.
- Menambahkan inbox persetujuan admin untuk review bukti mahasiswa, termasuk route pratinjau file privat, aksi setujui/tolak, badge jumlah pengajuan, dan activity log.
- Menambahkan Gymmi hybrid RAG berbasis knowledge JSON terkompilasi, database live aman, key pool Gemini, dan command sync key yang tidak mencetak secret.
- Menambahkan normalisasi teks Gymmi dan knowledge override agar variasi pertanyaan layanan, harga, jadwal, produk, bukti mahasiswa, dan kontak lebih stabil.
- Menambahkan Flatpickr untuk tanggal booking kelas Member/Admin agar tanggal di luar hari jadwal tidak bisa dipilih di UI.
- Menambahkan relasi penggunaan sesi kelas ke booking terkonfirmasi agar satu booking hanya dapat dipakai satu kali pada alur check-in.

### Changed

- Menyamakan eligibility checkout semua paket agar membership, Muaythai, Poundfit, dan Personal Trainer wajib melewati profil dasar lengkap sebelum payment/session dibuat.
- Menyempurnakan booking kelas: label Instruktur/Pro/Coach, jadwal Muaythai per trainer, aturan booking H-1, aturan cancel H-3 jam, datepicker sesuai hari jadwal, dan card kelas yang disabled tanpa caption tambahan saat akses membership atau paket sesi belum sesuai.
- Memoles sidebar/admin/member/owner account menu, shortcut Website Utama, profil admin ringkas, pengaturan admin whitelist, home public responsif, copy halaman Tentang untuk tim pelatih, serta widget Gymmi public/member.
- Mengubah admin check-in paket sesi Muaythai/Poundfit agar wajib memakai booking kelas `confirmed` hari ini yang cocok, belum attended, dan belum pernah dipakai.
- Mengubah halaman admin notifikasi dari feed aktivitas pasif menjadi approval inbox untuk pekerjaan yang perlu diproses admin.
- Menghapus NIM dari tabel/form/review admin anggota; status mahasiswa diverifikasi melalui bukti mahasiswa yang disimpan privat.
- Memperbarui knowledge Gymmi dari workbook internal menjadi FAQ 137 dan Alias 1578 tanpa membaca Excel saat runtime.

### Testing

- Menambahkan dan memperbarui coverage Auth, Member, Admin, Public Website, Booking, Gymmi, dan command key sync.
- Validasi fresh 2026-07-02 sebelum commit/push: `git diff --check`, `composer validate --strict --no-check-publish`, `composer audit`, `npm.cmd audit --audit-level=moderate`, `npm.cmd audit --omit=dev --audit-level=moderate`, `vendor\bin\pint --test`, `npm.cmd run build`, `php artisan test --no-ansi` (`680 passed / 5370 assertions`), `php artisan route:list --except-vendor --no-ansi` (`113 routes`), `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, `php artisan optimize:clear`, dan validasi skill lokal lulus.

## [Unreleased] - Owner Portal, Reports, Invoice, Dan Portal Polish

### Added

- Menambahkan Owner portal read-only dengan layout khusus, dashboard bisnis, KPI, grafik pendapatan, breakdown sumber pendapatan, transaksi terbaru, dan membership yang akan berakhir.
- Menambahkan halaman laporan Owner untuk keuangan, member, dan booking kelas dengan filter periode/status/metode serta export CSV/Excel/PDF.
- Menambahkan halaman invoice web untuk member dan owner berdasarkan transaksi yang sudah ada tanpa membuat logic pembayaran baru.
- Menambahkan export laporan Admin dan Owner dalam format Excel `.xlsx` dan PDF.
- Menambahkan PDF invoice formal dan struk POS compact untuk member, owner, dan admin.
- Menambahkan halaman profil Owner dengan layout/copy Owner, bukan shell Admin.
- Menambahkan upload foto profil Admin dan Owner memakai kolom avatar user existing dengan validasi image dan cleanup file lokal aman.
- Menambahkan input tanggal lahir yang lebih ramah untuk register dan complete profile.
- Menambahkan dukungan profil member yang lebih lengkap, avatar, verifikasi profil, QR download, dan guard kelayakan checkout.
- Menambahkan alur pratinjau check-in QR admin sebelum confirm check-in atau pemakaian sesi.
- Menambahkan grafik tren aktivitas admin dan owner berbasis data real dengan renderer SVG lokal ringan.

### Changed

- Mengurutkan paket layanan publik dengan prioritas baru: Gym/Senam umum lebih dulu, paket mahasiswa setelahnya, Muaythai 1x lalu Muaythai Umum/Mahasiswa per jumlah sesi, diikuti Personal Trainer dan Session.
- Memoles `page-hero` public menjadi compact-premium dengan ukuran teks lebih stabil dan animasi masuk CSS-only yang menghormati reduced motion.
- Menyamakan warna Gymmi public dengan pola light/dark Gymmi member agar panel, input, quick reply, bubble, dan action link tidak lagi terkunci tema gelap.
- Mengubah halaman `/kelas` menjadi section Aerobic, Zumba, Muaythai, dan Poundfit dengan filter jenis yang tetap aman serta kompatibel dengan data kelas lama bertipe `senam`.
- Mengubah quick replies Gymmi public/member menjadi rail horizontal yang bisa discroll, keyboard-focusable, dan tidak membuat overflow halaman.
- Mengganti avatar bubble Gymmi dari teks `GY` menjadi asset gambar light/dark dari sumber data proyek, dengan fallback initial tetap tersedia.
- Memoles caption dan error validasi login/register agar memakai Bahasa Indonesia production yang aman, ramah user, dan tidak membocorkan detail akun.
- Mengubah lifecycle QR member menjadi stabil per member; pembelian membership baru tidak lagi mengganti QR yang sudah aktif.
- Merapikan tampilan dan microcopy portal admin agar lebih ringkas, profesional, dan tidak terlalu berulang.
- Merapikan tampilan portal member, filter, pagination, card, chatbot shell, dan state responsif.
- Menambahkan shortcut `Website Utama` sebagai item menu bawah pada sidebar/drawer member tanpa menjadikannya route aktif portal.
- Merapikan halaman BMI publik agar hasil IMT, segmented range, dan kategori tidak mengulang informasi yang sama.
- Merapikan light theme website publik agar elemen utilitas tidak memakai surface gelap yang terlalu dominan.
- Memperbarui dokumentasi root agar status Owner, Reports CSV, invoice web, dan dependency frontend sesuai implementasi terbaru.

### Fixed

- Memperbaiki sidebar Owner agar hanya satu item aktif per halaman dan filter/reset laporan tetap berada di halaman laporan yang sedang dibuka.
- Memperbaiki QR legacy yang memiliki `expires_at` agar dipakai ulang dan dibersihkan saat membership diperbarui.
- Memperkuat fallback, cache, dan circuit breaker Gymmi AI agar kegagalan provider tidak merusak pengalaman pengguna.
- Memperbaiki sumber kontak publik yang dipakai konteks Gymmi.

### Dependency

- Menambahkan `maatwebsite/excel` untuk export Excel laporan.
- Menambahkan `barryvdh/laravel-dompdf` untuk PDF laporan, invoice, dan struk.
- Menghapus `apexcharts`; grafik dashboard Admin dan Owner memakai renderer SVG lokal agar bundle lebih ringan.

### Testing

- Merapikan struktur test Feature dari file legacy root ke suite domain Admin, Member, Owner, PublicWebsite, Auth, Gymmi, dan Invoices.
- Menambahkan coverage untuk copy validasi login/register, error kredensial generic, throttle login, duplikat email/WhatsApp, tanggal lahir invalid, dan konfirmasi kata sandi.
- Menambahkan coverage untuk Owner portal, invoice document, ownership policy, auth tanggal lahir, member portal, admin portal, public website, dan Gymmi.
- Validasi terakhir 2026-06-23: `php artisan test --no-ansi` lulus dengan `579 passed / 3813 assertions`, `npm.cmd run build` lulus, `vendor\bin\pint --test` lulus, dan `git diff --check` lulus dengan peringatan line-ending Git.

## [Unreleased] - Admin Panel UI/UX & A11y Polish

### Added

- Menambahkan `AdminStatusViewModel` dan kelas pill semantik (`admin-status-success/warning/danger/info/neutral`) untuk status member, pembayaran, dan booking di dashboard serta panel operasional admin.
- Menambahkan tombol `admin-button-danger` dan dukungan opsi `variant=danger` plus `confirm` pada partial aksi tabel admin.
- Menambahkan konfirmasi (`confirm()`) sebelum menyetujui/menolak pembayaran, membatalkan booking, dan menyimpan pengaturan website.
- Menambahkan label aksesibel dan `aria-label` pada input alasan penolakan pembayaran.
- Menambahkan render `type=password` dengan toggle show/hide untuk field pengaturan sensitif (defensif).
- Menambahkan blok identitas admin (avatar inisial, nama, role) pada footer sidebar desktop dan drawer mobile.
- Menambahkan preview file existing, `aria-invalid`/`aria-describedby`, dan loading state pada form resource admin.
- Menambahkan ikon admin `search`, `download`, `check`, `x`, `warning`, `eye`, `eye-off`, dan mendesain ulang ikon `empty`; menambahkan ikon search pada input pencarian tabel.
- Menambahkan link `Ganti Password` pada grup sidebar `Akun` (mengarah ke Breeze `profile.edit`) dan CTA `Edit Akun Saya` pada halaman Profil Admin.

### Changed

- Badge pembayaran tertunda kini tampil di mobile dengan `aria-label` deskriptif.
- Mobile drawer admin mengikuti tema light/dark agar konsisten dengan sidebar desktop.
- `<main>` admin memakai `overflow-x-clip` untuk mencegah clipping elemen melayang.
- Tombol "Reset" pada tabel admin menjadi "Bersihkan Pencarian"; tombol "Terapkan" filter laporan/audit memakai variant primer.
- Export CSV laporan tersinkron dengan tanggal yang sedang dipilih melalui Alpine binding, dan filter tanggal memvalidasi `date_to >= date_from`.
- Catatan cash payment memakai `textarea`; field wajib pada form operasional admin diberi tanda `*`.

### Testing

- Menambahkan `tests/Feature/AdminPortalImprovementsTest.php` dengan 9 pengujian untuk status pill semantik, konfirmasi aksi destructif, label aksesibel, preview file, identitas sidebar, link ganti password, badge pembayaran, dan binding export laporan.

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
- Menambahkan Gymmi public/member dengan endpoint Gemini, fallback lokal, quick replies, typing state, logging conversation, dan guardrail data.
- Menambahkan Google Maps iframe embed tanpa API key melalui setting public `maps_embed_url`.
- Menambahkan `PromoSeeder`, `TestimonialSeeder`, dan `GallerySeeder` untuk konten public.
- Menambahkan kontak public final ke `SettingSeeder`.
- Menambahkan asset brand lokal, favicon, web manifest, gallery images, dan Open Graph image public.
- Menambahkan `PublicWebsiteTest` untuk route public, CTA, data seeder, filter, kontak, dan proteksi setting sensitif.
- Menambahkan `PublicImagePerformanceTest` untuk memastikan asset public tetap berada dalam budget performa.
- Menambahkan coverage test untuk scope produk katalog/stok/lokasi dan budget performa gambar produk.
- Menambahkan workflow GitHub Actions CI lokal untuk Composer, Pint, Vite build, dan Pest test.
- Menambahkan screenshot evidence public home desktop dan katalog produk mobile untuk pendukung QA visual.
- Menambahkan member portal operasional untuk edit profil, checkout membership/paket sesi, booking/cancel kelas, transaksi/detail/pay Midtrans, QR visual, notifikasi read/read-all, dan Gymmi global.
- Menambahkan admin portal operasional berbasis Blade/Tailwind untuk dashboard, check-in, booking, notifikasi, anggota, paket, kelas, pembayaran, produk, galeri, testimoni, promo, trainer, laporan, audit log, pengaturan, dan profil admin.
- Menambahkan admin workbench dengan status strip, KPI ringkas, quick links, grouped sidebar, payment approve/reject, booking confirm/cancel, QR check-in, toggle status/tayang, masked settings, dan tabel data real dengan search/filter/mobile cards.
- Menambahkan `AdminPortalController`, `AdminDashboardQuery`, `AdminLayout`, partial tabel admin, dan `AdminPortalTest`.
- Menambahkan custom Blade resource CRUD untuk anggota, paket, kelas, jadwal kelas, kategori produk, produk, galeri, testimoni, promo, dan trainer.
- Menambahkan payment cash admin, booking create admin, check-in manual, settings whitelist, audit filter, report CSV export, dan partial `admin.pages.operations`.
- Menambahkan `MemberProfileController`, `UpdateMemberProfileRequest`, dan `UpdateMemberProfileAction` untuk update profil member dari `/member/profil`.
- Menambahkan server-side pagination, search, dan filter status pada tabel modul admin yang datanya dapat bertambah.
- Menambahkan server-side pagination, search, dan filter pada katalog membership, jadwal booking, riwayat booking, transaksi, dan notifikasi member.
- Menambahkan workflow pembayaran Midtrans Sandbox, invoice, webhook idempotent, QR member, check-in admin, dan notifikasi operasional member.

### Refactor

- Menambahkan struktur `app/Features` sebagai foundation clean architecture pragmatis.
- Memindahkan workflow register member, complete profile, dan Google OAuth ke Action class.
- Memindahkan validasi register dan complete profile ke FormRequest.
- Memindahkan query/list/filter public website ke Query class.
- Memusatkan normalisasi nomor Indonesia pada `NormalizeIndonesianPhone`.
- Memindahkan logic chatbot public/member ke `resources/js/public-chatbot.js` dan mempertahankan export lama untuk kompatibilitas.
- Menambahkan komponen dashboard dan UI Blade reusable untuk app shell internal.
- Memusatkan status label member ke `MemberPortalStatusViewModel` dan menambahkan partial member untuk filter toolbar, pagination, dan empty state.

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
- Memoles `/profile` menjadi halaman `Keamanan Akun` untuk email login, password, dan penghapusan akun.
- Memperbesar target checkbox mahasiswa di form profil member menjadi 24px untuk aksesibilitas.
- Merapikan copy dan layout admin agar fokus pada pekerjaan inti tanpa label internal seperti mode/demo.
- Merapikan sidebar dan copy member agar footer hanya `Keluar`, tanpa label internal `Member Area`/`Member Portal`, tanpa shortcut redundan, dan tanpa penyebutan environment payment pada UI member.

### Testing

- `php artisan test --no-ansi` lulus dengan 170 test dan 1101 assertion.
- `php artisan test --filter=MemberPortalTest --no-ansi` lulus dengan 38 test dan 227 assertion.
- Browser QA admin lulus untuk `/admin`, `/admin/pembayaran`, `/admin/produk`, `/admin/resource/products/tambah`, `/admin/booking`, `/admin/check-in`, `/admin/pengaturan`, dan `/admin/laporan` pada viewport 360px, 390px, 768px, 1366px, dan 1440px tanpa console error atau horizontal overflow.
- `npm.cmd run build` berhasil membuat asset Vite production.
- `vendor\bin\pint --test` lulus.
- `composer validate --no-check-publish --no-ansi` valid.

### Planned

- Change Request #1 (#18): Rekap jumlah member aktif per trainer dan status trainer tersedia/penuh.
- Planned Added: fitur rekap member aktif per trainer pada area admin/laporan.
- Planned Changed: halaman admin terkait trainer/laporan akan menampilkan status kapasitas trainer.
- Impacted Modules: Admin Report, Trainer, Member Package Session, Route admin, Dokumentasi.
- Export queue saat data laporan membesar.
- Invoice PDF caching bila diperlukan.
- Upload bukti pembayaran manual bila dibutuhkan.
- Refund/correction workflow pembayaran dan export queue saat data laporan membesar.
- Penyempurnaan dokumentasi refactoring.
- Status badge dan screenshot sukses GitHub Actions setelah workflow berjalan di GitHub.
