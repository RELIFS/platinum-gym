# Screenshot Fitur Platinum Gym

Status: Updated 2026-07-01.

Folder ini menyimpan dokumentasi visual fitur Platinum Gym Padang. Screenshot dibuat pada viewport desktop 1440px, mode light, dan memakai data lokal aman. Email pada area login-protected dimasking di DOM browser saat capture untuk menghindari penyebaran data akun pada dokumentasi. Screenshot public dan member memakai state motion yang sudah selesai, menunggu image tampil, dan menampilkan trigger Gymmi di bagian bawah dokumen agar konten utama tidak tertutup.

## Ringkasan

| Kategori | Jumlah | Folder |
|---|---:|---|
| Public website | 8 | [`public/`](public/) |
| Auth | 3 | [`auth/`](auth/) |
| Member portal | 9 | [`member/`](member/) |
| Admin portal | 12 | [`admin/`](admin/) |
| Owner portal | 6 | [`owner/`](owner/) |
| Detail representatif | 3 | [`detail/`](detail/) |

## Public Website

| Screenshot | Route | Fitur |
|---|---|---|
| [`public-home.png`](public/public-home.png) | `/` | Beranda public |
| [`public-about.png`](public/public-about.png) | `/tentang-kami` | Profil gym dan keunggulan |
| [`public-services.png`](public/public-services.png) | `/layanan` | Layanan dan paket |
| [`public-classes.png`](public/public-classes.png) | `/kelas` | Jadwal kelas public |
| [`public-products.png`](public/public-products.png) | `/produk` | Katalog produk |
| [`public-gallery.png`](public/public-gallery.png) | `/galeri` | Galeri aktivitas |
| [`public-location.png`](public/public-location.png) | `/lokasi` | Lokasi dan kontak |
| [`public-bmi.png`](public/public-bmi.png) | `/bmi` | Kalkulator BMI |

## Auth

| Screenshot | Route | Fitur |
|---|---|---|
| [`auth-login.png`](auth/auth-login.png) | `/login` | Login user |
| [`auth-register.png`](auth/auth-register.png) | `/register` | Registrasi member |
| [`auth-forgot-password.png`](auth/auth-forgot-password.png) | `/forgot-password` | Lupa password |

Catatan: `/verify-email` dan `/member/complete-profile` tidak dicapture pada batch ini karena membutuhkan state akun khusus yang aman dan stabil.

## Member Portal

| Screenshot | Route | Fitur |
|---|---|---|
| [`member-dashboard.png`](member/member-dashboard.png) | `/member/dashboard` | Dashboard member |
| [`member-profile.png`](member/member-profile.png) | `/member/profil` | Profil member |
| [`member-profile-edit.png`](member/member-profile-edit.png) | `/member/profil/edit` | Edit profil member |
| [`member-membership.png`](member/member-membership.png) | `/member/membership` | Membership dan paket |
| [`member-booking.png`](member/member-booking.png) | `/member/booking-kelas` | Booking kelas |
| [`member-booking-history.png`](member/member-booking-history.png) | `/member/riwayat-booking` | Riwayat booking |
| [`member-transactions.png`](member/member-transactions.png) | `/member/transaksi` | Transaksi member |
| [`member-qr.png`](member/member-qr.png) | `/member/qr` | QR member |
| [`member-notifications.png`](member/member-notifications.png) | `/member/notifikasi` | Notifikasi member |

## Admin Portal

| Screenshot | Route | Fitur |
|---|---|---|
| [`admin-dashboard.png`](admin/admin-dashboard.png) | `/admin` | Dashboard admin |
| [`admin-members.png`](admin/admin-members.png) | `/admin/anggota` | Data anggota |
| [`admin-packages.png`](admin/admin-packages.png) | `/admin/paket` | Paket layanan |
| [`admin-classes.png`](admin/admin-classes.png) | `/admin/kelas` | Kelas dan jadwal |
| [`admin-payments.png`](admin/admin-payments.png) | `/admin/pembayaran` | Pembayaran |
| [`admin-booking.png`](admin/admin-booking.png) | `/admin/booking` | Booking admin |
| [`admin-check-in.png`](admin/admin-check-in.png) | `/admin/check-in` | Check-in QR |
| [`admin-products.png`](admin/admin-products.png) | `/admin/produk` | Produk admin |
| [`admin-reports.png`](admin/admin-reports.png) | `/admin/laporan` | Laporan admin |
| [`admin-settings.png`](admin/admin-settings.png) | `/admin/pengaturan` | Pengaturan public dan masked settings |
| [`admin-audit-log.png`](admin/admin-audit-log.png) | `/admin/audit-log` | Audit log |
| [`admin-profile.png`](admin/admin-profile.png) | `/admin/profil` | Profil admin |

## Owner Portal

| Screenshot | Route | Fitur |
|---|---|---|
| [`owner-dashboard.png`](owner/owner-dashboard.png) | `/owner` | Dashboard owner |
| [`owner-reports.png`](owner/owner-reports.png) | `/owner/laporan` | Pusat laporan |
| [`owner-reports-finance.png`](owner/owner-reports-finance.png) | `/owner/laporan/keuangan` | Laporan keuangan |
| [`owner-reports-members.png`](owner/owner-reports-members.png) | `/owner/laporan/member` | Laporan member |
| [`owner-reports-classes.png`](owner/owner-reports-classes.png) | `/owner/laporan/booking-kelas` | Laporan booking kelas |
| [`owner-profile.png`](owner/owner-profile.png) | `/profile` | Profile dan keamanan akun owner |

## Detail Representatif

| Screenshot | Route | Fitur |
|---|---|---|
| [`member-transaction-detail.png`](detail/member-transaction-detail.png) | `/member/transaksi/{payment}` | Detail transaksi member |
| [`admin-resource-create-package.png`](detail/admin-resource-create-package.png) | `/admin/resource/packages/tambah` | Form tambah paket admin |
| [`owner-invoice-detail.png`](detail/owner-invoice-detail.png) | `/owner/invoice/{invoice}` | Invoice read-only owner |
