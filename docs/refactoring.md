# Refactoring Documentation

Status: Updated 2026-07-01. Dokumen ini diperbarui setiap ada perubahan struktur kode yang berdampak pada maintainability.

Dokumen ini mencatat perubahan struktur kode yang dilakukan untuk meningkatkan keterbacaan, maintainability, dan kesiapan evolusi sistem.

## Tujuan Refactoring

- Mengurangi duplikasi kode.
- Memisahkan tanggung jawab tampilan, konfigurasi brand, dan logic aplikasi.
- Membuat struktur kode lebih mudah dipelihara.
- Menyiapkan fondasi untuk pengembangan fitur berikutnya.

## Refactoring Awal Yang Sudah Dilakukan

## 1. Pemisahan Metadata Brand

### Sebelum

Metadata title, favicon, dan asset brand berpotensi ditulis berulang pada banyak layout.

### Masalah

Jika metadata brand berubah, developer perlu mengubah banyak file.

### Perubahan

Metadata brand dipusatkan pada partial:

```text
resources/views/partials/head-brand.blade.php
```

Partial digunakan pada layout dan halaman utama.

### Alasan

Perubahan brand lebih mudah dilakukan dari satu tempat.

### Dampak

- Duplikasi metadata berkurang.
- Favicon dan title lebih konsisten.

## 2. Penyesuaian Layout Guest Auth

### Sebelum

Layout autentikasi masih mengikuti struktur default Breeze.

### Masalah

Tampilan default belum sesuai identitas Platinum Gym.

### Perubahan

Layout autentikasi dipusatkan dan disesuaikan pada:

```text
resources/views/layouts/guest.blade.php
resources/css/app.css
```

Revisi visual terbaru menambahkan panel foto gym asli pada sisi desktop, form panel responsive, dan background ringan pada sisi form. Foto tidak ditampilkan sebagai hero besar di mobile agar halaman register tetap ringkas.

### Alasan

Halaman login, register, dan verify email membutuhkan tampilan brand yang konsisten.

### Dampak

- UI autentikasi lebih konsisten.
- Perubahan layout auth dapat dilakukan dari satu file.
- Halaman login/register terasa lebih production-ready tanpa mengorbankan keterbacaan form.

## 3. Penggunaan Logo Lokal

### Sebelum

Logo aplikasi belum memakai asset brand lokal Platinum Gym secara konsisten.

### Masalah

Brand aplikasi kurang kuat dan berpotensi bergantung pada sumber eksternal.

### Perubahan

Layout public dan dashboard memakai asset lokal:

```text
resources/views/layouts/public.blade.php
resources/views/layouts/navigation.blade.php
public/images/brand/platinum-gym-wordmark-480.webp
public/images/brand/platinum-gym-wordmark-1200.jpg
```

### Alasan

Asset production sebaiknya tersedia lokal di repository/public folder.

### Dampak

- Logo aplikasi konsisten.
- Aplikasi tidak bergantung pada URL eksternal untuk logo utama.

## 4. Normalisasi Nomor WhatsApp Register

### Sebelum

Nomor telepon user dapat masuk dengan berbagai format.

Contoh:

```text
+62 812-3456-7891
6281234567891
081234567891
```

### Masalah

Format nomor tidak konsisten dan dapat menyulitkan validasi duplicate phone.

### Perubahan

Nomor WhatsApp dinormalisasi ke format:

```text
08xxxxxxxxxx
```

Logic dipusatkan pada shared support dan dipakai oleh action/FormRequest terkait:

```text
app/Features/Shared/Support/NormalizeIndonesianPhone.php
app/Features/Auth/Actions/RegisterMemberAction.php
app/Features/Auth/Actions/CompleteMemberProfileAction.php
```

### Alasan

Format nomor yang konsisten membuat validasi lebih mudah dan data lebih rapi.

### Dampak

- Duplicate phone lebih mudah dicegah.
- Data user lebih konsisten.

## 5. Pemisahan Test Auth Berdasarkan Fitur

### Sebelum

Test auth masih mengikuti struktur dasar Breeze.

### Masalah

Kebutuhan register member dan verifikasi email perlu test tambahan.

### Perubahan

Test ditulis pada file feature test sesuai fitur:

```text
tests/Feature/Auth/RegistrationTest.php
tests/Feature/Auth/EmailVerificationTest.php
```

### Alasan

Test lebih mudah dibaca dan disesuaikan dengan behavior sistem.

### Dampak

- Behavior register member terdokumentasi melalui test.
- Risiko regresi pada verifikasi email lebih kecil.

## Rencana Refactoring Lanjutan

Refactoring berikut akan dievaluasi saat kompleksitas fitur bertambah:

- Penyempurnaan action pembayaran, booking, dan check-in jika aturan bisnis bertambah.
- Export queue untuk laporan besar.
- Upload bukti pembayaran jika dibutuhkan.
- Cleanup route dan komponen bila modul baru bertambah.

## 6. Feature-Based Clean Architecture Foundation

### Sebelum

Workflow register, complete profile, Google OAuth, public settings, public filters, dan query public masih banyak berada langsung di controller.

### Masalah

