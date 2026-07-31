<?php
/**
 * Hasna Shoes theme bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HASNA_THEME_VERSION', '1.0.0' );
define( 'HASNA_THEME_DIR', get_template_directory() );
define( 'HASNA_THEME_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function hasna_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 132,
		'width'       => 132,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'automatic-feed-links' );

	// WooCommerce.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary' => __( 'Menu principal', 'hasna-shoes' ),
		'footer-boutique' => __( 'Pied de page — Boutique', 'hasna-shoes' ),
		'footer-aide'     => __( 'Pied de page — Aide', 'hasna-shoes' ),
	) );
}
add_action( 'after_setup_theme', 'hasna_setup' );

/**
 * Loading the textdomain is deliberately NOT on `after_setup_theme` (the
 * usual WP recommendation) and deliberately NOT just a plain
 * load_theme_textdomain() call. Two compounding issues with Polylang:
 *
 * 1. Polylang determines the current request's language (from the URL)
 *    after `after_setup_theme` has already run, so loading there always
 *    resolves the *default* (French) locale regardless of which language
 *    was actually requested.
 * 2. The very first `__()`/`_e()` call for this textdomain — e.g. the
 *    `register_nav_menus()` labels in hasna_setup(), which still runs on
 *    after_setup_theme — triggers WordPress's automatic "just in time"
 *    textdomain loading using *that* (wrong, default-locale) lookup, and
 *    the resulting "no translation file" result then sticks for the rest
 *    of the request: a later, correctly-timed load_theme_textdomain()
 *    call is silently a no-op because WordPress believes this domain was
 *    already (unsuccessfully) loaded.
 *
 * Fix: explicitly unload before reloading, on `wp` — by then Polylang has
 * fully resolved the request's language and the main query has run.
 * Verified: without the unload, get_locale() correctly returns 'en_US'/
 * 'ar' but __() still returns the untranslated French string; with it,
 * translations resolve correctly for both locales.
 */
add_action( 'wp', function () {
	unload_textdomain( 'hasna-shoes' );
	load_textdomain( 'hasna-shoes', HASNA_THEME_DIR . '/languages/hasna-shoes-' . get_locale() . '.mo' );
} );

/**
 * The site tagline ("L'élégance à chaque pas") is a single global
 * `blogdescription` option, not translated by Polylang out of the box —
 * it feeds the front-page <title> and SEO/meta output, so it stayed
 * French even on the English/Arabic site. `is_admin()` guard keeps the
 * raw French value visible when editing it in Settings -> General.
 */
add_filter( 'option_blogdescription', function ( $tagline ) {
	if ( is_admin() || ! function_exists( 'pll_current_language' ) ) {
		return $tagline;
	}
	$lang = pll_current_language();
	$map  = array(
		'en' => 'Elegance in every step',
		'ar' => 'الأناقة في كل خطوة',
	);
	return $map[ $lang ] ?? $tagline;
} );

/**
 * Real destinations for the primary nav (used by both the desktop fallback
 * nav and the mobile nav, until the site owner sets a menu in Appearance ->
 * Menus). "Femme" and "Collections" both point to the shop — same as the
 * approved design, which linked both to the homepage's single categories
 * section; there's no separate "collections" taxonomy to send them to.
 */
function hasna_primary_nav_items() {
	$home_url  = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
	$shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : $home_url;
	// 'lang' => 'fr' — see hasna_category_url() for why the plain slug lookup needs it.
	$new_terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'slug'       => 'nouveautes',
		'lang'       => 'fr',
		'hide_empty' => false,
	) );
	$new_term  = ( ! is_wp_error( $new_terms ) && ! empty( $new_terms ) ) ? $new_terms[0] : null;
	if ( $new_term && ! is_wp_error( $new_term ) && function_exists( 'pll_get_term' ) ) {
		$translated_id = pll_get_term( $new_term->term_id );
		if ( $translated_id ) {
			$new_term = get_term( $translated_id, 'product_cat' );
		}
	}
	$new_url = ( $new_term && ! is_wp_error( $new_term ) ) ? get_term_link( $new_term ) : $shop_url;

	return array(
		array( $home_url, __( 'Accueil', 'hasna-shoes' ) ),
		array( $shop_url, __( 'Femme', 'hasna-shoes' ) ),
		array( $shop_url, __( 'Collections', 'hasna-shoes' ) ),
		array( $new_url, __( 'Nouveautés', 'hasna-shoes' ) ),
		array( hasna_contact_url(), __( 'Contact', 'hasna-shoes' ) ),
	);
}

/**
 * A product_cat term's URL in the current language, by its French (default
 * language) slug — used wherever the theme has a fixed category shortcut
 * (footer links, homepage category tiles).
 */
