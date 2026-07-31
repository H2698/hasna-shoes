<?php
/**
 * Security hardening (Agent 6). See security-audit.md for the full write-up
 * of what was audited, what's implemented here, and what's deliberately
 * deferred to deployment (SSL, file permissions, WAF/managed hosting
 * protections that don't apply to this local dev environment).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared by the login-attempt limiter, AJAX rate limiter, and (in
 * marketing.php) the Conversions API client_ip_address field.
 */
function hasna_get_client_ip() {
	foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) )[0];
			return trim( $ip );
		}
	}
	return '';
}

/**
 * ---------------------------------------------------------------------
 * 1. Login hardening / brute-force protection.
 * ---------------------------------------------------------------------
 */

/**
 * Rate-limit login attempts per IP+username: 5 failures locks that pair out
 * for 15 minutes. Deliberately not IP-only (a shared office/NAT IP shouldn't
 * lock out every employee because one account is being attacked) and not
 * username-only (an attacker shouldn't be able to lock a real customer/admin
 * out by deliberately failing their login from elsewhere).
 */
function hasna_login_lock_key( $username ) {
	return 'hasna_login_lock_' . md5( hasna_get_client_ip() . '|' . strtolower( $username ) );
}

add_filter( 'authenticate', function ( $user, $username, $password ) {
	if ( empty( $username ) ) {
		return $user;
	}
	$attempts = (int) get_transient( hasna_login_lock_key( $username ) );
	if ( $attempts >= 5 ) {
		return new WP_Error( 'hasna_locked_out', __( '<strong>Erreur :</strong> trop de tentatives de connexion échouées. Réessayez dans 15 minutes.', 'hasna-shoes' ) );
	}
	return $user;
}, 30, 3 );

add_action( 'wp_login_failed', function ( $username ) {
	$key = hasna_login_lock_key( $username );
	$attempts = (int) get_transient( $key );
	set_transient( $key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
} );

add_action( 'wp_login', function ( $username ) {
	delete_transient( hasna_login_lock_key( $username ) );
}, 10, 1 );

/**
 * Don't reveal whether a username/email exists via the login-error message
 * (WordPress's default "Invalid username" vs "The password you entered ...
 * is incorrect" lets an attacker enumerate valid accounts one guess at a
 * time).
 */
add_filter( 'login_errors', function ( $error ) {
	return __( 'Identifiants incorrects.', 'hasna-shoes' );
} );

/**
 * Username enumeration via ?author=1, ?author=2, ... redirecting to
 * /author/username/ (a common recon step before a brute-force attempt).
 */
add_action( 'template_redirect', function () {
	if ( is_admin() ) {
		return;
	}
	if ( isset( $_GET['author'] ) && preg_match( '/^\d+$/', wp_unslash( $_GET['author'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect guard.
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );
/**
 * Unauthenticated REST user listing is a direct enumeration vector.
 * Blocking at rest_pre_dispatch (route-name check) rather than mutating
 * the rest_endpoints array — the latter's internal shape isn't a stable
 * public API to rely on.
 */
add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( ! is_user_logged_in() && 0 === strpos( $request->get_route(), '/wp/v2/users' ) ) {
		return new WP_Error( 'rest_forbidden', __( 'Non autorisé.', 'hasna-shoes' ), array( 'status' => 401 ) );
	}
	return $result;
}, 10, 3 );

/**
 * XML-RPC is a well-known brute-force/amplification vector (system.multicall
 * lets an attacker test hundreds of password guesses in a single request)
 * and this site has no use for it — no mobile app, no remote publishing.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rsd_link' ); // Really Simple Discovery — points at xmlrpc.php.

/**
 * Hide exact WordPress version from anonymous visitors (helps an attacker
 * match known CVEs to this install).
 */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/**
 * ---------------------------------------------------------------------
 * 2. Security headers.
 * ---------------------------------------------------------------------
 * A strict Content-Security-Policy is deliberately NOT included here: this
 * theme relies on several inline <script> blocks (marketing pixel events,
 * GSAP hero config, admin media picker) and a locked-down CSP would need
 * per-script nonces threaded through every one of them — worth doing, but
 * as a dedicated pass with full regression testing (M7), not bolted on
 * blind here where a mistake would silently break checkout/tracking.
 * Documented as a follow-up in security-audit.md.
 */
add_action( 'send_headers', function () {
	if ( is_admin() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
} );

/**
 * ---------------------------------------------------------------------
 * 3. REST API: keep it available (WooCommerce Store API, block editor,
 * and media uploads all depend on it) but remove the "list every route"
 * discovery response for anonymous requests, and require authentication
 * for anything touching customer/order data.
 * ---------------------------------------------------------------------
 */
add_filter( 'rest_index', function ( $response ) {
	if ( is_user_logged_in() ) {
		return $response;
	}
	$data = $response->get_data();
	unset( $data['routes'] );
	$response->set_data( $data );
	return $response;
} );

/**
 * ---------------------------------------------------------------------
 * 4. Basic abuse protection on our own AJAX endpoints (newsletter/contact),
 * on top of the nonce check they already have — a valid nonce only proves
 * the request came from our own page, not that it isn't a script hammering
 * the endpoint from an open browser tab.
 * ---------------------------------------------------------------------
 */
function hasna_ajax_rate_limit( $action, $limit = 5, $window = MINUTE_IN_SECONDS ) {
	$key   = 'hasna_rl_' . $action . '_' . md5( hasna_get_client_ip() );
	$count = (int) get_transient( $key );
	if ( $count >= $limit ) {
		wp_send_json_error( array( 'message' => __( 'Trop de tentatives, merci de patienter un instant.', 'hasna-shoes' ) ), 429 );
	}
	set_transient( $key, $count + 1, $window );
}
add_action( 'wp_ajax_nopriv_hasna_newsletter_signup', function () { hasna_ajax_rate_limit( 'newsletter' ); }, 5 );
add_action( 'wp_ajax_nopriv_hasna_contact_submit', function () { hasna_ajax_rate_limit( 'contact' ); }, 5 );

/**
 * ---------------------------------------------------------------------
 * 5. WooCommerce / customer data.
 * ---------------------------------------------------------------------
 */

// Orders/customer PII must never be exposed through the REST API to
// unauthenticated requests (WooCommerce already requires auth for its own
// namespaces, this is an explicit belt-and-braces confirmation).
add_filter( 'woocommerce_rest_check_permissions', function ( $permission, $context, $object_id, $post_type ) {
	return is_user_logged_in() ? $permission : false;
}, 10, 4 );

// Never let a search query reach into private order/customer notes.
add_filter( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set( 'post_type', array( 'product', 'page' ) );
	}
} );
