# GitHub Actions Documentation

Status: Implemented locally. Workflow CI sudah dibuat pada `.github/workflows/ci.yml`.

Dokumen ini menjelaskan CI sederhana untuk proyek Laravel Platinum Gym Padang.

## Tujuan Workflow

Workflow CI digunakan untuk memastikan setiap perubahan kode dapat di-install, di-build, dicek formatnya, dan diuji secara otomatis di GitHub.

## Lokasi File

```text
.github/workflows/ci.yml
```

File tersebut sudah dibuat dan siap dijalankan setelah branch dipush ke GitHub.

## Trigger

Workflow berjalan saat:

- Push ke branch `rossi`, `luthfi`, `temporarily_main`, atau `main`.
- Pull request ke branch mana pun.

Contoh trigger:

```yaml
on:
  push:
    branches:
      - rossi
      - luthfi
      - temporarily_main
      - main
  pull_request:
```

## Tahapan Workflow

1. Checkout source code.
2. Setup PHP sesuai versi proyek.
3. Install Composer dependency.
4. Setup Node.js.
5. Install NPM dependency.
6. Copy `.env.example` menjadi `.env`.
7. Generate application key.
8. Validasi `composer.json`.
9. Jalankan Laravel Pint dalam mode test.
10. Build asset frontend.
11. Jalankan automated test dengan SQLite in-memory.

## Command Utama

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan key:generate
vendor/bin/pint --test
php artisan test --no-ansi
```

## Testing Framework

Project menggunakan Pest PHP.

```bash
php artisan test --no-ansi
```

Pest berjalan di atas PHPUnit, sehingga tetap kompatibel dengan workflow testing Laravel.

## Rencana Environment CI

CI akan memakai SQLite agar workflow ringan dan tidak perlu service database eksternal.

Contoh konfigurasi environment:

```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## Status Badge

Status badge dapat ditambahkan ke `README.md` setelah workflow berhasil berjalan di GitHub.

Contoh format badge:

```md
![CI](https://github.com/<owner>/<repo>/actions/workflows/ci.yml/badge.svg)
```

## Hasil Workflow

Hasil workflow final akan didokumentasikan dengan:

- Screenshot workflow berhasil.
- Status badge pada README.
- Catatan jika ada error dan cara perbaikannya.

## Catatan Saat Ini

Workflow sudah tersedia di repository lokal. Bukti sukses final tetap perlu diambil dari tab Actions GitHub setelah branch dipush, karena status CI baru muncul di GitHub setelah event `push` atau `pull_request` berjalan.
