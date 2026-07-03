# Feature Documentation

Status: Updated 2026-07-02. Dokumen ini diperbarui seiring finalisasi kebutuhan dan implementasi fitur.

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
| Member portal | Operasional: profil/avatar/bukti mahasiswa, eligibility checkout, membership checkout, paket sesi, booking dengan guard akses membership/paket sesi, transaksi, QR visual/download, notifikasi, dan server-side pagination/filter | Member |
| Admin portal | Production custom Blade: CRUD master data, approval inbox, review bukti mahasiswa, pembayaran, booking, preview-confirm check-in, pemakaian sesi kelas berbasis booking, settings, audit, laporan CSV/Excel/PDF, invoice/struk, dan tabel paginated | Admin |
| Owner portal | Operasional read-only: dashboard bisnis, laporan, export CSV/Excel/PDF, invoice web, dan struk transaksi | Owner |
| Membership package | Checkout Midtrans dan approval admin aktif | Member, admin |
| Booking kelas | Booking/cancel member dan confirm/cancel admin aktif | Member, admin |
| Pembayaran | Midtrans Sandbox, webhook, invoice, approval/reject admin aktif | Member, admin |
| Check-in gym | QR member stabil per member, aktif untuk membership atau sesi Muaythai/Poundfit aktif, download QR, preview-confirm admin, dan pemakaian paket sesi kelas yang wajib terhubung ke booking terkonfirmasi | Member, admin |
| Laporan owner | Sudah tersedia dengan filter dan export CSV/Excel/PDF | Owner |
| Gymmi Gemini AI | Operasional hybrid RAG dengan dataset, database live, normalizer teks, knowledge override, Gemini, jawaban lokal aman berbasis data resmi, guardrail, dan conversation log | Pengunjung, member |

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

### Screenshot Public Website

| Halaman | Screenshot |
|---|---|
| Beranda | [`public-home.png`](screenshot/public/public-home.png) |
| Tentang Kami | [`public-about.png`](screenshot/public/public-about.png) |
| Layanan | [`public-services.png`](screenshot/public/public-services.png) |
| Kelas | [`public-classes.png`](screenshot/public/public-classes.png) |
| Produk | [`public-products.png`](screenshot/public/public-products.png) |
| Galeri | [`public-gallery.png`](screenshot/public/public-gallery.png) |
| Lokasi | [`public-location.png`](screenshot/public/public-location.png) |
| BMI | [`public-bmi.png`](screenshot/public/public-bmi.png) |

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

Screenshot halaman register tersedia di [`auth-register.png`](screenshot/auth/auth-register.png).

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

Screenshot halaman login tersedia di [`auth-login.png`](screenshot/auth/auth-login.png).


## Admin Portal

### Tujuan

Admin portal digunakan sebagai area kerja operasional Platinum Gym untuk memproses CRUD master data, pembayaran, booking kelas, preview-confirm check-in QR kamera, settings publik, audit log, dan laporan operasional.

Admin login melalui `/login`. User dengan role `admin` diarahkan ke `/admin` dan seluruh route admin tetap dibatasi middleware `auth`, `verified`, dan `role:admin`.

### Route Aktif

| Route | Fungsi |
|---|---|
| `/admin` | Dashboard ringkasan operasional |
| `/admin/check-in` | Preview QR member, confirm check-in, dan pemakaian paket sesi kelas yang terhubung ke booking terkonfirmasi |
| `/admin/booking` | Booking kelas admin, konfirmasi, dan pembatalan |
| `/admin/notifikasi` | Inbox persetujuan admin untuk pengajuan yang perlu ditinjau |
| `/admin/anggota` | Daftar member, status akses, kategori, dan verifikasi bukti mahasiswa |
| `/admin/anggota/{member}/bukti-mahasiswa/review` | Halaman review bukti mahasiswa |
| `/admin/anggota/{member}/bukti-mahasiswa` | Preview aman bukti mahasiswa dari private storage |
| `/admin/anggota/{member}/bukti-mahasiswa/setujui` | Setujui bukti mahasiswa |
| `/admin/anggota/{member}/bukti-mahasiswa/tolak` | Tolak bukti mahasiswa |
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
| `/admin/pengaturan` | Form whitelist untuk informasi publik website dan invoice |
| `/admin/profil` | Profil admin login dengan upload foto profil |
| `/admin/profil/foto` | Update foto profil admin |

