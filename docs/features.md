# Feature Documentation

Status: Updated 2026-06-12. Dokumen ini diperbarui seiring finalisasi kebutuhan dan implementasi fitur.

Dokumen ini mencatat fitur yang sudah tersedia dan rencana fitur pada sistem Platinum Gym Padang.

## Ringkasan Status Fitur

| Fitur | Status | Aktor |
|---|---|---|
| Public website | Sudah tersedia | Pengunjung |
| Halaman layanan | Sudah tersedia | Pengunjung |
| Halaman kelas dan filter jadwal | Sudah tersedia | Pengunjung/member |
| Halaman produk, stok, dan pencarian | Sudah tersedia | Pengunjung/member |
| Halaman galeri | Sudah tersedia | Pengunjung |
| Halaman lokasi dan kontak | Sudah tersedia | Pengunjung |
| Kalkulator BMI | Sudah tersedia | Pengunjung/member |
| Register member | Sudah tersedia | Pengunjung/member |
| Login/register Google | Sudah tersedia | Pengunjung/member |
| Login | Sudah tersedia | Member, admin, owner |
| Logout | Sudah tersedia | User login |
| Verifikasi email | Sudah tersedia | Member |
| Resend verification email | Sudah tersedia | Member |
| Dashboard protected | Sudah tersedia dasar | User verified |
| Profile | Sudah tersedia dasar dari Breeze | User login |
| Role member/admin/owner | Sudah tersedia | Member, admin, owner |
| Policy own-data awal | Sudah tersedia | Member |
| Auth UI Platinum Gym | Sudah tersedia dan dipoles visual | Pengunjung/member |
| Theme toggle | Sudah tersedia | Pengguna UI |
| Member portal v1 | Sudah tersedia dan dipoles UI | Member |
| Dashboard admin/owner placeholder | Sudah tersedia dasar | Admin, owner |
| Membership package | Direncanakan | Member, admin |
| Booking kelas | Direncanakan | Member, admin |
| Pembayaran | Direncanakan | Member, admin |
| Check-in gym | Direncanakan | Member, admin |
| Laporan owner | Direncanakan | Owner |
| AI Assistant backend | Direncanakan | Pengunjung, member |

## Public Website

### Tujuan

Fitur public website digunakan sebagai company profile digital untuk calon member dan pengunjung Platinum Gym Padang.

### Aktor

- Pengunjung website.
- Calon member.
- Member yang ingin melihat jadwal, produk, lokasi, dan informasi layanan.

### Halaman Public

| Route | Nama route | Fungsi |
|---|---|---|
| `/` | `public.home` | Beranda dan ringkasan CTA |
| `/tentang-kami` | `public.about` | Profil gym, keunggulan, dan coach |
| `/layanan` | `public.services` | Paket membership, PT, dan Muaythai |
| `/kelas` | `public.classes` | Jadwal kelas dengan filter hari dan jenis |
| `/produk` | `public.products` | Katalog produk dengan foto/fallback, harga, stok aktual, filter, pencarian, dan arahan lokasi |
| `/galeri` | `public.gallery` | Galeri aktivitas dengan visual resmi yang tersedia |
| `/lokasi` | `public.location` | Alamat, kontak, jam operasional, Google Maps iframe/fallback, dan Instagram |
| `/bmi` | `public.bmi` | Kalkulator BMI client-side |

### Data Utama

- `settings` untuk kontak public.
- `packages` untuk layanan dan harga paket.
- `gym_classes` dan `class_schedules` untuk jadwal kelas.
- `trainers` untuk coach.
- `product_categories` dan `products` untuk katalog produk, foto produk, harga, dan stok aktual.
- `promos`, `testimonials`, dan `galleries` untuk konten public.

### Catatan Keamanan

- Public website hanya membaca setting yang di-whitelist.
- Setting sensitif seperti `qr_secret` dan prompt AI tidak ditampilkan.
- Google Maps di halaman lokasi memakai iframe embed tanpa API key melalui setting public `maps_embed_url`, dengan fallback visual jika setting dikosongkan.
- BMI berjalan di browser dan tidak menyimpan data.
- Link eksternal memakai `rel="noopener noreferrer"`.

### Catatan UX dan Responsive

- Header memakai direct official logo tanpa wrapper dekoratif.
- Theme toggle memakai pola action-style: icon/label menunjukkan aksi berikutnya.
- Mobile navigation memiliki scroll containment untuk device pendek.
- Dynamic content pada paket, kelas, produk, galeri, testimoni, kontak, dan chatbot diberi wrapping guard agar tidak overflow.
- Home hero mobile memakai visual gym/strength umum sebagai visual utama; Muaythai tetap menjadi visual pendukung pada collage desktop.
- Halaman produk memakai CTA umum `Lihat Lokasi`; tidak ada CTA per produk, cart, checkout, pembayaran produk, invoice produk, atau transaksi produk online.
- Halaman produk menampilkan foto produk WebP jika tersedia dan memakai fallback visual untuk produk tanpa foto.
- Ringkasan audit responsive sudah dikonsolidasikan pada catatan UX fitur public website.

