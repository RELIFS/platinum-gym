# Feature Documentation

Status: Draft. Dokumen ini diperbarui seiring finalisasi kebutuhan dan implementasi fitur.

Dokumen ini mencatat fitur yang sudah tersedia dan rencana fitur pada sistem Platinum Gym Padang.

## Ringkasan Status Fitur

| Fitur | Status | Aktor |
|---|---|---|
| Register member | Sudah tersedia | Pengunjung/member |
| Login | Sudah tersedia | Member, admin, owner |
| Logout | Sudah tersedia | User login |
| Verifikasi email | Sudah tersedia | Member |
| Resend verification email | Sudah tersedia | Member |
| Dashboard protected | Sudah tersedia dasar | User verified |
| Profile | Sudah tersedia dasar dari Breeze | User login |
| Role member | Sudah tersedia | Member |
| Auth UI Platinum Gym | Sudah tersedia | Pengunjung/member |
| Theme toggle | Sudah tersedia | Pengguna UI |
| Dashboard role | Direncanakan | Member, admin, owner |
| Membership package | Direncanakan | Member, admin |
| Booking kelas | Direncanakan | Member, admin |
| Pembayaran | Direncanakan | Member, admin |
| Check-in gym | Direncanakan | Member, admin |
| Laporan owner | Direncanakan | Owner |

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

## Role Member

### Tujuan

Role member digunakan sebagai dasar pembatasan akses fitur member.

### Aktor

- Member.
- Developer.

### Alur Fitur

```text
User register -> sistem membuat role member jika belum ada -> sistem assign role member ke user -> user dapat dikenali sebagai member
```

### Package Terkait

- `spatie/laravel-permission`

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

## Roadmap Fitur

Fitur berikut akan dijelaskan lebih detail setelah kebutuhan dan prioritas implementasi disepakati:

- Dashboard member.
- Dashboard admin.
- Dashboard owner.
- Package membership.
- Booking kelas.
- Payment gateway.
- Check-in gym.
- Laporan owner.
- Manajemen konten company profile.
