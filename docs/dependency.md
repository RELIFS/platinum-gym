# Dependency Documentation

Dokumen ini menjelaskan dependency proyek Platinum Gym Padang berdasarkan kondisi repository saat ini.

## Identitas

- Nama proyek: Website Company Profile dan Sistem Informasi Layanan Platinum Gym Padang.
- Framework utama: Laravel.
- Tujuan: Mengidentifikasi dependency/package Laravel yang digunakan atau direncanakan, menjelaskan kegunaannya dengan pendekatan 5W+1H, dan mencatat dampaknya terhadap evolusi perangkat lunak.

## Ringkasan

Dependency dikelola menggunakan Composer untuk package PHP/Laravel dan NPM untuk package frontend. Dependency yang sudah terpasang dicatat pada `composer.json`, `composer.lock`, `package.json`, dan `package-lock.json`.

Composer dikunci dengan `config.platform.php=8.2.31` agar proses `composer update` dari mesin developer PHP 8.4 tidak menarik package yang tidak bisa dipasang di CI/server PHP 8.2. Dengan begitu, `composer install` tetap stabil di lokal, CI, dan deployment target PHP 8.2+.

Status penggunaan dibagi menjadi:

- Sudah digunakan: package sudah terpasang dan sudah dipakai pada fitur aplikasi.
- Sudah dipasang, belum diimplementasikan penuh: package sudah ada pada dependency, tetapi fiturnya belum aktif penuh.
- Dependency development: package digunakan untuk testing, debugging, formatting, atau development.
- Dependency frontend: package digunakan untuk build dan tampilan frontend.
- Direncanakan: package belum terpasang atau belum digunakan, tetapi direncanakan untuk fitur berikutnya.
- Tidak digunakan pada arsitektur aktif: package pernah dipertimbangkan, tetapi keputusan saat ini memakai implementasi custom Laravel/Blade.

## Dependency Backend Terpasang

| Package | Fungsi | Alasan | Versi | Risiko | Status |
|---|---|---|---|---|---|
| `laravel/framework` | Framework utama aplikasi | Menyediakan routing, MVC, ORM, migration, middleware, queue, dan fitur inti Laravel | `^12.0` | Perubahan major version dapat memerlukan penyesuaian kode | Sudah digunakan |
| `laravel/socialite` | OAuth login menggunakan provider pihak ketiga | Digunakan untuk login/register Google member | `*` | Perlu konfigurasi OAuth, callback URL, dan pengamanan secret | Sudah digunakan |
| `laravel/tinker` | Interaksi aplikasi melalui REPL | Membantu debugging dan eksplorasi model saat development | `^2.10.1` | Tidak untuk workflow production user | Dependency development |
| `spatie/laravel-permission` | Role dan permission | Mengelola role `member`, `admin`, dan `owner` secara rapi | `*` | Salah konfigurasi role dapat membuka akses tidak sesuai | Sudah digunakan |
| `spatie/laravel-medialibrary` | Upload dan manajemen media | Disiapkan untuk foto profil, galeri, bukti pembayaran, dan konten website | `*` | Butuh pengaturan storage, validasi file, dan pembatasan ukuran upload | Sudah dipasang, belum diimplementasikan penuh |
| `spatie/laravel-activitylog` | Audit log aktivitas sistem | Mencatat dan membaca perubahan data penting serta aktivitas admin | `*` | Log dapat membesar jika tidak dibatasi atau dibersihkan | Sudah digunakan |
| `resend/resend-php` | Transport email Resend | Dipakai untuk pengiriman email aplikasi melalui `MAIL_MAILER=resend` | `^1.3` | Perlu domain/from address valid dan API key aman di `.env` | Sudah digunakan |
| `midtrans/midtrans-php` | Integrasi payment gateway Midtrans | Mendukung pembayaran membership, paket sesi, kelas berbayar, dan webhook Sandbox | `^2.3` | Signature/webhook dan server key wajib aman | Sudah digunakan |
| `simplesoftwareio/simple-qrcode` | QR Code generator Laravel | Menyediakan dependency Bacon QR untuk QR member/check-in | `^4.2` | QR token tidak boleh dirender sebagai teks mentah | Sudah digunakan |

## Dependency Development dan Testing

