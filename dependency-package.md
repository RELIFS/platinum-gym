# Identifikasi Dependency/Package Laravel Proyek PBL Platinum Gym Padang

## Identitas

- Nama proyek: Website Company Profile dan Sistem Informasi Layanan Platinum Gym Padang.
- Framework utama: Laravel.
- Tujuan: Mengidentifikasi dependency/package Laravel yang kemungkinan digunakan pada proyek PBL, menjelaskan kegunaannya dengan pendekatan 5W+1H, dan mencantumkan sumber referensi package.

## Daftar Dependency/Package

| No | Dependency/Package | Kegunaan Utama | Status Penggunaan |
|---|---|---|---|
| 1 | Laravel Breeze | Autentikasi dasar | Kemungkinan digunakan |
| 2 | Laravel Socialite | Login/register Google | Kemungkinan digunakan |
| 3 | Spatie Laravel Permission | Role dan permission | Kemungkinan digunakan |
| 4 | Spatie Laravel MediaLibrary | Upload dan manajemen file | Kemungkinan digunakan |
| 5 | simplesoftwareio/simple-qrcode | Generate QR member/check-in | Kemungkinan digunakan |
| 6 | Maatwebsite Laravel Excel | Import/export Excel dan CSV | Kemungkinan digunakan |
| 7 | barryvdh/laravel-dompdf | Generate PDF invoice/laporan | Kemungkinan digunakan |
| 8 | google-gemini-php/laravel | Integrasi AI Assistant | Kemungkinan digunakan |
| 9 | midtrans/midtrans-php | Payment gateway | Kemungkinan digunakan |
| 10 | Filament | Admin dan owner panel | Kemungkinan digunakan |
| 11 | Livewire | Komponen interaktif Laravel | Kemungkinan digunakan |
| 12 | Spatie Laravel Activitylog | Audit log aktivitas sistem | Kemungkinan digunakan |

## 1. Laravel Breeze

| 5W+1H | Penjelasan |
|---|---|
| What | Laravel Breeze adalah package starter kit autentikasi Laravel. Package ini menyediakan fitur login, register, forgot password, reset password, verifikasi email, dan update profil pengguna. |
| Why | Laravel Breeze digunakan karena sistem membutuhkan autentikasi dasar untuk member, admin, dan owner. Breeze cocok untuk proyek PBL karena strukturnya sederhana, mudah dipahami, dan sesuai dengan pembelajaran Laravel. |
| Who | Digunakan oleh semua pengguna yang membutuhkan akses login, yaitu member, admin, dan owner. Tim developer juga menggunakan Breeze sebagai dasar pengembangan fitur autentikasi. |
| When | Digunakan sejak tahap awal pengembangan, terutama saat membuat fitur registrasi, login, logout, verifikasi email, dan manajemen profil pengguna. |
| Where | Digunakan pada modul autentikasi aplikasi Laravel, khususnya halaman login, register, dashboard redirect, profil pengguna, dan reset password. |
| How | Breeze dipasang melalui Composer, lalu scaffolding dijalankan agar Laravel menghasilkan route, controller, view Blade, dan fitur autentikasi dasar. Setelah itu, fitur role dan redirect disesuaikan dengan kebutuhan sistem Platinum Gym. |

Sumber referensi:

- https://laravel.com/docs/starter-kits
- https://github.com/laravel/breeze

## 2. Laravel Socialite

| 5W+1H | Penjelasan |
|---|---|
| What | Laravel Socialite adalah package Laravel untuk autentikasi menggunakan akun pihak ketiga seperti Google. |
| Why | Socialite digunakan agar member bisa mendaftar dan login memakai akun Google. Ini membuat proses registrasi lebih cepat dan memudahkan pengguna yang tidak ingin membuat akun manual. |
| Who | Digunakan oleh member sebagai alternatif login dan register. Admin dan owner tetap dapat menggunakan login email dan password biasa. |
| When | Digunakan saat pengguna memilih tombol login atau daftar dengan Google pada halaman autentikasi. |
| Where | Digunakan pada modul autentikasi, khususnya halaman login, register, callback Google, dan proses pembuatan akun member otomatis. |
| How | Socialite dihubungkan dengan Google OAuth Client ID dan Client Secret. Setelah pengguna berhasil login Google, sistem mengambil data email dan nama, lalu membuat atau menghubungkan akun pengguna di database. |

