<?php
/**
 * Guest-checkout-only, Cash-on-Delivery-only store policy.
 *
 * Enforced at the plugin-options level (not just hidden in the UI) so it can't be
 * bypassed by a direct request, per the project brief: no accounts, COD only.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'pre_option_woocommerce_enable_guest_checkout', fn() => 'yes' );
add_filter( 'pre_option_woocommerce_enable_checkout_login_reminder', fn() => 'no' );
add_filter( 'pre_option_woocommerce_enable_myaccount_registration', fn() => 'no' );
add_filter( 'pre_option_woocommerce_enable_signup_and_login_from_checkout', fn() => 'no' );

/**
 * Only Cash on Delivery may ever be an available gateway, regardless of what's
 * installed/configured — a defense-in-depth check, not just an admin setting.
 */
function hasna_restrict_payment_gateways( $gateways ) {
	if ( is_admin() ) {
		return $gateways;
	}
	return array_filter( $gateways, fn( $id ) => 'cod' === $id, ARRAY_FILTER_USE_KEY );
}
add_filter( 'woocommerce_available_payment_gateways', 'hasna_restrict_payment_gateways' );

/**
 * Trim checkout fields to Name / Phone / Address / City / Notes per the brief
 * (no company, no separate postcode/state noise, no billing email requirement
 * beyond what WooCommerce needs internally for order notifications).
 */
function hasna_trim_checkout_fields( $fields ) {
	unset(
		$fields['billing']['billing_company'],
		$fields['billing']['billing_postcode'],
		$fields['billing']['billing_state'],
		$fields['billing']['billing_country'],
		$fields['billing']['billing_email'],
		$fields['billing']['billing_address_2'],
		$fields['shipping']
	);

	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['required'] = true;
		$fields['billing']['billing_phone']['priority']  = 25;
	}
	if ( isset( $fields['billing']['billing_city'] ) ) {
		$fields['billing']['billing_city']['priority'] = 40;
	}
	if ( isset( $fields['billing']['billing_address_1'] ) ) {
		$fields['billing']['billing_address_1']['label']    = __( 'Adresse', 'hasna-shoes' );
		$fields['billing']['billing_address_1']['priority'] = 30;
	}
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label'] = __( 'Notes de commande', 'hasna-shoes' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'hasna_trim_checkout_fields' );

/**
 * Billing email isn't part of the brief's field list, but WooCommerce requires *a*
 * value internally for order records — default it from the phone so no separate
 * "email" field needs to be shown to the customer.
 */
function hasna_default_billing_email( $data ) {
	if ( empty( $data['billing_email'] ) && ! empty( $data['billing_phone'] ) ) {
		$data['billing_email'] = preg_replace( '/\D/', '', $data['billing_phone'] ) . '@commande.hasnashoes.tn';
	}
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'hasna_default_billing_email' );
