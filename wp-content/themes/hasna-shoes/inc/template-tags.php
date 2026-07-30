<?php
/**
 * Small reusable helpers shared by the front-page and archive templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homepage "Nos catégories" — the five product_cat terms to feature, with a
 * theme-bundled fallback image until real category thumbnails are set in
 * WooCommerce (Products -> Categories -> Thumbnail).
 *
 * @return array<int, array{term: WP_Term|null, label: string, image: string}>
 */
function hasna_homepage_categories() {
	$slots = array(
		'talons'      => __( 'Talons', 'hasna-shoes' ),
		'sandales'    => __( 'Sandales', 'hasna-shoes' ),
		'confort'     => __( 'Confort', 'hasna-shoes' ),
		'plates'      => __( 'Plates', 'hasna-shoes' ),
		'nouveautes'  => __( 'Nouveautés', 'hasna-shoes' ),
	);

	$out = array();
	foreach ( $slots as $slug => $label ) {
		$term  = get_term_by( 'slug', $slug, 'product_cat' );
		$image = HASNA_THEME_URI . '/assets/img/categories/' . $slug . '.webp';

		if ( $term && ! is_wp_error( $term ) ) {
			$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
			if ( $thumb_id ) {
				$src = wp_get_attachment_image_url( $thumb_id, 'medium' );
				if ( $src ) {
					$image = $src;
				}
			}
		}

		$out[] = array(
			'term'  => ( $term && ! is_wp_error( $term ) ) ? $term : null,
			'label' => $label,
			'image' => $image,
		);
	}

	return $out;
}

/**
 * Homepage "Nos produits phares" — up to 6 featured WooCommerce products.
 * Returns an empty array gracefully until the catalog exists (M2).
 *
 * @return WC_Product[]
 */
function hasna_featured_products( $limit = 6 ) {
	if ( ! class_exists( 'WC_Product_Query' ) ) {
		return array();
	}

	$query = new WC_Product_Query( array(
		'status'   => 'publish',
		'limit'    => $limit,
		'orderby'  => 'date',
		'order'    => 'DESC',
		'featured' => true,
	) );

	$products = $query->get_products();

	if ( empty( $products ) ) {
		// Fall back to the newest published products so the section is never empty
		// once *any* catalog exists, even before items are explicitly marked "featured".
		$query    = new WC_Product_Query( array(
			'status'  => 'publish',
			'limit'   => $limit,
			'orderby' => 'date',
			'order'   => 'DESC',
		) );
		$products = $query->get_products();
	}

	return $products;
}

/**
 * Inline outline icons matching the mockup's hand-drawn style (search / account / cart / arrows).
 * Kept as real inline SVG (crisp at any DPI, styleable via currentColor) rather than the
 * CSS-border tricks used in the original static mockup.
 */
function hasna_icon( $name, $args = array() ) {
	$class = isset( $args['class'] ) ? $args['class'] : '';
	$icons = array(
		'search'  => '<circle cx="9" cy="9" r="6.5"/><line x1="18" y1="18" x2="13.5" y2="13.5"/>',
		'account' => '<circle cx="10" cy="6.5" r="3.5"/><path d="M2.5 18c0-4 3.4-7 7.5-7s7.5 3 7.5 7"/>',
		'cart'    => '<path d="M2 3h2l2.1 11.2A2 2 0 0 0 8 16h7.4a2 2 0 0 0 2-1.6L19 6.5H5.2"/><circle cx="8.5" cy="19.5" r="1.4" fill="currentColor" stroke="none"/><circle cx="16" cy="19.5" r="1.4" fill="currentColor" stroke="none"/>',
		'chevron-left'  => '<polyline points="13,5 7,11 13,17"/>',
		'chevron-right' => '<polyline points="7,5 13,11 7,17"/>',
		'heart'         => '<path d="M11 18.5S2.5 13.4 2.5 7.6A4.6 4.6 0 0 1 11 5.3a4.6 4.6 0 0 1 8.5 2.3c0 5.8-8.5 10.9-8.5 10.9z"/>',
	);
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="hs-icon %s" viewBox="0 0 22 22" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
		esc_attr( $class ),
		$icons[ $name ]
	);
}