| Package | Fungsi | Alasan | Versi | Risiko | Status |
|---|---|---|---|---|---|
| `laravel/breeze` | Starter kit autentikasi | Mempercepat pembuatan login, register, reset password, verifikasi email, dan profil | `*` | Scaffold perlu disesuaikan dengan kebutuhan UI dan role | Sudah digunakan |
| `pestphp/pest` | Framework testing | Sintaks test ringkas dan mudah dibaca | `^3.8` | Tim yang terbiasa PHPUnit klasik perlu adaptasi | Sudah digunakan |
| `pestphp/pest-plugin-laravel` | Integrasi Pest dengan Laravel | Memudahkan feature test, database refresh, dan helper Laravel | `^3.2` | Bergantung pada kompatibilitas Pest dan Laravel | Sudah digunakan |
| `laravel/pint` | Code style fixer | Menjaga konsistensi format kode PHP | `^1.24` | Perlu disepakati sebelum dijalankan pada banyak file | Dependency development |
| `fakerphp/faker` | Data dummy untuk factory/test | Membantu pembuatan data pengujian | `^1.23` | Data dummy tidak boleh dianggap data real | Dependency development |
| `mockery/mockery` | Mocking untuk test | Membantu isolasi unit test | `^1.6` | Mock berlebihan bisa membuat test kurang realistis | Dependency development |
| `nunomaduro/collision` | Error reporting CLI | Membuat pesan error command line lebih mudah dibaca | `^8.6` | Hanya untuk development | Dependency development |
| `laravel/sail` | Environment Docker Laravel | Alternatif menjalankan app dengan Docker | `^1.41` | Butuh Docker dan konfigurasi tambahan | Dependency development |
| `laravel/pail` | Log viewer CLI | Membantu membaca log aplikasi saat development | `^1.2.2` | Tidak menggantikan monitoring production | Dependency development |

## Dependency Frontend

Tailwind content scan mencakup Blade dan JavaScript app:

```text
resources/views/**/*.blade.php
resources/js/**/*.js
```

Scan `resources/js/**/*.js` diperlukan karena renderer Gymmi membuat sebagian class bubble/chat dari JavaScript.

| Package | Fungsi | Alasan | Versi | Risiko | Status |
|---|---|---|---|---|---|
| `vite` | Build tool frontend | Build asset CSS dan JavaScript Laravel | `^7.0.7` | Perlu versi Node yang kompatibel | Sudah digunakan |
| `laravel-vite-plugin` | Integrasi Laravel dengan Vite | Menghubungkan Blade dengan hasil build Vite | `^2.0.0` | Asset gagal dimuat jika build atau manifest bermasalah | Sudah digunakan |
| `tailwindcss` | Utility CSS framework | Membantu membuat UI responsif dan konsisten | `^3.1.0` | Class dinamis perlu dipastikan ikut ter-build | Sudah digunakan |
| `@tailwindcss/forms` | Styling form Tailwind | Memperbaiki tampilan input form autentikasi | `^0.5.2` | Perlu override jika desain custom sangat spesifik | Sudah digunakan |
| `@tailwindcss/vite` | Integrasi Tailwind dengan Vite | Mendukung proses build frontend | `^4.0.0` | Perlu perhatian kompatibilitas dengan versi Tailwind yang dipakai | Dependency frontend |
| `alpinejs` | Interaktivitas ringan frontend | Digunakan untuk behavior UI sederhana seperti dropdown atau toggle | `^3.4.2` | Tidak cocok untuk state management kompleks | Sudah digunakan |
| `axios` | HTTP client JavaScript | Disiapkan untuk request AJAX frontend | `^1.11.0` | Perlu pengaturan CSRF dan error handling | Dependency frontend |
| `apexcharts` | Grafik interaktif frontend | Digunakan untuk grafik dashboard Admin dan Owner dengan tooltip, gradient, marker hover, dan theme-aware rendering | `^5.15.2` | Menambah ukuran bundle sehingga harus di-load lazy hanya pada halaman yang memakai grafik | Sudah digunakan |
| `html5-qrcode` | Scanner QR kamera frontend | Digunakan secara lazy pada halaman admin check-in untuk membaca QR member dari kamera | `2.3.8` | Akses kamera membutuhkan secure context/HTTPS atau localhost dan permission browser | Sudah digunakan |
| `concurrently` | Menjalankan beberapa command dev | Membantu menjalankan server, queue, dan Vite bersamaan | `^9.0.1` | Hanya kebutuhan development | Dependency development |
| `postcss` | CSS processing | Digunakan dalam pipeline Tailwind/Vite | `^8.4.31` | Konfigurasi salah dapat membuat build CSS gagal | Dependency frontend |
| `autoprefixer` | Vendor prefix CSS | Menambah kompatibilitas browser | `^10.4.2` | Umumnya rendah, mengikuti konfigurasi browser target | Dependency frontend |

