<?php
/**
 * Loop item — delegates to the same card partial used on the homepage rail
 * so the shop grid and homepage stay visually identical.
 */
defined( 'ABSPATH' ) || exit;

global $product;
if ( ! $product || ! $product->is_visible() ) {
	return;
}

get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) );
