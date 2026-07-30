<?php
/**
 * Baseline WooCommerce integration: template hooks, gallery config, layout wrapper removal
 * (our page templates provide their own header/footer/wrapper markup).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

function hasna_wc_wrapper_start() {
	echo '<main id="primary" class="hs-shop">';
}
add_action( 'woocommerce_before_main_content', 'hasna_wc_wrapper_start', 10 );

function hasna_wc_wrapper_end() {
	echo '</main>';
}
add_action( 'woocommerce_after_main_content', 'hasna_wc_wrapper_end', 10 );

// Products per row / per page for the shop archive (final visual pass happens in M3).
add_filter( 'loop_shop_columns', fn() => 4 );
add_filter( 'loop_shop_per_page', fn() => 12 );

/**
 * Register the size/color attributes used by the catalog (created in M2 if missing),
 * so the store is usable even before the M2 milestone imports real products.
 */
function hasna_register_product_attributes() {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return;
	}

	$needed = array(
		'taille'  => __( 'Taille', 'hasna-shoes' ),
		'couleur' => __( 'Couleur', 'hasna-shoes' ),
	);

	$existing = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_name' );

	foreach ( $needed as $slug => $label ) {
		if ( in_array( $slug, $existing, true ) ) {
			continue;
		}
		wc_create_attribute( array(
			'name'         => $label,
			'slug'         => $slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
	}
}
add_action( 'init', 'hasna_register_product_attributes' );