Sumber referensi:

- https://laravel.com/docs/socialite
- https://github.com/laravel/socialite

## 3. Spatie Laravel Permission

| 5W+1H | Penjelasan |
|---|---|
| What | Spatie Laravel Permission adalah package untuk mengelola role dan permission pengguna pada aplikasi Laravel. |
| Why | Package ini digunakan karena sistem memiliki beberapa jenis pengguna dengan hak akses berbeda, yaitu admin, owner, dan member. Dengan package ini, pembatasan akses menu dan fitur bisa dibuat lebih rapi dan aman. |
| Who | Digunakan oleh admin, owner, dan member. Developer menggunakan package ini untuk membuat middleware, role checking, dan permission checking. |
| When | Digunakan setelah fitur autentikasi dibuat, terutama saat membatasi akses ke dashboard admin, dashboard owner, dan dashboard member. |
| Where | Digunakan pada route, controller, middleware, policy, dan tampilan menu sesuai role pengguna. |
| How | Package dipasang melalui Composer, kemudian migration dijalankan untuk membuat tabel role dan permission. Role seperti admin, owner, dan member dibuat melalui seeder, lalu setiap user diberikan role sesuai jenis akunnya. |

Sumber referensi:

- https://spatie.be/docs/laravel-permission
- https://github.com/spatie/laravel-permission

## 4. Spatie Laravel MediaLibrary

| 5W+1H | Penjelasan |
|---|---|
| What | Spatie Laravel MediaLibrary adalah package untuk mengelola upload, penyimpanan, dan relasi file media pada model Laravel. |
| Why | Package ini digunakan karena sistem membutuhkan pengelolaan file seperti foto galeri, gambar produk, bukti transfer pembayaran, foto profil member, dan gambar promosi. |
| Who | Digunakan oleh admin saat mengelola konten website, produk, galeri, promosi, dan bukti pembayaran. Member juga dapat menggunakannya secara tidak langsung saat mengunggah foto profil atau bukti transfer. |
| When | Digunakan saat pengguna mengunggah, mengganti, melihat, atau menghapus file media pada aplikasi. |
| Where | Digunakan pada modul galeri, produk, promo, profil member, dan pembayaran transfer manual. |
| How | Package dipasang melalui Composer dan migration dijalankan. Model yang membutuhkan file menggunakan trait `InteractsWithMedia`. File yang diunggah disimpan di storage Laravel dan dihubungkan ke model terkait. |

Sumber referensi:

- https://spatie.be/docs/laravel-medialibrary
- https://github.com/spatie/laravel-medialibrary

## 5. simplesoftwareio/simple-qrcode

| 5W+1H | Penjelasan |
|---|---|
| What | simplesoftwareio/simple-qrcode adalah package Laravel untuk membuat QR Code. |
| Why | Package ini digunakan karena sistem membutuhkan QR member untuk proses check-in di gym. QR Code membantu admin atau kasir melakukan validasi keanggotaan dengan cepat. |
| Who | Digunakan oleh member untuk menampilkan QR member, dan admin untuk memindai atau memverifikasi QR saat check-in. |
| When | Digunakan saat member membuka halaman QR Member dan saat admin melakukan proses check-in. |
| Where | Digunakan pada modul QR Member, Check-in QR, dashboard member, dan dashboard admin. |
| How | Sistem membuat QR Code berdasarkan kode unik member atau token check-in. QR ditampilkan di halaman member, lalu admin memindai atau memasukkan kode tersebut untuk memvalidasi status membership. |

Sumber referensi:

- https://github.com/SimpleSoftwareIO/simple-qrcode

## 6. Maatwebsite Laravel Excel

