# Public Website Responsive Audit

Tanggal audit: 2026-05-31
Target: public website Platinum Gym Padang di Laravel root.

## Ringkasan

Status responsive public website: **lulus untuk breakpoint representatif mobile, tablet, laptop, desktop, dan wide desktop**.

Update polish 2026-05-30: mobile navigation sudah memakai scroll containment internal, hamburger memakai aria label dinamis, footer link punya tap area lebih nyaman, konten dinamis diberi `break-words`, Maps iframe memakai title deskriptif dan helper copy permanen, serta chatbot quick replies wrap di dalam dialog mobile.

Update polish 2026-05-31: public button/link/icon control memakai `focus-visible` ring, header/logo/nav/footer/contact links memakai tap target lebih nyaman, hero home mobile diturunkan ke 36px pada 320px, hero memakai `100dvh`, visual collage dibuat lebih tenang, image hero/fallback Maps diberi dimensi eksplisit, image utama hero memakai `fetchpriority="high"`, dan chatbot mendapat dialog focus target, focus return ke trigger, `aria-live="polite"`, serta overscroll containment.

Update hero 2026-05-31: hero beranda dipadatkan lagi untuk small phone. Mobile tidak lagi memaksa tinggi `100dvh`; teks, dua CTA utama, visual gym compact, dan stats ringkas tampil berurutan. WhatsApp tetap tersedia sebagai text link, sementara desktop mempertahankan collage dengan tinggi sekitar `90dvh` agar section berikutnya lebih cepat terasa.

Update hero visual 2026-05-31: foto utama hero memakai visual gym/strength training agar mobile tidak terasa hanya mempromosikan Muaythai. Desktop tetap memakai 3 foto dalam collage untuk menunjukkan variasi layanan, dengan Muaythai sebagai foto pendukung.

Tidak ditemukan masalah nyata berupa horizontal scroll, elemen utama keluar viewport, gambar rusak, header/nav hilang, chatbot tidak muat, form filter pecah, Google Maps iframe pecah, atau error JavaScript aplikasi.

Catatan: pass awal sempat mendeteksi dekorasi absolute/background glow sebagai elemen keluar viewport. Itu bukan defect responsive karena parent section memakai `overflow-hidden` dan tidak menghasilkan horizontal scroll dokumen. Pass kedua memfilter dekorasi non-konten dan hasilnya bersih.

## Scope Route

Route yang diaudit:

```text
/
/tentang-kami
/layanan
/kelas
/produk
/galeri
/lokasi
/bmi
/syarat-ketentuan
/kebijakan-privasi
```

## Viewport Matrix

Viewport utama:

| Device representative | Size | Result |
|---|---:|---|
| Small phone | 320x568 | Pass |
| Android phone | 360x740 | Pass |
| iPhone class | 390x844 | Pass |
| Large phone | 430x932 | Pass |
| Tablet portrait | 768x1024 | Pass |
| Tablet landscape | 1024x768 | Pass |
| Desktop | 1280x720 | Pass |
| Desktop large | 1440x900 | Pass |
| Wide desktop | 1920x1080 | Pass |

Viewport tambahan:

| Device representative | Size | Result |
|---|---:|---|
| Phone landscape small | 568x320 | Pass |
| Phone landscape | 667x375 | Pass |
| iPad Air class | 820x1180 | Pass |
| Surface portrait class | 912x1368 | Pass |
| Common laptop | 1366x768 | Pass |
| Desktop medium | 1536x864 | Pass |

## Automated Evidence

Browser audit dengan Chrome headless + Playwright runtime lokal:

```text
90 route/viewport checks: 90 pass, 0 fail
48 additional orientation/device checks: 48 pass, 0 fail
20 interaction checks: 20 pass, 0 fail
```

Feature regression test:

```text
php artisan test tests\Feature\PublicWebsiteTest.php --no-ansi
20 passed / 65 assertions
```

Verifikasi setelah UX polish 2026-05-30:

```text
php artisan test --no-ansi
76 passed / 356 assertions

npm.cmd run build
vite build success

Browser smoke after polish
40 route/viewport checks pass, 0 fail
focused mobile interaction checks pass, 0 fail
```

Browser smoke setelah polish mencakup `/`, `/lokasi`, `/kelas`, `/produk`, dan `/bmi` pada 320x568, 390x844, 568x320 landscape, 768x1024, 1024x768, 1366x768, 1440x900, dan 1920x1080. Focused checks mencakup mobile nav open/close/scroll containment, Maps iframe/helper/buttons, chatbot open/send/quick replies, filter kelas/produk, BMI, dan dark mode.

Verifikasi setelah professional UI/UX polish 2026-05-31:

```text
vendor\bin\pint --test
pass

php artisan test --no-ansi
76 passed / 356 assertions

npm.cmd run build
vite build success

Browser smoke after professional polish
80 route/viewport checks pass, 0 fail
focused interaction checks pass for mobile nav, chatbot focus/send/close, Maps, BMI, kelas filter, produk filter, and dark mode
```

Verifikasi setelah compact home hero 2026-05-31:

```text
vendor\bin\pint --test
pass

php artisan test --no-ansi
76 passed / 356 assertions

npm.cmd run build
vite build success

Hero smoke viewport check
320x568, 390x844, 568x320, 768x1024, 1366x768, 1440x900, 1920x1080: no horizontal overflow
320x568 primary CTA bottom: 530px, inside first viewport
320x568 hero height after compact polish: 912px
390x844 hero height after compact polish: 877px
```

