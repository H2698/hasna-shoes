<?php
/**
 * Contact page — real form (AJAX, nonce-verified) wired to inc/contact.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="hs-contact">
	<section class="hs-shop-header">
		<div class="hs-container">
			<nav class="hs-breadcrumb"><span><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'hasna-shoes' ); ?></a></span> / <span><?php the_title(); ?></span></nav>
			<h1 class="hs-shop-header__title"><?php the_title(); ?></h1>
		</div>
	</section>

	<section class="hs-container hs-contact__grid">
		<div class="hs-contact__info">
			<?php the_content(); ?>
			<ul class="hs-contact__list">
				<li><strong><?php esc_html_e( 'Téléphone', 'hasna-shoes' ); ?></strong><span>26 000 000</span></li>
				<li><strong><?php esc_html_e( 'E-mail', 'hasna-shoes' ); ?></strong><span>contact@hasnashoes.tn</span></li>
				<li><strong><?php esc_html_e( 'Livraison', 'hasna-shoes' ); ?></strong><span><?php esc_html_e( 'Partout en Tunisie, paiement à la livraison', 'hasna-shoes' ); ?></span></li>
			</ul>
		</div>

		<form class="hs-contact__form" data-contact-form>
			<?php wp_nonce_field( 'hasna_contact', 'hasna_contact_nonce' ); ?>
			<div class="hs-form-row">
				<label for="hs-c-name"><?php esc_html_e( 'Nom complet', 'hasna-shoes' ); ?></label>
				<input type="text" id="hs-c-name" name="name" required>
			</div>
			<div class="hs-form-row hs-form-row--2col">
				<div>
					<label for="hs-c-email"><?php esc_html_e( 'E-mail', 'hasna-shoes' ); ?></label>
					<input type="email" id="hs-c-email" name="email">
				</div>
				<div>
					<label for="hs-c-phone"><?php esc_html_e( 'Téléphone', 'hasna-shoes' ); ?></label>
					<input type="tel" id="hs-c-phone" name="phone">
				</div>
			</div>
			<div class="hs-form-row">
				<label for="hs-c-message"><?php esc_html_e( 'Message', 'hasna-shoes' ); ?></label>
				<textarea id="hs-c-message" name="message" rows="5" required></textarea>
			</div>
			<button type="submit" class="hs-btn hs-btn--dark"><?php esc_html_e( 'Envoyer le message', 'hasna-shoes' ); ?></button>
			<div class="hs-contact__msg" data-contact-msg></div>
		</form>
	</section>
</main>
<?php get_footer(); ?>
