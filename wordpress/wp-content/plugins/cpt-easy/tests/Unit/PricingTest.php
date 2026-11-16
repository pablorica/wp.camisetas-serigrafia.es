<?php
/**
 * Pricing logic, without WordPress.
 *
 * @package cpt-easy
 */

use PHPUnit\Framework\TestCase;

final class PricingTest extends TestCase {

	/** Per-unit prices without VAT published on the landing (1 cara / 2 caras), 2026-11-16. */
	private const LANDING = array(
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

	private static function tiers( array $min_price ): array {
		$tiers = array();
		foreach ( $min_price as $min => $price ) {
			$tiers[] = array(
				'min'   => $min,
				'price' => $price,
			);
		}
		return $tiers;
	}

	public function test_install_prices_match_the_landing(): void {
		$this->assertSame( self::LANDING, CPT_Easy_Pricing::default_tiers() );
	}

	public function test_every_combination_has_a_price_list(): void {
		foreach ( CPT_Easy_Pricing::QUALITIES as $quality ) {
			foreach ( CPT_Easy_Pricing::PRINTS as $print ) {
				$this->assertArrayHasKey( $print, CPT_Easy_Pricing::default_tiers()[ $quality ], "$quality / $print" );
			}
		}
	}

	public function test_price_changes_exactly_at_each_tier(): void {
		// Quantity => tier minimum that must apply.
		$cases = array(
			10   => 10,
			11   => 10,
			24   => 10,
			25   => 25,
			49   => 25,
			50   => 50,
			99   => 50,
			100  => 100,
			101  => 100,
			5000 => 100,
		);
		foreach ( self::LANDING as $quality => $prints ) {
			foreach ( $prints as $print => $min_price ) {
				$tiers = self::tiers( $min_price );
				foreach ( $cases as $qty => $min ) {
					$this->assertSame( $min_price[ $min ], CPT_Easy_Pricing::price_at( $tiers, $qty ), "$quality / $print × $qty" );
				}
			}
		}
	}

	public function test_no_price_below_the_minimum(): void {
		$tiers = self::tiers( self::LANDING['Medium']['1 cara'] );
		foreach ( array( -5, 0, 1, 9 ) as $qty ) {
			$this->assertNull( CPT_Easy_Pricing::price_at( $tiers, $qty ), "× $qty" );
		}
		$this->assertNull( CPT_Easy_Pricing::price_at( array(), 50 ), 'no tiers' );
	}

	public function test_tiers_in_any_order_give_the_same_price(): void {
		$sorted   = self::tiers( self::LANDING['Basic']['1 cara'] );
		$shuffled = array( $sorted[2], $sorted[0], $sorted[3], $sorted[1] );
		foreach ( array( 10, 30, 75, 300 ) as $qty ) {
			$this->assertSame( CPT_Easy_Pricing::price_at( $sorted, $qty ), CPT_Easy_Pricing::price_at( $shuffled, $qty ), "× $qty" );
		}
	}

	public function test_parse_the_admin_box(): void {
		// What an editor may type in the variation box.
		$text = "10=8.50\n25: 6,95\r\n 50 ; 4.95 \nbasura\n100=3\n0=1\n30=0";
		$this->assertSame(
			array(
				array( 'min' => 10, 'price' => 8.5 ),
				array( 'min' => 25, 'price' => 6.95 ),
				array( 'min' => 50, 'price' => 4.95 ),
				array( 'min' => 100, 'price' => 3.0 ),
			),
			CPT_Easy_Pricing::parse( $text )
		);
	}

	public function test_normalise_keeps_one_tier_per_minimum_sorted(): void {
		$this->assertSame(
			array(
				array( 'min' => 10, 'price' => 9.0 ),
				array( 'min' => 50, 'price' => 5.0 ),
			),
			CPT_Easy_Pricing::normalise(
				array(
					array( 'min' => 50, 'price' => 5 ),
					array( 'min' => 10, 'price' => 8 ),
					array( 'min' => 10, 'price' => 9 ), // Last one wins.
					array( 'min' => -1, 'price' => 2 ),
					array( 'price' => 2 ),
				)
			)
		);
	}

	public function test_totals_with_vat(): void {
		// unit, qty, vat %, expected net, VAT, total.
		$cases = array(
			'Medium 1 cara × 50 (form default)' => array( 5.95, 50, 21, 297.50, 62.48, 359.98 ), // 62.475 → 62.48
			'Medium 2 caras × 30'               => array( 9.90, 30, 21, 297.00, 62.37, 359.37 ),
			'Premium 2 caras × 80'              => array( 7.95, 80, 21, 636.00, 133.56, 769.56 ),
			'Basic 1 cara × 120'                => array( 3.95, 120, 21, 474.00, 99.54, 573.54 ),
			'Basic 1 cara × 10'                 => array( 8.50, 10, 21, 85.00, 17.85, 102.85 ),
			'Premium 1 cara × 33'               => array( 9.90, 33, 21, 326.70, 68.61, 395.31 ),
			'No VAT (e.g. Canarias)'            => array( 4.95, 100, 0, 495.00, 0.00, 495.00 ),
		);
		foreach ( $cases as $label => [ $unit, $qty, $rate, $net, $vat, $total ] ) {
			$this->assertSame(
				array(
					'net'   => $net,
					'vat'   => $vat,
					'total' => $total,
				),
				CPT_Easy_Pricing::totals( $unit, $qty, $rate ),
				$label
			);
		}
	}

	public function test_cents_never_drift_over_the_whole_range(): void {
		// net + VAT must equal total to the cent for every quantity of every list.
		foreach ( self::LANDING as $prints ) {
			foreach ( $prints as $min_price ) {
				$tiers = self::tiers( $min_price );
				for ( $qty = 10; $qty <= 1000; $qty++ ) {
					$t = CPT_Easy_Pricing::totals( CPT_Easy_Pricing::price_at( $tiers, $qty ), $qty, 21 );
					$this->assertSame( round( $t['net'] + $t['vat'], 2 ), $t['total'] );
					$this->assertSame( $t['net'], round( $t['net'], 2 ) );
				}
			}
		}
	}
}
