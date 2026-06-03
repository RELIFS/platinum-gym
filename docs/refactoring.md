# Refactoring Documentation

Status: Updated 2026-06-03. Dokumen ini diperbarui setiap ada perubahan struktur kode yang berdampak pada maintainability.

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
```

### Alasan

Halaman login, register, dan verify email membutuhkan tampilan brand yang konsisten.

### Dampak

- UI autentikasi lebih konsisten.
- Perubahan layout auth dapat dilakukan dari satu file.

## 3. Penggunaan Logo Lokal

### Sebelum

Logo aplikasi belum memakai asset brand lokal Platinum Gym secara konsisten.

### Masalah

Brand aplikasi kurang kuat dan berpotensi bergantung pada sumber eksternal.

### Perubahan

Component logo dan public layout memakai asset lokal:

```text
resources/views/components/application-logo.blade.php
resources/views/layouts/public.blade.php
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

- Pemisahan dashboard berdasarkan role.
- Service untuk proses membership atau pembayaran jika controller mulai besar.
- Policy atau middleware untuk pembatasan akses role.
- Form Request untuk validasi fitur bisnis kompleks berikutnya.
- Komponen Blade reusable tambahan untuk form, status badge, tabel, dan layout dashboard bisnis.
- Cleanup route jika jumlah modul bertambah.

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
- Detail audit tersimpan di `docs/public-responsive-audit.md`.
