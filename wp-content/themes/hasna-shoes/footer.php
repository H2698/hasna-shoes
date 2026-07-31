<?php
/**
 * Footer (pixel source: home-page/Hasna Shoes - Accueil.dc.html, <footer id="contact">).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<footer id="contact" class="hs-footer">
		<div class="hs-footer__inner">
			<div class="hs-footer__grid">
				<div class="hs-footer__brand">
					<span class="hs-footer__logo">
						<img src="<?php echo esc_url( HASNA_THEME_URI . '/assets/img/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="84" height="84" loading="lazy">
					</span>
					<p><?php esc_html_e( 'Chaussures féminines élégantes et confortables, livrées partout en Tunisie avec paiement à la livraison.', 'hasna-shoes' ); ?></p>
				</div>

				<div class="hs-footer__col">
					<div class="hs-footer__heading"><?php esc_html_e( 'Boutique', 'hasna-shoes' ); ?></div>
					<?php if ( has_nav_menu( 'footer-boutique' ) ) : ?>
						<?php wp_nav_menu( array( 'theme_location' => 'footer-boutique', 'container' => false, 'menu_class' => '', 'depth' => 1, 'items_wrap' => '%3$s' ) ); ?>
					<?php else : ?>
						<a href="<?php echo esc_url( hasna_category_url( 'talons' ) ); ?>"><?php esc_html_e( 'Talons', 'hasna-shoes' ); ?></a>
						<a href="<?php echo esc_url( hasna_category_url( 'sandales' ) ); ?>"><?php esc_html_e( 'Sandales', 'hasna-shoes' ); ?></a>
						<a href="<?php echo esc_url( hasna_category_url( 'confort' ) ); ?>"><?php esc_html_e( 'Confort', 'hasna-shoes' ); ?></a>
						<a href="<?php echo esc_url( hasna_category_url( 'plates' ) ); ?>"><?php esc_html_e( 'Plates', 'hasna-shoes' ); ?></a>
						<a href="#produits"><?php esc_html_e( 'Nouveautés', 'hasna-shoes' ); ?></a>
					<?php endif; ?>
				</div>

				<div class="hs-footer__col">
					<div class="hs-footer__heading"><?php esc_html_e( 'Aide', 'hasna-shoes' ); ?></div>
					<a href="#livraison"><?php esc_html_e( 'Livraison & retours', 'hasna-shoes' ); ?></a>
					<a href="#tailles"><?php esc_html_e( 'Guide des tailles', 'hasna-shoes' ); ?></a>
					<a href="#entretien"><?php esc_html_e( 'Entretien', 'hasna-shoes' ); ?></a>
					<a href="#faq"><?php esc_html_e( 'FAQ', 'hasna-shoes' ); ?></a>
					<a href="<?php echo esc_url( hasna_contact_url() ); ?>"><?php esc_html_e( 'Nous contacter', 'hasna-shoes' ); ?></a>
				</div>

				<div class="hs-footer__col">
					<div class="hs-footer__heading"><?php esc_html_e( 'Contact', 'hasna-shoes' ); ?></div>
					<span class="hs-footer__strong"><?php echo esc_html( get_option( 'hasna_phone', '26 000 000' ) ); ?></span>
					<span><?php echo esc_html( get_option( 'hasna_contact_email', 'contact@hasnashoes.tn' ) ); ?></span>
					<div class="hs-footer__social">
						<a href="#instagram">Instagram</a>
						<a href="#facebook">Facebook</a>
					</div>
				</div>
			</div>

			<div class="hs-footer__bottom">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
				<div class="hs-lang-switch">
					<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
						<?php pll_the_languages( array( 'show_flags' => 0, 'show_names' => 1 ) ); ?>
					<?php else : ?>
						<a href="#fr" class="is-current">FR</a>
						<a href="#en">EN</a>
						<a href="#ar">AR</a>
					<?php endif; ?>
				</div>
				<span><?php esc_html_e( 'Paiement à la livraison · Tunisie', 'hasna-shoes' ); ?></span>
			</div>
		</div>
	</footer>
</div><!-- .hs-app -->
<?php wp_footer(); ?>
</body>
</html>
