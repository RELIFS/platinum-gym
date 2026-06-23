# Public Website Responsive QA

Scope: public website only. Do not use real production submissions, do not spam Gymmi, and do not capture screenshots that expose secrets or private portal data.

## Pages

- `/`
- `/tentang-kami`
- `/layanan`
- `/kelas`
- `/produk`
- `/galeri`
- `/lokasi`
- `/bmi`
- `/syarat-ketentuan`
- `/kebijakan-privasi`

## Viewport Matrix

Run smoke checks at these widths:

`320, 360, 375, 390, 393, 412, 414, 430, 480, 540, 600, 640, 768, 820, 834, 1024, 1180, 1280, 1366, 1440, 1536, 1728, 1920, 2560, 3440`

Use height `800` for mobile/tablet smoke, `900` for laptop/desktop, and `1200` for wide/ultrawide when visual hierarchy needs more vertical context.

## Automated Browser Smoke

- HTTP response is OK.
- Primary heading is visible.
- Browser console has no uncaught JavaScript errors.
- `document.documentElement.scrollWidth <= window.innerWidth`.
- Header logo, navigation, mobile menu trigger, theme toggle, main content, footer, and Gymmi trigger are present.
- Mobile menu opens and closes at mobile widths.
- Theme toggle can be activated without layout shift or console error.
- `/kelas` filter GET works for day/type and reset returns to the base route.
- `/produk` search/category filter works and reset returns to the base route.
- Gymmi panel is hidden on first paint, opens, traps obvious focus path, closes, and does not cover primary CTA after closing.
- BMI inputs update the result client-side without a network submit.
- Footer legal/contact links remain visible.

## Responsive Acceptance

- No horizontal overflow.
- No clipped headings, labels, prices, product names, or CTA text.
- Touch targets feel at least 44px for primary controls.
- Header/mobile menu does not overlap content.
- Hero remains scannable at 320-430px.
- Package, class, product, trainer, promo, testimonial, and gallery cards do not become too dense.
- Product notice, filter toolbar, category chips, and CTA buttons do not overflow.
- Gallery images keep reasonable crop quality.
- Maps iframe/fallback remains usable and does not force page width.
- Footer columns collapse cleanly.
- Tablet widths 768/820/834 feel intentional, not an awkward stretched mobile layout.
- Desktop, wide, and ultrawide content keeps `public-container` hierarchy and does not stretch into unreadable line lengths.

## Accessibility And SEO Smoke

- `html[lang="id"]`, skip link, and `main#main-content` are present.
- Navigation has accessible labels and active route state.
- Mobile menu trigger has `aria-controls` and changing expanded state.
- Theme/Gymmi icon buttons have accessible names.
- Product/class filters and BMI inputs have labels tied to input IDs.
- Status and empty states use visible text, not color only.
- Important images have meaningful alt text or decorative treatment.
- Each public page has title, meta description, canonical URL, OG/Twitter metadata, favicon/manifest, and valid JSON-LD `HealthClub`.

## Manual QA

- Real device touch comfort on 320-430px phones.
- Dark/light visual feel and focus visibility.
- Wide/ultrawide visual hierarchy at 1920, 2560, and 3440.
- Gallery image crop quality against real content.
- Maps iframe interaction and external Maps handoff.
- Gymmi real provider quota behavior with one safe test message only.
- Optional Lighthouse SEO/accessibility review on the home page and one dense page (`/produk` or `/kelas`).
