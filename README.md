# Hasna Shoes

Luxury WooCommerce site for Hasna Shoes (women's shoes, Tunisia). See [architecture.md](./architecture.md) for the full technical design.

## Local setup (XAMPP)

WordPress core and third-party plugins are not committed to this repo (they're vendored dependencies — see `.gitignore`). To stand the environment up from a fresh clone:

1. Download WordPress core from https://wordpress.org/latest.zip and extract it into this folder (everything except `wp-content/`, which this repo already provides).
2. Download WooCommerce from https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip and extract into `wp-content/plugins/`.
3. Create a MySQL database named `hasna_shoes` (utf8mb4 / utf8mb4_unicode_ci).
4. Copy `wp-config-sample.php` (from WordPress core) to `wp-config.php`, set `DB_NAME=hasna_shoes`, `$table_prefix = 'hs_';`, and generate fresh salts from https://api.wordpress.org/secret-key/1.1/salt/.
5. Ensure Apache has `mod_rewrite` enabled and `AllowOverride All` for `htdocs`, then add the standard WordPress rewrite block to `.htaccess` (permalinks: `/%postname%/`).
6. Activate WooCommerce, then this repo's `hasna-shoes` theme, from wp-admin.
7. Install the French language pack: Settings → General → Site Language → "Français". This installs both WordPress core's and WooCommerce's French translations — without it, core/WooCommerce strings (search results, sorting labels, breadcrumbs, etc.) render in English even though every string this theme writes itself is already French.

Local dev URL used during this build: `http://localhost:8080/hasna-shoes/` (this machine's XAMPP Apache listens on port 8080).

## Status

Build in progress — see the milestone list in `architecture.md` §12.
