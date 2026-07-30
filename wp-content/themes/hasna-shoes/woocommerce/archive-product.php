<?php
/**
 * Shop / category archive — overrides WooCommerce's default template so the
 * grid uses our design system (hs-product-card) instead of the stock markup.
 *
 * @see https://woocommerce.com/document/template-structure/
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="hs-shop-header">
	<div class="hs-container">
		<nav class="hs-breadcrumb"><?php woocommerce_breadcrumb( array( 'delimiter' => ' / ', 'wrap_before' => '', 'wrap_after' => '', 'before' => '<span>', 'after' => '</span>' ) ); ?></nav>
		<h1 class="hs-shop-header__title"><?php woocommerce_page_title(); ?></h1>
		<?php if ( is_product_taxonomy() ) : ?>
			<div class="hs-shop-header__desc"><?php echo wc_format_content( wp_kses_post( term_description() ) ); ?></div>
		<?php endif; ?>
	</div>
</section>

<section class="hs-shop">
	<div class="hs-container">

		<div class="hs-shop__chips">
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="hs-chip<?php echo is_shop() ? ' is-active' : ''; ?>"><?php esc_html_e( 'Tout', 'hasna-shoes' ); ?></a>
			<?php
			$cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => array( get_option( 'default_product_cat' ) ) ) );
			foreach ( $cats as $cat ) :
				$is_active = is_tax( 'product_cat', $cat->slug );
				?>
				<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="hs-chip<?php echo $is_active ? ' is-active' : ''; ?>"><?php echo esc_html( $cat->name ); ?></a>
			<?php endforeach; ?>
		</div>

		<?php do_action( 'woocommerce_before_main_content' ); ?>

		<?php if ( woocommerce_product_loop() ) : ?>

			<div class="hs-shop__toolbar">
				<div class="hs-shop__count"><?php woocommerce_result_count(); ?></div>
				<div class="hs-shop__sort"><?php woocommerce_catalog_ordering(); ?></div>
			</div>

			<div class="hs-shop__grid">
				<?php
				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();
						do_action( 'woocommerce_shop_loop' );
						wc_get_template_part( 'content', 'product' );
					}
				}
				?>
			</div>

			<div class="hs-shop__pagination"><?php do_action( 'woocommerce_after_shop_loop' ); ?></div>

		<?php else : ?>
			<?php do_action( 'woocommerce_no_products_found' ); ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_main_content' ); ?>
	</div>
</section>

<?php get_footer(); ?>
