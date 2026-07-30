<?php
/**
 * Fallback template (required by WordPress). The site is a single-page
 * storefront on front-page.php; this only renders for contexts WordPress
 * doesn't otherwise route (e.g. search results, the blog if ever enabled).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="hs-container" style="padding-block: 60px; min-height: 40vh;">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<div><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun contenu trouvé.', 'hasna-shoes' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