### Screenshot Admin Portal

| Halaman | Screenshot |
|---|---|
| Dashboard | [`admin-dashboard.png`](screenshot/admin/admin-dashboard.png) |
| Anggota | [`admin-members.png`](screenshot/admin/admin-members.png) |
| Paket | [`admin-packages.png`](screenshot/admin/admin-packages.png) |
| Kelas dan jadwal | [`admin-classes.png`](screenshot/admin/admin-classes.png) |
| Pembayaran | [`admin-payments.png`](screenshot/admin/admin-payments.png) |
| Booking | [`admin-booking.png`](screenshot/admin/admin-booking.png) |
| Check-in | [`admin-check-in.png`](screenshot/admin/admin-check-in.png) |
| Produk | [`admin-products.png`](screenshot/admin/admin-products.png) |
| Laporan | [`admin-reports.png`](screenshot/admin/admin-reports.png) |
| Pengaturan | [`admin-settings.png`](screenshot/admin/admin-settings.png) |
| Audit log | [`admin-audit-log.png`](screenshot/admin/admin-audit-log.png) |
| Profil admin | [`admin-profile.png`](screenshot/admin/admin-profile.png) |
| Form tambah paket | [`admin-resource-create-package.png`](screenshot/detail/admin-resource-create-package.png) |

### Catatan Scope

- Admin memakai Blade/Tailwind/Alpine production, bukan Filament.
- Dashboard admin memakai pusat kerja operasional dengan KPI ringkas, quick links, dan data terbaru dari database.
- Tabel modul admin memakai server-side search, status filter, query string persistence, dan pagination 12 data per halaman pada data yang dapat bertambah.
- Master data anggota, paket, kelas, jadwal kelas, produk, galeri, testimoni, promo, dan trainer memakai reusable custom Blade resource form untuk tambah/edit.
- `/admin/notifikasi` dipakai sebagai approval inbox untuk data yang membutuhkan tindakan admin. Tahap aktif saat ini adalah review bukti mahasiswa yang diunggah member.
- `/admin/anggota` memakai kolom operasional `Nama`, `Kode Member`, `WhatsApp`, `Status Member`, `Kategori`, `Verifikasi`, dan `Bergabung`; NIM tidak ditampilkan di tabel, review, atau form admin karena bukti KTM/portal mahasiswa menjadi dasar verifikasi.
- Bukti mahasiswa disimpan di private local disk dan hanya dipreview lewat route terproteksi admin/member pemilik. Admin dapat menyetujui atau menolak bukti dengan catatan tanpa membuka path file mentah.
- Pembayaran cash membuat payment, invoice, dan aktivasi layanan dalam transaksi aman.
- Pembayaran dapat disetujui/ditolak admin; Midtrans webhook tetap menjadi sumber kebenaran untuk payment online.
- Check-in admin memvalidasi QR dari kamera, menampilkan preview data member terlebih dahulu, lalu confirm check-in atau pemakaian paket sesi secara eksplisit. Scan QR saja tidak membuat check-in dan tidak mengurangi sesi.
- `Gunakan Sesi` untuk Muaythai/Poundfit hanya tersedia jika member punya booking kelas `confirmed` hari ini, jadwal/kelas aktif, paket dan coach cocok, serta booking belum pernah dipakai. Saat berhasil, sistem mengurangi satu sesi, membuat attendance kelas, menautkan usage ke booking, dan mengubah booking menjadi `attended`.
- QR member adalah identitas stabil per member. Pembelian membership baru tidak mengganti QR yang sudah aktif; QR juga bisa aktif untuk penggunaan sesi Muaythai/Poundfit aktif tanpa membership, sedangkan check-in membership tetap membutuhkan membership aktif. Personal Trainer tetap memakai flow sesi existing dengan membership Gym/Include aktif dan belum memakai booking kelas.
- Modul paket, produk, galeri, testimoni, promo, trainer, dan member memiliki aksi status aman berupa aktif/nonaktif atau tayang/draft, bukan hard delete.
- Semua route admin memakai middleware `auth`, `verified`, dan `role:admin`; aksi tulis juga mengecek permission.
- Nilai setting sensitif seperti API key, token, secret, OAuth, prompt, dan password tidak ditampilkan di halaman pengaturan operasional.
- Foto profil admin hanya menerima gambar lokal tervalidasi dan tidak menghapus avatar eksternal atau folder avatar role lain saat replace.
- Form pengaturan hanya mengubah kontak publik, Instagram, jam operasional, dan invoice footer; Google Maps dikelola sebagai konfigurasi teknis non-UI, dan secret/API key tidak bisa diedit dari UI ini.

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