## Dependency Rencana Pengembangan

| Package | Fungsi | Modul Rencana | Alasan | Status |
|---|---|---|---|---|
| `maatwebsite/excel` | Import/export Excel dan CSV | Import member lama dan export laporan | Memudahkan pengolahan laporan admin/owner | Direncanakan |
| `barryvdh/laravel-dompdf` | Generate PDF dari Blade | Invoice dan laporan cetak | PDF mudah dicetak dan diarsipkan | Direncanakan |

## Integrasi Eksternal Tanpa Package Tambahan

| Integrasi | Fungsi | Modul | Alasan | Status |
|---|---|---|---|---|
| Google Gemini API REST | AI assistant Gymmi | Public/member chatbot | Laravel HTTP client cukup untuk timeout, retry, fallback, dan test tanpa menambah package Composer | Sudah digunakan |
| Native streamed CSV | Export laporan ringan | Admin/owner reports | Response streaming Laravel cukup untuk export CSV awal tanpa menambah package Excel/PDF | Sudah digunakan |

## Dependency Yang Tidak Dipakai Pada Arsitektur Aktif

| Package | Fungsi | Keputusan | Alasan |
|---|---|---|---|
| `filament/filament` | Admin panel Laravel | Tidak dipasang | Admin production memakai custom Blade/Tailwind/Alpine agar UI, flow, permission, dan NFR mengikuti kebutuhan Platinum Gym. |
| `livewire/livewire` | Komponen interaktif Laravel | Tidak dipasang | Interaksi saat ini cukup memakai Blade, controller, FormRequest, Action, dan Alpine ringan. |

## Analisis 5W+1H Dependency Utama

## 1. Laravel Breeze

| 5W+1H | Penjelasan |
|---|---|
| What | Laravel Breeze adalah starter kit autentikasi Laravel. Package ini menyediakan login, register, forgot password, reset password, verifikasi email, dan profil pengguna. |
| Why | Breeze digunakan karena sistem membutuhkan autentikasi dasar untuk member, admin, dan owner. Struktur Breeze sederhana dan cocok untuk pembelajaran Laravel. |
| Who | Digunakan oleh member, admin, owner, dan developer sebagai dasar pengembangan fitur autentikasi. |
| When | Digunakan sejak tahap awal pengembangan saat membuat register, login, logout, verifikasi email, dan profil. |
| Where | Digunakan pada modul autentikasi, dashboard protected, profil pengguna, dan reset password. |
| How | Breeze dipasang melalui Composer, lalu scaffold Blade digunakan dan disesuaikan dengan kebutuhan UI serta role Platinum Gym. |

Referensi:

- https://laravel.com/docs/starter-kits
- https://github.com/laravel/breeze

## 2. Laravel Socialite

| 5W+1H | Penjelasan |
|---|---|
| What | Laravel Socialite adalah package Laravel untuk autentikasi menggunakan provider pihak ketiga seperti Google. |
| Why | Socialite digunakan agar member bisa mendaftar dan login menggunakan akun Google. |
| Who | Digunakan oleh member sebagai alternatif login/register. Admin dan owner tetap dapat memakai email dan password. |
| When | Digunakan pada route redirect dan callback Google OAuth. |
| Where | Digunakan pada modul autentikasi, callback Google, auto-link email existing, pembuatan user member Google, dan onboarding complete profile. |
| How | Socialite dikonfigurasi dengan Google OAuth Client ID, Client Secret, dan callback URL. Setelah user berhasil login Google, sistem mengambil email/nama/provider ID, menautkan atau membuat user, memberi role `member`, lalu mengarahkan user baru ke complete profile jika data member lokal belum lengkap. |

