<?php
/**
 * Meta Pixel + Conversions API, GA4, and GTM — all inert until the site
 * owner fills in the corresponding field on the Réglages page (no fake IDs,
 * nothing fires against a placeholder pixel). Events: PageView (base code,
 * automatic), ViewContent, Search, AddToCart, InitiateCheckout, Purchase.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ---------------------------------------------------------------------
 * Base codes (head).
 * ---------------------------------------------------------------------
 */
add_action( 'wp_head', 'hasna_meta_pixel_base', 1 );
function hasna_meta_pixel_base() {
	$pixel_id = get_option( 'hasna_meta_pixel_id' );
	if ( ! $pixel_id ) {
		return;
	}
	?>
	<script>
	!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
	n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
	t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
	document,'script','https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '<?php echo esc_js( $pixel_id ); ?>');
	fbq('track', 'PageView');
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( $pixel_id ); ?>&ev=PageView&noscript=1" alt="" /></noscript>
	<?php
}

add_action( 'wp_head', 'hasna_ga4_base', 1 );
function hasna_ga4_base() {
	$ga4_id = get_option( 'hasna_ga4_id' );
	if ( ! $ga4_id ) {
		return;
	}
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4_id ); ?>"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', '<?php echo esc_js( $ga4_id ); ?>');
	</script>
	<?php
}

add_action( 'wp_head', 'hasna_gtm_head', 2 );
function hasna_gtm_head() {
	$gtm_id = get_option( 'hasna_gtm_id' );
	if ( ! $gtm_id ) {
		return;
	}
	?>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');</script>
	<?php
}
add_action( 'wp_body_open', 'hasna_gtm_body' );
function hasna_gtm_body() {
	$gtm_id = get_option( 'hasna_gtm_id' );
	if ( ! $gtm_id ) {
		return;
	}
	?>
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm_id ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<?php
}

/**
 * ---------------------------------------------------------------------
 * Helpers.
 * ---------------------------------------------------------------------
 */
function hasna_marketing_enabled() {
	return (bool) ( get_option( 'hasna_meta_pixel_id' ) || get_option( 'hasna_ga4_id' ) );
}

function hasna_js_event( $fbq_args, $gtag_args = null ) {
	$out = '<script>';
	if ( get_option( 'hasna_meta_pixel_id' ) && $fbq_args ) {
		$out .= 'if(window.fbq){fbq(' . implode( ',', array_map( 'wp_json_encode', $fbq_args ) ) . ');}';
	}
	if ( get_option( 'hasna_ga4_id' ) && $gtag_args ) {
		$out .= 'if(window.gtag){gtag(' . implode( ',', array_map( 'wp_json_encode', $gtag_args ) ) . ');}';
	}
	$out .= '</script>';
	echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all args passed through wp_json_encode above.
}

/**
 * Meta Conversions API — server-side event, deduplicated against the
 * browser Pixel event via a shared event_id. No-op without a token.
 */
function hasna_capi_send( $event_name, $event_id, $custom_data = array(), $user_data_extra = array() ) {
	$pixel_id = get_option( 'hasna_meta_pixel_id' );
	$token    = get_option( 'hasna_meta_capi_token' );
	if ( ! $pixel_id || ! $token ) {
		return;
	}

	$user_data = array_merge( array(
		'client_ip_address' => hasna_get_client_ip(),
		'client_user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
	), $user_data_extra );

	if ( isset( $_COOKIE['_fbp'] ) ) {
		$user_data['fbp'] = sanitize_text_field( wp_unslash( $_COOKIE['_fbp'] ) );
	}
	if ( isset( $_COOKIE['_fbc'] ) ) {
		$user_data['fbc'] = sanitize_text_field( wp_unslash( $_COOKIE['_fbc'] ) );
	}

	$body = array(
		'data' => array(
			array(
				'event_name'       => $event_name,
				'event_time'       => time(),
				'event_id'         => $event_id,
				'event_source_url' => home_url( add_query_arg( null, null ) ),
				'action_source'    => 'website',
				'user_data'        => $user_data,
				'custom_data'      => $custom_data,
			),
		),
	);

	wp_remote_post( "https://graph.facebook.com/v19.0/{$pixel_id}/events?access_token=" . rawurlencode( $token ), array(
		'timeout'  => 5,
		'blocking' => false,
		'body'     => wp_json_encode( $body ),
		'headers'  => array( 'Content-Type' => 'application/json' ),
	) );
}