function hasna_category_url( $fr_slug ) {
	/*
	 * get_term_by() is silently scoped to the *current* language by
	 * Polylang, but $fr_slug is always the default-language (French)
	 * slug — on the English/Arabic site this lookup found nothing and
	 * silently fell back to the homepage. 'lang' => 'fr' forces the
	 * lookup into the language the slug actually belongs to.
	 */
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'slug'       => $fr_slug,
		'lang'       => 'fr',
		'hide_empty' => false,
	) );
	$term = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0] : null;
	if ( ! $term ) {
		return home_url( '/' );
	}
	if ( function_exists( 'pll_get_term' ) ) {
		$translated_id = pll_get_term( $term->term_id );
		if ( $translated_id ) {
			$term = get_term( $translated_id, 'product_cat' );
		}
	}
	return get_term_link( $term );
}

/**
 * The Contact page's URL in the current language, falling back to the
 * French default if no translation exists yet or Polylang isn't active.
 */
function hasna_contact_url() {
	$contact = get_page_by_path( 'contact' );
	if ( ! $contact ) {
		return home_url( '/contact/' );
	}
	if ( function_exists( 'pll_get_post' ) ) {
		$translated_id = pll_get_post( $contact->ID );
		if ( $translated_id ) {
			$contact = get_post( $translated_id );
		}
	}
	return get_permalink( $contact );
}

/**
 * Default primary menu (used until the site owner sets one in
 * Appearance -> Menus; matches the approved design's layout exactly).
 */
function hasna_nav_menu_fallback() {
	$current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
	echo '<nav class="hs-nav" aria-label="' . esc_attr__( 'Navigation principale', 'hasna-shoes' ) . '">';
	foreach ( hasna_primary_nav_items() as $item ) {
		list( $href, $label ) = $item;
		$is_current = is_front_page() ? ( __( 'Accueil', 'hasna-shoes' ) === $label ) : ( untrailingslashit( $href ) === untrailingslashit( $current_url ) );
		printf( '<a href="%s" class="%s">%s</a>', esc_url( $href ), $is_current ? 'is-current' : '', esc_html( $label ) );
	}
	echo '</nav>';
}

/**
 * Assets.
 */
function hasna_enqueue_assets() {
	wp_enqueue_style( 'hasna-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&family=Jost:wght@300;400;500;600&display=swap', array(), null );
	wp_enqueue_style( 'hasna-main', HASNA_THEME_URI . '/assets/css/main.css', array(), HASNA_THEME_VERSION );

	if ( is_rtl() ) {
		wp_enqueue_style( 'hasna-rtl', HASNA_THEME_URI . '/assets/css/rtl.css', array( 'hasna-main' ), HASNA_THEME_VERSION );
	}

	wp_enqueue_script( 'gsap', HASNA_THEME_URI . '/assets/vendor/gsap/gsap.min.js', array(), '3.12.5', true );
	wp_enqueue_script( 'gsap-scrolltrigger', HASNA_THEME_URI . '/assets/vendor/gsap/ScrollTrigger.min.js', array( 'gsap' ), '3.12.5', true );

	wp_enqueue_script( 'hasna-main', HASNA_THEME_URI . '/assets/js/main.js', array(), HASNA_THEME_VERSION, true );

	if ( is_front_page() ) {
		wp_enqueue_script( 'hasna-hero', HASNA_THEME_URI . '/assets/js/hero.js', array( 'gsap', 'gsap-scrolltrigger' ), HASNA_THEME_VERSION, true );
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_script( 'hasna-shop', HASNA_THEME_URI . '/assets/js/shop.js', array( 'jquery', 'wc-add-to-cart-variation' ), HASNA_THEME_VERSION, true );
	}

	wp_localize_script( 'hasna-main', 'hasnaSettings', array(
		'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
		'nonce'           => wp_create_nonce( 'hasna_ajax' ),
		'reducedMotion'   => false,
		'cartUrl'         => wc_get_cart_url(),
	) );
}
add_action( 'wp_enqueue_scripts', 'hasna_enqueue_assets' );

/**
 * Trim WP core assets we don't use (classic theme, no block editor styles on the front end).
 */
function hasna_dequeue_block_assets() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'hasna_dequeue_block_assets', 20 );

/**
 * WooCommerce: guest checkout, COD-only, admin-manageable content, hooks.
 */
require HASNA_THEME_DIR . '/inc/security.php';
require HASNA_THEME_DIR . '/inc/woocommerce-setup.php';
require HASNA_THEME_DIR . '/inc/woocommerce-hooks.php';
require HASNA_THEME_DIR . '/inc/template-tags.php';
require HASNA_THEME_DIR . '/inc/newsletter.php';
require HASNA_THEME_DIR . '/inc/single-product.php';
require HASNA_THEME_DIR . '/inc/contact.php';
require HASNA_THEME_DIR . '/inc/admin-dashboard.php';
require HASNA_THEME_DIR . '/inc/admin-settings.php';
require HASNA_THEME_DIR . '/inc/marketing.php';
require HASNA_THEME_DIR . '/inc/polylang-woocommerce.php';