### Screenshot Owner Portal

| Halaman | Screenshot |
|---|---|
| Dashboard | [`owner-dashboard.png`](screenshot/owner/owner-dashboard.png) |
| Pusat laporan | [`owner-reports.png`](screenshot/owner/owner-reports.png) |
| Laporan keuangan | [`owner-reports-finance.png`](screenshot/owner/owner-reports-finance.png) |
| Laporan member | [`owner-reports-members.png`](screenshot/owner/owner-reports-members.png) |
| Laporan booking kelas | [`owner-reports-classes.png`](screenshot/owner/owner-reports-classes.png) |
| Profile dan keamanan akun | [`owner-profile.png`](screenshot/owner/owner-profile.png) |
| Invoice read-only | [`owner-invoice-detail.png`](screenshot/detail/owner-invoice-detail.png) |

### Catatan Scope

- Owner hanya membaca data bisnis dan laporan; tidak ada aksi tambah, ubah, hapus, approve, reject, atau cancel.
- Pendapatan owner dihitung dari pembayaran dengan status `paid`; pembayaran pending, waiting, rejected, dan cancelled tidak masuk pendapatan.
- Export laporan tersedia sebagai CSV native, Excel `.xlsx`, dan PDF.
- Invoice web, PDF invoice, dan struk POS compact hanya menampilkan data transaksi yang aman dan tidak menampilkan token QR mentah, payload payment, secret, atau data internal provider.

## Gymmi Chatbot Public dan Member

### Tujuan

Gymmi membantu pengunjung dan member menemukan informasi layanan dari data resmi Platinum Gym. Sistem memakai hybrid RAG: dataset terkompilasi untuk FAQ/Alias/Config/knowledge stabil, database live untuk data yang berubah, lalu Gemini API hanya untuk merangkai jawaban natural dari snippet yang sudah dipilih.

### Behavior Aktif

