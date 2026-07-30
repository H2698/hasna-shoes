<?php
/**
 * Custom branded admin dashboard (pixel source: Hasna Shoes Admin.dc.html).
 *
 * Deliberately does NOT replace wp-admin's own left-hand menu/admin bar —
 * those already provide secure, well-tested navigation to every screen this
 * dashboard links out to (product/order editors, categories, coupons).
 * Rebuilding that chrome from the mockup's custom sidebar would duplicate
 * and risk breaking core WP navigation other plugins rely on. Instead this
 * page occupies the normal wp-admin content area and focuses on what the
 * mockup actually adds value with: live stats, recent orders/products, a
 * real sales chart, and a real activity feed — all from live data, not the
 * mockup's animated placeholder counters.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'hasna_register_dashboard_page' );
function hasna_register_dashboard_page() {
	add_menu_page(
		__( 'Tableau de bord Hasna', 'hasna-shoes' ),
		__( 'Hasna Shoes', 'hasna-shoes' ),
		'edit_shop_orders',
		'hasna-dashboard',
		'hasna_render_dashboard_page',
		'dashicons-store',
		3
	);
}

add_action( 'admin_enqueue_scripts', 'hasna_admin_assets' );
function hasna_admin_assets( $hook ) {
	if ( 'toplevel_page_hasna-dashboard' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'hasna-admin-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'hasna-admin', HASNA_THEME_URI . '/assets/css/admin.css', array(), HASNA_THEME_VERSION );
}

/**
 * Redirect shop-manager/admin users straight to this dashboard after login
 * (the mockup is meant to *be* their landing screen, not an extra menu item
 * they have to find).
 */
add_filter( 'login_redirect', function ( $redirect_to, $requested_redirect_to, $user ) {
	$default_landing = empty( $requested_redirect_to ) || trailingslashit( $requested_redirect_to ) === trailingslashit( admin_url() );
	if ( isset( $user->roles ) && is_array( $user->roles ) && $default_landing && ( in_array( 'administrator', $user->roles, true ) || in_array( 'shop_manager', $user->roles, true ) ) ) {
		return admin_url( 'admin.php?page=hasna-dashboard' );
	}
	return $redirect_to;
}, 10, 3 );

function hasna_status_pill( $label, $tone = 'default' ) {
	$tones = array(
		'new'     => array( '#EAF0FB', '#4A63B0' ),
		'prep'    => array( '#FBF0E1', '#B9762A' ),
		'done'    => array( '#EDF6EC', '#4F8A4B' ),
		'cancel'  => array( '#FBEAEA', '#B94F4F' ),
		'default' => array( '#F2F2F2', '#777777' ),
	);
	list( $bg, $color ) = $tones[ $tone ] ?? $tones['default'];
	return sprintf( '<span class="hs-pill" style="background:%s;color:%s;">%s</span>', esc_attr( $bg ), esc_attr( $color ), esc_html( $label ) );
}

/**
 * A small French "il y a…" formatter — human_time_diff()'s output isn't
 * translated unless a French language pack is installed, which would
 * otherwise mix "3 hours" with French copy everywhere else on this screen.
 */
function hasna_time_ago_fr( $timestamp ) {
	$diff = max( 0, current_time( 'timestamp' ) - $timestamp );
	if ( $diff < MINUTE_IN_SECONDS ) {
		return __( "à l'instant", 'hasna-shoes' );
	}
	$units = array(
		array( YEAR_IN_SECONDS, 'an', 'ans' ),
		array( MONTH_IN_SECONDS, 'mois', 'mois' ),
		array( WEEK_IN_SECONDS, 'semaine', 'semaines' ),
		array( DAY_IN_SECONDS, 'jour', 'jours' ),
		array( HOUR_IN_SECONDS, 'heure', 'heures' ),
		array( MINUTE_IN_SECONDS, 'minute', 'minutes' ),
	);
	foreach ( $units as [ $seconds, $singular, $plural ] ) {
		if ( $diff >= $seconds ) {
			$n = (int) floor( $diff / $seconds );
			return sprintf( __( 'Il y a %1$d %2$s', 'hasna-shoes' ), $n, 1 === $n ? $singular : $plural );
		}
	}
	return __( "à l'instant", 'hasna-shoes' );
}

function hasna_order_status_tone( $status ) {
	return array(
		'pending'    => 'new',
		'processing' => 'prep',
		'on-hold'    => 'prep',
		'completed'  => 'done',
		'cancelled'  => 'cancel',
		'refunded'   => 'cancel',
		'failed'     => 'cancel',
	)[ $status ] ?? 'default';
}

