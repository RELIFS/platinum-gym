# Installation Documentation

Dokumen ini menjelaskan langkah instalasi proyek Platinum Gym Padang pada lingkungan lokal.

## Persyaratan Sistem

- PHP 8.2 atau lebih baru.
- Composer.
- Node.js dan NPM.
- MySQL atau MariaDB.
- Git.
- Web browser modern.
- Terminal atau PowerShell.

## Clone Repository

```bash
git clone <url-repository>
cd platinum-gym
```

Ganti `<url-repository>` dengan URL repository GitHub proyek.

## Install Dependency Backend

```bash
composer install
```

Perintah ini membaca `composer.json` dan memasang package PHP yang terkunci pada `composer.lock`.

Catatan dependency: `composer.json` memakai `config.platform.php=8.2.31` agar `composer.lock` tetap kompatibel dengan environment CI dan server PHP 8.2, walaupun developer lokal memakai PHP yang lebih baru.

## Install Dependency Frontend

```bash
npm install
```

Perintah ini membaca `package.json` dan memasang package frontend yang terkunci pada `package-lock.json`.

## Setup Environment

Salin file environment contoh:

```bash
cp .env.example .env
```

Pada Windows PowerShell, jika `cp` tidak tersedia, gunakan:

```powershell
Copy-Item .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

## Setup Database

Buat database MySQL atau MariaDB, misalnya:

```text
platinum_gym
```

Sesuaikan konfigurasi `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=platinum_gym
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Buat link public storage untuk file yang harus bisa dibaca browser, seperti foto profil member:

```bash
php artisan storage:link
```

## Konfigurasi Integrasi Opsional

`.env.example` sudah menyediakan placeholder non-secret untuk Google OAuth, Resend, Midtrans, Gemini, mail, queue, dan session secure cookie. Isi hanya nilai yang dibutuhkan pada `.env` lokal atau production; jangan commit secret ke Git.

Untuk Google OAuth lokal, pastikan nilai berikut konsisten dengan Google Cloud Console:

```env
APP_URL=http://127.0.0.1:8000
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Untuk email Resend, isi nilai berikut pada `.env` lokal atau server:

```env
MAIL_MAILER=resend
RESEND_API_KEY=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Platinum Gym Padang"
```

Email production memakai Laravel notification/Markdown mail branded Platinum Gym untuk verifikasi email, reset password, undangan akun member dari admin, pembayaran, dan booking. Default lokal tetap boleh memakai `MAIL_MAILER=log`; production memakai `MAIL_MAILER=resend` dengan domain/from address yang sudah diverifikasi.

Untuk hosting sementara berbasis shared CWP/cPanel yang belum jelas memiliki Supervisor atau queue worker permanen, gunakan mode sync agar email Resend terkirim otomatis saat request register/resend/reset/invitation/payment/booking diproses:

```env
QUEUE_CONNECTION=sync
```

Mode ini praktis untuk trafik awal dan hosting terbatas. Jika nanti memakai VPS atau hosting yang mendukung worker permanen, gunakan queue database:

```env
QUEUE_CONNECTION=database
```

Jalankan worker pada server production melalui process manager:

```bash
php artisan queue:work --tries=3 --timeout=90
```

Notification penting tetap dibuat queued dan dipanggil `afterCommit()` supaya email tidak keluar sebelum transaksi database selesai. Dengan `QUEUE_CONNECTION=sync`, Laravel memproses job tersebut langsung pada request setelah transaksi commit.

Jika email verifikasi register tidak masuk saat `QUEUE_CONNECTION=database`, cek dulu apakah job masih menunggu worker:

```bash
php artisan tinker --execute="dump(['mail_default'=>config('mail.default'),'queue_default'=>config('queue.default'),'jobs'=>DB::table('jobs')->count(),'failed_jobs'=>DB::table('failed_jobs')->count()]);"
php artisan queue:failed --no-ansi
```

Jika `jobs` bertambah dan `failed_jobs=0`, form register sudah membuat email dengan benar; jalankan queue worker dan minta user memeriksa Inbox, Spam, atau Promosi.

Catatan untuk shared hosting: kuota mailbox cPanel/CWP seperti `Email 0/0` tidak menghalangi pengiriman melalui Resend API. Yang penting adalah `MAIL_MAILER=resend`, sender domain terverifikasi, API key valid di `.env`, dan mode queue sesuai kemampuan hosting.

Untuk Midtrans Sandbox, isi nilai berikut pada `.env` lokal atau server:

```env
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

Untuk Gymmi Gemini, isi minimal satu API key Gemini pada `.env`:

