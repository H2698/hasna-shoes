<?php
/**
 * View for hasna_render_dashboard_page() — receives its variables from
 * that function's local scope via include().
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon = function ( $name ) {
	$icons = array(
		'cart'    => '<path d="M2 3h3l2.4 12.4a2 2 0 0 0 2 1.9h8.3a2 2 0 0 0 2-1.6L21 8H6.2"/><circle cx="9" cy="20" r="1.5" fill="currentColor" stroke="none"/><circle cx="18" cy="20" r="1.5" fill="currentColor" stroke="none"/>',
		'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
		'box'     => '<path d="M3 8l9-5 9 5-9 5-9-5z"/><path d="M3 8v9l9 5 9-5V8"/><path d="M12 13v9"/>',
		'coins'   => '<circle cx="8" cy="12" r="5.2"/><circle cx="15.5" cy="9" r="5.2"/>',
		'truck'   => '<rect x="1" y="7" width="12" height="9"/><path d="M13 10h4l4 3v3h-8z"/><circle cx="5.5" cy="18.5" r="1.6" fill="currentColor" stroke="none"/><circle cx="16.5" cy="18.5" r="1.6" fill="currentColor" stroke="none"/>',
		'plus'    => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
		'eye'     => '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>',
		'edit'    => '<path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3z"/>',
		'message' => '<path d="M4 5h16v11H8.5L4 20V5z"/>',
		'users'   => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c0-3.3 2.5-6 5.5-6s5.5 2.7 5.5 6"/><circle cx="17.5" cy="9.5" r="2.4"/><path d="M15.8 14.2A5 5 0 0 1 20.5 20"/>',
		'arrow'   => '<line x1="7" y1="17" x2="17" y2="7"/><path d="M8 7h9v9"/>',
	);
	return $icons[ $name ] ?? '';
};
?>
<div class="hs-admin">
	<div class="hs-admin__stats">
		<div class="hs-stat-card">
			<div class="hs-stat-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( 'cart' ); ?></svg></div>
			<div class="hs-stat-card__label"><?php esc_html_e( 'TOTAL COMMANDES', 'hasna-shoes' ); ?></div>
			<div class="hs-stat-card__value"><?php echo esc_html( number_format_i18n( $total_orders ) ); ?></div>
		</div>
		<div class="hs-stat-card">
			<div class="hs-stat-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( 'clock' ); ?></svg></div>
			<div class="hs-stat-card__label"><?php esc_html_e( 'COMMANDES EN ATTENTE', 'hasna-shoes' ); ?></div>
			<div class="hs-stat-card__value"><?php echo esc_html( number_format_i18n( $pending_orders ) ); ?></div>
		</div>
		<div class="hs-stat-card">
			<div class="hs-stat-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( 'box' ); ?></svg></div>
			<div class="hs-stat-card__label"><?php esc_html_e( 'PRODUITS PUBLIÉS', 'hasna-shoes' ); ?></div>
			<div class="hs-stat-card__value"><?php echo esc_html( number_format_i18n( $total_products ) ); ?></div>
		</div>
		<div class="hs-stat-card">
			<div class="hs-stat-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( 'coins' ); ?></svg></div>
			<div class="hs-stat-card__label"><?php esc_html_e( "CHIFFRE D'AFFAIRES", 'hasna-shoes' ); ?></div>
			<div class="hs-stat-card__value"><?php echo wp_kses_post( wc_price( $total_revenue ) ); ?></div>
		</div>
		<div class="hs-stat-card hs-stat-card--accent">
			<div class="hs-stat-card__icon hs-stat-card__icon--light"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( 'truck' ); ?></svg></div>
			<div class="hs-stat-card__title"><?php esc_html_e( 'Paiement à la livraison uniquement', 'hasna-shoes' ); ?></div>
			<div class="hs-stat-card__sub"><?php esc_html_e( 'Aucun paiement en ligne disponible sur la boutique.', 'hasna-shoes' ); ?></div>
		</div>
	</div>

	<div class="hs-admin__cols">
		<div class="hs-admin__main">

			<div class="hs-panel">
				<div class="hs-panel__head">
					<div class="hs-panel__title"><?php esc_html_e( 'COMMANDES RÉCENTES', 'hasna-shoes' ); ?></div>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_order' ) ); ?>"><?php esc_html_e( 'Voir toutes les commandes', 'hasna-shoes' ); ?> →</a>
				</div>
				<div class="hs-panel__scroll">
				<table class="hs-table">
					<thead><tr>
						<th><?php esc_html_e( 'N° Commande', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Client', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Ville', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Total', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Paiement', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'hasna-shoes' ); ?></th>
					</tr></thead>
					<tbody>
					<?php if ( empty( $recent_orders ) ) : ?>
						<tr><td colspan="7" class="hs-table__empty"><?php esc_html_e( 'Aucune commande pour le moment.', 'hasna-shoes' ); ?></td></tr>
					<?php endif; ?>
					<?php foreach ( $recent_orders as $order ) : ?>
						<tr>
							<td class="hs-table__strong">#<?php echo esc_html( $order->get_order_number() ); ?></td>
							<td><?php echo esc_html( trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ?: '—' ); ?></td>
							<td class="hs-table__muted"><?php echo esc_html( $order->get_billing_city() ?: '—' ); ?></td>
							<td class="hs-table__strong"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
							<td class="hs-table__muted"><?php echo esc_html( $order->get_payment_method_title() ?: '—' ); ?></td>
							<td><?php echo hasna_status_pill( wc_get_order_status_name( $order->get_status() ), hasna_order_status_tone( $order->get_status() ) ); ?></td>
							<td>
								<div class="hs-table__actions">
									<a class="hs-icon-action" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders&action=view&id=' . $order->get_id() ) ); ?>" aria-label="<?php esc_attr_e( 'Voir', 'hasna-shoes' ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( 'eye' ); ?></svg></a>
									<a class="hs-icon-action" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $order->get_id() ) ); ?>" aria-label="<?php esc_attr_e( 'Modifier', 'hasna-shoes' ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( 'edit' ); ?></svg></a>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			</div>

			<div class="hs-panel">
				<div class="hs-panel__head">
					<div class="hs-panel__title"><?php esc_html_e( 'PRODUITS RÉCENTS', 'hasna-shoes' ); ?></div>
					<a class="hs-btn-gold" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?php echo $icon( 'plus' ); ?></svg><?php esc_html_e( 'Ajouter un produit', 'hasna-shoes' ); ?></a>
				</div>
				<div class="hs-panel__scroll">
				<table class="hs-table">
					<thead><tr>
						<th><?php esc_html_e( 'Produit', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Prix', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Stock', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Catégorie', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'hasna-shoes' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'hasna-shoes' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $recent_products as $product ) : ?>
						<tr>
							<td>
								<div class="hs-table__product">
									<span class="hs-table__thumb"><?php echo wp_kses_post( $product->get_image( array( 46, 46 ) ) ); ?></span>
									<div>
										<div class="hs-table__strong"><?php echo esc_html( $product->get_name() ); ?></div>
										<div class="hs-table__muted"><?php echo esc_html( $product->get_sku() ?: '—' ); ?></div>
									</div>
								</div>
							</td>
							<td class="hs-table__strong"><?php echo wp_kses_post( $product->get_price_html() ); ?></td>
							<td>
								<div class="hs-table__strong"><?php echo esc_html( $product->get_stock_quantity() ?? '—' ); ?></div>
								<div class="hs-table__muted"><?php esc_html_e( 'en stock', 'hasna-shoes' ); ?></div>
							</td>
							<td class="hs-table__muted"><?php
								$terms = get_the_terms( $product->get_id(), 'product_cat' );
								echo esc_html( $terms && ! is_wp_error( $terms ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '—' );
							?></td>
							<td><?php echo hasna_status_pill( $product->get_stock_status() === 'instock' ? __( 'Actif', 'hasna-shoes' ) : __( 'Rupture', 'hasna-shoes' ), $product->get_stock_status() === 'instock' ? 'done' : 'cancel' ); ?></td>
							<td>
								<div class="hs-table__actions">
									<a class="hs-icon-action" href="<?php echo esc_url( get_edit_post_link( $product->get_id() ) ); ?>" aria-label="<?php esc_attr_e( 'Modifier', 'hasna-shoes' ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( 'edit' ); ?></svg></a>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			</div>

		</div>

		<div class="hs-admin__side">

			<div class="hs-panel hs-panel--pad">
				<div class="hs-panel__head">
					<div class="hs-panel__title"><?php esc_html_e( 'VENTES', 'hasna-shoes' ); ?> <span class="hs-panel__title-sub"><?php esc_html_e( '(30 derniers jours)', 'hasna-shoes' ); ?></span></div>
				</div>
				<svg viewBox="0 0 640 200" class="hs-chart" preserveAspectRatio="none">
					<line x1="0" y1="0" x2="640" y2="0" stroke="#F0EAE0"/>
					<line x1="0" y1="50" x2="640" y2="50" stroke="#F0EAE0"/>
					<line x1="0" y1="100" x2="640" y2="100" stroke="#F0EAE0"/>
					<line x1="0" y1="150" x2="640" y2="150" stroke="#F0EAE0"/>
					<line x1="0" y1="199" x2="640" y2="199" stroke="#F0EAE0"/>
					<path d="<?php echo esc_attr( $chart_area ); ?>" fill="#B8873C" opacity="0.08" stroke="none"></path>
					<path d="<?php echo esc_attr( $chart_path ); ?>" fill="none" stroke="#B8873C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hs-chart__line"></path>
				</svg>
				<div class="hs-chart__labels">
					<span><?php echo esc_html( gmdate( 'j M', strtotime( '-29 days' ) ) ); ?></span>
					<span><?php echo esc_html( gmdate( 'j M', strtotime( '-15 days' ) ) ); ?></span>
					<span><?php echo esc_html( gmdate( 'j M' ) ); ?></span>
				</div>
			</div>

			<div class="hs-panel hs-panel--pad">
				<div class="hs-panel__head">
					<div class="hs-panel__title"><?php esc_html_e( 'ACTIVITÉS RÉCENTES', 'hasna-shoes' ); ?></div>
					<?php if ( $unread_messages > 0 ) : ?><span class="hs-badge"><?php echo esc_html( $unread_messages ); ?></span><?php endif; ?>
				</div>
				<div class="hs-activity">
					<?php if ( empty( $activity ) ) : ?>
						<p class="hs-table__empty"><?php esc_html_e( 'Aucune activité récente.', 'hasna-shoes' ); ?></p>
					<?php endif; ?>
					<?php foreach ( $activity as $a ) : ?>
						<div class="hs-activity__item">
							<span class="hs-activity__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo $icon( $a['icon'] ); ?></svg></span>
							<div class="hs-activity__body">
								<div class="hs-activity__title"><?php echo esc_html( $a['title'] ); ?></div>
								<div class="hs-activity__sub"><?php echo esc_html( $a['sub'] ); ?></div>
							</div>
							<div class="hs-activity__time"><?php echo esc_html( hasna_time_ago_fr( strtotime( is_object( $a['time'] ) ? $a['time']->date( 'Y-m-d H:i:s' ) : $a['time'] ) ) ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<a class="hs-panel__more" href="<?php echo esc_url( admin_url( 'edit.php?post_type=hs_message' ) ); ?>"><?php esc_html_e( "Voir tous les messages", 'hasna-shoes' ); ?></a>
			</div>

			<div class="hs-panel hs-panel--pad hs-quicklinks">
				<div class="hs-panel__title"><?php esc_html_e( 'ACCÈS RAPIDE', 'hasna-shoes' ); ?></div>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>"><?php esc_html_e( 'Produits', 'hasna-shoes' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_order' ) ); ?>"><?php esc_html_e( 'Commandes', 'hasna-shoes' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ) ); ?>"><?php esc_html_e( 'Catégories', 'hasna-shoes' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_coupon' ) ); ?>"><?php esc_html_e( 'Promotions / Coupons', 'hasna-shoes' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"><?php esc_html_e( 'Clients', 'hasna-shoes' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=hasna-settings' ) ); ?>"><?php esc_html_e( 'Réglages Hasna Shoes', 'hasna-shoes' ); ?></a>
			</div>

		</div>
	</div>
</div>