Controller berisiko membesar saat modul admin, member portal, payment, booking, QR, export, dan AI mulai dibuat.

### Perubahan

Struktur feature-based ditambahkan tanpa memindahkan Eloquent model:

```text
app/Features/Auth/Actions
app/Features/Auth/Http/Requests
app/Features/PublicWebsite/Queries
app/Features/Shared/Support
```

Workflow auth dipindah ke action:

```text
RegisterMemberAction
CompleteMemberProfileAction
ResolveGoogleUserAction
```

Validasi penting dipindah ke FormRequest:

```text
RegisterMemberRequest
CompleteMemberProfileRequest
```

Query public dipindah ke query layer:

```text
PublicSettingsQuery
PublicHomeQuery
PublicClassScheduleQuery
PublicProductQuery
PublicAboutQuery
PublicServicesQuery
PublicGalleryQuery
```

Logic chatbot Alpine dipindah dari Blade ke:

```text
resources/js/public-chatbot.js
```

### Alasan

- Controller menjadi lebih tipis.
- Workflow tulis data lebih mudah dites dan diamankan dengan transaction.
- Query public lebih mudah dikembangkan tanpa mengotori controller.
- Blade tetap fokus pada render markup dan config.

### Dampak

- Behavior user-facing tetap sama.
- Foundation siap untuk modul berikutnya.
- Test auth/public tetap menjadi regression guard utama.

## 7. Production Baseline Environment

### Perubahan

`.env.example` diperbarui dengan placeholder non-secret untuk Google OAuth, Midtrans, Gemini, mail sender, queue/database, dan session secure flags.

`config/services.php` sekarang memiliki entry config untuk:

```text
google
midtrans
gemini
```

### Catatan

Nilai secret tetap harus diisi hanya di `.env` lokal/production dan tidak boleh masuk Git.

## 8. Modular Views Best Practices

### Perubahan

Sisa view default Breeze dibersihkan dari dashboard root yang tidak dipakai, sementara internal app shell direbrand agar konsisten dengan identitas Platinum Gym.

Komponen dashboard reusable ditambahkan:

```text
resources/views/components/dashboard/page.blade.php
resources/views/components/dashboard/card.blade.php
resources/views/components/dashboard/stat-card.blade.php
resources/views/components/dashboard/empty-state.blade.php
```

Dashboard role memakai komponen tersebut:

```text
resources/views/member/dashboard.blade.php
resources/views/admin/dashboard.blade.php
resources/views/owner/dashboard.blade.php
resources/views/profile/edit.blade.php
```

Legal pages memakai public layout agar SEO, header, footer, dan brand konsisten:

```text
resources/views/legal/terms.blade.php
resources/views/legal/privacy.blade.php
```

Auth form JavaScript dipindah dari Blade ke:

```text
resources/js/auth-form.js
```

Public layout dan chatbot config dipindah ke ViewModel:

```text
PublicLayoutViewModel
PublicChatbotViewModel
```

### Dampak

- Folder views tetap Laravel-native dan tidak mengikuti mirror `app/Features`.
- Blade lebih fokus pada presentasi.
- Internal dashboard tidak lagi terasa default Laravel/Breeze.
- Public/legal/auth/app shell memakai brand dan struktur yang lebih konsisten.

## 9. Public Google Maps Embed Tanpa API Key

### Perubahan

Halaman lokasi sekarang membaca setting public:

```text
maps_embed_url
```

Nilai ini berisi `src` iframe dari Google Maps `Share > Embed a map`, bukan Google Maps Embed API endpoint dan bukan API key.

### Dampak

- `/lokasi` menampilkan iframe Google Maps responsive jika `maps_embed_url` tersedia.
- Jika setting dikosongkan, halaman memakai fallback visual yang tetap menyediakan tombol `Buka Google Maps` dan `Cari di Maps`.
- Public settings whitelist dan `SettingSeeder` tetap menjadi sumber data lokasi yang konsisten.

## 10. Public Website UX dan Responsive Polish

### Perubahan

Public website dipoles tanpa redesign besar agar lebih nyaman di mobile, tablet, desktop, dan wide desktop.

Perubahan utama:

```text
resources/views/public/partials/header.blade.php
resources/views/public/partials/footer.blade.php
resources/views/public/partials/page-hero.blade.php
resources/views/public/home.blade.php
resources/views/public/location.blade.php
resources/views/public/partials/chatbot.blade.php
resources/css/app.css
resources/js/public-chatbot.js
```

### Detail

- Header logo, CTA, hamburger, footer link, dan contact link memakai tap target lebih nyaman.
- Focus state public control memakai `focus-visible` agar jelas untuk keyboard dan tidak mengganggu klik mouse.
- Mobile nav memiliki scroll containment agar tetap bisa dipakai pada device pendek.
- Dynamic content diberi wrapping guard untuk mencegah horizontal overflow.
- Chatbot memakai focus return, `aria-live`, dan scroll containment ringan.
- Home hero mobile dibuat compact dengan visual gym/strength umum sebagai visual utama; Muaythai tetap menjadi visual pendukung di desktop collage.
- `page-hero` public dipusatkan sebagai partial compact-premium dengan token CSS reusable, animasi masuk halus, dan guard `prefers-reduced-motion`.
- Image public statis diberi dimensi eksplisit, dan hero image above-the-fold memakai `fetchpriority="high"`.
- Urutan paket layanan dijaga di `PublicServicesQuery`, bukan Blade, sehingga ranking membership, Muaythai, Personal Trainer, dan Session tetap stabil untuk data seed maupun database production.
- `PublicClassScheduleQuery` mengelompokkan jadwal ke section Aerobic, Zumba, Muaythai, dan Poundfit dengan resolver yang tetap menerima data lama bertipe `senam`.

