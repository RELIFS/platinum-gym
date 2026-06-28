# Member Portal Responsive Browser QA

Scope: Portal Member only. Use the current browser session or a safe local testing account. Do not buy real memberships, submit real payments, scan raw QR tokens in screenshots, or mutate production-like data.

## Pages

- `/member/dashboard`
- `/member/profil`
- `/member/profil/edit`
- `/member/membership`
- `/member/booking-kelas`
- `/member/riwayat-booking`
- `/member/transaksi`
- `/member/transaksi/{payment}`
- `/member/invoice/{invoice}`
- `/member/invoice/{invoice}/struk`
- `/member/qr`
- `/member/notifikasi`
- `/member/complete-profile`
- `/profile` as member

## Viewports

Run smoke checks at these widths:

`320, 360, 375, 390, 393, 412, 414, 430, 480, 540, 600, 640, 768, 820, 834, 1024, 1180, 1280, 1366, 1440, 1536, 1728, 1920, 2560, 3440`

Use height `800` for mobile/tablet smoke, `900` for laptop/desktop, and `1200` for ultrawide when visual hierarchy needs more vertical context.

## Automated Browser Smoke

- Page returns HTTP 200 after login and shows the expected Member heading.
- Browser console has no uncaught JavaScript error.
- `document.documentElement.scrollWidth <= document.documentElement.clientWidth`.
- `#member-main` is visible.
- Mobile drawer trigger exposes `aria-controls`, opens, closes, supports Escape, and returns focus.
- Desktop sidebar is visible at desktop widths and mobile drawer is hidden.
- `Website Utama` is visible and focusable as the bottom menu item in the desktop sidebar and mobile drawer, links to `/`, and does not replace or obscure footer `Keluar`.
- Topbar, flash banners, notification badge, theme toggle, and Gymmi trigger remain visible and do not cover primary actions.
- Membership catalog filters/search/pagination do not create overflow.
- Booking schedule cards, booking history, transaction table/cards, notifications, and QR panel remain readable.
- Transaction, invoice, receipt, QR, and notification pages do not expose raw QR token, payment token, redirect URL, raw gateway payload, or internal notes.
- Profile forms keep labels, helper text, and validation errors near fields; inputs fit at 320-430px.

## Responsive Acceptance

- No horizontal overflow.
- No clipped headings, labels, prices, status text, or CTA text.
- Touch targets feel comfortable for primary controls, ideally 44px.
- Cards are not too dense at 320-430px.
- Tablet widths 768/820/834 feel intentional, with tables either scrollable or replaced by cards.
- QR visual stays proportional and never exposes raw token text.
- Gymmi stays below drawers/modals and does not cover checkout, booking, or payment actions.
- Desktop, wide, and ultrawide layouts keep readable max-width and hierarchy.
- Statuses include visible text, not color alone.

## Manual QA

- Real-device touch comfort for drawer, filters, payment actions, booking cancel, QR download, and avatar upload.
- Dark/light readability.
- Print invoice and receipt.
- Real payment redirect behavior on sandbox only.
- QR download/open behavior without exposing the token in screenshots or logs.
- Gymmi one safe message only when provider quota needs smoke testing.
