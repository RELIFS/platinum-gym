# Feature Documentation

Status: Updated 2026-06-22. Dokumen ini diperbarui seiring finalisasi kebutuhan dan implementasi fitur.

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
| Dashboard protected | Sudah tersedia | User verified |
| Profile dan keamanan akun | Sudah tersedia; profil member view-only di `/member/profil`, edit di `/member/profil/edit`, keamanan akun di `/profile` | Member/user login |
| Role member/admin/owner | Sudah tersedia | Member, admin, owner |
| Policy own-data | Sudah tersedia | Member |
| Auth UI Platinum Gym | Sudah tersedia dan dipoles visual | Pengunjung/member |
| Theme toggle | Sudah tersedia | Pengguna UI |
| Member portal | Operasional: profil/avatar, eligibility checkout, membership checkout, paket sesi, booking, transaksi, QR visual/download, notifikasi, dan server-side pagination/filter | Member |
| Admin portal | Production custom Blade: CRUD master data, pembayaran, booking, preview-confirm check-in, settings, audit, laporan CSV/Excel/PDF, invoice/struk, dan tabel paginated | Admin |
| Owner portal | Operasional read-only: dashboard bisnis, laporan, export CSV/Excel/PDF, invoice web, dan struk transaksi | Owner |
| Membership package | Checkout Midtrans dan approval admin aktif | Member, admin |
| Booking kelas | Booking/cancel member dan confirm/cancel admin aktif | Member, admin |
| Pembayaran | Midtrans Sandbox, webhook, invoice, approval/reject admin aktif | Member, admin |
| Check-in gym | QR member stabil per member, download QR aktif, preview-confirm admin, dan pemakaian paket sesi eksplisit | Member, admin |
| Laporan owner | Sudah tersedia dengan filter dan export CSV/Excel/PDF | Owner |
| Gymmi Gemini AI | Operasional dengan Gemini, fallback lokal, guardrail, dan conversation log | Pengunjung, member |

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
- Halaman layanan mengurutkan paket dari query layer agar konsisten: membership dimulai dari Gym/Senam umum, lalu Gym/Senam mahasiswa; Muaythai dimulai dari 1x, Umum 4x/8x, lalu Mahasiswa 4x/8x; setelah itu Personal Trainer dan Session.
- Halaman kelas dikelompokkan menjadi section Aerobic, Zumba, Muaythai, dan Poundfit dengan filter jenis yang tetap kompatibel dengan data jadwal lama.
- `page-hero` public dipoles sebagai partial reusable compact-premium dengan ukuran teks responsif, dekorasi ringan, dan animasi masuk CSS-only yang menghormati reduced motion.
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

### Catatan Validasi

- Error validasi register memakai Bahasa Indonesia yang ramah user untuk field wajib, format email, format No. WhatsApp, tanggal lahir, duplikat email/WhatsApp, persetujuan terms, dan konfirmasi kata sandi.
- Tanggal lahir yang diisi tetapi tidak valid menampilkan pesan format `dd/mm/yyyy`, bukan pesan teknis field internal.

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

### Catatan Validasi

