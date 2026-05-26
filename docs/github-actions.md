# GitHub Actions Documentation

Status: Planned. Workflow CI akan ditambahkan setelah pipeline testing dan build distabilkan.

Dokumen ini menjelaskan rancangan CI sederhana untuk proyek Laravel Platinum Gym Padang.

## Tujuan Workflow

Workflow CI direncanakan untuk memastikan setiap perubahan kode tetap dapat di-install, di-build, dan diuji secara otomatis di GitHub.

## Lokasi File

```text
.github/workflows/ci.yml
```

File tersebut belum dibuat pada tahap ini. Implementasi dilakukan setelah kebutuhan pipeline disepakati.

## Trigger Rencana

Workflow akan berjalan saat:

- Push ke branch utama pengembangan.
- Pull request ke branch utama.

Contoh trigger:

```yaml
on:
  push:
  pull_request:
```

## Tahapan Workflow Rencana

1. Checkout source code.
2. Setup PHP sesuai versi proyek.
3. Install Composer dependency.
4. Setup Node.js.
5. Install NPM dependency.
6. Copy `.env.example` menjadi `.env`.
7. Generate application key.
8. Setup database testing menggunakan SQLite.
9. Jalankan migration.
10. Build asset frontend.
11. Jalankan automated test.

## Command Utama

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan key:generate
php artisan migrate --force
php artisan test
```

## Testing Framework

Project menggunakan Pest PHP.

```bash
php artisan test
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

Status badge akan ditambahkan ke `README.md` setelah workflow dibuat.

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

GitHub Actions belum dibuat pada tahap ini. Workflow akan ditambahkan setelah kebutuhan pipeline build dan testing disepakati.