function hasna_render_dashboard_page() {
	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		wp_die( esc_html__( 'Accès non autorisé.', 'hasna-shoes' ) );
	}

	// ---- Live stats -----------------------------------------------------
	// wc_orders_count() works regardless of storage backend (HPOS or legacy posts table).
	$total_orders = 0;
	foreach ( array_keys( wc_get_order_statuses() ) as $status ) {
		$total_orders += wc_orders_count( str_replace( 'wc-', '', $status ) );
	}
	$pending_orders   = wc_orders_count( 'processing' ) + wc_orders_count( 'on-hold' ) + wc_orders_count( 'pending' );
	$product_counts   = wp_count_posts( 'product' );
	$total_products   = (int) ( $product_counts->publish ?? 0 );

	$revenue_orders = wc_get_orders( array(
		'status' => array( 'processing', 'completed' ),
		'limit'  => -1,
		'return' => 'objects',
	) );
	$total_revenue = array_reduce( $revenue_orders, fn( $sum, $o ) => $sum + (float) $o->get_total(), 0 );

	// ---- Recent orders ----------------------------------------------------
	$recent_orders = wc_get_orders( array( 'limit' => 6, 'orderby' => 'date', 'order' => 'DESC' ) );

	// ---- Recent products ----------------------------------------------------
	$recent_products = wc_get_products( array( 'limit' => 4, 'orderby' => 'date', 'order' => 'DESC', 'status' => 'publish' ) );

	// ---- 30-day sales chart (real order totals per day) ----------------
	$days = array();
	for ( $i = 29; $i >= 0; $i-- ) {
		$days[ gmdate( 'Y-m-d', strtotime( "-{$i} days" ) ) ] = 0.0;
	}
	$chart_orders = wc_get_orders( array(
		'status'       => array( 'processing', 'completed' ),
		'limit'        => -1,
		'date_created' => '>=' . ( time() - 30 * DAY_IN_SECONDS ),
		'return'       => 'objects',
	) );
	foreach ( $chart_orders as $o ) {
		$day = $o->get_date_created() ? $o->get_date_created()->date( 'Y-m-d' ) : null;
		if ( $day && isset( $days[ $day ] ) ) {
			$days[ $day ] += (float) $o->get_total();
		}
	}
	$values = array_values( $days );
	$max_val = max( 1, max( $values ) );
	$w = 640;
	$h = 200;
	$step = $w / max( 1, count( $values ) - 1 );
	$points = array();
	foreach ( $values as $i => $v ) {
		$points[] = array( 'x' => round( $i * $step ), 'y' => round( $h - ( $v / $max_val ) * ( $h - 20 ) - 10 ) );
	}
	$chart_path = 'M' . implode( ' L', array_map( fn( $p ) => $p['x'] . ',' . $p['y'], $points ) );
	$chart_area = $chart_path . " L {$w},{$h} L 0,{$h} Z";

	// ---- Activity feed: merge recent orders, products, messages ----------
	$activity = array();
	foreach ( wc_get_orders( array( 'limit' => 3, 'orderby' => 'date', 'order' => 'DESC' ) ) as $o ) {
		$activity[] = array(
			'icon'  => 'cart',
			'title' => sprintf( __( 'Nouvelle commande #%s', 'hasna-shoes' ), $o->get_order_number() ),
			'sub'   => sprintf( __( 'par %s', 'hasna-shoes' ), trim( $o->get_billing_first_name() . ' ' . $o->get_billing_last_name() ) ?: __( 'Client', 'hasna-shoes' ) ),
			'time'  => $o->get_date_created(),
		);
	}
	foreach ( get_posts( array( 'post_type' => 'product', 'posts_per_page' => 2, 'orderby' => 'date', 'order' => 'DESC' ) ) as $p ) {
		$activity[] = array(
			'icon'  => 'box',
			'title' => __( 'Produit publié', 'hasna-shoes' ),
			'sub'   => get_the_title( $p ),
			'time'  => get_the_date( 'Y-m-d H:i:s', $p ),
		);
	}
	foreach ( get_posts( array( 'post_type' => 'hs_message', 'posts_per_page' => 2, 'orderby' => 'date', 'order' => 'DESC' ) ) as $m ) {
		$activity[] = array(
			'icon'  => 'message',
			'title' => __( 'Nouveau message reçu', 'hasna-shoes' ),
			'sub'   => sprintf( __( 'de %s', 'hasna-shoes' ), get_the_title( $m ) ),
			'time'  => get_the_date( 'Y-m-d H:i:s', $m ),
		);
	}
	foreach ( get_posts( array( 'post_type' => 'hs_newsletter', 'posts_per_page' => 2, 'orderby' => 'date', 'order' => 'DESC' ) ) as $n ) {
		$activity[] = array(
			'icon'  => 'users',
			'title' => __( 'Nouvel abonné newsletter', 'hasna-shoes' ),
			'sub'   => get_the_title( $n ),
			'time'  => get_the_date( 'Y-m-d H:i:s', $n ),
		);
	}
	usort( $activity, fn( $a, $b ) => strtotime( is_object( $a['time'] ) ? $a['time']->date( 'Y-m-d H:i:s' ) : $a['time'] ) <=> strtotime( is_object( $b['time'] ) ? $b['time']->date( 'Y-m-d H:i:s' ) : $b['time'] ) );
	$activity = array_reverse( $activity );
	$activity = array_slice( $activity, 0, 6 );

	$unread_messages = hasna_unread_messages_count();

	include HASNA_THEME_DIR . '/inc/views/admin-dashboard-view.php';
}