Referensi:

- https://laravel.com/docs/socialite
- https://github.com/laravel/socialite

## 3. Spatie Laravel Permission

| 5W+1H | Penjelasan |
|---|---|
| What | Spatie Laravel Permission adalah package untuk mengelola role dan permission pengguna pada Laravel. |
| Why | Package ini digunakan karena sistem memiliki beberapa jenis pengguna dengan hak akses berbeda, yaitu member, admin, dan owner. |
| Who | Digunakan oleh user sesuai role dan developer untuk middleware, role checking, dan permission checking. |
| When | Digunakan setelah autentikasi dibuat, terutama saat assign role member pada registrasi dan pembatasan akses fitur. |
| Where | Digunakan pada user model, proses registrasi, route, controller, middleware, policy, dan tampilan menu sesuai role. |
| How | Package dipasang melalui Composer, migration dijalankan, role dibuat melalui seeder atau `findOrCreate`, lalu user diberi role sesuai jenis akun. |

Referensi:

- https://spatie.be/docs/laravel-permission
- https://github.com/spatie/laravel-permission

## 4. Spatie Laravel MediaLibrary

| 5W+1H | Penjelasan |
|---|---|
| What | Spatie Laravel MediaLibrary adalah package untuk upload, penyimpanan, dan relasi file media pada model Laravel. |
| Why | Package ini disiapkan untuk foto galeri, gambar produk, bukti pembayaran, foto profil member, dan gambar promosi. |
| Who | Digunakan oleh admin untuk konten dan member secara tidak langsung saat upload foto profil atau bukti pembayaran. |
| When | Digunakan saat modul upload media mulai diimplementasikan. |
| Where | Direncanakan pada modul galeri, produk, promo, profil member, dan pembayaran. |
| How | Model yang membutuhkan file akan memakai trait `InteractsWithMedia`; file disimpan di storage Laravel dan dihubungkan ke model terkait. |

Referensi:

- https://spatie.be/docs/laravel-medialibrary
- https://github.com/spatie/laravel-medialibrary

## 5. Resend

| 5W+1H | Penjelasan |
|---|---|
| What | `resend/resend-php` adalah library PHP untuk mengirim email melalui Resend. |
| Why | Package ini dipakai agar email verifikasi, reset password, dan notifikasi operasional bisa dikirim lewat provider email yang punya free tier. |
| Who | Digunakan oleh sistem untuk mengirim email kepada member dan admin sesuai flow aplikasi. |
| When | Digunakan saat mailer aplikasi memakai `MAIL_MAILER=resend` dan `RESEND_API_KEY` tersedia di `.env`. |
| Where | Digunakan pada konfigurasi mail Laravel dan notifikasi/email transaksional. |
| How | Laravel mailer memakai transport `resend`; secret hanya dibaca dari `.env`, bukan dari source code. |

Referensi:

- https://resend.com/docs
- https://github.com/resend/resend-php

## 6. Simple QRCode

| 5W+1H | Penjelasan |
|---|---|
| What | `simplesoftwareio/simple-qrcode` adalah package Laravel untuk membuat QR Code dan membawa dependency Bacon QR. |
| Why | Package ini dipasang karena sistem membutuhkan QR member untuk proses check-in gym. |
| Who | Digunakan oleh member untuk menampilkan QR dan admin untuk memverifikasi check-in. |
| When | Digunakan saat QR member aktif setelah membership aktif dan admin melakukan check-in. |
| Where | Digunakan pada halaman QR member dan modul check-in admin. |
| How | Sistem membuat QR visual dari token check-in; token tidak ditampilkan sebagai teks mentah di UI member. |

Referensi:

- https://github.com/SimpleSoftwareIO/simple-qrcode

## 7. Maatwebsite Laravel Excel

| 5W+1H | Penjelasan |
|---|---|
| What | Maatwebsite Laravel Excel adalah package untuk import dan export Excel/CSV pada Laravel. |
| Why | Package ini direncanakan untuk import data member lama dan export laporan admin/owner. |
| Who | Digunakan oleh admin dan owner. |
| When | Digunakan saat migrasi data awal, import member, dan export laporan. |
| Where | Direncanakan pada modul member, transaksi, booking, dan laporan. |
| How | Developer membuat class Import/Export untuk memvalidasi, membaca, menyimpan, dan menghasilkan file Excel. |