## Google OAuth Member

### Tujuan

Google OAuth digunakan agar pengunjung dapat login/register member dengan akun Google tanpa mengurangi kebutuhan data profil lokal.

### Alur Utama

```text
User memilih Google -> sistem redirect ke Google -> callback diterima -> sistem mencari social account atau email existing -> user login -> user Google baru diarahkan ke complete profile jika belum punya row member
```

### Behavior Penting

- Existing social account langsung login.
- Email Google yang sama dengan user lokal akan ditautkan ke user tersebut.
- User Google baru dibuat verified, diberi role `member`, lalu diarahkan ke `/member/complete-profile`.
- Token OAuth tidak boleh tampil di UI, docs, log, atau error response.
- Data `birth_date`, `gender`, `phone`, dan persetujuan terms tetap wajib diisi pada complete profile.

## Register Member

### Tujuan

Fitur register member digunakan agar pengunjung dapat membuat akun member Platinum Gym secara mandiri.

### Aktor

- Pengunjung.
- Calon member.

### Alur Fitur

```text
User membuka halaman register -> user mengisi data member -> sistem validasi data -> sistem membuat user -> sistem membuat data member -> sistem memberi role member -> sistem mengirim email verifikasi -> user diarahkan ke halaman verifikasi email
```

### Input Utama

- Nama lengkap.
- Tanggal lahir.
- Jenis kelamin.
- Nomor WhatsApp.
- Email.
- Password.
- Konfirmasi password.
- Persetujuan syarat layanan.

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/register` | `RegisteredUserController@create` |
| POST | `/register` | `RegisteredUserController@store` |

### Screenshot

Screenshot halaman register akan ditambahkan setelah dokumentasi visual disiapkan.

## Login

### Tujuan

Fitur login digunakan agar user dapat masuk ke sistem menggunakan email dan password.

### Aktor

- Member.
- Admin.
- Owner.

### Alur Fitur

```text
User membuka halaman login -> user memasukkan email dan password -> sistem validasi kredensial -> user masuk ke aplikasi -> sistem mengarahkan user ke dashboard
```

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/login` | `AuthenticatedSessionController@create` |
| POST | `/login` | `AuthenticatedSessionController@store` |

### Screenshot

Screenshot halaman login akan ditambahkan setelah dokumentasi visual disiapkan.

## Auth UI Platinum Gym

### Tujuan

Auth UI digunakan agar halaman login, register, forgot password, reset password, verify email, dan complete profile terasa konsisten dengan brand Platinum Gym Padang.

### Perubahan Visual Aktif

- Desktop memakai panel visual foto gym asli dengan overlay gelap agar brand terasa kuat tanpa mengganggu form.
- Mobile tidak memuat foto besar pada area form agar halaman register tetap ringan dan tidak terlalu panjang.
- Form auth memakai panel kontras dengan border, shadow, dan background light/dark yang tetap mudah dibaca.
- Elemen interaktif tetap memakai label, focus ring, dan target klik yang aman untuk keyboard/touch.

## Logout

### Tujuan

Fitur logout digunakan agar user dapat keluar dari sesi aplikasi dengan aman.

### Aktor

- User login.

### Alur Fitur

```text
User menekan tombol logout -> sistem menghapus sesi login -> user diarahkan keluar dari area protected
```

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| POST | `/logout` | `AuthenticatedSessionController@destroy` |

## Verifikasi Email

### Tujuan

Fitur verifikasi email memastikan email user valid sebelum user mengakses dashboard.

### Aktor

- Member baru.

### Alur Fitur

```text
Member berhasil register -> sistem mengirim email verifikasi -> member membuka link verifikasi -> sistem memvalidasi signed URL -> email member ditandai verified -> member diarahkan ke dashboard
```

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/verify-email` | `EmailVerificationPromptController` |
| GET | `/verify-email/{id}/{hash}` | `VerifyEmailController` |

### Screenshot

Screenshot halaman verifikasi email akan ditambahkan setelah dokumentasi visual disiapkan.

## Resend Verification Email

### Tujuan

Fitur ini digunakan jika member belum menerima email verifikasi atau link sebelumnya tidak ditemukan.

### Aktor

- Member belum verified.

### Alur Fitur

```text
Member membuka halaman verify-email -> member menekan tombol kirim ulang -> sistem mengirim ulang email verifikasi -> sistem menampilkan status sukses
```

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| POST | `/email/verification-notification` | `EmailVerificationNotificationController@store` |

## Dashboard Protected

### Tujuan

Dashboard hanya dapat diakses oleh user yang sudah login dan emailnya sudah diverifikasi.

### Aktor

- User verified.

### Alur Fitur

```text
User login -> sistem cek email_verified_at -> jika belum verified diarahkan ke halaman verifikasi -> jika sudah verified dashboard dapat dibuka
```

### Route

| Method | Route | Middleware |
|---|---|---|
| GET | `/dashboard` | `auth`, `verified` |

## Profile

### Tujuan

Fitur profile digunakan untuk melihat dan memperbarui informasi akun dasar user.

### Aktor

- User login.

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/profile` | `ProfileController@edit` |
| PATCH | `/profile` | `ProfileController@update` |
| DELETE | `/profile` | `ProfileController@destroy` |

