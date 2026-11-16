<?php
/**
 * Backend: price tiers field on each CPT Easy variation, and guards on the product.
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

class CPT_Easy_Admin {

	public static function init(): void {
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'render_tiers_field' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_tiers_field' ), 10, 2 );

		// The product is only sold through the CPT Easy form, never through the normal cart.
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'block_add_to_cart' ), 10, 2 );

		add_action( 'woocommerce_admin_order_data_after_billing_address', array( __CLASS__, 'show_nif_in_admin' ) );
	}

	public static function render_tiers_field( $loop, $variation_data, $variation ): void {
		if ( ! CPT_Easy_Pricing::is_cpt_easy( $variation->ID ) ) {
			return;
		}
		woocommerce_wp_textarea_input(
			array(
				'id'            => "cpt_easy_tiers_{$loop}",
				'name'          => "cpt_easy_tiers[{$loop}]",
				'label'         => __( 'CPT Easy · precios por cantidad (sin IVA)', 'cpt-easy' ),
				'description'   => __( 'Una línea por tramo: cantidad mínima = precio por unidad. Ej.: 10=8.50 / 25=6.95 / 50=4.95 / 100=3.95. El precio normal de la variación se actualiza con el primer tramo.', 'cpt-easy' ),
				'desc_tip'      => false,
				'value'         => CPT_Easy_Pricing::format( CPT_Easy_Pricing::get_tiers( $variation->ID ) ),
				'wrapper_class' => 'form-row form-row-full',
				'rows'          => 5,
			)
		);
	}

	public static function save_tiers_field( $variation_id, $loop ): void {
		if ( ! isset( $_POST['cpt_easy_tiers'][ $loop ] ) || ! CPT_Easy_Pricing::is_cpt_easy( $variation_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- WooCommerce verifies the variations nonce.
			return;
		}
		$text = sanitize_textarea_field( wp_unslash( $_POST['cpt_easy_tiers'][ $loop ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		CPT_Easy_Pricing::save_tiers( (int) $variation_id, CPT_Easy_Pricing::parse( $text ) );
	}

	public static function block_add_to_cart( $passed, $product_id ) {
		if ( CPT_Easy_Pricing::is_cpt_easy( (int) $product_id ) ) {
			wc_add_notice( __( 'Este producto se pide desde el formulario CPT Easy.', 'cpt-easy' ), 'error' );
			return false;
		}
		return $passed;
	}

	public static function show_nif_in_admin( $order ): void {
		$nif = $order->get_meta( CPT_Easy_Order::META_NIF );
		if ( $nif && ! CPT_Easy_Order::apg_active() ) {
			printf( '<p><strong>%s:</strong> %s</p>', esc_html__( 'NIF / CIF', 'cpt-easy' ), esc_html( $nif ) );
		}
	}
}