- Public dan member memakai floating chatbot melalui endpoint `POST /gymmi/chat`; kontrak response tetap `{ reply: { text }, source }`.
- Knowledge base utama berasal dari workbook internal `data_AI_Chatbot.xlsx` dan dikompilasi menjadi `resources/data/gymmi/knowledge-base.json` lewat `php artisan gymmi:import-knowledge`.
- Artifact knowledge base terbaru berisi FAQ 137 dan Alias 1578, termasuk tambahan variasi alias untuk gym, Muaythai, pendaftaran, bukti mahasiswa/KTM, kontak, produk, fasilitas, dan kebijakan.
- Runtime chat membaca JSON terkompilasi dengan cache, bukan membaca Excel per request.
- Strategi jawaban: normalisasi teks, input guard, FAQ direct answer, Alias untuk memahami variasi pertanyaan, intent ringan untuk topik seperti jadwal kelas/harga/coach/kapasitas/private, Config/catalog snippets, knowledge override, database live snippets, Gemini composition, lalu jawaban lokal aman.
- Database live hanya mengambil snippet aman dari data aktif/published: paket, promo valid, jadwal kelas aktif, trainer aktif, produk/kategori aktif, dan setting publik whitelist. Data live menang untuk harga, promo, jadwal, dan stok jika berbeda dari dataset.
- Retrieval kelas dibuat spesifik agar pertanyaan Muaythai, termasuk typo seperti `muaytai`, tidak menarik Aerobic/Poundfit. Jika data resmi belum menyebut sesi privat, Gymmi menjawab konservatif dan mengarahkan konfirmasi ke admin.
- `resources/data/gymmi/knowledge-overrides.json` menyimpan koreksi knowledge yang sudah divalidasi setelah import workbook, termasuk variasi alias layanan, produk, bukti mahasiswa, kontak, dan pertanyaan Muaythai; file ini digabung saat runtime tanpa membaca Excel per request.
- Gymmi member hanya boleh memakai data user login sendiri: ringkasan membership aktif, paket sesi aktif, transaksi menunggu, booking sendiri, dan status QR tanpa token mentah. Data member lain, raw payment payload, raw QR token, dan secret tidak boleh dikirim ke Gemini atau ditampilkan.
- Produk tetap bersifat katalog informasi; Gymmi tidak membuat klaim checkout produk online.
- Gemini hanya dipanggil jika input lolos guard dan ada snippet data resmi yang relevan; pertanyaan kosong, spam, prompt injection, permintaan API key/token/secret, bypass role, akses database, dan topik di luar Platinum Gym dijawab aman tanpa dikirim ke provider.
- Multi-key Gemini disimpan hanya di `.env`/environment server melalui `GEMINI_API_KEYS`; command `php artisan gymmi:sync-gemini-keys` membantu dry-run/status/sinkronisasi `.env` lokal dari file private tanpa mencetak key dan bukan dependency runtime request.
- Jawaban yang tampil ke user memakai Bahasa Indonesia natural seperti customer service Platinum Gym, ringkas, dan tidak menyebut istilah internal seperti `Gemini`, `fallback`, `provider`, `rate limit`, `snippet`, `prompt`, atau `data lokal`.
- Message log memakai `role="log"` dan `aria-live="polite"`.
- Pesan user tampil di kanan tanpa avatar visual `AN`.
- FAQ quick reply tampil sebagai rail horizontal yang bisa discroll, keyboard-focusable, dan tidak membuat halaman overflow.
- Pesan bot tampil di kiri dengan avatar gambar Gymmi light/dark; fallback initial `GY` tetap tersedia jika asset tidak termuat.
- Warna Gymmi public dan member mengikuti tema light/dark aktif untuk panel, input, bubble, quick reply, typing state, dan action link.
- Saat Gymmi mengetik, send button dan quick replies dinonaktifkan agar pesan tidak dobel.
- Prompt Gymmi memakai konteks terstruktur dari snippet knowledge base, database live, dan data member login sendiri bila ada. Jika layanan AI tidak tersedia, jalur aman tetap merangkai jawaban natural dari data resmi, bukan menampilkan snippet mentah atau mekanisme internal. Conversation log disimpan ke `ai_conversations`/`ai_messages`, route memakai throttle `gymmi`, dan gangguan provider tidak merusak UI.

## Auth UI Platinum Gym

### Tujuan

Auth UI digunakan agar halaman login, register, forgot password, reset password, verify email, dan complete profile terasa konsisten dengan brand Platinum Gym Padang.

### Perubahan Visual Aktif

- Desktop memakai panel visual foto gym asli dengan overlay gelap agar brand terasa kuat tanpa mengganggu form.
- Mobile tidak memuat foto besar pada area form agar halaman register tetap ringan dan tidak terlalu panjang.
- Form auth memakai panel kontras dengan border, shadow, dan background light/dark yang tetap mudah dibaca.
- Elemen interaktif tetap memakai label, focus ring, dan target klik yang aman untuk keyboard/touch.
- Halaman login/register menampilkan ringkasan error `Periksa kembali data yang ditandai di bawah ini.`, field error dekat input, `aria-invalid`, `aria-describedby`, dan fokus otomatis ke field invalid pertama.

### Screenshot Auth