| 5W+1H | Penjelasan |
|---|---|
| What | Maatwebsite Laravel Excel adalah package untuk import dan export data Excel atau CSV pada Laravel. |
| Why | Package ini digunakan karena sistem membutuhkan import data member lama dari file CSV/Excel dan export laporan untuk admin atau owner. |
| Who | Digunakan oleh admin untuk import data member lama dan oleh admin atau owner untuk export laporan. |
| When | Digunakan saat migrasi data awal, import data member, export laporan transaksi, export laporan member, dan export laporan booking. |
| Where | Digunakan pada modul Member, Laporan, Transaksi, Booking, dan Export Laporan Owner. |
| How | Package dipasang melalui Composer. Developer membuat class Import dan Export untuk menentukan kolom data. Saat file diunggah, sistem membaca data Excel/CSV, melakukan validasi, lalu menyimpan data ke database. Untuk export, sistem mengambil data dari database dan menghasilkan file Excel. |

Sumber referensi:

- https://docs.laravel-excel.com
- https://github.com/SpartnerNL/Laravel-Excel

## 7. barryvdh/laravel-dompdf

| 5W+1H | Penjelasan |
|---|---|
| What | barryvdh/laravel-dompdf adalah package Laravel untuk membuat file PDF dari view Blade. |
| Why | Package ini digunakan agar sistem dapat menghasilkan invoice, struk pembayaran, dan laporan dalam bentuk PDF. PDF diperlukan karena mudah dicetak, diarsipkan, dan dibagikan. |
| Who | Digunakan oleh member untuk melihat atau mengunduh invoice transaksi. Admin dan owner menggunakannya untuk mencetak laporan. |
| When | Digunakan setelah pembayaran dibuat, setelah transaksi selesai, atau saat owner/admin membutuhkan laporan cetak. |
| Where | Digunakan pada modul Transaksi, Pembayaran, Invoice, dan Laporan. |
| How | Sistem membuat view Blade khusus untuk invoice atau laporan, lalu DomPDF mengubah view tersebut menjadi file PDF yang bisa ditampilkan atau diunduh. |

Sumber referensi:

- https://github.com/barryvdh/laravel-dompdf

## 8. google-gemini-php/laravel

| 5W+1H | Penjelasan |
|---|---|
| What | google-gemini-php/laravel adalah package untuk menghubungkan aplikasi Laravel dengan API Gemini dari Google. |
| Why | Package ini digunakan untuk fitur AI Assistant yang membantu pengguna mendapatkan informasi tentang layanan gym, paket membership, jadwal kelas, produk, dan pertanyaan umum. |
| Who | Digunakan oleh pengunjung website, member, dan admin jika membutuhkan bantuan informasi berbasis AI. |
| When | Digunakan saat pengguna membuka fitur AI Assistant dan mengirim pertanyaan. |
| Where | Digunakan pada modul AI Assistant di halaman public dan member area. |
| How | Package dikonfigurasi dengan API key Gemini. Saat pengguna mengirim pertanyaan, sistem menambahkan konteks data Platinum Gym, lalu mengirim prompt ke Gemini dan menampilkan jawaban kepada pengguna. |

Sumber referensi:

- https://github.com/google-gemini-php/laravel
- https://ai.google.dev/gemini-api/docs

## 9. midtrans/midtrans-php

| 5W+1H | Penjelasan |
|---|---|
| What | midtrans/midtrans-php adalah package resmi untuk menghubungkan aplikasi PHP atau Laravel dengan payment gateway Midtrans. |
| Why | Package ini digunakan agar sistem dapat menerima pembayaran online, terutama QRIS Sandbox pada tahap pengembangan. Midtrans membantu proses pembayaran lebih praktis dan terdokumentasi. |
| Who | Digunakan oleh member saat membayar membership, kelas, atau layanan lain. Admin menggunakan data pembayaran untuk verifikasi dan monitoring transaksi. |
| When | Digunakan saat member memilih metode pembayaran online melalui Midtrans. |
| Where | Digunakan pada modul Membership, Booking, Transaksi, Pembayaran, dan callback/webhook Midtrans. |
| How | Sistem membuat transaksi dan mengirim data pembayaran ke Midtrans. Midtrans mengembalikan payment URL atau token pembayaran. Setelah pembayaran diproses, Midtrans mengirim status transaksi melalui webhook ke aplikasi. |

