# Refactoring Documentation

Status: Draft. Dokumen ini diperbarui setiap ada perubahan struktur kode yang berdampak pada maintainability.

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

Component logo memakai asset lokal:

```text
resources/views/components/application-logo.blade.php
public/images/logo-platinum-gym.jpg
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

Logic berada pada proses register di:

```text
app/Http/Controllers/Auth/RegisteredUserController.php
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
- Form Request untuk validasi fitur kompleks.
- Komponen Blade reusable untuk card, form, status badge, dan layout dashboard.
- Cleanup route jika jumlah modul bertambah.