- Error login memakai Bahasa Indonesia.
- Kredensial salah memakai pesan generic aman agar UI tidak membedakan apakah email tidak terdaftar atau kata sandi salah.
- Percobaan login berulang tetap dibatasi rate limiter dan menampilkan pesan tunggu yang ramah user.

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/login` | `AuthenticatedSessionController@create` |
| POST | `/login` | `AuthenticatedSessionController@store` |

### Screenshot

Screenshot halaman login akan ditambahkan setelah dokumentasi visual disiapkan.


## Admin Portal

### Tujuan

Admin portal digunakan sebagai area kerja operasional Platinum Gym untuk memproses CRUD master data, pembayaran, booking kelas, preview-confirm check-in QR kamera, settings publik, audit log, dan laporan operasional.

Admin login melalui `/login`. User dengan role `admin` diarahkan ke `/admin` dan seluruh route admin tetap dibatasi middleware `auth`, `verified`, dan `role:admin`.

### Route Aktif

| Route | Fungsi |
|---|---|
| `/admin` | Dashboard ringkasan operasional |
| `/admin/check-in` | Preview QR member, confirm check-in, pemakaian paket sesi, dan input manual |
| `/admin/booking` | Booking kelas admin, konfirmasi, dan pembatalan |
| `/admin/notifikasi` | Ringkasan notifikasi operasional |
| `/admin/anggota` | Daftar member terbaru |
| `/admin/paket` | Katalog paket layanan |
| `/admin/kelas` | Jadwal kelas aktif |
| `/admin/pembayaran` | Pembayaran terbaru, cash payment, approve, dan reject |
| `/admin/produk` | Katalog produk dan stok |
| `/admin/galeri` | Galeri website |
| `/admin/testimoni` | Testimoni website |
| `/admin/promo` | Promo website |
| `/admin/trainer` | Data trainer |
| `/admin/laporan` | Ringkasan laporan dan export CSV/Excel/PDF |
| `/admin/invoice/{invoice}` | Invoice transaksi read-only untuk admin |
| `/admin/invoice/{invoice}/struk` | Struk POS compact transaksi |
| `/admin/invoice/{invoice}/download` | Download PDF invoice atau struk |
| `/admin/audit-log` | Activity log terbaru dengan filter |
| `/admin/pengaturan` | Setting website whitelist dengan value sensitif tersamarkan |
| `/admin/profil` | Profil admin login dengan upload foto profil |
| `/admin/profil/foto` | Update foto profil admin |

### Catatan Scope

- Admin memakai Blade/Tailwind/Alpine production, bukan Filament.
- Dashboard admin memakai pusat kerja operasional dengan KPI ringkas, quick links, dan data terbaru dari database.
- Tabel modul admin memakai server-side search, status filter, query string persistence, dan pagination 12 data per halaman pada data yang dapat bertambah.
- Master data anggota, paket, kelas, jadwal kelas, produk, galeri, testimoni, promo, dan trainer memakai reusable custom Blade resource form untuk tambah/edit.
- Pembayaran cash membuat payment, invoice, dan aktivasi layanan dalam transaksi aman.
- Pembayaran dapat disetujui/ditolak admin; Midtrans webhook tetap menjadi sumber kebenaran untuk payment online.
- Check-in admin memvalidasi QR dari kamera, menampilkan preview data member terlebih dahulu, lalu confirm check-in atau pemakaian paket sesi secara eksplisit. Scan QR saja tidak membuat check-in dan tidak mengurangi sesi.
- QR member adalah identitas check-in stabil per member. Pembelian membership baru tidak mengganti QR yang sudah aktif; kelayakan check-in tetap dicek dari membership aktif saat scan.
- Modul paket, produk, galeri, testimoni, promo, trainer, dan member memiliki aksi status aman berupa aktif/nonaktif atau tayang/draft, bukan hard delete.
- Semua route admin memakai middleware `auth`, `verified`, dan `role:admin`; aksi tulis juga mengecek permission.
- Nilai setting sensitif seperti API key, token, secret, OAuth, prompt, dan password disamarkan sebagai `Tersamarkan`.
- Foto profil admin hanya menerima gambar lokal tervalidasi dan tidak menghapus avatar eksternal atau folder avatar role lain saat replace.
- Form pengaturan hanya mengubah kontak publik, maps, jam operasional, dan invoice footer; secret/API key tidak bisa diedit dari UI ini.

## Owner Portal

### Tujuan

Owner portal digunakan sebagai area monitoring bisnis read-only untuk melihat kondisi pendapatan, transaksi, member, membership, booking kelas, laporan, dan invoice tanpa mengubah data operasional.

Owner login melalui `/login`. User dengan role `owner` diarahkan ke `/owner` dan seluruh route owner dibatasi middleware `auth`, `verified`, dan `role:owner`.

### Route Aktif

| Route | Fungsi |
|---|---|
| `/owner` | Dashboard bisnis dengan KPI, grafik pendapatan, breakdown, transaksi terbaru, dan membership yang akan berakhir |
| `/owner/laporan` | Pusat laporan dengan filter periode, status, metode, dan tipe laporan |
| `/owner/laporan/keuangan` | Laporan pendapatan dan transaksi terkonfirmasi |
| `/owner/laporan/member` | Laporan member dan membership |
| `/owner/laporan/booking-kelas` | Laporan booking dan kelas |
| `/owner/laporan/export` | Export CSV, Excel, atau PDF untuk laporan owner |
| `/owner/invoice/{invoice}` | Tampilan invoice web read-only |
| `/owner/invoice/{invoice}/struk` | Struk POS compact transaksi |
| `/owner/invoice/{invoice}/download` | Download PDF invoice atau struk |
| `/owner/profil/foto` | Update foto profil owner |

### Catatan Scope

- Owner hanya membaca data bisnis dan laporan; tidak ada aksi tambah, ubah, hapus, approve, reject, atau cancel.
- Pendapatan owner dihitung dari pembayaran dengan status `paid`; pembayaran pending, waiting, rejected, dan cancelled tidak masuk pendapatan.
- Export laporan tersedia sebagai CSV native, Excel `.xlsx`, dan PDF.
- Invoice web, PDF invoice, dan struk POS compact hanya menampilkan data transaksi yang aman dan tidak menampilkan token QR mentah, payload payment, secret, atau data internal provider.

## Gymmi Chatbot Public dan Member

### Tujuan

Gymmi membantu pengunjung dan member menemukan informasi layanan lewat Gemini API dengan fallback lokal jika provider gagal atau quota habis.

### Behavior Aktif

- Public dan member memakai floating chatbot Gemini-backed melalui endpoint `POST /gymmi/chat`, dengan fallback intention-based lokal.
- Message log memakai `role="log"` dan `aria-live="polite"`.
- Pesan user tampil di kanan tanpa avatar visual `AN`.
- FAQ quick reply tampil sebagai rail horizontal yang bisa discroll, keyboard-focusable, dan tidak membuat halaman overflow.
- Pesan bot tampil di kiri dengan avatar gambar Gymmi light/dark; fallback initial `GY` tetap tersedia jika asset tidak termuat.
- Warna Gymmi public dan member mengikuti tema light/dark aktif untuk panel, input, bubble, quick reply, typing state, dan action link.
- Saat Gymmi mengetik, send button dan quick replies dinonaktifkan agar pesan tidak dobel.
- Prompt Gymmi memakai konteks aman dari data publik dan data member sendiri jika login. Conversation log disimpan ke `ai_conversations`/`ai_messages`, route memakai throttle `gymmi`, dan provider failure tidak merusak UI.

## Auth UI Platinum Gym

### Tujuan

Auth UI digunakan agar halaman login, register, forgot password, reset password, verify email, dan complete profile terasa konsisten dengan brand Platinum Gym Padang.

### Perubahan Visual Aktif

- Desktop memakai panel visual foto gym asli dengan overlay gelap agar brand terasa kuat tanpa mengganggu form.
- Mobile tidak memuat foto besar pada area form agar halaman register tetap ringan dan tidak terlalu panjang.
- Form auth memakai panel kontras dengan border, shadow, dan background light/dark yang tetap mudah dibaca.
- Elemen interaktif tetap memakai label, focus ring, dan target klik yang aman untuk keyboard/touch.
- Halaman login/register menampilkan ringkasan error `Periksa kembali data yang ditandai di bawah ini.`, field error dekat input, `aria-invalid`, `aria-describedby`, dan fokus otomatis ke field invalid pertama.

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

## Profile dan Keamanan Akun

### Tujuan

Fitur profile dipisah menjadi dua area agar data member dan credential login tidak tercampur.

### Aktor

- Member/user login.

### Area Fitur

- `/member/profil` digunakan member untuk melihat dan mengubah data profil layanan: nama, email, WhatsApp, gender, tanggal lahir, alamat, kontak darurat, status mahasiswa, tinggi, dan berat badan.
- `/profile` digunakan untuk keamanan akun: email login, password, verifikasi email, dan penghapusan akun.
- Perubahan email dari `/member/profil` mereset status verifikasi email dan mengarahkan user ke flow verifikasi.
- Nomor WhatsApp dinormalisasi dan harus unik.

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/member/profil` | `MemberPortalController@profile` |
| PATCH | `/member/profil` | `MemberProfileController@update` |
| GET | `/profile` | `ProfileController@edit` |
| PATCH | `/profile` | `ProfileController@update` |
| DELETE | `/profile` | `ProfileController@destroy` |

