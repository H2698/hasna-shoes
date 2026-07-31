<?php
/**
 * Header: top info bar + sticky nav (pixel source: home-page/Hasna Shoes - Accueil.dc.html).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="top" class="hs-app">

	<div class="hs-topbar">
		<div class="hs-topbar__item">
			<span class="hs-icon-parcel" aria-hidden="true"></span>
			<span><?php esc_html_e( 'Livraison partout en Tunisie', 'hasna-shoes' ); ?></span>
		</div>
		<span class="hs-topbar__center"><?php esc_html_e( 'Paiement à la livraison', 'hasna-shoes' ); ?></span>
		<div class="hs-topbar__item hs-topbar__item--end">
			<span class="hs-icon-headset" aria-hidden="true"></span>
			<span><?php echo esc_html__( 'Service client : ', 'hasna-shoes' ) . esc_html( get_option( 'hasna_phone', '26 000 000' ) ); ?></span>
		</div>
	</div>

	<header id="hs-header" class="hs-header">
		<a href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>" class="hs-logo" aria-label="<?php bloginfo( 'name' ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( HASNA_THEME_URI . '/assets/img/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="66" height="66">
			<?php endif; ?>
		</a>

		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => 'nav',
				'container_class'=> 'hs-nav',
				'menu_class'     => 'hs-nav__list',
				'depth'          => 1,
			) );
		} else {
			hasna_nav_menu_fallback();
		}
		?>

		<div class="hs-header__actions">
			<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
				<?php $hs_languages = pll_the_languages( array( 'show_flags' => 0, 'show_names' => 1, 'raw' => 1 ) ); ?>
				<?php if ( $hs_languages ) : ?>
					<div class="hs-lang-switch hs-lang-switch--header">
						<?php foreach ( $hs_languages as $hs_lang ) : ?>
							<a href="<?php echo esc_url( $hs_lang['url'] ); ?>" lang="<?php echo esc_attr( $hs_lang['locale'] ); ?>" class="<?php echo $hs_lang['current_lang'] ? 'is-current' : ''; ?>" <?php echo $hs_lang['current_lang'] ? 'aria-current="true"' : ''; ?>><?php echo esc_html( $hs_lang['name'] ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<button type="button" class="hs-icon-btn" data-search-toggle aria-expanded="false" aria-controls="hs-search-panel" aria-label="<?php esc_attr_e( 'Recherche', 'hasna-shoes' ); ?>"><?php echo hasna_icon( 'search' ); ?></button>
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '#compte' ); ?>" class="hs-icon-btn" aria-label="<?php esc_attr_e( 'Compte', 'hasna-shoes' ); ?>"><?php echo hasna_icon( 'account' ); ?></a>
			<a href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#panier' ); ?>" class="hs-icon-btn hs-cart-link" aria-label="<?php esc_attr_e( 'Panier', 'hasna-shoes' ); ?>">
				<?php echo hasna_icon( 'cart' ); ?>
				<span class="hs-cart-count"><?php echo function_exists( 'WC' ) ? absint( WC()->cart->get_cart_contents_count() ) : 0; ?></span>
			</a>
			<button type="button" class="hs-burger" data-mobile-nav-toggle aria-expanded="false" aria-controls="hs-mobile-nav" aria-label="<?php esc_attr_e( 'Menu', 'hasna-shoes' ); ?>">
				<span></span><span></span><span></span>
			</button>
		</div>
	</header>

	<div id="hs-search-panel" class="hs-search-panel" data-search-panel hidden>
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="hs-search-panel__form">
			<?php echo hasna_icon( 'search' ); ?>
			<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un produit…', 'hasna-shoes' ); ?>" autocomplete="off" data-search-input>
			<input type="hidden" name="post_type" value="product">
			<button type="button" class="hs-search-panel__close" data-search-close aria-label="<?php esc_attr_e( 'Fermer la recherche', 'hasna-shoes' ); ?>">&times;</button>
		</form>
	</div>

	<div id="hs-mobile-nav" class="hs-mobile-nav" data-mobile-nav hidden>
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'hs-mobile-nav__list',
				'depth'          => 1,
			) );
		} else {
			echo '<ul class="hs-mobile-nav__list">';
			foreach ( hasna_primary_nav_items() as $item ) {
				printf( '<li><a href="%s">%s</a></li>', esc_url( $item[0] ), esc_html( $item[1] ) );
			}
			echo '</ul>';
		}
		?>
	</div>
