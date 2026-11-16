<?php
/**
 * CPT Easy orders on the real local site: the order must charge exactly what the
 * product tiers say and what the form shows (net + VAT), for every combination.
 *
 * Every test runs in a transaction that is rolled back: no orders are kept.
 *
 * @package cpt-easy
 */

use PHPUnit\Framework\TestCase;

final class OrderPricingTest extends TestCase {

	/** Quantities around every tier boundary. */
	private const QUANTITIES = array( 10, 24, 25, 49, 50, 99, 100, 250 );

	/** Mainland Spain address (standard VAT). */
	private const ADDRESS = array(
		'fac_nombre'    => 'Pruebas CPT Easy SL',
		'fac_nif'       => 'B12345678',
		'email'         => 'cpt-easy-tests@example.com',
		'telefono'      => '600000000',
		'fac_direccion' => 'Calle de Prueba 1',
		'fac_cp'        => '28013',
		'fac_poblacion' => 'Madrid',
		'envio_igual'   => true,
		'env_nombre'    => '',
		'env_telefono'  => '',
		'env_direccion' => '',
		'env_cp'        => '',
		'env_poblacion' => '',
	);

	protected function setUp(): void {
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
		wp_set_current_user( 0 ); // Guest: nothing is saved to a customer account.
		add_filter( 'cpt_easy_send_invoice_email', '__return_false' );
		add_filter( 'pre_wp_mail', '__return_true' ); // No emails at all.

		if ( ! CPT_Easy_Pricing::get_product() ) {
			$this->markTestSkipped( 'No CPT Easy product (run: wp cpt-easy install).' );
		}
	}

	protected function tearDown(): void {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
		remove_filter( 'cpt_easy_send_invoice_email', '__return_false' );
		remove_filter( 'pre_wp_mail', '__return_true' );
	}

	private static function vat_rate(): float {
		if ( ! wc_tax_enabled() ) {
			return 0.0;
		}
		$rates = WC_Tax::find_rates(
			array(
				'country'   => 'ES',
				'state'     => CPT_Easy_Order::province_from_postcode( self::ADDRESS['fac_cp'] ),
				'postcode'  => self::ADDRESS['fac_cp'],
				'city'      => self::ADDRESS['fac_poblacion'],
				'tax_class' => '',
			)
		);
		return (float) array_sum( array_column( $rates, 'rate' ) );
	}

	private function order( string $quality, string $print, int $qty ): WC_Order {
		$data = self::ADDRESS + array(
			'calidad'   => $quality,
			'impresion' => $print,
			'cantidad'  => $qty,
			'color'     => 'negro',
			'tallas'    => "M $qty",
		);
		$order = CPT_Easy_Order::create( $data, array( 'diseno' => 'https://example.com/diseno.pdf', 'mockup' => null ) );
		$this->assertInstanceOf( WC_Order::class, $order, "$quality / $print × $qty: " . ( is_wp_error( $order ) ? $order->get_error_message() : '' ) );
		return $order;
	}

	public function test_every_combination_is_priced_from_the_product_tiers(): void {
		$rate = self::vat_rate();

		foreach ( CPT_Easy_Pricing::QUALITIES as $quality ) {
			foreach ( CPT_Easy_Pricing::PRINTS as $print ) {
				$variation_id = CPT_Easy_Pricing::find_variation_id( $quality, $print );
				$this->assertGreaterThan( 0, $variation_id, "Variation $quality / $print" );
				$tiers = CPT_Easy_Pricing::get_tiers( $variation_id );
				$this->assertNotEmpty( $tiers, "Tiers of $quality / $print" );

				foreach ( self::QUANTITIES as $qty ) {
					$label    = "$quality / $print × $qty";
					$unit     = CPT_Easy_Pricing::price_at( $tiers, $qty );
					$expected = CPT_Easy_Pricing::totals( $unit, $qty, $rate );
					$order    = $this->order( $quality, $print, $qty );

					$items = $order->get_items();
					$this->assertCount( 1, $items, $label );
					$item = reset( $items );

					$this->assertSame( $variation_id, $item->get_variation_id(), "$label: variation" );
					$this->assertSame( $qty, $item->get_quantity(), "$label: quantity" );
					$this->assertEqualsWithDelta( $expected['net'], (float) $item->get_subtotal(), 0.001, "$label: line subtotal" );
					$this->assertEqualsWithDelta( $expected['net'], (float) $item->get_total(), 0.001, "$label: line total" );
					$this->assertEqualsWithDelta( $expected['vat'], (float) $order->get_total_tax(), 0.001, "$label: VAT" );
					$this->assertEqualsWithDelta( $expected['total'], (float) $order->get_total(), 0.001, "$label: order total" );
					$this->assertSame( 'pending', $order->get_status(), "$label: status" );
					$this->assertTrue( $order->needs_payment(), "$label: needs payment" );
				}
			}
		}
	}

	public function test_order_keeps_the_customer_data(): void {
		$order = $this->order( 'Medium', '1 cara', 50 );
		$item  = current( $order->get_items() );

		$this->assertSame( 'negro', $item->get_meta( CPT_Easy_Order::ITEM_COLOR ) );
		$this->assertSame( 'M 50', $item->get_meta( CPT_Easy_Order::ITEM_SIZES ) );
		$this->assertSame( 'B12345678', $order->get_meta( CPT_Easy_Order::META_NIF ) );
		$this->assertSame( 'M', $order->get_billing_state() );
		$this->assertSame( 'cpt-easy', $order->get_created_via() );
	}

	public function test_the_form_uses_the_same_prices_and_vat(): void {
		$js = CPT_Easy_Form::js_data();
		$this->assertSame( CPT_Easy_Pricing::matrix(), $js['prices'], 'window.cptEasy.prices' );
		$this->assertEqualsWithDelta( self::vat_rate(), $js['vat'], 0.0001, 'window.cptEasy.vat (mainland)' );
	}

	public function test_the_landing_shows_the_product_prices(): void {
		foreach ( CPT_Easy_Pricing::QUALITIES as $quality ) {
			$html = CPT_Easy_Landing::price_rows( array( 'calidad' => $quality ) );
			foreach ( CPT_Easy_Pricing::PRINTS as $i => $print ) {
				$class = 0 === $i ? 'c1' : 'c2';
				foreach ( CPT_Easy_Pricing::matrix()[ $quality ][ $print ] as $tier ) {
					$price = number_format( $tier['price'], 2, ',', '.' ) . ' €';
					$this->assertStringContainsString( '<span class="' . $class . '">' . $price . '</span>', $html, "$quality / $print from {$tier['min']}" );
				}
			}
		}
	}

	public function test_quantities_outside_the_limits_are_rejected(): void {
		$base = self::ADDRESS + array(
			'cpt_calidad_ui' => 'Basic',
			'impresion'      => '1 cara',
			'color'          => 'blanco',
			'tallas'         => 'M',
			'privacidad'     => '1',
		);
		foreach ( array( CPT_Easy_Pricing::min_qty() - 1, CPT_Easy_Pricing::MAX_QTY + 1 ) as $qty ) {
			[ , $errors ] = CPT_Easy_Form::validate( $base + array( 'cantidad' => $qty ) );
			$this->assertArrayHasKey( 'cantidad', $errors, "× $qty" );
		}
		[ , $errors ] = CPT_Easy_Form::validate( $base + array( 'cantidad' => CPT_Easy_Pricing::min_qty() ) );
		$this->assertArrayNotHasKey( 'cantidad', $errors, 'minimum is accepted' );
	}
}
