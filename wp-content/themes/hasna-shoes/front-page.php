<?php
/**
 * Homepage (pixel source: home-page/Hasna Shoes - Accueil.dc.html).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$hero_slides = array(
	array(
		'title'     => __( 'Mule Vernis Noir', 'hasna-shoes' ),
		'price'     => '149,000',
		'old_price' => '199,000',
		'note'      => '',
		'sizes'     => array( '36', '38', '40' ),
		'active'    => 0,
		'image'     => HASNA_THEME_URI . '/assets/img/hero-1.png',
	),
	array(
		'title'     => __( 'Mule Bloc Noir', 'hasna-shoes' ),
		'price'     => '169,000',
		'old_price' => '',
		'note'      => __( 'Talon 6 cm', 'hasna-shoes' ),
		'sizes'     => array( '37', '38', '39' ),
		'active'    => 0,
		'image'     => HASNA_THEME_URI . '/assets/img/hero-2.png',
	),
	array(
		'title'     => __( 'Tong Cuir Cognac', 'hasna-shoes' ),
		'price'     => '99,000',
		'old_price' => '',
		'note'      => __( 'Semelle plate', 'hasna-shoes' ),
		'sizes'     => array( '36', '39', '40' ),
		'active'    => 0,
		'image'     => HASNA_THEME_URI . '/assets/img/hero-3.png',
	),
);
?>

<section class="hs-hero-track" id="hs-hero-track" data-hero-track>
	<div class="hs-hero" id="hs-hero" data-hero>
		<div class="hs-hero__glow" aria-hidden="true"></div>

		<div class="hs-hero__copy">
			<h1 class="hs-hero__title"><?php esc_html_e( "L'élégance", 'hasna-shoes' ); ?><br><?php esc_html_e( 'à chaque pas', 'hasna-shoes' ); ?></h1>
			<p class="hs-hero__text"><?php esc_html_e( 'Alliez confort et raffinement avec des sandales conçues pour sublimer votre quotidien.', 'hasna-shoes' ); ?><br><?php esc_html_e( 'Marchez avec assurance, brillez avec élégance.', 'hasna-shoes' ); ?></p>
			<a href="#produits" class="hs-btn hs-btn--dark"><?php esc_html_e( 'Découvrir la collection', 'hasna-shoes' ); ?></a>
			<div class="hs-hero__social">
				<a href="#instagram">Instagram</a>
				<a href="#facebook">Facebook</a>
				<a href="#whatsapp">WhatsApp</a>
			</div>
		</div>

		<div class="hs-hero__stage">
			<div class="hs-hero__shadow" aria-hidden="true"></div>
			<div class="hs-hero__shoe" id="hs-hero-shoe" data-hero-shoe>
				<?php foreach ( $hero_slides as $i => $slide ) : ?>
					<div class="hs-hero__shoe-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-shoe="<?php echo esc_attr( $i ); ?>">
						<img src="<?php echo esc_url( $slide['image'] ); ?>" alt="<?php echo esc_attr( $slide['title'] ); ?>" <?php echo 0 === $i ? '' : 'loading="lazy"'; ?>>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="hs-hero__info">
			<div class="hs-hero__info-stage">
				<?php foreach ( $hero_slides as $i => $slide ) : ?>
					<div class="hs-hero__info-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-info="<?php echo esc_attr( $i ); ?>">
						<div class="hs-hero__label"><?php echo esc_html( $slide['title'] ); ?></div>
						<div class="hs-hero__price">
							<span class="hs-hero__price-current"><?php echo esc_html( $slide['price'] ); ?></span>
							<span class="hs-hero__price-currency">TND</span>
						</div>
						<?php if ( $slide['old_price'] ) : ?>
							<div class="hs-hero__price-old"><?php echo esc_html( $slide['old_price'] ); ?> <span>TND</span></div>
						<?php elseif ( $slide['note'] ) : ?>
							<div class="hs-hero__note"><?php echo esc_html( $slide['note'] ); ?></div>
						<?php endif; ?>
						<div class="hs-hero__size-label"><?php esc_html_e( 'Choisissez votre taille', 'hasna-shoes' ); ?></div>
						<div class="hs-hero__sizes" role="group" aria-label="<?php esc_attr_e( 'Tailles disponibles', 'hasna-shoes' ); ?>">
							<?php foreach ( $slide['sizes'] as $si => $size ) : ?>
								<button type="button" class="hs-hero__size<?php echo 0 === $si ? ' is-active' : ''; ?>" aria-pressed="<?php echo 0 === $si ? 'true' : 'false'; ?>"><?php echo esc_html( $size ); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="hs-hero__controls">
				<div class="hs-hero__nav">
					<button type="button" class="hs-hero__navbtn" data-hero-nav="-1" aria-label="<?php esc_attr_e( 'Précédent', 'hasna-shoes' ); ?>"><?php echo hasna_icon( 'chevron-left' ); ?></button>
					<button type="button" class="hs-hero__navbtn" data-hero-nav="1" aria-label="<?php esc_attr_e( 'Suivant', 'hasna-shoes' ); ?>"><?php echo hasna_icon( 'chevron-right' ); ?></button>
				</div>
				<div class="hs-hero__thumbs">
					<?php foreach ( $hero_slides as $i => $slide ) : ?>
						<button type="button" class="hs-hero__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-hero-goto="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( $slide['title'] ); ?>">
							<img src="<?php echo esc_url( $slide['image'] ); ?>" alt="" loading="lazy">
						</button>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="hs-hero__counter" data-hero-counter>01 / <?php echo esc_html( sprintf( '%02d', count( $hero_slides ) ) ); ?></div>
		</div>
	</div>
</section>

<section class="hs-advantages" aria-label="<?php esc_attr_e( 'Avantages', 'hasna-shoes' ); ?>">
	<div class="hs-container hs-advantages__grid">
		<?php
		$advantages = array(
			array( 'icon' => 'shipping', 'title' => __( 'Livraison rapide', 'hasna-shoes' ), 'text' => __( 'Partout en Tunisie', 'hasna-shoes' ) ),
			array( 'icon' => 'cod', 'title' => __( 'Paiement à la livraison', 'hasna-shoes' ), 'text' => __( 'Vous payez à la réception', 'hasna-shoes' ) ),
			array( 'icon' => 'quality', 'title' => __( 'Produits de qualité', 'hasna-shoes' ), 'text' => __( 'Confort et durabilité', 'hasna-shoes' ) ),
			array( 'icon' => 'support', 'title' => __( 'Service client', 'hasna-shoes' ), 'text' => __( 'À votre écoute', 'hasna-shoes' ) ),
		);
		foreach ( $advantages as $a ) :
			?>
			<div class="hs-advantages__item" data-reveal>
				<span class="hs-advantages__icon hs-advantages__icon--<?php echo esc_attr( $a['icon'] ); ?>" aria-hidden="true"></span>
				<div>
					<div class="hs-advantages__title"><?php echo esc_html( $a['title'] ); ?></div>
					<div class="hs-advantages__text"><?php echo esc_html( $a['text'] ); ?></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section id="categories" class="hs-categories">
	<div class="hs-container">
		<div class="hs-section-head">
			<h2 data-reveal><?php esc_html_e( 'Nos catégories', 'hasna-shoes' ); ?></h2>
			<span class="hs-rule"></span>
		</div>
		<div class="hs-categories__grid">
			<?php foreach ( hasna_homepage_categories() as $cat ) : ?>
				<a href="<?php echo $cat['term'] ? esc_url( get_term_link( $cat['term'] ) ) : '#produits'; ?>" class="hs-category" data-reveal>
					<span class="hs-category__circle">
						<span class="hs-category__zoom">
							<img src="<?php echo esc_url( $cat['image'] ); ?>" alt="<?php echo esc_attr( $cat['label'] ); ?>" loading="lazy">
						</span>
					</span>
					<span class="hs-category__label"><?php echo esc_html( $cat['label'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section id="produits" class="hs-featured">
	<div class="hs-container hs-featured__inner">
		<div class="hs-section-head">
			<h2 data-reveal><?php esc_html_e( 'Nos produits phares', 'hasna-shoes' ); ?></h2>
			<span class="hs-rule"></span>
		</div>

		<?php $featured = hasna_featured_products(); ?>

		<?php if ( ! empty( $featured ) ) : ?>
			<button type="button" class="hs-rail-nav hs-rail-nav--prev" data-rail-nav="-1" aria-label="<?php esc_attr_e( 'Précédent', 'hasna-shoes' ); ?>"><?php echo hasna_icon( 'chevron-left' ); ?></button>
			<button type="button" class="hs-rail-nav hs-rail-nav--next" data-rail-nav="1" aria-label="<?php esc_attr_e( 'Suivant', 'hasna-shoes' ); ?>"><?php echo hasna_icon( 'chevron-right' ); ?></button>

			<div class="hs-rail" data-rail>
				<?php foreach ( $featured as $product ) : ?>
					<?php get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="hs-featured__empty"><?php esc_html_e( 'Notre collection arrive très bientôt.', 'hasna-shoes' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="hs-banner">
	<div class="hs-banner__inner" data-reveal>
		<div class="hs-banner__left">
			<span class="hs-banner__logo">
				<img src="<?php echo esc_url( HASNA_THEME_URI . '/assets/img/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" loading="lazy">
			</span>
			<a href="#produits" class="hs-btn hs-btn--outline"><?php esc_html_e( 'Découvrir', 'hasna-shoes' ); ?></a>
		</div>
		<div class="hs-banner__image">
			<img src="<?php echo esc_url( HASNA_THEME_URI . '/assets/img/banner/trio.webp' ); ?>" alt="<?php esc_attr_e( 'Trio de chaussures Hasna Shoes', 'hasna-shoes' ); ?>" loading="lazy">
		</div>
		<div class="hs-banner__right">
			<h2><?php esc_html_e( 'Confort & style', 'hasna-shoes' ); ?><br><span><?php esc_html_e( 'pour toutes vos occasions', 'hasna-shoes' ); ?></span></h2>
			<span class="hs-rule hs-rule--left"></span>
			<p><?php esc_html_e( 'Découvrez notre sélection et trouvez la paire parfaite !', 'hasna-shoes' ); ?></p>
		</div>
	</div>
</section>

<section class="hs-guarantees">
	<div class="hs-container hs-guarantees__grid">
		<?php
		$guarantees = array(
			array( 'icon' => 'materials', 'title' => __( 'Matières sélectionnées', 'hasna-shoes' ), 'text' => __( 'Qualité premium', 'hasna-shoes' ) ),
			array( 'icon' => 'design', 'title' => __( 'Design moderne', 'hasna-shoes' ), 'text' => __( 'Pour toutes les occasions', 'hasna-shoes' ) ),
			array( 'icon' => 'comfort', 'title' => __( 'Confort absolu', 'hasna-shoes' ), 'text' => __( 'Pensé pour vous', 'hasna-shoes' ) ),
			array( 'icon' => 'satisfaction', 'title' => __( 'Satisfaction garantie', 'hasna-shoes' ), 'text' => __( 'Échanges et retours faciles', 'hasna-shoes' ) ),
		);
		foreach ( $guarantees as $g ) :
			?>
			<div class="hs-guarantees__item">
				<span class="hs-guarantees__icon hs-guarantees__icon--<?php echo esc_attr( $g['icon'] ); ?>" aria-hidden="true"></span>
				<div>
					<div class="hs-guarantees__title"><?php echo esc_html( $g['title'] ); ?></div>
					<div class="hs-guarantees__text"><?php echo esc_html( $g['text'] ); ?></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="hs-testimonials">
	<div class="hs-container hs-testimonials__inner">
		<div class="hs-section-head">
			<h2 data-reveal><?php esc_html_e( 'Elles nous font confiance', 'hasna-shoes' ); ?></h2>
			<span class="hs-rule"></span>
		</div>
		<div class="hs-testimonials__stage" data-quotes>
			<?php
			$testimonials = array(
				array( 'quote' => __( '« Je les porte du matin au soir sans aucune gêne. Le cuir est magnifique et la livraison a été très rapide. »', 'hasna-shoes' ), 'author' => 'Yasmine B. — Tunis' ),
				array( 'quote' => __( '« Deuxième commande et toujours la même qualité. Le paiement à la livraison me rassure énormément. »', 'hasna-shoes' ), 'author' => 'Nour F. — Sousse' ),
				array( 'quote' => __( '« Des modèles élégants qui vont avec tout, et un service client qui répond en quelques minutes. »', 'hasna-shoes' ), 'author' => 'Ines M. — Sfax' ),
			);
			foreach ( $testimonials as $i => $t ) :
				?>
				<blockquote class="hs-testimonial<?php echo 0 === $i ? ' is-active' : ''; ?>" data-quote="<?php echo esc_attr( $i ); ?>">
					<p><?php echo esc_html( $t['quote'] ); ?></p>
					<footer><?php echo esc_html( $t['author'] ); ?></footer>
				</blockquote>
			<?php endforeach; ?>
		</div>
		<div class="hs-testimonials__dots">
			<?php foreach ( $testimonials as $i => $t ) : ?>
				<button type="button" class="hs-dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-dot="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( $i + 1 ); ?>"></button>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section id="instagram" class="hs-instagram">
	<div class="hs-container">
		<div class="hs-section-head">
			<h2 data-reveal>@hasnashoes</h2>
			<span class="hs-rule"></span>
		</div>
		<div class="hs-instagram__stage">
			<div class="hs-instagram__ring" data-ig-ring>
				<?php
				$ig_images = array( 'post-1', 'post-2', 'post-3', 'post-4', 'post-5' );
				foreach ( $ig_images as $i => $slug ) :
					$angle = round( $i * ( 360 / count( $ig_images ) ) );
					?>
					<a href="#instagram" class="hs-instagram__item" style="transform: rotateY(<?php echo esc_attr( $angle ); ?>deg) translateZ(340px);">
						<img src="<?php echo esc_url( HASNA_THEME_URI . '/assets/img/instagram/' . $slug . '.webp' ); ?>" alt="Instagram post" loading="lazy">
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<section class="hs-newsletter">
	<div class="hs-newsletter__inner">
		<h2><?php esc_html_e( 'Restez informée', 'hasna-shoes' ); ?></h2>
		<p><?php esc_html_e( 'Nouveaux modèles, réassorts et offres privées — une fois par mois.', 'hasna-shoes' ); ?></p>
		<form class="hs-newsletter__form" data-newsletter-form>
			<?php wp_nonce_field( 'hasna_newsletter', 'hasna_newsletter_nonce' ); ?>
			<input type="email" name="email" required placeholder="<?php esc_attr_e( 'Votre adresse e-mail', 'hasna-shoes' ); ?>">
			<button type="submit" class="hs-btn hs-btn--dark"><?php esc_html_e( "S'inscrire", 'hasna-shoes' ); ?></button>
		</form>
		<div class="hs-newsletter__msg" data-newsletter-msg><?php esc_html_e( 'Merci pour votre inscription', 'hasna-shoes' ); ?></div>
	</div>
</section>

<?php get_footer(); ?>