### Dampak

- Public website lebih stabil pada viewport 320px sampai wide desktop.
- Risiko overflow dari konten admin/seeder lebih kecil.
- UX keyboard dan touch lebih profesional.
- Catatan audit responsive diringkas pada dokumentasi fitur dan refactoring agar struktur `docs/` tetap mengikuti modul PBL.

## 11. Product Catalog Scope Dan Asset Cleanup

### Sebelum

Halaman produk masih belum sepenuhnya terkunci sebagai katalog informasi, dan beberapa asset/dokumen tambahan masih berada di root repository walaupun tidak dipakai oleh aplikasi Laravel production.

### Masalah

Scope produk berisiko terbaca seperti toko online jika copy/CTA tidak dibatasi. Asset brand lama, komponen Blade default yang tidak digunakan, dan dokumen audit tambahan di root juga dapat membuat struktur proyek terlihat kurang rapi untuk dokumentasi PBL.

### Perubahan

Produk dikunci sebagai katalog informasi dengan stok aktual dan arahan pembelian langsung di lokasi. Field foto produk ditambahkan ke model/database, foto produk WebP dipakai dari asset optimized, dan produk tanpa foto tetap memakai fallback visual.

Cleanup juga menghapus scaffold kosong, komponen Blade default yang tidak digunakan, asset brand lama yang tidak direferensikan, dan dokumen tambahan root yang sudah dipindahkan ke arsip pendukung di luar repository production Laravel.

### Dampak

- Scope produk lebih jelas: tidak ada checkout, cart, invoice, atau transaksi produk online.
- Public website memakai asset real yang lebih ringan dan stabil.
- Root Laravel lebih fokus pada kode production dan dokumen modul PBL yang wajib.

## 12. Admin Portal Query/Layout Foundation

### Sebelum

`/admin` masih memakai placeholder dashboard sederhana dan belum memiliki menu modul awal.

### Masalah

Admin membutuhkan titik masuk operasional untuk membaca dan mengubah data member, pembayaran, booking, check-in, konten, produk, setting, dan audit log tanpa bergantung pada Filament.

### Perubahan

Admin portal ditingkatkan dari area pantau menjadi area operasional Blade dengan pola action/query yang sama seperti member portal:

```text
app/Http/Controllers/AdminPortalController.php
app/Http/Controllers/Admin/*
app/Features/Admin/Actions/*
app/Features/Admin/Queries/AdminDashboardQuery.php
app/Features/Admin/Support/AdminResourceRegistry.php
app/Features/Admin/Support/AdminEditableSettingRegistry.php
app/View/Components/AdminLayout.php
resources/views/layouts/admin.blade.php
resources/views/admin/dashboard.blade.php
resources/views/admin/page.blade.php
resources/views/admin/pages/operations.blade.php
resources/views/admin/partials/data-table.blade.php
resources/views/admin/partials/icon.blade.php
resources/views/admin/resources/form.blade.php
```

### Alasan

Controller tetap tipis, query data terpusat, Blade fokus pada presentasi, dan aksi tulis ditempatkan pada FormRequest/Action/Controller kecil dengan permission check.

### Dampak

- Admin mendapat route operasional untuk CRUD master data, pembayaran cash/approve/reject, booking, check-in, settings whitelist, audit filter, report CSV, dan toggle status data/konten.
- Dashboard menjadi pusat kerja operasional dengan status strip, KPI ringkas, quick links, dan data terbaru.
- Partial tabel admin reusable mendukung server-side search, status filter, pagination, count, empty/no-result state, caption/aria polish, mobile card fallback, dan row actions aman.
- Data sensitif pada setting disamarkan di query layer, sedangkan update setting hanya membuka whitelist kontak/maps/jam operasional/invoice publik.
- Test `AdminPortalTest` menjaga auth guard, role guard, render route, data operasional, masking setting, approval/cash pembayaran, check-in QR preview-confirm, report export, resource CRUD, dan toggle status.

## 13. Gymmi Chatbot Identity

### Perubahan

Chatbot public dan member diberi identitas produk `Gymmi` melalui ViewModel, Blade shell, dan renderer pusat di `resources/js/public-chatbot.js`. Initial bot berubah ke `GY`, user bubble kanan tidak lagi memakai avatar `AN`, FAQ quick reply menjadi rail horizontal, action link bot wrap aman, dan export JS lama tetap dipertahankan untuk kompatibilitas.

Avatar Gymmi memakai asset gambar light/dark dari `public/images/gymmi/` dengan fallback initial jika gambar gagal termuat. Theme public disamakan dengan member: light mode memakai panel terang, dark mode memakai panel gelap, sementara bubble, typing state, quick reply, dan action link mengikuti kontras tema aktif.

