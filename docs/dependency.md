# Dependency Documentation

Dokumen ini menjelaskan dependency proyek Platinum Gym Padang berdasarkan kondisi repository saat ini.

## Identitas

- Nama proyek: Website Company Profile dan Sistem Informasi Layanan Platinum Gym Padang.
- Framework utama: Laravel.
- Tujuan: Mengidentifikasi dependency/package Laravel yang digunakan atau direncanakan, menjelaskan kegunaannya dengan pendekatan 5W+1H, dan mencatat dampaknya terhadap evolusi perangkat lunak.

## Ringkasan

Dependency dikelola menggunakan Composer untuk package PHP/Laravel dan NPM untuk package frontend. Dependency yang sudah terpasang dicatat pada `composer.json`, `composer.lock`, `package.json`, dan `package-lock.json`.

Status penggunaan dibagi menjadi:

- Sudah digunakan: package sudah terpasang dan sudah dipakai pada fitur aplikasi.
- Sudah dipasang, belum diimplementasikan penuh: package sudah ada pada dependency, tetapi fiturnya belum aktif penuh.
- Dependency development: package digunakan untuk testing, debugging, formatting, atau development.
- Dependency frontend: package digunakan untuk build dan tampilan frontend.
- Direncanakan: package belum terpasang atau belum digunakan, tetapi direncanakan untuk fitur berikutnya.

## Dependency Backend Terpasang

| Package | Fungsi | Alasan | Versi | Risiko | Status |
|---|---|---|---|---|---|
| `laravel/framework` | Framework utama aplikasi | Menyediakan routing, MVC, ORM, migration, middleware, queue, dan fitur inti Laravel | `^12.0` | Perubahan major version dapat memerlukan penyesuaian kode | Sudah digunakan |
| `laravel/socialite` | OAuth login menggunakan provider pihak ketiga | Disiapkan untuk login/register Google | `*` | Perlu konfigurasi OAuth, callback URL, dan pengamanan secret | Sudah dipasang, belum diimplementasikan penuh |
| `laravel/tinker` | Interaksi aplikasi melalui REPL | Membantu debugging dan eksplorasi model saat development | `^2.10.1` | Tidak untuk workflow production user | Dependency development |
| `spatie/laravel-permission` | Role dan permission | Mengelola role `member`, `admin`, dan `owner` secara rapi | `*` | Salah konfigurasi role dapat membuka akses tidak sesuai | Sudah digunakan |
| `spatie/laravel-medialibrary` | Upload dan manajemen media | Disiapkan untuk foto profil, galeri, bukti pembayaran, dan konten website | `*` | Butuh pengaturan storage, validasi file, dan pembatasan ukuran upload | Sudah dipasang, belum diimplementasikan penuh |
| `spatie/laravel-activitylog` | Audit log aktivitas sistem | Disiapkan untuk mencatat perubahan data penting dan aktivitas admin | `*` | Log dapat membesar jika tidak dibatasi atau dibersihkan | Sudah dipasang, belum diimplementasikan penuh |

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