function hasna_hash( $value ) {
	return $value ? hash( 'sha256', strtolower( trim( $value ) ) ) : null;
}

/**
 * ---------------------------------------------------------------------
 * ViewContent — single product page.
 * ---------------------------------------------------------------------
 */
add_action( 'woocommerce_after_single_product', function () {
	if ( ! hasna_marketing_enabled() ) {
		return;
	}
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$event_id = 'vc_' . $product->get_id() . '_' . substr( (string) time(), -4 );
	hasna_js_event(
		array( 'track', 'ViewContent', array(
			'content_ids'  => array( (string) $product->get_id() ),
			'content_name' => $product->get_name(),
			'content_type' => 'product',
			'value'        => (float) $product->get_price(),
			'currency'     => get_woocommerce_currency(),
		), array( 'eventID' => $event_id ) ),
		array( 'event', 'view_item', array(
			'currency' => get_woocommerce_currency(),
			'value'    => (float) $product->get_price(),
			'items'    => array( array( 'item_id' => (string) $product->get_id(), 'item_name' => $product->get_name(), 'price' => (float) $product->get_price() ) ),
		) )
	);
	hasna_capi_send( 'ViewContent', $event_id, array(
		'content_ids'  => array( (string) $product->get_id() ),
		'content_name' => $product->get_name(),
		'content_type' => 'product',
		'value'        => (float) $product->get_price(),
		'currency'     => get_woocommerce_currency(),
	) );
} );

/**
 * ---------------------------------------------------------------------
 * Search.
 * ---------------------------------------------------------------------
 */
add_action( 'wp_footer', function () {
	if ( ! hasna_marketing_enabled() || ! is_search() ) {
		return;
	}
	$term = get_search_query();
	if ( ! $term ) {
		return;
	}
	hasna_js_event(
		array( 'track', 'Search', array( 'search_string' => $term ) ),
		array( 'event', 'search', array( 'search_term' => $term ) )
	);
} );

/**
 * ---------------------------------------------------------------------
 * AddToCart.
 * ---------------------------------------------------------------------
 * Server-side (CAPI): hooks WooCommerce's own add-to-cart action, which
 * fires identically for AJAX and non-AJAX adds — fully reliable regardless
 * of how the product was added.
 * Client-side (Pixel/GA4): our real add-to-cart flow is the single-product
 * variation form, a classic (non-AJAX) submission that redirects back with
 * ?added-to-cart=<id> — detected on that page load. The generic
 * 'added_to_cart' jQuery listener is also wired for when/if simple, directly
 * AJAX-addable products are ever added to the catalog.
 */
/**
 * Deterministic event_id shared between the CAPI call (fired synchronously
 * during the add-to-cart request) and the browser Pixel call (fired on the
 * *next* request, after WooCommerce's non-AJAX redirect) — the two calls
 * can't share PHP state since they're different HTTP requests, so the ID is
 * derived from data both sides have. WooCommerce's redirect only carries the
 * *parent* product ID (`?added-to-cart=`), not the variation, so the key is
 * scoped to product+session only (no timestamp), trading variation-level
 * precision for a guaranteed match between the two sides.
 */
function hasna_add_to_cart_event_id( $product_id ) {
	$session = ( function_exists( 'WC' ) && WC()->session ) ? WC()->session->get_customer_id() : 'anon';
	return 'atc_' . $product_id . '_' . $session;
}

add_action( 'woocommerce_add_to_cart', function ( $cart_item_key, $product_id, $quantity, $variation_id ) {
	if ( ! get_option( 'hasna_meta_capi_token' ) ) {
		return;
	}
	$id      = $variation_id ?: $product_id;
	$product = wc_get_product( $id );
	if ( ! $product ) {
		return;
	}
	hasna_capi_send( 'AddToCart', hasna_add_to_cart_event_id( $product_id ), array(
		'content_ids'  => array( (string) $id ),
		'content_name' => $product->get_name(),
		'content_type' => 'product',
		'value'        => (float) $product->get_price() * $quantity,
		'currency'     => get_woocommerce_currency(),
	) );
}, 10, 4 );