```env
GEMINI_API_KEY=
GEMINI_API_KEYS=
GEMINI_MODEL=gemini-2.0-flash
GEMINI_TIMEOUT=12
GEMINI_MAX_RETRIES=2
GYMMI_AI_NORMALIZER_ENABLED=true
GYMMI_AI_NORMALIZER_MIN_CONFIDENCE=60
GYMMI_AI_NORMALIZER_MAX_OUTPUT_TOKENS=260
```

Nilai key tidak boleh ditulis di dokumentasi, commit, screenshot, atau output terminal yang dibagikan.

Jika tim memiliki banyak key Gemini di file private, sinkronkan ke `.env` lokal lewat command aman. Default command hanya dry-run dan menampilkan jumlah/fingerprint non-secret:

```bash
php artisan gymmi:sync-gemini-keys
php artisan gymmi:sync-gemini-keys --status
php artisan gymmi:sync-gemini-keys --write-env
```

`GEMINI_API_KEYS` mendukung format comma-separated atau newline-separated. Command tidak mencetak nilai key, melakukan trim/deduplicate/validasi format, dan menolak `--write-env` saat `APP_ENV=production` tanpa `--force`. Untuk batch 100 key, jalankan dry-run dulu dan jangan tulis `.env` kecuali output menunjukkan tepat `Valid unique keys: 100`. Untuk production/cPanel, masukkan key melalui environment/secret manager hosting, bukan membaca file `.txt` pada runtime request.

Knowledge base Gymmi dikompilasi dari workbook internal tim ke JSON runtime. Jalankan ulang command ini setelah `data_AI_Chatbot.xlsx` berubah:

```bash
php artisan gymmi:import-knowledge
```

Runtime membaca `resources/data/gymmi/knowledge-base.json`, bukan file Excel per request. File `Gymmi API Key.txt` tidak dipakai runtime dan tidak boleh dibaca, dicetak, atau disalin ke source code.

Saat runtime, Gymmi memakai pola hybrid RAG dua tahap: Gemini normalizer hanya merapikan bahasa user menjadi JSON aman, Laravel memilih JSON knowledge base dan database live yang boleh diakses, lalu Gemini answer writer menyusun jawaban dari snippet yang sudah dipilih. JSON knowledge base dipakai untuk FAQ/Alias/Config/knowledge stabil, sedangkan database live dipakai untuk paket aktif, promo valid, jadwal kelas aktif, produk aktif/stok, setting publik whitelist, serta ringkasan data member login sendiri. Artifact workbook terbaru berisi FAQ 137 dan Alias 1578. File `resources/data/gymmi/knowledge-overrides.json` boleh dipakai untuk koreksi kecil yang sudah divalidasi setelah import workbook, tanpa membuat CSV runtime baru. Jika normalizer atau answer writer rate limit/gagal, fallback lokal tetap dipakai dan tetap menjawab natural dari data resmi.

## Build Asset Frontend

Untuk production build:

```bash
npm.cmd run build
```

## Deploy Shared CWP/cPanel Production

Target sementara production: `https://platinumgympadang.web.id` pada shared CWP/cPanel. Jangan mengasumsikan server memiliki Supervisor atau queue worker permanen.

