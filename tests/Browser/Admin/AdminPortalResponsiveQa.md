# Admin Portal Responsive Browser QA

Scope: Portal Admin only. Run on local/testing data with a safe admin account. Do not approve/reject real payments, delete real data, or change production settings.

## Pages

- `/admin`
- `/admin/check-in`
- `/admin/booking`
- `/admin/notifikasi`
- `/admin/anggota`
- `/admin/paket`
- `/admin/kelas`
- `/admin/pembayaran`
- `/admin/produk`
- `/admin/galeri`
- `/admin/testimoni`
- `/admin/promo`
- `/admin/trainer`
- `/admin/laporan`
- `/admin/audit-log`
- `/admin/pengaturan`
- `/admin/profil`
- `/admin/invoice/{invoice}`
- `/admin/invoice/{invoice}/struk`
- `/admin/resource/products/tambah`
- `/admin/resource/products/{id}/edit`
- `/admin/resource/members/tambah`
- `/admin/resource/packages/tambah`
- `/admin/resource/classes/tambah`

## Viewports

Use height `800` for mobile/tablet smoke, `900` for laptop/desktop, and `1200` for ultrawide when needed.

| Width | Device class | Required checks |
| ---: | --- | --- |
| 320 | Mobile narrow | Drawer opens/closes, no horizontal overflow, touch targets usable, forms stack cleanly. |
| 360 | Mobile | Same as 320; check table mobile cards and filters. |
| 375 | Mobile | Same as 360. |
| 390 | Mobile | Same as 360. |
| 393 | Mobile | Same as 360. |
| 412 | Mobile large | Cards remain readable; button groups do not wrap badly. |
| 414 | Mobile large | Same as 412. |
| 430 | Mobile large | Same as 412. |
| 480 | Mobile wide | Drawer and form controls remain aligned. |
| 540 | Mobile wide | Tables still use mobile cards or clear scroll affordance. |
| 600 | Small tablet | Topbar, filters, and cards do not feel cramped. |
| 640 | Small tablet | Same as 600. |
| 768 | Tablet | Sidebar state, tablet table density, filters, pagination, and action buttons. |
| 820 | Tablet | Same as 768. |
| 834 | Tablet | Same as 768. |
| 1024 | Laptop/tablet landscape | Desktop sidebar appears, content wrapper remains stable. |
| 1180 | Laptop | Table columns, dashboard cards, report/export actions. |
| 1280 | Laptop | Same as 1180. |
| 1366 | Desktop | Normal desktop baseline. |
| 1440 | Desktop | Same as 1366. |
| 1536 | Desktop wide | Max width and hierarchy remain controlled. |
| 1728 | Desktop wide | Same as 1536. |
| 1920 | Desktop wide | Same as 1536. |
| 2560 | Ultrawide | Content does not stretch without hierarchy. |
| 3440 | Ultrawide | Content stays bounded and readable. |

## Automated Browser Checks

- Page loads with HTTP 200 after login.
- No console errors.
- `document.documentElement.scrollWidth <= window.innerWidth`.
- `#admin-main` exists and visible.
- Mobile widths: drawer trigger has `aria-controls="admin-mobile-navigation"`; drawer opens and closes; Escape closes it; focus returns to trigger.
- Desktop widths: sidebar visible; mobile drawer hidden.
- Table pages: `.admin-table-wrap` exists on tablet/desktop and `.admin-table-mobile-card` exists for mobile fallback.
- Forms: labels are visible, inputs fit parent width, validation errors appear near fields after safe invalid submit.
- Modal/confirmation flows: open and cancel only; do not confirm destructive actions on real data.
- Filter/search/pagination: GET interactions preserve layout and do not create overflow.
- Flash messages: success uses status semantics, error uses alert semantics.
- Light and dark themes remain readable.

## Manual QA Checks

- Camera permission prompt and real QR scan flow.
- Visual feel of light/dark contrast on real devices.
- Print preview for invoice and receipt.
- Destructive operational flows using seeded sandbox data only.
- Touch comfort for action menus, dropdowns, and confirmation controls.