add_action( 'wp_footer', function () {
	if ( ! hasna_marketing_enabled() || is_admin() ) {
		return;
	}
	$added_id = isset( $_GET['added-to-cart'] ) ? absint( $_GET['added-to-cart'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display trigger, no state change.
	if ( $added_id ) {
		$product = wc_get_product( $added_id );
		if ( $product ) {
			$qty      = isset( $_GET['quantity'] ) ? absint( $_GET['quantity'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$event_id = hasna_add_to_cart_event_id( $added_id );
			hasna_js_event(
				array( 'track', 'AddToCart', array(
					'content_ids'  => array( (string) $added_id ),
					'content_name' => $product->get_name(),
					'content_type' => 'product',
					'value'        => (float) $product->get_price() * max( 1, $qty ),
					'currency'     => get_woocommerce_currency(),
				), array( 'eventID' => $event_id ) ),
				array( 'event', 'add_to_cart', array(
					'currency' => get_woocommerce_currency(),
					'value'    => (float) $product->get_price() * max( 1, $qty ),
					'items'    => array( array( 'item_id' => (string) $added_id, 'item_name' => $product->get_name() ) ),
				) )
			);
		}
	}
	?>
	<script>
	(function () {
		if (typeof jQuery === 'undefined') return;
		jQuery(document.body).on('added_to_cart', function (e, fragments, cartHash, button) {
			if (!button || !button.length) return;
			var id = button.data('product_id');
			var name = button.data('product_name') || '';
			if (!id) return;
			if (window.fbq) fbq('track', 'AddToCart', { content_ids: [String(id)], content_type: 'product', content_name: name });
			if (window.gtag) gtag('event', 'add_to_cart', { items: [{ item_id: String(id), item_name: name }] });
		});
	})();
	</script>
	<?php
}, 30 );

/**
 * ---------------------------------------------------------------------
 * InitiateCheckout.
 * ---------------------------------------------------------------------
 */
add_action( 'woocommerce_before_checkout_form', function () {
	if ( ! hasna_marketing_enabled() || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$cart = WC()->cart;
	if ( $cart->is_empty() ) {
		return;
	}
	$event_id = 'ic_' . WC()->session->get_customer_id() . '_' . substr( (string) time(), -5 );
	$ids      = array();
	foreach ( $cart->get_cart() as $item ) {
		$ids[] = (string) ( $item['variation_id'] ?: $item['product_id'] );
	}
	hasna_js_event(
		array( 'track', 'InitiateCheckout', array(
			'content_ids' => $ids,
			'value'       => (float) $cart->get_total( 'edit' ),
			'currency'    => get_woocommerce_currency(),
			'num_items'   => $cart->get_cart_contents_count(),
		), array( 'eventID' => $event_id ) ),
		array( 'event', 'begin_checkout', array(
			'currency' => get_woocommerce_currency(),
			'value'    => (float) $cart->get_total( 'edit' ),
		) )
	);
	hasna_capi_send( 'InitiateCheckout', $event_id, array(
		'content_ids' => $ids,
		'value'       => (float) $cart->get_total( 'edit' ),
		'currency'    => get_woocommerce_currency(),
		'num_items'   => $cart->get_cart_contents_count(),
	) );
} );

/**
 * ---------------------------------------------------------------------
 * Purchase — fired once per order (guarded by order meta), on the
 * thank-you/order-received page.
 * ---------------------------------------------------------------------
 */
add_action( 'woocommerce_thankyou', function ( $order_id ) {
	if ( ! hasna_marketing_enabled() || ! $order_id ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_hasna_purchase_tracked' ) ) {
		return;
	}

	$ids = array();
	foreach ( $order->get_items() as $item ) {
		$ids[] = (string) ( $item->get_variation_id() ?: $item->get_product_id() );
	}
	$event_id = 'purchase_' . $order_id;
	$value    = (float) $order->get_total();
	$currency = $order->get_currency();

	hasna_js_event(
		array( 'track', 'Purchase', array(
			'content_ids' => $ids,
			'value'       => $value,
			'currency'    => $currency,
			'num_items'   => count( $order->get_items() ),
		), array( 'eventID' => $event_id ) ),
		array( 'event', 'purchase', array(
			'transaction_id' => $order->get_order_number(),
			'currency'       => $currency,
			'value'          => $value,
		) )
	);

	hasna_capi_send( 'Purchase', $event_id, array(
		'content_ids' => $ids,
		'value'       => $value,
		'currency'    => $currency,
		'num_items'   => count( $order->get_items() ),
	), array(
		'em' => array( hasna_hash( $order->get_billing_email() ) ),
		'ph' => array( hasna_hash( preg_replace( '/\D/', '', $order->get_billing_phone() ) ) ),
	) );

	$order->update_meta_data( '_hasna_purchase_tracked', 1 );
	$order->save();
}, 20 );