Sumber referensi:

- https://docs.midtrans.com
- https://github.com/Midtrans/midtrans-php

## 10. Filament

| 5W+1H | Penjelasan |
|---|---|
| What | Filament adalah admin panel framework untuk Laravel yang menyediakan fitur CRUD, form, table, filter, dashboard, dan resource management. |
| Why | Filament digunakan agar pengembangan dashboard admin dan owner lebih cepat, rapi, dan tetap berbasis Laravel. Package ini cocok untuk sistem yang memiliki banyak data master dan transaksi. |
| Who | Digunakan oleh admin dan owner. Admin mengelola data operasional, sedangkan owner melihat laporan dan ringkasan bisnis. |
| When | Digunakan saat membangun halaman admin panel, seperti data member, paket layanan, kelas, booking, pembayaran, produk, galeri, promo, testimoni, dan laporan. |
| Where | Digunakan pada dashboard Admin dan dashboard Owner. |
| How | Filament dipasang melalui Composer, lalu developer membuat Resource untuk setiap model. Setiap Resource berisi form input, tabel data, filter, action, dan permission sesuai role pengguna. |

Sumber referensi:

- https://filamentphp.com/docs
- https://github.com/filamentphp/filament

## 11. Livewire

| 5W+1H | Penjelasan |
|---|---|
| What | Livewire adalah framework full-stack untuk Laravel yang memungkinkan pembuatan komponen interaktif tanpa menulis JavaScript yang kompleks. |
| Why | Livewire digunakan agar halaman member lebih interaktif, tetapi tetap menggunakan Laravel dan Blade. Ini cocok untuk fitur seperti booking kelas, update profil, riwayat transaksi, notifikasi, dan QR member. |
| Who | Digunakan oleh member saat mengakses portal member. Developer menggunakan Livewire untuk membuat komponen dinamis. |
| When | Digunakan saat sistem membutuhkan interaksi langsung, seperti memilih jadwal kelas, melihat status booking, memperbarui profil, dan menampilkan QR member. |
| Where | Digunakan pada Member Portal, terutama halaman Dashboard Member, Profil Saya, Membership, Booking Kelas, Riwayat Booking, Transaksi, QR Member, dan Notifikasi. |
| How | Developer membuat class komponen Livewire dan file view Blade. Komponen tersebut mengelola state, validasi, event, dan update tampilan tanpa reload halaman penuh. |

Sumber referensi:

- https://livewire.laravel.com/docs
- https://github.com/livewire/livewire

## 12. Spatie Laravel Activitylog

| 5W+1H | Penjelasan |
|---|---|
| What | Spatie Laravel Activitylog adalah package untuk mencatat aktivitas pengguna dan perubahan data pada aplikasi Laravel. |
| Why | Package ini digunakan agar sistem memiliki riwayat aktivitas penting seperti perubahan data member, verifikasi pembayaran, perubahan paket layanan, dan pengelolaan konten website. Ini membantu audit, pelacakan kesalahan, dan keamanan sistem. |
| Who | Digunakan secara internal oleh sistem. Admin dan owner dapat melihat log aktivitas jika fitur ini ditampilkan di dashboard. |
| When | Digunakan saat ada aktivitas penting seperti tambah, ubah, hapus data, login, verifikasi pembayaran, check-in, dan update konten. |
| Where | Digunakan pada modul Member, Pembayaran, Booking, Paket Layanan, Produk, Konten Website, dan Laporan Aktivitas. |
| How | Package dipasang melalui Composer dan migration dijalankan. Model penting diberi konfigurasi logging, sehingga setiap perubahan data otomatis tercatat dengan informasi user, waktu, model, dan perubahan yang terjadi. |

Sumber referensi:

- https://spatie.be/docs/laravel-activitylog
- https://github.com/spatie/laravel-activitylog