## Role dan Dashboard

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
- Member portal sudah aktif untuk dashboard, profil, checkout membership/paket sesi, booking kelas, riwayat booking, transaksi, QR status, notifikasi, dan chatbot global Gymmi Gemini-backed dengan fallback lokal.
- Admin portal sudah aktif untuk CRUD master data, pembayaran, booking, check-in, settings, audit, laporan CSV/Excel/PDF, dan invoice/struk.
- Owner portal sudah aktif untuk dashboard bisnis read-only, laporan web, export CSV/Excel/PDF, invoice web, dan struk transaksi.
- AI admin automation belum dibuat.

## Member Portal

### Tujuan

Member portal digunakan agar member yang sudah login dapat mengecek informasi akun dan aktivitas layanan dari satu area operasional.

### Route Aktif

| Route | Fungsi |
|---|---|
| `/member/dashboard` | Ringkasan membership, paket sesi, transaksi, booking, QR, dan notifikasi |
| `/member/profil` | Profil member editable dan akses ke keamanan akun |
| `/member/membership` | Status membership, katalog paket, dan checkout Midtrans |
| `/member/booking-kelas` | Booking jadwal kelas sesuai membership/paket sesi/payment |
| `/member/riwayat-booking` | Riwayat booking member |
| `/member/transaksi` | Riwayat transaksi, detail, invoice, struk, PDF, dan tombol bayar Midtrans |
| `/member/qr` | QR member stabil per member tanpa menampilkan token mentah |
| `/member/notifikasi` | Daftar notifikasi, baca satu, dan baca semua |
| `/member/complete-profile` | Pelengkapan profil member Google |

