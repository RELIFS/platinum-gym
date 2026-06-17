# Refactoring Documentation

Status: Updated 2026-06-15. Dokumen ini diperbarui setiap ada perubahan struktur kode yang berdampak pada maintainability.

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

- Penyempurnaan dashboard owner dan laporan/export saat scope owner dimulai.
- Penyempurnaan action pembayaran, booking, dan check-in jika aturan bisnis bertambah.
- Export queue untuk laporan besar.
- Invoice PDF/download dan upload bukti pembayaran jika dibutuhkan.
- Cleanup route dan komponen bila scope owner atau modul baru bertambah.

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
- Image public statis diberi dimensi eksplisit, dan hero image above-the-fold memakai `fetchpriority="high"`.

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
- Test `AdminPortalTest` menjaga auth guard, role guard, render route, data operasional, masking setting, approval/cash pembayaran, check-in QR/manual, report export, resource CRUD, dan toggle status.

## 13. Gymmi Chatbot Identity

### Perubahan

Chatbot public dan member diberi identitas produk `Gymmi` melalui ViewModel, Blade shell, dan renderer pusat di `resources/js/public-chatbot.js`. Initial bot berubah ke `GY`, user bubble kanan tidak lagi memakai avatar `AN`, FAQ quick reply menjadi chip kanan, action link bot wrap aman, dan export JS lama tetap dipertahankan untuk kompatibilitas.

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
- Admin dapat create/update master data, mencatat pembayaran cash, approve/reject pembayaran, create/confirm/cancel booking, scan/input QR/manual check-in, update setting publik whitelist, export laporan CSV, dan toggle status/tayang data operasional.
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
- Sidebar/drawer member lebih minimal dengan footer `Keluar` saja.
- Test `MemberPortalTest` menjaga pagination/filter, own-data boundary, dan copy production member.
