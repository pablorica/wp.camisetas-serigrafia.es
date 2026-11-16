<?php
/**
 * Product lookup and tiered pricing for CPT Easy.
 *
 * Each variation (Calidad × Impresión) stores its quantity tiers in the
 * `_cpt_easy_tiers` meta as [ [ 'min' => 10, 'price' => 8.5 ], ... ].
 * Prices are per unit and WITHOUT VAT (the shop is set to "prices exclude tax").
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

class CPT_Easy_Pricing {

	const META_TIERS = '_cpt_easy_tiers';

	/** Local (product-level) attribute names. Not global pa_ taxonomies, so they stay out of the shop filters. */
	const ATTR_QUALITY = 'Calidad';
	const ATTR_PRINT   = 'Impresión';

	const QUALITIES = array( 'Basic', 'Medium', 'Premium' );
	const PRINTS    = array( '1 cara', '2 caras' );

	const MAX_QTY = 5000;

	/**
	 * Prices from the landing prototype (home-nueva-no-publicar), used by `wp cpt-easy install`.
	 *
	 * @return array<string, array<string, array<int, float>>> quality => print => [ min_qty => unit_price ]
	 */
	public static function default_tiers(): array {
		return array(
			'Basic'   => array(
				'1 cara'  => array( 10 => 8.50, 25 => 6.95, 50 => 4.95, 100 => 3.95 ),
				'2 caras' => array( 10 => 9.90, 25 => 7.95, 50 => 5.95, 100 => 4.95 ),
			),
			'Medium'  => array(
				'1 cara'  => array( 10 => 9.90, 25 => 8.50, 50 => 5.95, 100 => 4.95 ),
				'2 caras' => array( 10 => 11.90, 25 => 9.90, 50 => 6.95, 100 => 5.95 ),
			),
			'Premium' => array(
				'1 cara'  => array( 10 => 11.90, 25 => 9.90, 50 => 6.95, 100 => 5.95 ),
				'2 caras' => array( 10 => 13.90, 25 => 11.90, 50 => 7.95, 100 => 6.95 ),
			),
		);
	}

	/** Attribute key as stored on variations, e.g. attribute_calidad. */
	public static function attr_key( string $name ): string {
		return 'attribute_' . sanitize_title( $name );
	}

	public static function get_product_id(): int {
		return (int) wc_get_product_id_by_sku( CPT_EASY_SKU );
	}

	public static function get_product(): ?WC_Product_Variable {
		$id      = self::get_product_id();
		$product = $id ? wc_get_product( $id ) : null;
		return $product instanceof WC_Product_Variable ? $product : null;
	}

	/** True if the product (or variation) belongs to CPT Easy. */
	public static function is_cpt_easy( $product ): bool {
		$product = $product instanceof WC_Product ? $product : wc_get_product( $product );
		if ( ! $product ) {
			return false;
		}
		$parent_id = $product->get_parent_id() ?: $product->get_id();
		return $parent_id === self::get_product_id();
	}

	public static function find_variation_id( string $quality, string $print ): int {
		$product = self::get_product();
		if ( ! $product || ! in_array( $quality, self::QUALITIES, true ) || ! in_array( $print, self::PRINTS, true ) ) {
			return 0;
		}
		$data_store = WC_Data_Store::load( 'product' );
		return (int) $data_store->find_matching_product_variation(
			$product,
			array(
				self::attr_key( self::ATTR_QUALITY ) => $quality,
				self::attr_key( self::ATTR_PRINT )   => $print,
			)
		);
	}

	/**
	 * @return array<int, array{min:int, price:float}> sorted by min ascending.
	 */
	public static function get_tiers( int $variation_id ): array {
		$tiers = get_post_meta( $variation_id, self::META_TIERS, true );
		return is_array( $tiers ) ? self::normalise( $tiers ) : array();
	}

	public static function save_tiers( int $variation_id, array $tiers ): void {
		$tiers = self::normalise( $tiers );
		update_post_meta( $variation_id, self::META_TIERS, $tiers );

		// Keep the WooCommerce price = first tier, so "desde X €" and reports make sense.
		if ( $tiers ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation ) {
				$variation->set_regular_price( (string) $tiers[0]['price'] );
				$variation->save();
			}
		}
	}

	/** Unit price for a quantity, or null if below the minimum / no tiers. */
	public static function unit_price( int $variation_id, int $qty ): ?float {
		return self::price_at( self::get_tiers( $variation_id ), $qty );
	}

	/**
	 * Unit price of the highest tier whose minimum is <= $qty; null below the first tier.
	 * Pure function (no WordPress), covered by tests/Unit.
	 *
	 * @param array<int, array{min:int, price:float}> $tiers
	 */
	public static function price_at( array $tiers, int $qty ): ?float {
		$price = null;
		foreach ( self::normalise( $tiers ) as $tier ) {
			if ( $qty >= $tier['min'] ) {
				$price = $tier['price'];
			}
		}
		return $price;
	}

	/**
	 * Order totals for a unit price, quantity and VAT rate (%), rounded to cents the way
	 * WooCommerce rounds a single line: net = unit × qty, VAT = net × rate. The form shows
	 * the same numbers (assets/cpt-easy.js). Pure function, covered by tests/Unit.
	 *
	 * @return array{net: float, vat: float, total: float}
	 */
	public static function totals( float $unit, int $qty, float $vat_rate ): array {
		$net = round( $unit * $qty, 2 );
		$vat = round( $net * $vat_rate / 100, 2 );
		return array(
			'net'   => $net,
			'vat'   => $vat,
			'total' => round( $net + $vat, 2 ),
		);
	}

	/** Lowest minimum quantity across all variations (10 by default). */
	public static function min_qty(): int {
		$min = PHP_INT_MAX;
		foreach ( self::matrix() as $prints ) {
			foreach ( $prints as $tiers ) {
				if ( $tiers ) {
					$min = min( $min, $tiers[0]['min'] );
				}
			}
		}
		return PHP_INT_MAX === $min ? 10 : $min;
	}

	/**
	 * Full price matrix read from the variations.
	 *
	 * @return array<string, array<string, array>> quality => print => tiers
	 */
	public static function matrix(): array {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}
		$cache = array();
		foreach ( self::QUALITIES as $quality ) {
			foreach ( self::PRINTS as $print ) {
				$id                          = self::find_variation_id( $quality, $print );
				$cache[ $quality ][ $print ] = $id ? self::get_tiers( $id ) : array();
			}
		}
		return $cache;
	}

	/** Standard VAT rate for Spain from the WooCommerce tax table (21 if none). */
	public static function vat_rate(): float {
		if ( ! wc_tax_enabled() ) {
			return 0.0;
		}
		$rates = WC_Tax::find_rates(
			array(
				'country'   => 'ES',
				'state'     => '',
				'postcode'  => '',
				'city'      => '',
				'tax_class' => '',
			)
		);
		$rate = $rates ? reset( $rates ) : null;
		return $rate ? (float) $rate['rate'] : 21.0;
	}

	/** "10=8.50\n25=6.95" → tiers. Accepts "=", ":" or ";" and decimal comma. */
	public static function parse( string $text ): array {
		$tiers = array();
		foreach ( preg_split( '/\R/', $text ) as $line ) {
			if ( preg_match( '/^\s*(\d+)\s*[=:;]\s*([\d.,]+)\s*$/', $line, $m ) ) {
				$tiers[] = array(
					'min'   => (int) $m[1],
					'price' => (float) str_replace( ',', '.', $m[2] ),
				);
			}
		}
		return self::normalise( $tiers );
	}

	public static function format( array $tiers ): string {
		return implode(
			"\n",
			array_map(
				static fn( $t ) => $t['min'] . '=' . wc_format_decimal( $t['price'], 2 ),
				self::normalise( $tiers )
			)
		);
	}

	/** Valid tiers only (min > 0, price > 0), one per minimum, sorted by minimum. */
	public static function normalise( array $tiers ): array {
		$clean = array();
		foreach ( $tiers as $tier ) {
			$min   = (int) ( $tier['min'] ?? 0 );
			$price = (float) ( $tier['price'] ?? 0 );
			if ( $min > 0 && $price > 0 ) {
				$clean[ $min ] = array(
					'min'   => $min,
					'price' => round( $price, 4 ),
				);
			}
		}
		ksort( $clean );
		return array_values( $clean );
	}
}
