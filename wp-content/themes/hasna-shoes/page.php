<?php
/**
 * Generic page template (About, Contact, Privacy, Terms). Full bespoke
 * layouts for these land in a later milestone; this keeps every page
 * usable and on-brand in the meantime.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="hs-container" style="padding-block: clamp(50px,6vw,86px); min-height: 40vh;">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'hs-page' ); ?>>
			<header class="hs-section-head" style="text-align:start; margin-bottom: 30px;">
				<h1 style="font-family: var(--font-heading); font-weight:400; font-size: clamp(26px,3vw,38px); text-transform:uppercase; letter-spacing:.06em; color:var(--ink-2); margin:0;"><?php the_title(); ?></h1>
			</header>
			<div class="hs-page__content" style="font-weight:300; line-height:1.8; color:var(--charcoal-2); max-width:760px;">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
