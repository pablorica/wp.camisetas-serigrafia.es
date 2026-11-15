<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */


defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id );

if ( ! $order ) {
	return;
}

$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads             = $order->get_downloadable_items();
$actions            = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);


$show_customer_details = $order->get_user_id() === get_current_user_id();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'cartify' ); ?></h2>

	<div class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<div class="woocommerce-table__line-items">
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );

			foreach ( $order_items as $item_id => $item ) {
				$product = $item->get_product();

				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $order,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $product ? $product->get_purchase_note() : '',
						'product'            => $product,
					)
				);
			}

			do_action( 'woocommerce_order_details_after_order_table_items', $order );
			?>
		</div>

		<?php
		if ( ! empty( $actions ) ) :
			?>
			<div class="woocommerce-table__summary">
				<div class="woocommerce-table__summary-item">
					<?php
					$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
					foreach ( $actions as $key => $action ) {
						if ( empty( $action['aria-label'] ) ) {


							$action_aria_label = sprintf( __( '%1$s order number %2$s', 'cartify' ), $action['name'], $order->get_order_number() );
						} else {
							$action_aria_label = $action['aria-label'];
						}
							echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . ' order-actions-button " aria-label="' . esc_attr( $action_aria_label ) . '">' . esc_html( $action['name'] ) . '</a>';
							unset( $action_aria_label );
					}
					?>
				</div>
			</div>
		<?php endif ?>
		<div class="woocommerce-table__summary">
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
					<div class="woocommerce-table__summary-item <?php echo esc_attr($key); ?>">
						<span><?php echo esc_html( $total['label'] ); ?></span>
						<strong><?php echo wp_kses( $total['value'], 'price' ); ?></strong>
					</div>
					<?php
			}
			?>
			<?php if ( $order->get_customer_note() ) : ?>
				<div class="woocommerce-table__special-note">
					<span><?php esc_html_e( 'Note:', 'cartify' ); ?></span>
					<span><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