| Halaman | Screenshot |
|---|---|
| Login | [`auth-login.png`](screenshot/auth/auth-login.png) |
| Register | [`auth-register.png`](screenshot/auth/auth-register.png) |
| Lupa password | [`auth-forgot-password.png`](screenshot/auth/auth-forgot-password.png) |

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
Member berhasil register -> sistem mengirim email berisi kode 6 digit dan tombol link fallback -> member memasukkan kode di halaman verify-email atau membuka signed link -> sistem memvalidasi kode/link -> email member ditandai verified -> member diarahkan ke dashboard
```

Catatan operasional: email verifikasi dikirim otomatis oleh aplikasi, bukan manual. Pada shared hosting tanpa worker permanen, gunakan `QUEUE_CONNECTION=sync` agar Resend dipanggil langsung saat register/resend. Jika `QUEUE_CONNECTION=database`, email baru keluar setelah queue worker berjalan. Pending job dengan `attempts=0` berarti email masih menunggu worker, bukan form register gagal atau Resend memblokir recipient yang pernah dipakai.

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/verify-email` | `EmailVerificationPromptController` |
| POST | `/verify-email` | `VerifyEmailCodeController` |
| GET | `/verify-email/{id}/{hash}` | `VerifyEmailController` |

### Screenshot

Screenshot halaman verifikasi email belum dicapture pada batch ini karena membutuhkan state akun belum verified yang aman dan stabil.

## Kirim Ulang Kode Verifikasi Email

### Tujuan

Fitur ini digunakan jika member belum menerima kode verifikasi atau link fallback sebelumnya tidak ditemukan.

### Aktor

- Member belum verified.

### Alur Fitur

```text
Member membuka halaman verify-email -> member menekan tombol kirim ulang -> sistem membatalkan kode lama, membuat kode baru, mengirim ulang email verifikasi branded, dan menampilkan status sukses
```

Halaman verifikasi menampilkan email tujuan dalam bentuk masked, misalnya `lut***@gmail.com`, agar user tahu alamat tujuan tanpa membuka full email di layar.

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| POST | `/email/verification-notification` | `EmailVerificationNotificationController@store` |

## Undangan Akun Member Dari Admin

### Tujuan

Flow ini dipakai ketika admin membuat akun member secara manual. Sistem tidak mengirim kode verifikasi biasa; member menerima link sekali pakai untuk mengatur kata sandi dan mengaktifkan akun.

### Aktor

- Admin.
- Member baru yang dibuat admin.

### Alur Fitur

