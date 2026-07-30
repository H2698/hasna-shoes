<?php
/**
 * Product card used on the homepage rail and (M3) the shop archive.
 *
 * @var WC_Product $product Passed in via get_template_part()'s $args.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array $args */
global $product; // WooCommerce template functions/filters below read this global.
$product = $args['product'] ?? null;
if ( ! $product instanceof WC_Product ) {
	return;
}

$date_created = $product->get_date_created();
$is_new       = $date_created && $date_created->getTimestamp() > strtotime( '-30 days' );
?>
<article class="hs-product-card" data-reveal>
	<div class="hs-product-card__media">
		<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="hs-product-card__zoom">
			<?php echo $product->get_image( 'medium' ); ?>
		</a>
		<?php if ( $is_new ) : ?>
			<span class="hs-product-card__badge"><?php esc_html_e( 'Nouveau', 'hasna-shoes' ); ?></span>
		<?php endif; ?>
		<button type="button" class="hs-product-card__wish" data-wish aria-label="<?php esc_attr_e( 'Favori', 'hasna-shoes' ); ?>" aria-pressed="false"><?php echo hasna_icon( 'heart' ); ?></button>
	</div>
	<div class="hs-product-card__body">
		<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="hs-product-card__title"><?php echo esc_html( $product->get_name() ); ?></a>
		<div class="hs-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<?php
		echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce core filter output.
			'woocommerce_loop_add_to_cart_link',
			sprintf(
				'<a href="%s" data-quantity="1" class="%s hs-btn hs-btn--gold" %s>%s</a>',
				esc_url( $product->add_to_cart_url() ),
				esc_attr( implode( ' ', array_filter( array( 'button', 'product_type_' . $product->get_type(), 'add_to_cart_button', $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() ? 'ajax_add_to_cart' : '' ) ) ) ),
				wc_implode_html_attributes( array(
					'data-product_id'  => $product->get_id(),
					'data-product_sku' => $product->get_sku(),
					'aria-label'       => $product->add_to_cart_description(),
					'rel'              => 'nofollow',
				) ),
				esc_html( $product->add_to_cart_text() )
			),
			$product,
			array()
		);
		?>
	</div>
</article>
