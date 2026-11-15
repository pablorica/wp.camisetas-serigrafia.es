<?php
/**
 * Order Item Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-item.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 *
 * Sadoje: copy of cartify/woocommerce/order/order-details-item.php with the item meta escaping fixed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}
?>
<div class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">
	<?php
	$is_visible        = $product && $product->is_visible();
	$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

		?>
	<div class="woocommerce-table__product-thumbnail product-thumbnail">
		<?php
		echo wp_kses( apply_filters( 'woocommerce_order_item_thumbnail', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $product->get_image() ) : $product->get_image(), $product, $item, $is_visible ), 'img' );
		?>
	</div>
	<div class="woocommerce-table__product-details product-details">
		<h6 class="woocommerce-table__product-name product-name">
			<?php



			echo wp_kses( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ), 'title' );
			?>
		</h6>
		<div class="woocommerce-table__product-quantity product-quantity">
			<span><?php echo esc_html__( 'Qty:', 'cartify' ); ?></span>
			<?php
			$qty          = $item->get_quantity();
			$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

			if ( $refunded_qty ) {
				$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
			} else {
				$qty_display = esc_html( $qty );
			}
			?>
			<span><?php
				echo apply_filters( 'woocommerce_order_item_quantity_html', $qty_display, $item );
			?></span>
		</div>
		<div class="sadoje-item-meta-wrap">
			<?php
			do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );


			// Sadoje override (2026-11-15):
			// - display_value is already HTML from WooCommerce (wrapped in <p>, links made clickable),
			//   so Cartify's esc_html() printed the tags as text. Allow only inline tags instead.
			// - Rendered as a label/value list (<dl>) styled in sadoje/style.css, like the admin order view.
			$sadoje_inline_tags = array(
				'a'      => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
				'strong' => array(),
				'em'     => array(),
				'br'     => array(),
			);
			$strings = array();
			foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
				$strings[] = '<dt>' . esc_html( $meta->display_key ) . ':</dt><dd>' . wp_kses( trim( $meta->display_value ), $sadoje_inline_tags ) . '</dd>';
			}
			if ( $strings ) {
				echo apply_filters( 'woocommerce_display_item_meta', '<dl class="sadoje-item-meta">' . implode( '', $strings ) . '</dl>' );
			}

			do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
			?>
		</div>
	</div>

		<div class="woocommerce-table__product-total product-total">
		<?php echo wp_kses( $order->get_formatted_line_subtotal( $item ), array(
			'bdi' => array(),
			'span' => array(
				'class' => array()
			),
		 ) );
		 ?>
	</div>

</div>

<?php if ( $show_purchase_note && $purchase_note ) : ?>

<div class="woocommerce-table__product-purchase-note product-purchase-note">

	<span colspan="2"><?php echo wpautop( do_shortcode( esc_html( $purchase_note ) ) ); ?></span>

</div>

<?php endif; ?>