## Role dan Dashboard Placeholder

### Tujuan

Role digunakan sebagai dasar pembatasan akses fitur member, admin, dan owner.

### Aktor

- Member.
- Admin.
- Owner.
- Developer.

### Alur Fitur

```text
User login -> sistem membaca role -> sistem redirect ke dashboard role -> route protected mengecek auth, verified, dan role
```

### Package Terkait

- `spatie/laravel-permission`

### Status

- Role final: `member`, `admin`, `owner`.
- Member portal v1 sudah aktif untuk dashboard, profil, membership, jadwal kelas, riwayat booking, transaksi, QR status, notifikasi, dan chatbot global statis.
- Dashboard admin dan owner saat ini masih placeholder untuk validasi auth dan role.
- Fitur bisnis lanjutan seperti checkout, booking submit, QR check-in, admin panel, dan owner report tetap mengikuti modul masing-masing.

## Member Portal v1

### Tujuan

Member portal digunakan agar member yang sudah login dapat mengecek informasi akun dan aktivitas layanan dari satu area operasional.

### Route Aktif

| Route | Fungsi |
|---|---|
| `/member/dashboard` | Ringkasan membership, paket sesi, transaksi, booking, QR, dan notifikasi |
| `/member/profil` | Profil member dan akses ke akun login Breeze |
| `/member/membership` | Status membership dan katalog paket layanan |
| `/member/booking-kelas` | Entry jadwal kelas yang tersedia untuk member |
| `/member/riwayat-booking` | Riwayat booking member |
| `/member/transaksi` | Ringkasan transaksi dan status pembayaran member |
| `/member/qr` | Status QR member tanpa menampilkan token mentah |
| `/member/notifikasi` | Daftar notifikasi dan status baca |
| `/member/complete-profile` | Pelengkapan profil member Google |

### Catatan UI dan Scope

- Sidebar dan mobile drawer hanya berisi navigasi, footer action, dan grouped menu `Utama`, `Aktivitas`, dan `Akun`.
- Identitas member, kode member, status membership, dan invoice tidak ditampilkan di sidebar agar tidak redundan.
- Chatbot member tersedia sebagai floating widget global statis di semua halaman member dan mengarah ke route internal.
- Route/page `/member/ai-assistant` tidak aktif; backend AI tetap masuk modul lanjutan.
- Checkout, booking submit, QR scannable, upload bukti bayar, invoice download, admin panel, dan laporan owner belum diaktifkan pada fase ini.

## Auth UI Platinum Gym

### Tujuan

Menyesuaikan tampilan halaman autentikasi dengan identitas brand Platinum Gym.

### Aktor

- Pengunjung.
- Member.

### Halaman Terkait

- Login.
- Register.
- Verify email.
- Forgot password.
- Reset password.
- Confirm password.

## Theme Toggle

### Tujuan

Memberikan pilihan tampilan dark/light dan mengikuti preferensi perangkat user.

### Aktor

- Pengguna UI.

### Alur Fitur

```text
Sistem membaca localStorage.theme -> jika belum ada sistem membaca prefers-color-scheme -> class dark dipasang atau dihapus -> user dapat mengubah tema melalui tombol toggle
```

Catatan standar UI: toggle tema menggunakan action-style. Saat light mode aktif, tombol menunjukkan aksi mengaktifkan dark mode; saat dark mode aktif, tombol menunjukkan aksi mengaktifkan light mode.

## Roadmap Fitur

Fitur berikut akan dijelaskan lebih detail setelah kebutuhan dan prioritas implementasi disepakati:

- Dashboard admin.
- Dashboard owner.
- Package membership.
- Booking kelas.
- Payment gateway.
- Check-in gym.
- Laporan owner.
- Manajemen konten company profile.

## Architecture Foundation

Refactor clean architecture pragmatis sudah dimulai sebelum fitur bisnis berikutnya.

### Struktur Baru

```text
app/Features/Auth/Actions
app/Features/Auth/Http/Requests
app/Features/PublicWebsite/Queries
app/Features/MemberPortal/Queries
app/Features/MemberPortal/ViewModels
app/Features/Shared/Support
resources/js/public-chatbot.js
```

### Prinsip

- Controller hanya orchestration.
- Workflow tulis data memakai Action.
- Validasi penting memakai FormRequest.
- Query/list/filter public memakai Query class.
- Eloquent model tetap berada di `app/Models`.
- Tidak memakai repository generic sebelum ada kebutuhan nyata.

### Production Handoff Commands

```bash
composer install
npm install
php artisan migrate --seed
php artisan test --no-ansi
npm.cmd run build
```