Tailwind scan mencakup `resources/js/**/*.js` karena beberapa class bubble Gymmi dibuat dari JavaScript.

### Dampak

- Public dan member memiliki nama chatbot yang konsisten.
- Message log memakai label a11y yang lebih jelas dan typing guard mencegah double submit.
- Test public/member diperbarui agar mengunci label dan aria baru.

## 14. Production Member/Admin Operational Flow

### Perubahan

Member dan admin tidak lagi berhenti pada tampilan monitoring. Workflow operasional dipisahkan ke controller kecil, FormRequest, dan Action class:

```text
app/Features/Payments/Actions
app/Features/Payments/Contracts
app/Features/Payments/Gateways
app/Features/Bookings/Actions
app/Features/CheckIns/Actions
app/Http/Controllers/Member
app/Http/Controllers/Admin
app/Http/Controllers/Webhook
app/Http/Requests/Member
app/Http/Requests/Admin
app/Notifications/MemberOperationalNotification.php
app/Support/QrSvgRenderer.php
```

### Alasan

Checkout, webhook payment, booking, QR check-in, dan approval admin menyentuh banyak tabel sehingga perlu transaction, validasi request, permission check, dan boundary integrasi yang jelas.

### Dampak

- Member dapat checkout membership/paket sesi, booking kelas, melihat transaksi/detail invoice, membayar lewat Midtrans Sandbox, melihat QR, dan mengelola notifikasi.
- Admin dapat create/update master data, mencatat pembayaran cash, approve/reject pembayaran, create/confirm/cancel booking, scan QR kamera ke preview lalu confirm check-in/pemakaian sesi, update setting publik whitelist, export laporan CSV, dan toggle status/tayang data operasional.
- Midtrans Sandbox dan Resend dikonfigurasi lewat `.env`; secret tidak masuk source code.
- Produk tetap katalog informasi, bukan checkout produk.
## 15. Gymmi Gemini Backend

### Perubahan

Gymmi public dan member tidak lagi hanya static/intention-based. Backend baru memakai endpoint `POST /gymmi/chat`, `GymmiChatRequest`, `GymmiChatController`, action `AskGymmiAction`, context builder, port `GymmiAssistantClient`, adapter `GeminiGymmiClient`, dan Laravel HTTP client untuk Google Gemini `generateContent`.

### Alasan

Gymmi membutuhkan jawaban natural tetapi tetap aman. Service boundary menjaga API key tetap di `.env`, prompt hanya memakai konteks yang boleh diketahui, request diberi rate limit, dan kegagalan provider tetap memakai fallback lokal agar UX tidak rusak.

### Dampak

- Public dan member chatbot memakai Gemini saat key/quota tersedia.
- Semua pesan tetap punya fallback lokal.
- Conversation log disimpan ke `ai_conversations` dan `ai_messages`.
- Member context hanya memakai user login dan data member miliknya sendiri.
- Trigger member/public seragam dengan ikon chat dan label `Tanya Gymmi`, tanpa badge merah.

## 16. Admin Server-Side Pagination

### Sebelum

Tabel admin awalnya memakai pembatasan data di query dan pencarian lokal pada data yang sudah tampil.

### Masalah

Saat data bertambah, pencarian hanya berlaku pada data halaman saat itu dan sebagian data tidak terlihat dari tabel admin.

### Perubahan

Query modul admin dipusatkan pada `AdminDashboardQuery` dengan server-side pagination 12 data per halaman, search, status filter, date filter untuk audit/report, dan query string persistence.

Komponen tabel berikut menerima paginator Laravel dan merender navigasi halaman yang responsive:

```text
resources/views/admin/partials/data-table.blade.php
```

### Alasan

Admin membutuhkan workbench yang tetap cepat, dapat dicari lintas seluruh data, dan tidak membebani browser saat dataset bertambah.

### Dampak

- Halaman seperti produk, anggota, pembayaran, kelas, booking, check-in, galeri, testimoni, promo, trainer, audit log, dan pengaturan dapat memuat data bertahap.
- Search dan filter bekerja dari query database, bukan hanya data yang sedang terlihat.
- Mobile card fallback dan tabel desktop tetap memakai komponen yang sama.

## 17. Member Server-Side Pagination Dan Status ViewModel

### Sebelum

Halaman member yang berisi daftar memakai data terbatas dari dashboard query. Beberapa status pembayaran, booking, notifikasi, paket, dan jadwal juga masih diformat langsung di Blade.

### Masalah

Saat data member bertambah, pencarian dan filter perlu berlaku pada seluruh data milik member, bukan hanya data yang sedang tampil. Logic status yang berulang di Blade juga membuat halaman lebih sulit dipelihara.

### Perubahan

`MemberDashboardQuery` sekarang membedakan kebutuhan dashboard ringkas dan halaman list aktif. Halaman membership, booking kelas, riwayat booking, transaksi, dan notifikasi memakai pagination Laravel dengan query string dan batas tetap per halaman.

Status label/class dipusatkan pada:

```text
app/Features/MemberPortal/ViewModels/MemberPortalStatusViewModel.php
```

