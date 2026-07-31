<?php
/**
 * Free Polylang doesn't make WooCommerce's core page options
 * (woocommerce_shop_page_id, cart, checkout, myaccount) language-aware —
 * that's specifically what the paid "Polylang for WooCommerce" add-on
 * does. Without it, wc_get_page_id('shop') always returns the *default*
 * language's page regardless of which language is currently being
 * viewed, so the Shop archive title/breadcrumb (and any wc_get_page_id()
 * consumer) would silently stay French on the English/Arabic site even
 * though the product listing itself is correctly filtered by language
 * (Polylang's post-query filtering is a separate, already-working
 * mechanism). This filters the four page-id options to the current
 * language's translation, if one exists.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'option_woocommerce_shop_page_id', 'hasna_pll_translate_wc_page_option' );
add_filter( 'option_woocommerce_cart_page_id', 'hasna_pll_translate_wc_page_option' );
add_filter( 'option_woocommerce_checkout_page_id', 'hasna_pll_translate_wc_page_option' );
add_filter( 'option_woocommerce_myaccount_page_id', 'hasna_pll_translate_wc_page_option' );
add_filter( 'option_wp_page_for_privacy_policy', 'hasna_pll_translate_wc_page_option' );

function hasna_pll_translate_wc_page_option( $page_id ) {
	if ( ! $page_id || ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_current_language' ) ) {
		return $page_id;
	}
	$lang = pll_current_language();
	if ( ! $lang ) {
		return $page_id;
	}
	$translated = pll_get_post( $page_id, $lang );
	return $translated ? $translated : $page_id;
}

/**
 * Product attribute labels (pa_taille -> "Taille", pa_couleur -> "Couleur")
 * are a single global value in WooCommerce's attribute taxonomy table, not
 * per-language — without this they'd stay in French ("Taille:") in the
 * cart/checkout/product-page variation labels even on the English/Arabic
 * site, unlike everything else which is properly per-language.
 */
add_filter( 'woocommerce_attribute_label', function ( $label, $name ) {
	if ( ! function_exists( 'pll_current_language' ) ) {
		return $label;
	}
	$lang = pll_current_language();
	$map  = array(
		'pa_taille'  => array( 'en' => 'Size', 'ar' => 'المقاس' ),
		'pa_couleur' => array( 'en' => 'Color', 'ar' => 'اللون' ),
	);
	return $map[ $name ][ $lang ] ?? $label;
}, 10, 2 );

/**
 * The COD gateway's "title" (shown as the payment method name at
 * checkout) is a single raw string saved in the woocommerce_cod_settings
 * option by the merchant — it never passes through gettext, so unlike
 * everything else on the page it stays in French regardless of language.
 */
add_filter( 'woocommerce_gateway_title', function ( $title, $gateway_id ) {
	if ( 'cod' !== $gateway_id || ! function_exists( 'pll_current_language' ) ) {
		return $title;
	}
	$lang = pll_current_language();
	$map  = array(
		'en' => 'Cash on Delivery',
		'ar' => 'الدفع عند الاستلام',
	);
	return $map[ $lang ] ?? $title;
}, 10, 2 );

/**
 * woocommerce_checkout_privacy_policy_text / woocommerce_registration_privacy_policy_text
 * are raw option values WooCommerce ships in English by default — never
 * set to French, so this notice was showing English text even on the
 * French checkout, and (once multilingual was added) stayed English on
 * the Arabic checkout too instead of following the current language.
 * The [privacy_policy] placeholder stays intact; WooCommerce swaps it
 * for the linked Privacy Policy page (already made language-aware above).
 */
add_filter( 'option_woocommerce_checkout_privacy_policy_text', 'hasna_pll_privacy_policy_text' );
add_filter( 'option_woocommerce_registration_privacy_policy_text', 'hasna_pll_privacy_policy_text' );

function hasna_pll_privacy_policy_text( $text ) {
	if ( ! function_exists( 'pll_current_language' ) ) {
		return $text;
	}
	$lang = pll_current_language();
	$map  = array(
		'fr' => array(
			'checkout'     => 'Vos données personnelles seront utilisées pour traiter votre commande, améliorer votre expérience sur ce site, et à d’autres fins décrites dans notre [privacy_policy].',
			'registration' => 'Vos données personnelles seront utilisées pour améliorer votre expérience sur ce site, gérer l’accès à votre compte, et à d’autres fins décrites dans notre [privacy_policy].',
		),
		'ar' => array(
			'checkout'     => 'ستُستخدم بياناتك الشخصية لمعالجة طلبك، ودعم تجربتك عبر هذا الموقع، ولأغراض أخرى موضحة في [privacy_policy].',
			'registration' => 'ستُستخدم بياناتك الشخصية لدعم تجربتك عبر هذا الموقع، وإدارة الوصول إلى حسابك، ولأغراض أخرى موضحة في [privacy_policy].',
		),
	);
	if ( ! isset( $map[ $lang ] ) ) {
		return $text; // English stays as WooCommerce's own default.
	}
	$key = ( false !== strpos( current_filter(), 'registration' ) ) ? 'registration' : 'checkout';
	return $map[ $lang ][ $key ];
}