### Catatan UI dan Scope

- Sidebar dan mobile drawer berisi navigasi portal, shortcut menu bawah, footer identity, dan grouped menu `Utama`, `Aktivitas`, dan `Akun`.
- Identitas member, kode member, status membership, dan invoice tidak ditampilkan di sidebar agar tidak redundan.
- `Website Utama` ditampilkan sebagai item menu paling bawah menuju website publik, sementara footer sidebar/drawer fokus pada identity member dan `Keluar`; shortcut akun login tidak diduplikasi di sidebar member.
- Katalog membership, booking kelas, riwayat booking, transaksi, dan notifikasi memakai server-side pagination/filter dengan query string agar pencarian berlaku pada seluruh data milik member, bukan hanya item yang sedang terlihat.
- Batas list member dibuat tetap: paket 6 item, jadwal 9 item, transaksi 8 item, riwayat booking 8 item, dan notifikasi 8 item per halaman.
- Gymmi tersedia sebagai floating widget global Gemini-backed di semua halaman member dan tetap mengarah ke route internal untuk action aman.
- Gymmi member menampilkan action `QR Member` ke `/member/qr` dan tidak menampilkan token QR mentah.
- QR member tetap sama selama token tidak dirotasi/dicabut secara internal; status aktifnya mengikuti membership aktif, bukan paket membership tertentu.
- Route/page `/member/ai-assistant` tidak aktif; Gymmi tetap berupa widget global, bukan halaman terpisah.
- Checkout membership/paket sesi, booking kelas, QR check-in admin, payment webhook Midtrans Sandbox, approval admin, dan notifikasi member aktif. Produk tetap katalog informasi, bukan checkout produk.




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

- Upload bukti pembayaran manual jika dibutuhkan.
- Export queue untuk laporan saat dataset operasional besar.
- Refund/correction workflow pembayaran.
- Pengembangan Gymmi lanjutan untuk knowledge base FAQ dan monitoring admin.
- Upload media konten website melalui storage/media library.

## Architecture Foundation

Clean architecture pragmatis sudah dipakai pada modul auth, public, member, admin, payment, booking, check-in, dan Gymmi.

### Struktur Baru

```text
app/Features/Auth/Actions
app/Features/Auth/Http/Requests
app/Features/PublicWebsite/Queries
app/Features/MemberPortal/Queries
app/Features/MemberPortal/Actions
app/Features/MemberPortal/ViewModels
app/Features/Admin/Queries
app/Features/Admin/Actions
app/Features/Admin/Support
app/Features/OwnerPortal/Queries
app/Features/Reports/Data
app/Features/Reports/Actions
app/Features/Reports/Exports
app/Features/Reports/Queries
app/Features/Invoices/Actions
app/Features/Invoices/Queries
app/Features/Payments/Actions
app/Features/Payments/Contracts
app/Features/Payments/Gateways
app/Features/Bookings/Actions
app/Features/CheckIns/Actions
app/Features/Gymmi/Actions
app/Features/Gymmi/Clients
app/Features/Gymmi/Contracts
app/Features/Shared/Support
resources/js/public-chatbot.js
```

### Prinsip

- Controller hanya orchestration.
- Workflow tulis data memakai Action.
- Validasi penting memakai FormRequest.
- Query/list/filter public, member, dan admin memakai Query class sesuai kebutuhan.
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