Partial member reusable ditambahkan untuk toolbar filter, pagination, dan empty state:

```text
resources/views/member/partials/filter-toolbar.blade.php
resources/views/member/partials/pagination.blade.php
resources/views/member/partials/empty-state.blade.php
```

### Alasan

- Query tetap own-data dan bekerja dari database.
- Blade fokus pada render markup.
- Search/filter tetap akurat ketika transaksi, booking, paket, atau notifikasi bertambah.
- UI member memakai pola yang lebih konsisten tanpa mengulang markup filter dan pagination.

### Dampak

- List member menjadi lebih siap untuk data production.
- Copy member lebih fokus dan tidak menampilkan label internal.
- Sidebar/drawer member lebih minimal; desktop identity/logout pindah ke topbar account menu, sementara mobile drawer tetap menyimpan identity dan `Keluar`.
- Test `MemberPortalTest` menjaga pagination/filter, own-data boundary, dan copy production member.

## 18. Owner Portal, Reports, Dan Invoice Web

### Sebelum

Owner dashboard masih berupa halaman dasar untuk validasi role dan belum memiliki layout, laporan, grafik, atau invoice web.

### Masalah

Owner membutuhkan monitoring bisnis yang berbeda dari admin. Admin fokus operasional harian, sedangkan owner perlu membaca pendapatan, transaksi, member, membership, booking kelas, dan invoice secara read-only.

### Perubahan

Owner portal dipisahkan ke controller, query, layout, dan view sendiri:

```text
app/Http/Controllers/Owner
app/Features/OwnerPortal/Queries
app/Features/Reports/Data
app/Features/Reports/Queries
app/Features/Invoices/Queries
app/View/Components/OwnerLayout.php
resources/views/layouts/owner.blade.php
resources/views/owner
resources/views/invoices
```

Reports owner memakai filter periode/status/metode/tipe laporan dan export CSV/Excel/PDF. Invoice web, PDF invoice, dan struk POS compact memakai data transaksi yang sudah ada dan tidak membuat logic pembayaran baru.

### Alasan

- Owner tetap read-only dan tidak membawa aksi mutasi admin ke area bisnis.
- Query laporan berada di layer fitur, bukan di Blade.
- CSV tetap kompatibel, sedangkan Excel/PDF memakai package produksi yang sesuai untuk dokumen laporan.
- Invoice web, PDF, dan struk menjadi presentasi dokumen transaksi tanpa mengekspos token, payload provider, secret, atau data internal.

### Dampak

- `/owner` menjadi dashboard bisnis dengan KPI, grafik pendapatan, breakdown, transaksi terbaru, dan membership yang akan berakhir.
- `/owner/laporan` dan halaman laporan detail dapat membaca ringkasan serta tabel preview.
- `/owner/laporan/export` menghasilkan CSV, Excel, atau PDF laporan sesuai filter.
- `/owner/invoice/{invoice}`, `/member/invoice/{invoice}`, dan `/admin/invoice/{invoice}` menampilkan invoice web sesuai policy.
- Route `/struk` dan `/download` menghasilkan struk web serta PDF invoice/struk.
- `PaymentPolicy` dan `InvoicePolicy` memberi akses owner untuk membaca laporan/invoice, sementara aksi mutasi tetap tidak diberikan.

## 19. Reports Export PDF/Excel Dan Struk POS

### Perubahan

Laporan Admin dan Owner sekarang memakai layer export terpisah untuk CSV, Excel, dan PDF:

```text
app/Features/Reports/Actions
app/Features/Reports/Exports
resources/views/reports/pdf
```

Invoice transaksi juga diperluas menjadi dokumen formal dan struk POS compact:

```text
app/Features/Invoices/Actions/RenderInvoicePdfAction.php
resources/views/invoices/pdf.blade.php
resources/views/invoices/receipt.blade.php
resources/views/invoices/receipt-pdf.blade.php
resources/views/invoices/partials/receipt-paper.blade.php
```

### Alasan

- Controller tetap hanya mengatur authorization, filter, dan response format.
- CSV lama tetap kompatibel, sementara Excel `.xlsx` dan PDF memenuhi kebutuhan dokumen production.
- Struk POS menjadi format ringkas untuk bukti transaksi tanpa mengganti invoice formal.
- Template PDF memakai CSS sederhana agar aman dirender DomPDF dan tidak bergantung pada Vite/Tailwind runtime.

### Dampak

- `/admin/laporan/export` dan `/owner/laporan/export` menerima `format=csv|xlsx|pdf`.
- `/member/invoice/{invoice}`, `/owner/invoice/{invoice}`, dan `/admin/invoice/{invoice}` memiliki tampilan invoice, struk, dan download PDF.
- Profil Owner di `/profile` memakai layout/copy Owner, bukan shell Admin.
- Dokumen invoice/struk tetap tidak menampilkan token QR mentah, Midtrans snap token, redirect URL, raw response, note internal, atau secret provider.

## 20. Renderer Grafik Lokal Dan Cleanup Test Domain

### Perubahan

Grafik tren Admin dan Owner dipindahkan dari dependency chart eksternal ke renderer SVG lokal kecil:

