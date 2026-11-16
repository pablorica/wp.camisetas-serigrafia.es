<?php
/**
 * WP-CLI: create / repair the CPT Easy product and show its prices.
 *
 *   docker compose run --rm wpcli cpt-easy install
 *   docker compose run --rm wpcli cpt-easy install --reset-prices
 *   docker compose run --rm wpcli cpt-easy prices
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

class CPT_Easy_CLI {

	/**
	 * Creates the "CPT Easy" variable product (SKU CPT-EASY) with its 6 variations.
	 * Safe to run again: it only adds what is missing.
	 *
	 * ## OPTIONS
	 *
	 * [--reset-prices]
	 * : Overwrite the tiers of every variation with the landing prices.
	 *
	 * [--name=<name>]
	 * : Product name when creating it.
	 * ---
	 * default: CPT Easy
	 * ---
	 */
	public function install( $args, $assoc ) {
		$reset   = ! empty( $assoc['reset-prices'] );
		$product = CPT_Easy_Pricing::get_product();

		if ( ! $product ) {
			$product = new WC_Product_Variable();
			$product->set_name( $assoc['name'] ?? 'CPT Easy' );
			$product->set_sku( CPT_EASY_SKU );
			$product->set_status( 'publish' );
			$product->set_short_description( 'Camisetas personalizadas con impresión DTF a todo color, hasta A4. Pedido mínimo 10 unidades.' );
			WP_CLI::log( 'Creating product…' );
		} else {
			WP_CLI::log( "Product found: #{$product->get_id()}" );
		}

		// Only sold through the form: hidden from shop, search and feeds; no stock control.
		$product->set_catalog_visibility( 'hidden' );
		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );
		$product->set_tax_status( 'taxable' );
		$product->set_sold_individually( false );

		$attributes = array();
		foreach ( array(
			CPT_Easy_Pricing::ATTR_QUALITY => CPT_Easy_Pricing::QUALITIES,
			CPT_Easy_Pricing::ATTR_PRINT   => CPT_Easy_Pricing::PRINTS,
		) as $name => $options ) {
			$attr = new WC_Product_Attribute();
			$attr->set_id( 0 ); // Local attribute, not a pa_ taxonomy.
			$attr->set_name( $name );
			$attr->set_options( $options );
			$attr->set_visible( true );
			$attr->set_variation( true );
			$attributes[ sanitize_title( $name ) ] = $attr;
		}
		$product->set_attributes( $attributes );
		$product->set_default_attributes(
			array(
				sanitize_title( CPT_Easy_Pricing::ATTR_QUALITY ) => 'Medium',
				sanitize_title( CPT_Easy_Pricing::ATTR_PRINT )   => '1 cara',
			)
		);
		$product_id = $product->save();

		$defaults = CPT_Easy_Pricing::default_tiers();
		foreach ( CPT_Easy_Pricing::QUALITIES as $quality ) {
			foreach ( CPT_Easy_Pricing::PRINTS as $print ) {
				$variation_id = CPT_Easy_Pricing::find_variation_id( $quality, $print );
				$is_new       = ! $variation_id;

				if ( $is_new ) {
					$variation = new WC_Product_Variation();
					$variation->set_parent_id( $product_id );
					$variation->set_attributes(
						array(
							sanitize_title( CPT_Easy_Pricing::ATTR_QUALITY ) => $quality,
							sanitize_title( CPT_Easy_Pricing::ATTR_PRINT )   => $print,
						)
					);
					$variation->set_sku( CPT_EASY_SKU . '-' . strtoupper( $quality ) . '-' . ( '2 caras' === $print ? '2C' : '1C' ) );
					$variation->set_status( 'publish' );
					$variation->set_manage_stock( false );
					$variation_id = $variation->save();
				}

				if ( $is_new || $reset || ! CPT_Easy_Pricing::get_tiers( $variation_id ) ) {
					$tiers = array();
					foreach ( $defaults[ $quality ][ $print ] as $min => $price ) {
						$tiers[] = array(
							'min'   => $min,
							'price' => $price,
						);
					}
					CPT_Easy_Pricing::save_tiers( $variation_id, $tiers );
				}

				WP_CLI::log( sprintf( '  %-8s %-8s #%d%s', $quality, $print, $variation_id, $is_new ? ' (new)' : '' ) );
			}
		}

		WC_Product_Variable::sync( $product_id );
		wc_delete_product_transients( $product_id );

		WP_CLI::success( "CPT Easy ready: product #{$product_id}. Put [cpt_easy_form] on the landing page." );
	}

	/**
	 * Prints the price matrix (per unit, without VAT).
	 */
	public function prices() {
		$rows = array();
		foreach ( CPT_Easy_Pricing::matrix() as $quality => $prints ) {
			foreach ( $prints as $print => $tiers ) {
				$rows[] = array(
					'calidad'   => $quality,
					'impresion' => $print,
					'tramos'    => implode( '  ', array_map( static fn( $t ) => "{$t['min']}+ {$t['price']}€", $tiers ) ),
				);
			}
		}
		WP_CLI\Utils\format_items( 'table', $rows, array( 'calidad', 'impresion', 'tramos' ) );
		WP_CLI::log( 'IVA: ' . CPT_Easy_Pricing::vat_rate() . '%  ·  mínimo: ' . CPT_Easy_Pricing::min_qty() . ' uds.' );
	}
}