Nilai `.env` production minimum:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://platinumgympadang.web.id
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
SESSION_SECURE_COOKIE=true
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=noreply@mail.platinumgympadang.web.id
QUEUE_CONNECTION=sync
MIDTRANS_IS_PRODUCTION=false
```

Catatan Midtrans: jangan mengubah `MIDTRANS_IS_PRODUCTION=true` sampai akun production Midtrans, domain live, callback/webhook URL, dan credential live sudah siap serta diuji eksplisit.

Struktur deploy yang disarankan:

- Document root hosting diarahkan ke folder Laravel `public/`.
- Jika hosting memaksa `public_html`, pindahkan isi `public/` ke `public_html` hanya dengan penyesuaian path bootstrap yang eksplisit dan aman; folder aplikasi Laravel tetap berada di luar document root.
- Pastikan `storage/` dan `bootstrap/cache/` writable oleh user PHP hosting.
- Exclude dari package/upload production: `.git`, `.env` backup, `node_modules`, `tests`, log, cache, browser profile, artifact audit UI, dan workspace konteks/private internal.

Command install/build/deploy:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setelah deploy, jalankan smoke test:

- HTTPS aktif dan tidak menampilkan debug page.
- Public pages `/`, `/tentang-kami`, `/layanan`, `/kelas`, `/produk`, `/galeri`, `/lokasi`, `/bmi`, `/syarat-ketentuan`, dan `/kebijakan-privasi` terbuka.
- Login/register dan email verification bekerja memakai Resend.
- Boundary role member/admin/owner tetap benar.
- Mail path memakai queue `sync` pada shared hosting sementara.
- Storage link bisa menampilkan file public yang memang boleh public.
- Gymmi tetap fallback aman jika provider quota/key bermasalah.
- Midtrans tetap Sandbox kecuali production switch sudah siap.
- Tidak ada `.env`, secret, raw payment payload, raw QR token, atau token OAuth yang tampil di UI/log/screenshot.

Untuk development mode:

```bash
npm.cmd run dev
```

Catatan Windows PowerShell: jika `npm` diblokir karena execution policy, gunakan `npm.cmd`.

## Menjalankan Aplikasi

```bash
php artisan serve
```

Aplikasi akan berjalan pada:

```text
http://127.0.0.1:8000
```

## Menjalankan Test

```bash
php artisan test --no-ansi
```

Proyek menggunakan Pest PHP sebagai framework testing. Pest berjalan di atas PHPUnit dan tetap memakai ekosistem testing Laravel.

## Akun dan Role

Role yang disiapkan pada proyek:

- `member`
- `admin`
- `owner`

Saat registrasi member berhasil, sistem membuat user, membuat data member, memberikan role `member`, mengirim email verifikasi berisi kode 6 digit plus tombol signed-link fallback, lalu mengarahkan user ke halaman verifikasi email. Jika admin membuat member dari panel admin, sistem mengirim undangan akun sekali pakai agar member mengatur kata sandi sendiri.

Akun seeded untuk local/development setelah `php artisan migrate --seed`:

| Role | Email | Password | Catatan |
|---|---|---|---|
| Admin | `admin@platinumgympadang.com` | `password` | Login lewat `/login`, lalu redirect ke `/admin` |
| Owner | `owner@platinumgympadang.com` | `password` | Login lewat `/login`, lalu redirect ke `/owner` |

Akun seeded hanya untuk local/development. Ganti password atau nonaktifkan akun tersebut sebelum production.

## Akses Admin Lokal

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Buka halaman login:

```text
/login
```

Masuk memakai akun admin seeded. Jika role valid, sistem mengarahkan user ke:

```text
/admin
```

Admin memakai halaman auth umum `/login`; tidak ada halaman login admin terpisah pada arsitektur aktif saat ini.

## Troubleshooting

### APP_KEY belum dibuat

Gejala:

```text
No application encryption key has been specified.
```

Solusi:

```bash
php artisan key:generate
```

### Database belum tersedia

Gejala:

```text
SQLSTATE[HY000] [1049] Unknown database
```

Solusi:

- Buat database sesuai nilai `DB_DATABASE`.
- Cek `DB_USERNAME` dan `DB_PASSWORD` pada `.env`.
- Jalankan ulang migration.

```bash
php artisan migrate --seed
```

### NPM diblokir PowerShell

Gejala:

```text
npm.ps1 cannot be loaded because running scripts is disabled on this system
```

Solusi:

```bash
npm.cmd install
npm.cmd run build
```

### Asset masih mengarah ke Vite dev server

Gejala:

- Halaman mencoba memuat asset dari `http://[::1]:5173`.
- File CSS atau JS tidak muncul saat mode production.

Solusi:

- Hentikan Vite dev server jika tidak dipakai.
- Hapus file `public/hot` jika masih ada.
- Jalankan build ulang.

```bash
npm.cmd run build
```

### Cache konfigurasi bermasalah

Solusi:

```bash
php artisan optimize:clear
```

### Permission storage bermasalah pada Linux/macOS

Solusi:

```bash
chmod -R 775 storage bootstrap/cache
```

Pada Windows, pastikan folder `storage` dan `bootstrap/cache` dapat ditulis oleh aplikasi.

### Foto profil atau file public storage tidak tampil

Gejala:

- Upload foto berhasil, tetapi gambar tidak tampil di browser.
- URL `/storage/...` menghasilkan 403 atau 404.

Solusi:

```bash
php artisan storage:link
```

Pastikan folder `storage/app/public` dapat ditulis aplikasi dan `public/storage` mengarah ke folder tersebut.

## Verifikasi Instalasi

Instalasi dianggap berhasil jika:

- Halaman utama dapat dibuka.
- Halaman public `/`, `/tentang-kami`, `/layanan`, `/kelas`, `/produk`, `/galeri`, `/lokasi`, dan `/bmi` dapat dibuka.
- Halaman register dapat dibuka.
- User member dapat registrasi.
- Halaman pemberitahuan verifikasi email muncul setelah registrasi.
- Member dapat membuka dashboard, membership, transaksi, booking, QR, dan notifikasi setelah login dan email verified.
- Admin local dapat login lewat `/login` dan masuk ke `/admin`.
- Admin dapat membuka dashboard, pembayaran, booking, check-in, produk, pengaturan, audit log, dan laporan.
- Owner local dapat login lewat `/login` dan masuk ke `/owner`.
- Owner dapat membuka dashboard bisnis, laporan, export CSV, dan invoice web sesuai data yang tersedia.
- Test berjalan tanpa gagal.
- Asset frontend berhasil dibuild.