```text
resources/js/shared/svg-trend-chart.js
resources/js/admin/operational-trend-chart.js
resources/js/owner/business-trend-chart.js
```

Suite feature test juga dirapikan dari file legacy root ke folder domain:

```text
tests/Feature/Admin
tests/Feature/Member
tests/Feature/Owner
tests/Feature/PublicWebsite
tests/Feature/Gymmi
tests/Feature/Invoices
```

### Alasan

- Bundle frontend lebih ringan dan tidak membawa dependency chart besar untuk visual sederhana.
- Test domain lebih mudah dirawat daripada file monolith root yang mencampur banyak perilaku.
- File legacy tetap dipertahankan sebagai `*LegacyTest` pada domain terkait saat masih menyimpan coverage penting.

### Dampak

- `apexcharts` tidak lagi menjadi dependency frontend aktif.
- Mount ID dan payload chart tetap kompatibel dengan Blade existing.
- Root `tests/Feature` lebih fokus pada folder domain dan cross-domain yang jelas.

## 21. Production Readiness Hardening 2026-06-30

### Perubahan

Production-readiness pass 2026-06-30 melakukan hardening konservatif tanpa menambah route, migration, role, checkout produk, atau behavior pembayaran baru.

Perubahan utama:

```text
app/Features/Admin/Support/AdminResourceRegistry.php
app/Models/Payment.php
app/Models/SocialAccount.php
app/Models/QrToken.php
app/Models/AccountInvitation.php
resources/views/public/partials/header.blade.php
resources/views/invoices/receipt.blade.php
resources/views/invoices/partials/document.blade.php
resources/views/admin/pages/operations.blade.php
resources/css/app.css
tests/Feature/Admin/AdminContentResourceTest.php
tests/Feature/Security/SensitiveModelSerializationTest.php
```

### Detail

- Upload gambar produk/galeri admin sekarang membatasi image ke JPG, JPEG, PNG, dan WebP melalui MIME dan extension whitelist; SVG/GIF ditolak.
- Model `Payment`, `SocialAccount`, `QrToken`, dan `AccountInvitation` menyembunyikan token/payload sensitif saat serialisasi model.
- Public product card tidak lagi memotong nama produk; deskripsi tetap dibatasi agar card stabil.
- Public mobile menu sekarang dapat ditutup dengan Escape.
- Invoice/struk member dan admin memakai portal layout masing-masing sehingga responsive QA tetap menemukan `#member-main` dan `#admin-main`.
- Panel invoice diberi `min-w-0` pada grid/panel yang memuat data member/gym agar tidak melebar di viewport sempit.
- Export link laporan admin memperbaiki scope Alpine `dateFrom/dateTo` sehingga halaman `/admin/laporan` tidak menghasilkan console error.

### Browser QA

Responsive QA headless lokal menjalankan matrix width 320, 360, 375, 390, 393, 412, 414, 430, 480, 540, 600, 640, 768, 820, 834, 1024, 1180, 1280, 1366, 1440, 1536, 1728, 1920, 2560, dan 3440 px untuk public/member/admin/owner sesuai checklist browser. Drawer smoke pada 390 px juga memverifikasi public/member/admin/owner mobile menu bisa open, close dengan Escape, dan `aria-expanded` kembali `false`.

### Final Gate Evidence