| Package | Fungsi | Alasan | Versi | Risiko | Status |
|---|---|---|---|---|---|
| `vite` | Build tool frontend | Build asset CSS dan JavaScript Laravel | `^7.0.7` | Perlu versi Node yang kompatibel | Sudah digunakan |
| `laravel-vite-plugin` | Integrasi Laravel dengan Vite | Menghubungkan Blade dengan hasil build Vite | `^2.0.0` | Asset gagal dimuat jika build atau manifest bermasalah | Sudah digunakan |
| `tailwindcss` | Utility CSS framework | Membantu membuat UI responsif dan konsisten | `^3.1.0` | Class dinamis perlu dipastikan ikut ter-build | Sudah digunakan |
| `@tailwindcss/forms` | Styling form Tailwind | Memperbaiki tampilan input form autentikasi | `^0.5.2` | Perlu override jika desain custom sangat spesifik | Sudah digunakan |
| `@tailwindcss/vite` | Integrasi Tailwind dengan Vite | Mendukung proses build frontend | `^4.0.0` | Perlu perhatian kompatibilitas dengan versi Tailwind yang dipakai | Dependency frontend |
| `alpinejs` | Interaktivitas ringan frontend | Digunakan untuk behavior UI sederhana seperti dropdown atau toggle | `^3.4.2` | Tidak cocok untuk state management kompleks | Sudah digunakan |
| `axios` | HTTP client JavaScript | Disiapkan untuk request AJAX frontend | `^1.11.0` | Perlu pengaturan CSRF dan error handling | Dependency frontend |
| `concurrently` | Menjalankan beberapa command dev | Membantu menjalankan server, queue, dan Vite bersamaan | `^9.0.1` | Hanya kebutuhan development | Dependency development |
| `postcss` | CSS processing | Digunakan dalam pipeline Tailwind/Vite | `^8.4.31` | Konfigurasi salah dapat membuat build CSS gagal | Dependency frontend |
| `autoprefixer` | Vendor prefix CSS | Menambah kompatibilitas browser | `^10.4.2` | Umumnya rendah, mengikuti konfigurasi browser target | Dependency frontend |

## Dependency Rencana Pengembangan

| Package | Fungsi | Modul Rencana | Alasan | Status |
|---|---|---|---|---|
| `simplesoftwareio/simple-qrcode` | Generate QR Code | QR member dan check-in gym | Mempercepat validasi member saat check-in | Direncanakan |
| `maatwebsite/excel` | Import/export Excel dan CSV | Import member lama dan export laporan | Memudahkan pengolahan laporan admin/owner | Direncanakan |
| `barryvdh/laravel-dompdf` | Generate PDF dari Blade | Invoice dan laporan cetak | PDF mudah dicetak dan diarsipkan | Direncanakan |
| `google-gemini-php/laravel` | Integrasi Gemini AI | AI assistant layanan gym | Membantu user mendapat informasi layanan | Direncanakan |
| `midtrans/midtrans-php` | Payment gateway | Pembayaran membership dan layanan | Mendukung pembayaran online/QRIS sandbox | Direncanakan |
| `filament/filament` | Admin panel Laravel | Dashboard admin dan owner | Mempercepat CRUD dan resource management | Direncanakan |
| `livewire/livewire` | Komponen interaktif Laravel | Portal member, booking, dan dashboard | Membuat UI dinamis tanpa JavaScript kompleks | Direncanakan |

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
| Why | Socialite disiapkan agar member bisa mendaftar dan login menggunakan akun Google. |
| Who | Digunakan oleh member sebagai alternatif login/register. Admin dan owner tetap dapat memakai email dan password. |
| When | Digunakan saat fitur Google Auth mulai diimplementasikan. |
| Where | Direncanakan pada modul autentikasi, callback Google, dan pembuatan akun member otomatis. |
| How | Socialite dikonfigurasi dengan Google OAuth Client ID, Client Secret, dan callback URL. Setelah user berhasil login Google, sistem mengambil data email dan nama. |

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

## 5. Simple QRCode

| 5W+1H | Penjelasan |
|---|---|
| What | `simplesoftwareio/simple-qrcode` adalah package Laravel untuk membuat QR Code. |
| Why | Package ini direncanakan karena sistem membutuhkan QR member untuk proses check-in gym. |
| Who | Digunakan oleh member untuk menampilkan QR dan admin untuk memverifikasi check-in. |
| When | Digunakan saat fitur QR member dan check-in mulai dibuat. |
| Where | Direncanakan pada dashboard member, modul QR member, dan modul check-in admin. |
| How | Sistem membuat QR berdasarkan kode member atau token check-in, lalu admin memindai atau memvalidasi kode tersebut. |

Referensi:

- https://github.com/SimpleSoftwareIO/simple-qrcode

## 6. Maatwebsite Laravel Excel

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

## 7. barryvdh/laravel-dompdf

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

## 8. google-gemini-php/laravel