Referensi:

- https://docs.laravel-excel.com
- https://github.com/SpartnerNL/Laravel-Excel

## 8. barryvdh/laravel-dompdf

| 5W+1H | Penjelasan |
|---|---|
| What | `barryvdh/laravel-dompdf` adalah package Laravel untuk membuat PDF dari view Blade. |
| Why | Package ini direncanakan untuk invoice, struk pembayaran, dan laporan cetak. |
| Who | Digunakan oleh member, admin, dan owner. |
| When | Digunakan setelah transaksi selesai atau saat laporan perlu dicetak. |
| Where | Direncanakan pada modul transaksi, pembayaran, invoice, dan laporan. |
| How | Sistem membuat view Blade khusus, lalu DomPDF mengubah view tersebut menjadi file PDF. |

Referensi:

- https://github.com/barryvdh/laravel-dompdf

## 9. Google Gemini API REST

| 5W+1H | Penjelasan |
|---|---|
| What | Google Gemini API REST digunakan langsung melalui Laravel HTTP client untuk fitur Gymmi. |
| Why | Direct HTTP client cukup untuk kebutuhan generateContent, timeout, retry, fallback, dan test tanpa menambah package Composer. |
| Who | Digunakan oleh pengunjung dan member saat bertanya lewat Gymmi. Admin automation belum dibuat. |
| When | Digunakan saat user mengirim pesan dari widget Gymmi public atau member. |
| Where | Endpoint `POST /gymmi/chat`, frontend `resources/js/public-chatbot.js`, dan partial public/member chatbot. |
| How | Sistem membangun konteks aman, memilih salah satu key Gemini dari `.env`, memanggil `generateContent`, menyimpan conversation log, dan memakai fallback lokal jika provider gagal. |

Referensi:

- https://ai.google.dev/gemini-api/docs

## 10. midtrans/midtrans-php

| 5W+1H | Penjelasan |
|---|---|
| What | `midtrans/midtrans-php` adalah package resmi Midtrans untuk integrasi payment gateway pada PHP/Laravel. |
| Why | Package ini dipakai agar sistem dapat menerima pembayaran online melalui Midtrans Sandbox. |
| Who | Digunakan oleh member saat membayar membership atau layanan, dan admin untuk monitoring transaksi. |
| When | Digunakan pada checkout membership, paket sesi, kelas berbayar, dan webhook pembayaran. |
| Where | Digunakan pada modul membership, booking, transaksi, pembayaran admin, dan webhook. |
| How | Sistem membuat transaksi, mengirim data ke Midtrans, menerima payment URL/token, lalu menerima status pembayaran melalui webhook. |

Referensi:

- https://docs.midtrans.com
- https://github.com/Midtrans/midtrans-php

## 11. Filament

| 5W+1H | Penjelasan |
|---|---|
| What | Filament adalah admin panel framework untuk Laravel yang menyediakan CRUD, form, table, filter, dashboard, dan resource management. |
| Why | Filament pernah dipertimbangkan untuk mempercepat CRUD, tetapi bukan pilihan arsitektur aktif proyek ini. |
| Who | Tidak digunakan oleh user production saat ini. Developer cukup memakai admin custom Blade yang sudah ada. |
| When | Baru dievaluasi ulang jika kebutuhan admin/owner berubah besar dan manfaatnya melebihi biaya migrasi UI. |
| Where | Tidak dipasang pada repository production saat ini. |
| How | Admin production sekarang dibangun dengan controller, FormRequest, Action, Query/ViewModel, Blade, Tailwind, dan Alpine. |

Catatan status 2026-06-14: Filament tidak dipasang. Admin production memakai custom Blade/Tailwind/Alpine agar desain, permission, dan performa tetap sesuai kebutuhan Platinum Gym.

Referensi:

- https://filamentphp.com/docs
- https://github.com/filamentphp/filament

## 12. Livewire

