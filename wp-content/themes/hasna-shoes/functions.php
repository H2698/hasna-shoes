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

	load_theme_textdomain( 'hasna-shoes', HASNA_THEME_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'hasna_setup' );

/**
 * Real destinations for the primary nav (used by both the desktop fallback
 * nav and the mobile nav, until the site owner sets a menu in Appearance ->
 * Menus). "Femme" and "Collections" both point to the shop — same as the
 * approved design, which linked both to the homepage's single categories
 * section; there's no separate "collections" taxonomy to send them to.
 */
function hasna_primary_nav_items() {
	$shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$new_term  = get_term_by( 'slug', 'nouveautes', 'product_cat' );
	$new_url   = ( $new_term && ! is_wp_error( $new_term ) ) ? get_term_link( $new_term ) : $shop_url;

	return array(
		array( home_url( '/' ), __( 'Accueil', 'hasna-shoes' ) ),
		array( $shop_url, __( 'Femme', 'hasna-shoes' ) ),
		array( $shop_url, __( 'Collections', 'hasna-shoes' ) ),
		array( $new_url, __( 'Nouveautés', 'hasna-shoes' ) ),
		array( home_url( '/contact/' ), __( 'Contact', 'hasna-shoes' ) ),
	);
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
require HASNA_THEME_DIR . '/inc/woocommerce-setup.php';
require HASNA_THEME_DIR . '/inc/woocommerce-hooks.php';
require HASNA_THEME_DIR . '/inc/template-tags.php';
require HASNA_THEME_DIR . '/inc/newsletter.php';
require HASNA_THEME_DIR . '/inc/single-product.php';
require HASNA_THEME_DIR . '/inc/contact.php';
require HASNA_THEME_DIR . '/inc/admin-dashboard.php';
require HASNA_THEME_DIR . '/inc/admin-settings.php';
require HASNA_THEME_DIR . '/inc/marketing.php';
