<?php
/**
 * "Réglages Hasna Shoes" — the site owner's editable settings: contact
 * numbers shown across the theme, the homepage banner image, and (from M5)
 * the Meta Pixel ID. Deliberately scoped to what's genuinely useful to make
 * admin-editable without introducing a page-builder/ACF dependency for a
 * handful of fields — see architecture.md for the full rationale.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_submenu_page(
		'hasna-dashboard',
		__( 'Réglages Hasna Shoes', 'hasna-shoes' ),
		__( 'Réglages', 'hasna-shoes' ),
		'manage_options',
		'hasna-settings',
		'hasna_render_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'hasna_settings', 'hasna_phone', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '26 000 000' ) );
	register_setting( 'hasna_settings', 'hasna_whatsapp', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'hasna_settings', 'hasna_contact_email', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_email', 'default' => 'contact@hasnashoes.tn' ) );
	register_setting( 'hasna_settings', 'hasna_banner_image_id', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0 ) );
	register_setting( 'hasna_settings', 'hasna_meta_pixel_id', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'hasna_settings', 'hasna_meta_capi_token', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'hasna_settings', 'hasna_ga4_id', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'hasna_settings', 'hasna_gtm_id', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
} );

function hasna_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Accès non autorisé.', 'hasna-shoes' ) );
	}
	$banner_id  = (int) get_option( 'hasna_banner_image_id' );
	$banner_src = $banner_id ? wp_get_attachment_image_url( $banner_id, 'medium' ) : HASNA_THEME_URI . '/assets/img/banner/trio.webp';
	?>
	<div class="wrap hs-settings">
		<h1><?php esc_html_e( 'Réglages Hasna Shoes', 'hasna-shoes' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'hasna_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="hasna_phone"><?php esc_html_e( 'Téléphone service client', 'hasna-shoes' ); ?></label></th>
					<td><input type="text" id="hasna_phone" name="hasna_phone" value="<?php echo esc_attr( get_option( 'hasna_phone' ) ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="hasna_whatsapp"><?php esc_html_e( 'Numéro WhatsApp', 'hasna-shoes' ); ?></label></th>
					<td><input type="text" id="hasna_whatsapp" name="hasna_whatsapp" value="<?php echo esc_attr( get_option( 'hasna_whatsapp' ) ); ?>" class="regular-text" placeholder="21620123456"></td>
				</tr>
				<tr>
					<th scope="row"><label for="hasna_contact_email"><?php esc_html_e( 'E-mail de contact', 'hasna-shoes' ); ?></label></th>
					<td><input type="email" id="hasna_contact_email" name="hasna_contact_email" value="<?php echo esc_attr( get_option( 'hasna_contact_email' ) ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Image de la bannière (accueil)', 'hasna-shoes' ); ?></th>
					<td>
						<div class="hs-media-field">
							<img id="hasna_banner_preview" src="<?php echo esc_url( $banner_src ); ?>" alt="" style="max-width:220px;height:auto;display:block;margin-bottom:10px;border:1px solid #dcdcde;">
							<input type="hidden" id="hasna_banner_image_id" name="hasna_banner_image_id" value="<?php echo esc_attr( $banner_id ); ?>">
							<button type="button" class="button" id="hasna_banner_select"><?php esc_html_e( 'Choisir une image', 'hasna-shoes' ); ?></button>
							<button type="button" class="button" id="hasna_banner_remove"><?php esc_html_e( 'Retirer', 'hasna-shoes' ); ?></button>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row" colspan="2"><h2 style="margin-bottom:0;"><?php esc_html_e( 'Marketing & Analytics', 'hasna-shoes' ); ?></h2></th>
				</tr>
				<tr>
					<th scope="row"><label for="hasna_meta_pixel_id"><?php esc_html_e( 'Meta Pixel ID', 'hasna-shoes' ); ?></label></th>
					<td>
						<input type="text" id="hasna_meta_pixel_id" name="hasna_meta_pixel_id" value="<?php echo esc_attr( get_option( 'hasna_meta_pixel_id' ) ); ?>" class="regular-text" placeholder="123456789012345">
						<p class="description"><?php esc_html_e( 'Active le Pixel et les événements PageView, ViewContent, Search, AddToCart, InitiateCheckout et Purchase dès qu\'un ID est renseigné.', 'hasna-shoes' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="hasna_meta_capi_token"><?php esc_html_e( 'Meta Conversions API — Jeton d\'accès', 'hasna-shoes' ); ?></label></th>
					<td>
						<input type="password" id="hasna_meta_capi_token" name="hasna_meta_capi_token" value="<?php echo esc_attr( get_option( 'hasna_meta_capi_token' ) ); ?>" class="regular-text" autocomplete="off">
						<p class="description"><?php esc_html_e( 'Optionnel — jeton d\'accès système généré depuis Meta Business Manager. Envoie les mêmes événements côté serveur en plus du Pixel (déduplication automatique), pour ne rien perdre si le Pixel est bloqué côté navigateur. Sans jeton, seul le Pixel navigateur fonctionne.', 'hasna-shoes' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="hasna_ga4_id"><?php esc_html_e( 'Google Analytics 4 — ID de mesure', 'hasna-shoes' ); ?></label></th>
					<td><input type="text" id="hasna_ga4_id" name="hasna_ga4_id" value="<?php echo esc_attr( get_option( 'hasna_ga4_id' ) ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX"></td>
				</tr>
				<tr>
					<th scope="row"><label for="hasna_gtm_id"><?php esc_html_e( 'Google Tag Manager — ID de conteneur', 'hasna-shoes' ); ?></label></th>
					<td>
						<input type="text" id="hasna_gtm_id" name="hasna_gtm_id" value="<?php echo esc_attr( get_option( 'hasna_gtm_id' ) ); ?>" class="regular-text" placeholder="GTM-XXXXXXX">
						<p class="description"><?php esc_html_e( 'Optionnel, en plus de GA4 — si vous préférez gérer vos tags depuis un conteneur GTM.', 'hasna-shoes' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<script>
	jQuery(function ($) {
		var frame;
		$('#hasna_banner_select').on('click', function (e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({ title: '<?php echo esc_js( __( 'Choisir une image de bannière', 'hasna-shoes' ) ); ?>', multiple: false, library: { type: 'image' } });
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$('#hasna_banner_image_id').val(att.id);
				$('#hasna_banner_preview').attr('src', att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url);
			});
			frame.open();
		});
		$('#hasna_banner_remove').on('click', function (e) {
			e.preventDefault();
			$('#hasna_banner_image_id').val('');
			$('#hasna_banner_preview').attr('src', '<?php echo esc_js( HASNA_THEME_URI . '/assets/img/banner/trio.webp' ); ?>');
		});
	});
	</script>
	<?php
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'hasna-shoes_page_hasna-settings' === $hook || 'hasna-dashboard_page_hasna-settings' === $hook ) {
		wp_enqueue_media();
		wp_enqueue_style( 'hasna-admin', HASNA_THEME_URI . '/assets/css/admin.css', array(), HASNA_THEME_VERSION );
	}
} );
