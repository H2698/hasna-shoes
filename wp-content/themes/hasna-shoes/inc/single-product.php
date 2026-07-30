<?php
/**
 * Single product page: wishlist/share actions near Add to Cart, and a
 * breadcrumb wrapper so it lines up with the shop archive's container.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_single_product_summary', 'hasna_single_product_actions', 35 );
function hasna_single_product_actions() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$share_url = rawurlencode( get_permalink( $product->get_id() ) );
	$share_text = rawurlencode( $product->get_name() );
	?>
	<div class="hs-summary-actions">
		<button type="button" class="hs-icon-btn" data-wish aria-pressed="false" aria-label="<?php esc_attr_e( 'Ajouter aux favoris', 'hasna-shoes' ); ?>"><?php echo hasna_icon( 'heart' ); ?></button>
		<a class="hs-icon-btn" href="https://wa.me/?text=<?php echo esc_attr( $share_text . ' ' . $share_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Partager sur WhatsApp', 'hasna-shoes' ); ?>">
			<svg class="hs-icon" viewBox="0 0 22 22" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="8.5"/><path d="M7.5 13.5c1 1.2 2.2 1.8 3.6 1.8 2.4 0 4.3-2 4.3-4.3S13.5 6.7 11 6.7 6.7 8.6 6.7 11c0 .9.3 1.7.7 2.4L6.7 15z"/></svg>
		</a>
		<a class="hs-icon-btn" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $share_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Partager sur Facebook', 'hasna-shoes' ); ?>">
			<svg class="hs-icon" viewBox="0 0 22 22" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M13.5 7.5h-1.8c-.7 0-1.2.5-1.2 1.2V10.5H13.5L13.1 13H10.5V19.5H8V13H6V10.5H8V8.3C8 6.5 9.4 5 11.3 5H13.5V7.5Z"/></svg>
		</a>
	</div>
	<div class="hs-delivery-note">
		<span class="hs-icon-parcel" aria-hidden="true"></span>
		<span><?php esc_html_e( 'Livraison partout en Tunisie · Paiement à la livraison', 'hasna-shoes' ); ?></span>
	</div>
	<?php
}

/**
 * Wrap the single-product breadcrumb in our container so it aligns with
 * the rest of the page (WooCommerce prints it unwrapped by default).
 */
add_action( 'woocommerce_before_main_content', function () {
	if ( ! is_product() ) {
		return;
	}
	echo '<nav class="hs-breadcrumb">';
	woocommerce_breadcrumb( array( 'delimiter' => ' / ', 'wrap_before' => '', 'wrap_after' => '', 'before' => '<span>', 'after' => '</span>' ) );
	echo '</nav>';
}, 20 );