```text
Admin membuat member -> sistem membuat user dengan kata sandi acak internal -> sistem membuat token undangan hashed yang berlaku 72 jam -> email undangan dikirim -> member membuka link -> member mengatur password -> email ditandai verified -> token marked accepted -> member login
```

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/undangan-akun/{token}` | `AccountInvitationController@show` |
| POST | `/undangan-akun/{token}` | `AccountInvitationController@store` |
| POST | `/admin/anggota/{member}/undangan` | `AdminMemberInvitationController` |

## Email Operasional

Pembayaran berhasil/ditolak dan lifecycle booking memakai email branded Platinum Gym berbasis Laravel Markdown mail. Email dikirim melalui Laravel notification dan `afterCommit()` agar tidak dikirim sebelum transaksi database sukses; pada shared hosting sementara mode queue dapat memakai `sync`, sedangkan VPS/worker-capable production dapat memakai `database` queue dengan worker aktif. Database notification member tetap dipertahankan untuk portal, sedangkan email operasional memuat data yang relevan seperti kode pembayaran, layanan, nominal, status, jadwal kelas, trainer bila ada, dan CTA internal aplikasi. Theme email `platinum` memakai card putih responsif, CTA gold, OTP card, panel ringkas, dan detail transaksi/booking table-like agar aman dibaca di email client mobile maupun desktop.

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

- `/member/profil` digunakan member untuk melihat dan mengubah data profil layanan: nama, email, WhatsApp, gender, tanggal lahir, alamat, kontak darurat, status mahasiswa, dan bukti mahasiswa berupa KTM atau screenshot akun portal mahasiswa.
- `/profile` digunakan untuk keamanan akun: email login, password, verifikasi email, dan penghapusan akun.
- Perubahan email dari `/member/profil` mereset status verifikasi email dan mengarahkan user ke flow verifikasi.
- Nomor WhatsApp dinormalisasi dan harus unik.

### Route dan Controller

| Method | Route | Controller |
|---|---|---|
| GET | `/member/profil` | `MemberPortalController@profile` |
| GET | `/member/profil/bukti-mahasiswa` | `MemberProfileController@studentProof` |
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
- Member portal sudah aktif untuk dashboard, profil, checkout membership/paket sesi, booking kelas, riwayat booking, transaksi, QR status, notifikasi, dan chatbot global Gymmi Gemini-backed dengan fallback aman berbasis data resmi.
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
| `/member/booking-kelas` | Booking jadwal kelas sesuai membership atau paket sesi aktif |
| `/member/riwayat-booking` | Riwayat booking member |
| `/member/transaksi` | Riwayat transaksi, detail, invoice, struk, PDF, dan tombol bayar Midtrans |
| `/member/qr` | QR member stabil per member tanpa menampilkan token mentah |
| `/member/notifikasi` | Daftar notifikasi, baca satu, dan baca semua |
| `/member/complete-profile` | Pelengkapan profil member Google |

### Screenshot Member Portal

| Halaman | Screenshot |
|---|---|
| Dashboard | [`member-dashboard.png`](screenshot/member/member-dashboard.png) |
| Profil | [`member-profile.png`](screenshot/member/member-profile.png) |
| Edit profil | [`member-profile-edit.png`](screenshot/member/member-profile-edit.png) |
| Membership | [`member-membership.png`](screenshot/member/member-membership.png) |
| Booking kelas | [`member-booking.png`](screenshot/member/member-booking.png) |
| Riwayat booking | [`member-booking-history.png`](screenshot/member/member-booking-history.png) |
| Transaksi | [`member-transactions.png`](screenshot/member/member-transactions.png) |
| Detail transaksi | [`member-transaction-detail.png`](screenshot/detail/member-transaction-detail.png) |
| QR member | [`member-qr.png`](screenshot/member/member-qr.png) |
| Notifikasi | [`member-notifications.png`](screenshot/member/member-notifications.png) |

### Catatan UI dan Scope

- Sidebar dan mobile drawer berisi navigasi portal, shortcut menu bawah, footer identity, dan grouped menu `Utama`, `Aktivitas`, dan `Akun`.
- Identitas member, kode member, status membership, dan invoice tidak ditampilkan di sidebar agar tidak redundan.
- `Website Utama` ditampilkan sebagai item menu paling bawah menuju website publik; desktop identity/logout berada di topbar account menu, sementara mobile drawer tetap menyimpan identity member dan `Keluar`. Shortcut akun login tidak diduplikasi di sidebar member.
- Booking kelas memakai datepicker Flatpickr default dengan display lokal `dd/mm/yyyy` yang menonaktifkan tanggal di luar hari jadwal kelas; jika member belum punya membership atau paket sesi yang sesuai, card tetap tampil tetapi tanggal dan tombol `Booking Kelas` disabled tanpa caption tambahan. Membership `include` membuka semua kelas included, membership dengan tipe yang sama membuka kelas included yang sesuai, dan membership yang tidak relevan tidak membuka akses kelas lain.
- Katalog membership, booking kelas, riwayat booking, transaksi, dan notifikasi memakai server-side pagination/filter dengan query string agar pencarian berlaku pada seluruh data milik member, bukan hanya item yang sedang terlihat.
- Batas list member dibuat tetap: paket 6 item, jadwal 9 item, transaksi 8 item, riwayat booking 8 item, dan notifikasi 8 item per halaman.
- Checkout membership dan paket sesi mewajibkan profil dasar lengkap plus foto profil sebelum payment/session dibuat; Muaythai, Poundfit, dan Personal Trainer menampilkan disabled state dengan CTA `Lengkapi data` jika profil belum lengkap.
- Gymmi tersedia sebagai floating widget global Gemini-backed di semua halaman member dan tetap memakai data member login sendiri untuk action aman.
- Gymmi member menampilkan action `QR Member` ke `/member/qr` dan tidak menampilkan token QR mentah.
- QR member tetap sama selama token tidak dirotasi/dicabut secara internal; status aktifnya mengikuti membership aktif atau paket sesi Muaythai/Poundfit aktif. Pemakaian sesi Muaythai/Poundfit melalui admin check-in tetap mensyaratkan booking kelas `confirmed` hari ini yang cocok dan belum dipakai. Paket Personal Trainer tetap membutuhkan membership Gym/Include aktif dan tidak mengaktifkan QR sendiri.
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
- Monitoring/admin tooling Gymmi lanjutan bila nanti dibutuhkan.
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
