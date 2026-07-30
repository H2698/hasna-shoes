<?php
/**
 * Contact form submissions — stored as a CPT (same lightweight pattern as
 * inc/newsletter.php) so they show up as real "Messages" in the admin
 * dashboard, and emailed to the site admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hasna_register_message_cpt() {
	register_post_type( 'hs_message', array(
		'label'           => __( 'Messages de contact', 'hasna-shoes' ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=product',
		'capability_type' => 'page',
		'supports'        => array( 'title', 'editor' ),
	) );
	register_post_meta( 'hs_message', '_hs_email', array( 'type' => 'string', 'single' => true, 'show_in_rest' => false ) );
	register_post_meta( 'hs_message', '_hs_phone', array( 'type' => 'string', 'single' => true, 'show_in_rest' => false ) );
	register_post_meta( 'hs_message', '_hs_read', array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => false ) );
}
add_action( 'init', 'hasna_register_message_cpt' );

function hasna_handle_contact_submit() {
	check_ajax_referer( 'hasna_contact', 'nonce' );

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( empty( $name ) || empty( $message ) || ( empty( $email ) && empty( $phone ) ) ) {
		wp_send_json_error( array( 'message' => __( 'Merci de renseigner votre nom, un moyen de contact et votre message.', 'hasna-shoes' ) ), 400 );
	}
	if ( ! empty( $email ) && ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Adresse e-mail invalide.', 'hasna-shoes' ) ), 400 );
	}

	$post_id = wp_insert_post( array(
		'post_type'    => 'hs_message',
		'post_title'   => $name,
		'post_content' => $message,
		'post_status'  => 'publish',
	) );

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_hs_email', $email );
		update_post_meta( $post_id, '_hs_phone', $phone );
		update_post_meta( $post_id, '_hs_read', false );

		wp_mail(
			get_option( 'admin_email' ),
			sprintf( '[Hasna Shoes] %s — %s', __( 'Nouveau message de contact', 'hasna-shoes' ), $name ),
			$message . "\n\n" . __( 'Contact :', 'hasna-shoes' ) . ' ' . ( $email ?: $phone )
		);
	}

	wp_send_json_success( array( 'message' => __( 'Merci, votre message a bien été envoyé. Nous vous répondrons rapidement.', 'hasna-shoes' ) ) );
}
add_action( 'wp_ajax_hasna_contact_submit', 'hasna_handle_contact_submit' );
add_action( 'wp_ajax_nopriv_hasna_contact_submit', 'hasna_handle_contact_submit' );

/**
 * Unread count, used by both the wp-admin nav badge and the custom dashboard.
 */
function hasna_unread_messages_count() {
	$query = new WP_Query( array(
		'post_type'      => 'hs_message',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_hs_read', 'value' => '1', 'compare' => '!=' ) ),
	) );
	return $query->found_posts;
}