- `php artisan test --no-ansi` lulus dengan 648 tests dan 4883 assertions.
- `php artisan route:list --except-vendor --no-ansi` tetap menunjukkan 109 routes.
- `composer validate --strict --no-check-publish`, `composer audit`, `npm.cmd audit --audit-level=moderate`, `npm.cmd audit --omit=dev --audit-level=moderate`, `vendor\bin\pint --test`, dan `npm.cmd run build` lulus.
- `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, lalu `php artisan optimize:clear` lulus.
- Admin affected responsive subset `/admin/laporan`, `/admin/pengaturan`, `/admin/booking`, `/admin/notifikasi`, dan `/admin/paket` lulus 125 checks tanpa failure setelah fix.

## 22. Gymmi Conversational Polish 2026-06-30

### Perubahan

Gymmi public dan member mendapat jalur conversational lokal sebelum matcher, live data, dan Gemini:

```text
app/Features/Gymmi/Support/GymmiConversationalResponder.php
app/Features/Gymmi/Actions/AskGymmiAction.php
app/Features/Gymmi/Support/GymmiFallbackResponder.php
app/Features/Gymmi/Support/GymmiIntentDetector.php
app/Features/Gymmi/Support/GymmiKnowledgeMatcher.php
app/Features/Gymmi/Support/GymmiLiveDataProvider.php
resources/js/public-chatbot.js
app/Features/PublicWebsite/ViewModels/PublicChatbotViewModel.php
app/Features/MemberPortal/ViewModels/MemberChatbotViewModel.php
```

### Detail

- Sapaan, terima kasih, perkenalan kemampuan, pamit, dan small-talk aman dijawab ringkas seperti CS Platinum Gym tanpa memanggil Gemini.
- Fallback public/member dibuat lebih natural dan sesuai konteks. Public diarahkan ke membership, kelas, lokasi, produk katalog, dan WhatsApp. Member diarahkan ke membership, booking, transaksi, QR, dan profil tanpa membuka data member lain.
- Intent lokasi/kontak diprioritaskan sebelum membership sehingga pertanyaan seperti `dimana lokasi gym?` mengambil alamat/maps/kontak dan tidak tertarik ke paket `Gym Umum`.
- Frontend fallback public/member disinkronkan untuk greeting/thanks/wellbeing/capability/goodbye, lokasi/kontak diprioritaskan, dan action button lokal tidak ditempel pada response `guard` atau `fallback` yang tidak relevan.

### Evidence

- `php artisan test --filter=Gymmi --no-ansi` lulus 36 tests / 338 assertions.
- `php artisan test --filter=PublicGymmiTest --no-ansi` lulus 2 tests / 48 assertions.
- `php artisan test --filter=MemberPortalUiTest --no-ansi` lulus 11 tests / 496 assertions.
- Full `php artisan test --no-ansi` lulus 656 tests / 4959 assertions.
- Composer validate/audit, NPM audit full dan production-only, Pint, Vite build, route list 109 routes, config cache, route cache, view cache, dan optimize clear lulus.
- Browser matrix in-app pada local server memeriksa public `/`, `/layanan`, `/kelas`, `/produk`, `/lokasi` dan member `/member/dashboard`, `/member/membership`, `/member/transaksi`, `/member/qr` pada 25 width 320-3440 px tanpa horizontal overflow atau console error. Interaction smoke Gymmi terkena timeout runtime browser pada `domcontentloaded`, tetapi request `/gymmi/chat` berjalan dan action-level timing menunjukkan `halo` 51.33 ms, `makasih` 15.52 ms, `apa kabar` 9.6 ms, guard out-of-scope 8.14 ms, dan lokasi knowledge 164.1 ms tanpa provider call untuk jalur conversational/guard.

## 23. Booking Datepicker dan QR Sesi Final Polish 2026-07-01

### Perubahan

Polish terakhir untuk booking dan QR memindahkan aturan akses QR sesi ke helper kecil serta menyederhanakan datepicker agar memakai popup default Flatpickr.

```text
app/Support/MemberQrAccess.php
app/Features/CheckIns/Actions/PreviewMemberQrCheckInAction.php
app/Features/CheckIns/Actions/ConfirmMemberQrCheckInAction.php
app/Features/MemberPortal/Queries/MemberDashboardQuery.php
app/Features/MemberPortal/Actions/DownloadMemberQrAction.php
resources/js/local-date-input.js
resources/js/admin/booking-form.js
resources/views/components/local-date-input.blade.php
resources/views/admin/page.blade.php
resources/views/member/pages/qr.blade.php
```

### Detail

- `MemberQrAccess` memusatkan daftar paket sesi standalone yang boleh mengaktifkan QR: Muaythai dan Poundfit.
- Payment fulfillment untuk paket sesi Muaythai/Poundfit menerbitkan atau memakai ulang QR member; Personal Trainer tetap membutuhkan membership Gym/Include aktif.
- Admin QR preview sekarang dapat menampilkan QR `Aktif untuk sesi` tanpa membership aktif, tetapi hanya tombol `Gunakan Sesi` yang tersedia. `Check-in Member` dan `Check-in + Gunakan Sesi` tetap membutuhkan membership aktif.
- Member QR/dashboard menampilkan status `Aktif untuk sesi` dan label `Paket sesi aktif` saat QR aktif dari sesi standalone.
- Flatpickr booking memakai popup default package. Override visual `.platinum-date-calendar` dan panah/month selector custom dihapus.
- Admin booking datepicker disabled sampai jadwal dipilih; setelah jadwal dipilih, hanya tanggal sesuai `day_of_week` yang aktif. Backend request tetap menolak tanggal beda hari jadwal.

### Evidence

- `php artisan test --filter=MemberQrTest --no-ansi` lulus 6 tests / 22 assertions.
- `php artisan test --filter=AdminCheckInTest --no-ansi` lulus 10 tests / 67 assertions.
- `php artisan test --filter=AdminBookingTest --no-ansi` lulus 8 tests / 97 assertions.
- `php artisan test --filter=MemberBookingTest --no-ansi` lulus 9 tests / 81 assertions.
- `php artisan test --filter=MemberPortalLegacyTest --no-ansi` lulus 55 tests / 512 assertions.
- `php artisan test --filter=AdminPortalLegacyTest --no-ansi` lulus 60 tests / 424 assertions.
- `MemberPortalUiTest`, `AdminPortalUiTest`, dan `OwnerPortalUiTest` lulus.
- Final gate sebelum commit/push 2026-07-01 lulus: `composer validate --strict --no-check-publish`, `composer audit`, `npm.cmd audit --audit-level=moderate`, `npm.cmd audit --omit=dev --audit-level=moderate`, `vendor\bin\pint --test`, `npm.cmd run build`, `php artisan test --no-ansi` 662 tests / 5125 assertions, `php artisan route:list --except-vendor --no-ansi` 109 routes, `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, dan `php artisan optimize:clear`.

## 24. Guard Attendance Untuk Gunakan Sesi 2026-07-01

### Perubahan

