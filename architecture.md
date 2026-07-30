# Hasna Shoes — Architecture

Agent 1 deliverable (WordPress Architect). Produced before any theme/plugin code was written, per the project's phase-gating rule.

## 1. Summary

A custom-themed WordPress + WooCommerce site for a luxury women's-shoe brand (Tunisia), converting existing pixel-accurate designs (Homepage, Admin Dashboard) into production PHP/CSS/JS, with original-but-consistent designs for the pages that had no mockup (Shop, Category, Product, Cart, Checkout). Guest checkout only, Cash on Delivery only, FR/EN/AR(RTL) multilingual, Meta Pixel + GA4 tracking, hardened security baseline.

## 2. Source-of-truth design assets

| Asset | Location (outside repo) | Role |
|---|---|---|
| Homepage mockup | `e-commerce/home-page/Hasna Shoes - Accueil.dc.html` | Pixel source for header, hero, homepage sections, footer |
| Admin Dashboard mockup | `e-commerce/Hasna Shoes admin dashboard/Hasna Shoes Admin.dc.html` | Pixel source for the custom admin experience |
| Product photography | `e-commerce/produit/model 1..10/*.webp` | 10 shoe models, 2-3 angles each — source for WooCommerce product galleries |
| Logo | `e-commerce/logo-Photoroom.png` | Brand mark (transparent PNG) |
| Hero animation spec | `work/prompte-build.txt` | Detailed GSAP/ScrollTrigger phase-by-phase spec for the homepage hero |

These live outside the git repo (design-source files, not application code). Final optimized assets get imported into `wp-content/uploads` (gitignored, environment-specific) and theme `assets/` during M1/M8.

### Design tokens extracted from the Homepage mockup

```
--color-gold:        #B8874A   (primary accent, "--g")
--color-gold-light:  #C4A05A   (selection highlight)
--color-burnt-orange: #C2551B  ("--o", secondary accent / CTAs)
--color-cream:        #F7F2EA  (top info bar background)
--color-charcoal:     #2C2C2C  (body text)
--color-charcoal-2:   #33302C  (nav text)
--color-text-muted:   #4A4238

font-heading: 'Playfair Display', serif;   /* 400/500/600 */
font-body:    'Jost', system-ui, sans-serif; /* 300/400/500/600 */
```

Admin dashboard adds `Inter` (400-800) for UI chrome and its own gold `#B8873C`, plus a custom inline SVG icon sprite (home/bag/cart/tag/box/users/message/percent/settings/logout/truck/search/bell).

No Arabic mockup exists — RTL is derived from these same tokens using CSS logical properties (`margin-inline-start` etc.) rather than a hand-mirrored design, flagged for visual QA in M7.

## 3. Environment