## Checklist Yang Diverifikasi

- No horizontal scroll pada document root/body.
- Header sticky tetap terbaca dan tidak keluar viewport.
- Desktop navigation muncul pada breakpoint `lg` ke atas.
- Mobile hamburger muncul pada breakpoint di bawah `lg`.
- Mobile navigation dapat dibuka pada 320, 390, dan 768px tanpa overflow horizontal.
- H1 tersedia di semua halaman public/legal.
- CTA utama tetap visible dan touch target tombol utama memadai.
- Header logo, desktop nav, theme toggle, hamburger, CTA, footer links, footer contact links, dan contact lokasi punya tap target nyaman.
- Focus ring public controls muncul via keyboard/focus-visible tanpa mengganggu klik mouse.
- Grid card layanan, jadwal, produk, galeri, testimoni, dan lokasi turun ke 1 kolom pada mobile.
- Filter kelas dan produk fit pada 320px.
- BMI input dan hasil update pada 320px tanpa overflow.
- Chatbot trigger visible di semua route dan viewport.
- Chatbot dialog fit pada 320x568, 390x844, dan 1280x720.
- Chatbot send message tidak merusak layout.
- Google Maps iframe `/lokasi` responsive dan tidak membuat overflow.
- Light/dark mode responsive pada home, kelas, lokasi, dan BMI.
- Gambar public tidak broken.
- Tidak ada page error JavaScript aplikasi pada audit.

## Analisis Per Area

### Header dan Navigasi

Header menggunakan pola yang benar: desktop nav `lg:flex`, mobile action area `lg:hidden`, touch target 44px untuk logo/nav/theme toggle/hamburger/CTA, serta mobile nav full-width card dalam container. Panel mobile sekarang memakai `max-h-[calc(100dvh-6rem)] overflow-y-auto overscroll-contain`, sehingga menu tetap usable pada device pendek tanpa memaksa scroll halaman utama.

### Layout Section dan Grid

`public-container`, `public-section`, dan grid responsif sudah konsisten. Halaman layanan, kelas, produk, galeri, lokasi, dan BMI turun ke 1 kolom pada mobile; tablet mulai memakai 2 kolom; desktop memakai 3-4 kolom sesuai kebutuhan.

### Hero

Hero public memakai heading besar tetapi tetap wrap normal pada 320px. Home hero sekarang memakai 36px pada small phone dan naik progresif ke 72px di desktop. Pada mobile, hero memakai alur compact: copy utama, dua CTA utama, text link WhatsApp, visual gym/strength training rasio lebar, lalu stats 3 kolom kecil. Pada desktop, hero mempertahankan collage premium dengan tinggi sekitar `90dvh` sehingga halaman berikutnya tidak terasa terlalu jauh. Foto utama mewakili gym secara umum; Muaythai tetap hadir sebagai foto pendukung desktop. Image hero punya dimensi eksplisit dan image utama memakai `fetchpriority="high"`.

### Form dan Input

Filter kelas dan produk stack pada mobile. Tombol submit/reset menggunakan full width pada mobile dan auto width di desktop. BMI input memakai grid 1 kolom pada mobile dan 2 kolom pada `sm`.

### Chatbot

Chatbot mobile memakai dialog fixed `inset-x-3 bottom-3` dengan tinggi `min(620px, calc(100dvh - 1.5rem))`. Ini fit pada 320x568 dan tetap menyisakan margin. Quick reply sekarang wrap di dalam dialog pada mobile, punya tap target lebih nyaman, message area memakai `aria-live="polite"`, dialog fokus saat dibuka, dan fokus kembali ke trigger saat ditutup.

### Lokasi dan Google Maps

Maps memakai iframe responsive dalam wrapper `min-h-[26rem]` dan `sm:min-h-[30rem]`. Pada 390x844 iframe berukuran sekitar 356x416, cukup untuk interaksi dasar. Iframe memakai title `Peta lokasi Platinum Gym Padang di Google Maps`, helper copy permanen, dan tombol Maps tetap berada di bawah iframe tanpa overlap atau overflow.

### Footer

Footer stack menjadi 1 kolom pada mobile dan 3 kolom pada desktop. Link footer tetap berupa text link, tetapi sekarang memakai minimum height 44px, focus-visible ring, dan text wrapping agar lebih nyaman ditekan tanpa berubah menjadi button besar.

## Risiko dan Rekomendasi

- Dekorasi absolute/background glow aman karena section memakai `overflow-hidden`. Jika nanti root `overflow-hidden` dihapus, audit horizontal scroll harus dijalankan ulang.
- Guard `break-words` sudah ditambahkan pada card dinamis utama, kontak, footer, Maps helper, testimonial, dan chatbot. Jika modul admin media/konten nanti menambah field public baru, field tersebut perlu mengikuti pola yang sama.
- Jika copy hero berubah menjadi jauh lebih panjang, ulangi smoke 320x568 karena hero sengaja dibuat padat untuk menjaga CTA dan visual tetap muncul cepat.
- Jika chatbot backend AI mulai mengembalikan jawaban panjang atau URL panjang, perlu uji ulang untuk paragraf panjang lintas device.

## Verdict

Responsive public website saat ini **production-ready untuk fase company profile public** pada breakpoint representatif. Polish 2026-05-31 menyelesaikan refinement UX mikro yang tersisa tanpa mengubah route, flow, atau visual brand utama.