| 5W+1H | Penjelasan |
|---|---|
| What | Livewire adalah framework full-stack untuk Laravel yang membuat komponen interaktif tanpa JavaScript kompleks. |
| Why | Livewire pernah dipertimbangkan, tetapi interaksi member/admin saat ini sudah cukup dengan Blade, request klasik, dan Alpine ringan. |
| Who | Tidak digunakan oleh user production saat ini. |
| When | Baru dievaluasi ulang jika ada kebutuhan real-time atau form interaktif kompleks yang tidak ergonomis dengan pola saat ini. |
| Where | Tidak dipasang pada repository production saat ini. |
| How | Flow member/admin sekarang memakai controller, FormRequest, Action, redirect/flash message, dan komponen Blade. |

Referensi:

- https://livewire.laravel.com/docs
- https://github.com/livewire/livewire

## 13. Spatie Laravel Activitylog

| 5W+1H | Penjelasan |
|---|---|
| What | Spatie Laravel Activitylog adalah package untuk mencatat aktivitas pengguna dan perubahan data pada aplikasi Laravel. |
| Why | Package ini disiapkan agar sistem memiliki riwayat aktivitas penting seperti perubahan data member, verifikasi pembayaran, perubahan paket, dan pengelolaan konten. |
| Who | Digunakan secara internal oleh sistem; admin dan owner dapat melihat log jika fitur ditampilkan. |
| When | Digunakan pada halaman admin audit log dan proses operasional yang mencatat aktivitas penting. |
| Where | Digunakan pada audit log admin dan disiapkan untuk perluasan pencatatan perubahan member, pembayaran, booking, paket layanan, produk, konten website, dan laporan aktivitas. |
| How | Model penting diberi konfigurasi logging, sehingga perubahan data tercatat dengan informasi user, waktu, model, dan perubahan yang terjadi. |

Catatan status 2026-06-14: package sudah tersedia. Halaman admin audit-log membaca data log dengan filter; perluasan logging detail per model tetap dilakukan bertahap sesuai kebutuhan operasional.

Referensi:

- https://spatie.be/docs/laravel-activitylog
- https://github.com/spatie/laravel-activitylog

## Cara Install Dependency

Install dependency Composer:

```bash
composer require vendor/package
```

Contoh dependency yang sudah dipakai:

```bash
composer require spatie/laravel-permission
```

Install dependency development Composer:

```bash
composer require vendor/package --dev
```

Install dependency frontend:

```bash
npm install package-name
```

Setelah menambah dependency frontend, jalankan build:

```bash
npm.cmd run build
```

## Analisis Perubahan File Dependency

### composer.json

`composer.json` mencatat package utama yang dipasang secara langsung oleh developer. File ini menjelaskan dependency apa yang dibutuhkan aplikasi.

### composer.lock

`composer.lock` mencatat versi pasti dari package utama dan dependency turunannya. File ini penting agar semua developer mendapat versi package yang sama saat menjalankan `composer install`.

### package.json

`package.json` mencatat package frontend yang dipasang secara langsung, seperti Vite, Tailwind CSS, Alpine.js, dan Axios.

### package-lock.json

`package-lock.json` mencatat versi pasti dependency frontend dan dependency turunannya agar hasil instalasi NPM konsisten antar perangkat.

## Dampak Dependency Pada Proyek

- Mempercepat pengembangan autentikasi, role, testing, dan frontend build.
- Membuat struktur aplikasi lebih sesuai praktik Laravel modern.
- Menambah kebutuhan dokumentasi agar tim memahami fungsi dan risiko package.
- Menambah risiko maintenance jika versi package berubah atau tidak kompatibel.
- Membutuhkan pengecekan keamanan dan update berkala.

## Risiko Umum Dependency

- Package tidak kompatibel dengan versi Laravel atau PHP terbaru.
- Package tidak lagi aktif dipelihara.
- Konfigurasi salah dapat menyebabkan bug atau celah akses.
- Dependency terlalu banyak dapat memperbesar beban maintenance.
- Secret untuk package eksternal seperti OAuth dan payment gateway harus dijaga agar tidak masuk repository.

## Evaluasi Dependency

Dependency membantu pengembangan proyek karena fitur umum tidak perlu dibuat dari nol. Namun, setiap dependency tetap harus dipilih sesuai kebutuhan. Package yang belum dipakai penuh tidak boleh dianggap sebagai fitur selesai. Oleh karena itu, dokumentasi ini memisahkan dependency yang sudah digunakan, sudah dipasang, dan masih direncanakan.