Pemakaian sesi kelas di admin check-in sekarang ditautkan ke booking kelas yang dipakai:

```text
database/migrations/2026_07_01_000001_add_class_enrollment_id_to_member_package_session_usages.php
app/Features/CheckIns/Actions/PreviewMemberQrCheckInAction.php
app/Features/CheckIns/Actions/ConfirmMemberQrCheckInAction.php
app/Http/Requests/Admin/ConfirmQrCheckInRequest.php
app/Http/Controllers/Admin/AdminCheckInController.php
app/Models/MemberPackageSessionUsage.php
app/Models/ClassEnrollment.php
resources/views/admin/page.blade.php
```

### Detail

- `member_package_session_usages.class_enrollment_id` menautkan satu penggunaan sesi ke satu booking kelas dan mencegah booking yang sama dipakai dua kali.
- Preview QR admin hanya menawarkan opsi Muaythai/Poundfit yang punya booking `confirmed` hari ini, jadwal/kelas aktif, paket dan coach cocok, belum attended, dan belum punya usage.
- Confirm action memvalidasi ulang semua aturan di dalam database transaction dengan row lock sebelum mengurangi sesi.
- Saat `Gunakan Sesi` berhasil untuk kelas, sistem mengurangi sisa sesi, membuat `class_attendances`, mencatat usage dengan `class_enrollment_id`, dan mengubah `class_enrollments.status` menjadi `attended`.
- Personal Trainer tetap memakai flow existing tanpa booking kelas, tetapi tetap membutuhkan membership Gym/Include aktif.

### Evidence

- `php artisan test --filter=AdminCheckInTest --no-ansi` lulus 8 tests / 79 assertions.
- `php artisan test --filter=MemberQrTest --no-ansi` lulus 6 tests / 22 assertions.
- `php artisan test --filter=AdminBookingTest --no-ansi` lulus 8 tests / 97 assertions.
- `php artisan test --filter=MemberBookingTest --no-ansi` lulus 9 tests / 81 assertions.
- `php artisan test --filter=AdminPortalUiTest --no-ansi` lulus 4 tests / 69 assertions.
- `php artisan test --filter=AdminPortalLegacyTest --no-ansi` lulus 60 tests / 429 assertions.
- `php artisan test --filter=MemberPortalLegacyTest --no-ansi` lulus 55 tests / 513 assertions setelah assertion katalog Poundfit dibuat spesifik terhadap section/card agar tidak bentrok dengan copy Gymmi global.
- Full `php artisan test --no-ansi` lulus 676 tests / 5279 assertions.

## 25. Approval Inbox, Gymmi Normalizer, Dan Portal Guard 2026-07-02

### Perubahan

Batch ini merapikan beberapa area operasional tanpa mengubah scope produk checkout atau alur payment provider:

```text
app/Features/Admin/Actions/ReviewMemberStudentProofAction.php
app/Http/Controllers/Admin/AdminStudentProofReviewController.php
app/Http/Requests/Admin/ReviewMemberStudentProofRequest.php
app/Features/Gymmi/Support/GymmiTextNormalizer.php
app/Features/Gymmi/Support/GymmiLiveDataProvider.php
app/Features/MemberPortal/Queries/MemberDashboardQuery.php
resources/views/admin/members/student-proof-review.blade.php
resources/views/admin/pages/profile-overview.blade.php
resources/views/admin/pages/settings-form.blade.php
```

### Detail

- Admin notifikasi menjadi approval inbox untuk bukti mahasiswa yang menunggu review. File bukti mahasiswa tetap berada di storage privat dan hanya disajikan melalui route terproteksi.
- Tabel anggota admin menampilkan kolom operasional yang lebih relevan: nama, kode member, WhatsApp, status member, kategori, verifikasi, dan tanggal bergabung. NIM tidak lagi menjadi field kerja admin.
- Review bukti mahasiswa memakai action terpisah dengan transaction, row lock, validasi catatan, dan activity log untuk setuju/tolak.
- Gymmi menambahkan normalisasi teks dan knowledge override agar variasi pertanyaan layanan, harga, jadwal, produk, bukti mahasiswa, dan kontak tetap diarahkan ke data resmi.
- Booking member sekarang menonaktifkan semua kelas yang tidak didukung membership/paket sesi aktif. Membership `include` membuka semua kelas included, sedangkan membership bertipe spesifik hanya membuka kelas included yang cocok.
- Profil admin dipadatkan agar fokus pada akun admin login, upload foto, dan shortcut keamanan akun. Pengaturan admin tetap berupa form whitelist operasional tanpa tabel teknis dan tanpa field Google Maps harian.

### Evidence

- `git diff --check` lulus.
- `composer validate --strict --no-check-publish` dan `composer audit` lulus.
- `npm.cmd audit --audit-level=moderate` dan `npm.cmd audit --omit=dev --audit-level=moderate` lulus.
- `vendor\bin\pint --test` lulus.
- `npm.cmd run build` lulus.
- `php artisan test --no-ansi` lulus 680 tests / 5370 assertions.
- `php artisan route:list --except-vendor --no-ansi` lulus dengan 113 routes.
- `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, dan `php artisan optimize:clear` lulus.