| 5W+1H | Penjelasan |
|---|---|
| What | `google-gemini-php/laravel` adalah package untuk menghubungkan Laravel dengan API Gemini dari Google. |
| Why | Package ini direncanakan untuk fitur AI Assistant informasi layanan gym. |
| Who | Digunakan oleh pengunjung, member, dan admin jika membutuhkan bantuan informasi. |
| When | Digunakan saat fitur AI Assistant dikembangkan. |
| Where | Direncanakan pada halaman public dan member area. |
| How | Sistem mengirim prompt berisi pertanyaan dan konteks data Platinum Gym ke Gemini, lalu menampilkan jawaban ke user. |

Referensi:

- https://github.com/google-gemini-php/laravel
- https://ai.google.dev/gemini-api/docs

## 9. midtrans/midtrans-php

| 5W+1H | Penjelasan |
|---|---|
| What | `midtrans/midtrans-php` adalah package resmi Midtrans untuk integrasi payment gateway pada PHP/Laravel. |
| Why | Package ini direncanakan agar sistem dapat menerima pembayaran online, terutama QRIS Sandbox pada tahap pengembangan. |
| Who | Digunakan oleh member saat membayar membership atau layanan, dan admin untuk monitoring transaksi. |
| When | Digunakan saat fitur pembayaran online dibuat. |
| Where | Direncanakan pada modul membership, booking, transaksi, pembayaran, dan webhook. |
| How | Sistem membuat transaksi, mengirim data ke Midtrans, menerima payment URL/token, lalu menerima status pembayaran melalui webhook. |

Referensi:

- https://docs.midtrans.com
- https://github.com/Midtrans/midtrans-php

## 10. Filament

| 5W+1H | Penjelasan |
|---|---|
| What | Filament adalah admin panel framework untuk Laravel yang menyediakan CRUD, form, table, filter, dashboard, dan resource management. |
| Why | Filament direncanakan agar dashboard admin dan owner lebih cepat dibuat dan tetap berbasis Laravel. |
| Who | Digunakan oleh admin dan owner. |
| When | Digunakan saat membangun admin panel dan owner panel. |
| Where | Direncanakan pada dashboard admin dan dashboard owner. |
| How | Developer membuat Resource untuk model penting, lalu mengatur form, tabel, filter, action, dan permission sesuai role. |

Referensi:

- https://filamentphp.com/docs
- https://github.com/filamentphp/filament

## 11. Livewire

| 5W+1H | Penjelasan |
|---|---|
| What | Livewire adalah framework full-stack untuk Laravel yang membuat komponen interaktif tanpa JavaScript kompleks. |
| Why | Livewire direncanakan untuk portal member yang interaktif tetapi tetap memakai Laravel dan Blade. |
| Who | Digunakan oleh member dan developer. |
| When | Digunakan saat fitur seperti booking, profil, transaksi, notifikasi, dan QR member membutuhkan interaksi langsung. |
| Where | Direncanakan pada member portal dan dashboard. |
| How | Developer membuat class komponen Livewire dan view Blade untuk mengelola state, validasi, event, dan update tampilan. |

Referensi:

- https://livewire.laravel.com/docs
- https://github.com/livewire/livewire

## 12. Spatie Laravel Activitylog

| 5W+1H | Penjelasan |
|---|---|
| What | Spatie Laravel Activitylog adalah package untuk mencatat aktivitas pengguna dan perubahan data pada aplikasi Laravel. |
| Why | Package ini disiapkan agar sistem memiliki riwayat aktivitas penting seperti perubahan data member, verifikasi pembayaran, perubahan paket, dan pengelolaan konten. |
| Who | Digunakan secara internal oleh sistem; admin dan owner dapat melihat log jika fitur ditampilkan. |
| When | Digunakan saat modul audit log mulai diimplementasikan. |
| Where | Direncanakan pada modul member, pembayaran, booking, paket layanan, produk, konten website, dan laporan aktivitas. |
| How | Model penting diberi konfigurasi logging, sehingga perubahan data tercatat dengan informasi user, waktu, model, dan perubahan yang terjadi. |

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
