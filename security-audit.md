# Hasna Shoes — Security Audit

Agent 6 deliverable. Covers what was audited in the existing codebase (M0–M5), what's implemented in this pass (`inc/security.php` unless noted), and what's explicitly deferred to a real production environment (this build runs on local XAMPP — some controls only make sense against a live server).

## 1. Code audit findings (before this pass)

Systematic grep-based review of every custom PHP file in the theme, ahead of adding new hardening:

- **SQL injection**: zero raw `$wpdb` queries anywhere in the codebase — every data access goes through WordPress/WooCommerce APIs (`WP_Query`, `wc_get_orders()`, `get_posts()`, etc.), which parameterize internally. Nothing to fix.
- **XSS / output escaping**: every `echo` of a variable found (`grep -rn "<?php echo "` across the theme, filtered for the small number of known-safe cases like `$product->get_image()` returning WooCommerce's own pre-escaped markup) uses `esc_html`/`esc_attr`/`esc_url`/`esc_js`/`wp_kses_post`. Confirmed the specific case that looked riskiest — order billing name/city rendered in the admin dashboard table — is escaped at output.
- **CSRF**: both custom AJAX endpoints (newsletter signup, contact form) already used `wp_nonce_field()` + `check_ajax_referer()` from when they were built (M1/M4). Confirmed still correct.
- **Capability checks**: the custom dashboard requires `edit_shop_orders`, the settings page requires `manage_options` — both checked with `current_user_can()` before rendering.

Conclusion: the application-level code was already in good shape (built with escaping/sanitization/nonces as a habit throughout, not bolted on after). This pass is almost entirely *new* WordPress-level and infrastructure-level hardening, not vulnerability fixes.

## 2. What this pass adds (`inc/security.php`)

**Login / brute-force protection**
- Login attempts rate-limited per IP+username pair (not IP-only — a shared office IP shouldn't lock out every employee over one attacked account; not username-only — an attacker shouldn't be able to lock out a real admin by deliberately failing their login). 5 failures → 15-minute lockout, checked via the `authenticate` filter so it blocks even a *correct* password during the lockout window. Verified: a failed login shows the generic message, a subsequent correct login still succeeds and clears the counter.
- Login error messages generalized to "Identifiants incorrects." — WordPress's default reveals whether a *username* exists (vs. just the password being wrong), which is a username-enumeration vector.
- `?author=1`, `?author=2`, … numeric author-ID enumeration redirected away.
- `/wp-json/wp/v2/users` (and the single-user variant) requires authentication — the other classic enumeration vector, via the REST API. Verified: returns 401 for anonymous requests, unaffected for logged-in admin use (block editor, etc.).

**XML-RPC**: disabled (`xmlrpc_enabled` filter). Verified: authenticated methods (`wp.getUsersBlogs`, the ones usable for brute-forcing or content injection) return "XML-RPC services are disabled on this site."; unauthenticated protocol-discovery methods (`system.listMethods`) still respond, which is normal WordPress behavior and not a vulnerability — no credentials can be tested through them. Pingback header/link removed too.

**Version disclosure**: `wp_generator` meta tag removed so the exact WordPress version isn't advertised to anonymous visitors (makes it marginally harder to match this install to a known CVE).

**Security headers** (`send_headers`, front-end only): `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` disabling geolocation/microphone/camera (none are used). Verified present via `curl -I`.

**Content-Security-Policy — deliberately not added.** This theme has several legitimate inline `<script>` blocks (GSAP hero config, marketing pixel events, the admin media picker). A CSP strict enough to matter would need per-script nonces threaded through every one of them, and getting that wrong silently breaks checkout tracking or the hero animation rather than throwing a visible error. Recommended as a dedicated follow-up with full regression testing (fits naturally into M7 QA), not bolted on blind here.

**REST API**: kept enabled (WooCommerce, media uploads, and the block editor all depend on it) but the anonymous index response no longer lists every registered route (`rest_index` filter), and `/wp/v2/users` requires auth (above). WooCommerce's own order/customer REST endpoints already require authentication by default; added an explicit `woocommerce_rest_check_permissions` filter as a belt-and-braces confirmation, not a new restriction.

**AJAX abuse protection**: the newsletter/contact endpoints already had CSRF nonces, but a valid nonce only proves the request came from our page — not that a script isn't hammering it. Added a simple per-IP rate limit (5 requests/minute) on top, checked before the nonce/business logic runs. Verified a normal single submission still succeeds.

**wp-config.php** (not tracked in git — see README.md for reproducing it): `DISALLOW_FILE_EDIT` and unique salts were already set from the very first commit (M0). Added `DISALLOW_UNFILTERED_HTML` — even admin accounts get their post content run through KSES filtering, closing off script-tag injection via post content as an attack path even if an admin account were compromised.

## 3. WooCommerce / customer data

- Guest-checkout-only + Cash-on-Delivery-only enforced at the hook level since M2 (`inc/woocommerce-hooks.php`) — not just hidden UI, the payment gateway list is filtered server-side so no other gateway can be reached even by a direct request.
- Checkout fields trimmed to exactly what the brief specifies (name/phone/address/city/notes) — nothing extra collected.
- Order PII (billing name/phone/address) only ever rendered inside `current_user_can( 'edit_shop_orders' )`-gated admin screens, always through `esc_html()`.
- Meta Conversions API (M5) hashes email/phone with SHA-256 before they ever leave the server, per Meta's own API requirement — raw PII is never transmitted to a third party from this codebase.

## 4. Deferred to deployment (M9) — not applicable to local XAMPP

- **SSL/HTTPS**: `FORCE_SSL_ADMIN` is set to `false` with an inline comment to flip it once a real certificate exists; there's no cert to force on localhost.
- **File permissions**: this environment is Windows/XAMPP, which doesn't use Unix permission bits the way production Linux hosting does. Recommended production values (documented for M9): `wp-config.php` → `440`, other PHP files → `644`, directories → `755`, `wp-content/uploads` writable by the web server user only.
- **Backup strategy**: needs a real host to back up to. M9 will document the recommended approach (automated daily DB dump + file snapshot, offsite storage, tested restore procedure) — not implemented against a database that only exists on this machine.
- **WAF / managed-host-level protections** (e.g. Wordfence, Cloudflare, host-level brute-force protection): evaluated against what's already hand-built here (per architecture.md §5) — worth revisiting once real hosting is chosen, since some hosts include equivalent protection at the infrastructure level, making a redundant plugin just extra overhead.
- **Database user**: currently `root`/no password, correct only for an isolated local dev machine. Production needs a dedicated least-privilege DB user (SELECT/INSERT/UPDATE/DELETE on the `hasna_shoes` database only, no `GRANT`/`DROP`/admin privileges).

## 5. Verification performed

- `curl -I` against the live site confirms all four security headers present.
- XML-RPC: confirmed authenticated methods rejected, confirmed no fatal errors introduced (an earlier draft of the `/wp/v2/users` REST restriction caused a fatal error by assuming an internal WordPress array shape that turned out to be wrong — caught immediately via the debug log, fixed by switching to the documented `rest_pre_dispatch` pattern instead of mutating `rest_endpoints` directly).
- REST `/wp-json/` returns valid JSON with `routes` stripped for anonymous requests; `/wp-json/wp/v2/users` returns 401.
- Full login cycle tested: wrong password → generic error, counter increments; correct password afterward → succeeds, counter clears, lands on the custom dashboard (M4's `login_redirect` still works with the new `authenticate` filter in place).
- Newsletter AJAX endpoint retested end-to-end with the new rate limiter active — still succeeds for a normal single request.
- Product search retested — the `pre_get_posts` search-scope restriction (limits search to `product`/`page`, defense-in-depth against ever accidentally exposing a non-public CPT like the contact-message or newsletter-signup post types through search) doesn't affect the existing product search feature.
- `wp-content/debug.log` checked before and after every change in this pass — no new warnings/notices/errors introduced by the final code (the one fatal error hit during development was fixed before merging, as noted above).
