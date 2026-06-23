# Owner Portal Responsive QA Checklist

Scope: Portal Owner only. Use a local testing account and safe seeded data. Avoid destructive actions and avoid repeated export/download checks.

## Pages

- `/owner`
- `/owner/laporan`
- `/owner/laporan/keuangan`
- `/owner/laporan/member`
- `/owner/laporan/booking-kelas`
- `/profile` as owner
- `/owner/invoice/{invoice}`
- `/owner/invoice/{invoice}/struk`

## Viewports

`320, 360, 375, 390, 393, 412, 414, 430, 480, 540, 600, 640, 768, 820, 834, 1024, 1180, 1280, 1366, 1440, 1536, 1728, 1920, 2560, 3440`

Use height `800` for mobile/tablet smoke, `900` for laptop/desktop, and `1200` for ultrawide if the viewport feels vertically cramped.

## Automated Browser Smoke

- Page returns HTTP 200 and shows the expected Owner heading.
- Console has no JavaScript error.
- `document.documentElement.scrollWidth <= document.documentElement.clientWidth`.
- `#owner-main` is visible.
- Mobile drawer opens, closes, keeps footer identity/logout visible, and returns focus.
- Topbar buttons remain at comfortable touch size.
- Report filter submit/reset keeps the current report route.
- Finance report shows mobile cards below `md` and a scrollable table from `md`.
- Invoice and receipt pages do not expose gateway token, raw payment payload, QR token, or internal notes.

## Manual QA

- Print invoice and receipt from browser.
- Check dark/light readability.
- Check real-device touch comfort for drawer, filters, export buttons, invoice actions, and profile upload.
- Run at most one CSV, XLSX, and PDF export smoke test on sandbox data.
- Confirm desktop, wide, and ultrawide layouts keep hierarchy and do not stretch content without bounds.

## Acceptance

- No horizontal overflow.
- No clipped text or incoherent overlap.
- Dashboard metrics and chart remain scannable.
- Report filters, export buttons, tables, mobile cards, pagination, and empty states are readable.
- Invoice preview is readable on mobile, tablet, laptop, desktop, and ultrawide.
- Statuses include readable text, not color alone.