/**
 * WordPress only registers the `product` post type's archive rewrite
 * rule once, using the DEFAULT-language Shop page's slug (WooCommerce
 * sets `has_archive` from the French "shop" page at registration time).
 * So on the French URL, `/shop/` genuinely matches that archive rule and
 * the main query is a real `post_type=product` query. On the English/
 * Arabic translations ("shop-2", "shop-3" — auto-suffixed, see the free
 * Polylang slug limitation noted elsewhere), no such rewrite rule exists:
 * the URL just matches the ordinary WP Page rewrite, so the main query
 * resolves to a single Page post, not products. WooCommerce still picks
 * archive-product.php as the template (is_shop() only checks
 * is_page(wc_get_page_id('shop')), which passes), so the correct
 * template renders around a genuinely empty product loop — categories
 * and products both silently disappear on the translated Shop pages.
 * Fix: whenever the queried page is a translation of the default-language
 * Shop page, swap the main query to a real product archive ourselves,
 * matching what WooCommerce does natively for the French one. Runs at
 * priority 5 — WC_Query::pre_get_posts() itself hooks at the default
 * priority (10) and only calls its own product_query() setup (tax
 * query, ordering, per-page, the `wc_query` flag `wc_setup_loop()`
 * needs) when is_post_type_archive('product') is already true, so this
 * swap must land before WooCommerce's own check runs.
 */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! $q->is_main_query() || is_admin() || ! $q->is_page() ) {
		return;
	}
	if ( ! function_exists( 'pll_get_post_translations' ) ) {
		return;
	}
	global $wpdb;
	$default_shop_id = (int) $wpdb->get_var( "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'woocommerce_shop_page_id'" );
	$queried_id       = (int) $q->get( 'page_id' );
	if ( ! $queried_id && $q->get( 'pagename' ) ) {
		// `pagename` is resolved to `page_id` later in WP_Query::get_posts(),
		// well after `pre_get_posts` fires — resolve it ourselves here.
		$page = get_page_by_path( $q->get( 'pagename' ) );
		if ( $page ) {
			$queried_id = $page->ID;
		}
	}
	if ( ! $default_shop_id || ! $queried_id || $queried_id === $default_shop_id ) {
		return; // The default-language Shop page is already handled natively by WooCommerce.
	}
	$translations = pll_get_post_translations( $default_shop_id );
	if ( ! in_array( $queried_id, $translations, true ) ) {
		return;
	}
	$q->set( 'page_id', '' );
	$q->set( 'pagename', '' );
	$q->set( 'post_type', 'product' );
	$q->is_page              = false;
	$q->is_singular          = false;
	$q->is_post_type_archive = true;
	$q->is_archive           = true;
}, 5 );

/**
 * WC_AJAX::get_endpoint() builds the checkout's ajax_url (used for
 * update_order_review, apply_coupon, etc.) from the *default-language*
 * home_url(), never the current page's language-prefixed URL. Polylang
 * resolves language purely from the request URL, so every one of those
 * AJAX calls was silently treated as French — re-rendering the order
 * review table (and any other ajax-refreshed fragment) in French even on
 * the English/Arabic checkout page, while the rest of the
 * server-rendered page stayed correctly translated. Rebuilding the
 * endpoint from the current language's home URL fixes it at the source.
 */
add_filter( 'woocommerce_ajax_get_endpoint', function ( $endpoint, $request ) {
	if ( ! function_exists( 'pll_current_language' ) || ! function_exists( 'pll_home_url' ) ) {
		return $endpoint;
	}
	$lang = pll_current_language();
	if ( ! $lang ) {
		return $endpoint;
	}
	$lang_home = remove_query_arg( array( 'remove_item', 'add-to-cart', 'removed_item' ), pll_home_url( $lang ) );
	return add_query_arg( 'wc-ajax', $request, untrailingslashit( $lang_home ) . '/' );
}, 10, 2 );
