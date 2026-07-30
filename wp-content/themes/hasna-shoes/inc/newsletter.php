<?php
/**
 * Homepage newsletter signup — stored as a lightweight custom post type so
 * the site owner can see/export subscribers from wp-admin without a
 * dedicated ESP integration yet.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hasna_register_newsletter_cpt() {
	register_post_type( 'hs_newsletter', array(
		'label'           => __( 'Abonnés newsletter', 'hasna-shoes' ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=product',
		'capability_type' => 'page',
		'supports'        => array( 'title' ),
	) );
}
add_action( 'init', 'hasna_register_newsletter_cpt' );

function hasna_handle_newsletter_signup() {
	check_ajax_referer( 'hasna_newsletter', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Adresse e-mail invalide.', 'hasna-shoes' ) ), 400 );
	}

	$existing = get_posts( array(
		'post_type'      => 'hs_newsletter',
		'title'          => $email,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( empty( $existing ) ) {
		wp_insert_post( array(
			'post_type'   => 'hs_newsletter',
			'post_title'  => $email,
			'post_status' => 'publish',
		) );
	}

	wp_send_json_success( array( 'message' => __( 'Merci pour votre inscription', 'hasna-shoes' ) ) );
}
add_action( 'wp_ajax_hasna_newsletter_signup', 'hasna_handle_newsletter_signup' );
add_action( 'wp_ajax_nopriv_hasna_newsletter_signup', 'hasna_handle_newsletter_signup' );