- **Stack**: WordPress (latest core), WooCommerce (latest stable), PHP 8.2.12, MySQL (XAMPP), Apache 2.4.58.
- **Local URL**: `http://localhost:8080/hasna-shoes/` (XAMPP Apache listens on 8080, not 80, per existing machine config — confirmed via `httpd.conf`).
- **DB**: `hasna_shoes`, table prefix `hs_`, user `root` (local dev only, no password — never used outside localhost).
- **Table prefix and DB name are placeholders for local dev.** Production deployment (M9) must use a dedicated DB user with least-privilege grants, not root.
- **Admin account**: bootstrap user `hasnaadmin`, credentials shared with the site owner out of band (not committed to git or this file).
- **Permalinks**: `/%postname%/`, `.htaccess` rewrite block written manually (WordPress's automatic `.htaccess` writer didn't fire under this SAPI — documented so it isn't re-debugged later).

## 4. Theme architecture — `wp-content/themes/hasna-shoes/`

Classic PHP theme (not block/FSE) — the source designs are hand-built HTML/CSS/JS, not block markup, so a classic theme with WooCommerce template overrides is the direct, faithful conversion path.

```
hasna-shoes/
  style.css                 theme header + base resets
  functions.php             enqueue, theme supports, hook registrations
  header.php / footer.php   from the Homepage mockup's header/footer markup
  front-page.php            Homepage sections (hero, new collection, best sellers,
                             categories, featured, banner, reviews, IG gallery, newsletter)
  page-*.php                About, Contact, Privacy, Terms
  woocommerce.php            + woocommerce/ template override directory
    archive-product.php     Shop / category listing
    single-product.php      Product page
    cart/cart.php, checkout/form-checkout.php  (guest + COD enforced, fields trimmed)
  inc/
    class-*.php             any custom post types / options (none anticipated beyond WC)
    woocommerce-hooks.php    guest checkout, COD-only, checkout field trimming
    marketing-hooks.php      Meta Pixel / CAPI / GA4 event wiring (M5)
    security-hardening.php   REST restrictions, nonce/AJAX helpers (M6)
  assets/
    src/  (scss, js — GSAP/ScrollTrigger hero, product-card interactions)
    dist/ (built output via a small npm/PostCSS pipeline; gitignored build artifacts, source tracked)
  languages/                Polylang .po/.mo + string exports
```

Rationale for **not** defaulting to Elementor Pro: the brief marks it "only if required," and the mockups are pixel-precise hand-built HTML — a hard-coded template reproduces them exactly and stays lighter/faster than a page-builder render tree. Elementor Pro is added later only for content areas the site owner explicitly wants to edit visually post-launch.

## 5. Plugin stack

| Plugin | Purpose | Notes |
|---|---|---|
| WooCommerce | Store engine | installed (M0) |
| Polylang | FR/EN/AR incl. native RTL | free, no license server dependency (vs. WPML) |
| Advanced Custom Fields | Admin-editable homepage banners/text, Meta Pixel ID field | free tier sufficient |
| Rank Math or Yoast SEO | Schema.org, sitemap, meta/OG/Twitter cards | decided at M5/SEO pass |
| A security plugin (Wordfence or equivalent) | Brute-force protection, firewall | decided at M6, evaluated against the hand-rolled hardening already in `wp-config.php` to avoid redundant overhead |
| Elementor Pro | Opt-in visual editing for select content areas | not installed by default |

## 6. WooCommerce data model

- **Product type**: variable products, one size axis (`pa_taille`, 36-40), `pa_couleur` kept as an informational (non-variation) attribute since each of the 10 source photo sets only covers one color.
- **Categories actually used**: Talons, Sandales, Confort, Plates (mapped from the real photography — all sandals/mules/heels, no sneakers or boots exist in the supplied photos, so those two brief-listed categories weren't fabricated), plus Nouveautés (every product, all newly added) and Promotions (the 2 products given a sale price).
- **Checkout**: guest checkout forced on, registration/login disabled, only Name / Phone / Address / City / Notes fields kept, only Cash on Delivery enabled — enforced at the hook level (`inc/woocommerce-hooks.php`), verified end-to-end with a real test order (placed, confirmed, then deleted).
- **Checkout page type**: switched from WooCommerce's default Cart/Checkout **blocks** to the classic `[woocommerce_cart]` / `[woocommerce_checkout]` **shortcodes** — the blocks use a separate fields API that ignores `woocommerce_checkout_fields`, so the guest/COD/trimmed-fields hooks silently didn't apply until this switch. Classic checkout also gives M3 direct template-override control for the pixel-matched design.
- **Store visibility**: WooCommerce's "Coming soon" mode (new default as of WC 8.8+) was showing a placeholder on Shop/Product/Cart/Checkout; turned off (`woocommerce_coming_soon` = `no`) so the real store renders.
- **Currency**: TND, comma decimal separator, 3 decimals, forced to display literal "TND" (design-accurate) instead of WooCommerce's default `د.ت` glyph for that currency code.
- **Content status**: product names/prices/descriptions are placeholder content (explicitly flagged, French, TND pricing) pending the real catalog data from the site owner — never presented as final in the admin UI.
- **⚠ Product photo provenance**: the supplied `produit/model N` photos are stock/reference images — at least 7 of the 10 show a *different* shoe brand's name printed on the insole or hardware (e.g. "Ellysee", "Erraves", "Avizah Shoes", "Kako Shoes"), only model 1 shows genuine Hasna Shoes branding. These are in use as placeholders so the catalog isn't empty, but **must be replaced with real Hasna-branded photography before launch** — publishing another brand's name on a live storefront is a real (not cosmetic) risk. Tracked for the M8 product-media pass.

## 7. Multilingual / RTL

Polylang with three languages (fr default, en, ar). Arabic gets `dir="rtl"` at `<html>`, and all custom CSS uses logical properties (`inline-start/end`, `margin-inline-*`) instead of `left/right` so RTL mirroring is automatic rather than a second hand-maintained stylesheet. Manual visual QA pass on Arabic in M7 since no Arabic mockup exists to diff against.

## 8. Marketing/analytics (M5)

Meta Pixel + Conversions API and GA4/GTM fired from WooCommerce action hooks:
`woocommerce_after_single_product` → ViewContent, cart AJAX fragments → AddToCart, `woocommerce_before_checkout_form` → InitiateCheckout, `woocommerce_thankyou` → Purchase. Pixel ID stored as an ACF options-page field (admin-editable, per brief).

## 9. Security baseline (established from commit 1, expanded at M6)

Already in place: `wp-config.php` hardening (`DISALLOW_FILE_EDIT`, debug logged not displayed, fresh unique salts, non-default `hs_` table prefix), pretty-permalink `.htaccess`. Planned for M6: REST API endpoint restrictions, nonce/capability checks on every custom AJAX handler, `esc_html`/`esc_attr`/`wp_kses` output escaping audit across all custom templates, security headers (CSP/X-Frame-Options/etc.), login hardening (rename/limit wp-admin access surface, rate limiting), file-permission review, and a documented backup strategy — written up as `security-audit.md`.

## 10. Deployment (M9, not executed against a live server in this phase)

Site currently runs local-only via XAMPP. A deployment checklist (SSL/HTTPS enforcement, object/page caching, CDN for product images, automated backups, `FORCE_SSL_ADMIN` flip, DB user least-privilege, WP core/plugin update policy) is written at M9 for when the site owner has real hosting — nothing is pushed to a live domain as part of this build.

## 11. Version control

Git Flow: `main` (releases) / `develop` (integration) / `feature/*` per milestone, Conventional Commits, PRs into `develop`, remote: `https://github.com/H2698/hasna-shoes.git`. Feature branches are cut from `develop` at the start of the milestone they correspond to, not all pre-created empty.

## 12. Milestone roadmap

See the task list tracked for this build (M0–M9): environment scaffold → theme/homepage → WooCommerce catalog → shop/product/cart/checkout → admin dashboard → marketing → security → QA → product media → deployment prep. Each milestone gets a local smoke test, a commit, and a check-in before the next one starts.
