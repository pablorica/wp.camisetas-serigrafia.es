<?php
/**
 * Landing helpers: price lists rendered from the product, and payment methods for CPT Easy orders.
 *
 *   [cpt_easy_precios calidad="Basic"]  → <ul class="cpt-rows"> with the 1-cara (.c1) and 2-caras (.c2) price per tier.
 *   [cpt_easy_minimo]                   → minimum quantity (10).
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

class CPT_Easy_Landing {

	public static function init(): void {
		add_shortcode( 'cpt_easy_precios', array( __CLASS__, 'price_rows' ) );
		add_shortcode( 'cpt_easy_minimo', static fn() => (string) CPT_Easy_Pricing::min_qty() );
		add_filter( 'woocommerce_available_payment_gateways', array( __CLASS__, 'payment_gateways' ) );
	}

	public static function price_rows( $atts ): string {
		$atts    = shortcode_atts( array( 'calidad' => 'Medium' ), $atts, 'cpt_easy_precios' );
		$quality = in_array( $atts['calidad'], CPT_Easy_Pricing::QUALITIES, true ) ? $atts['calidad'] : 'Medium';
		$matrix  = CPT_Easy_Pricing::matrix();
		$one     = $matrix[ $quality ]['1 cara'] ?? array();
		$two     = $matrix[ $quality ]['2 caras'] ?? array();

		// Rows = every minimum used by either print, so both columns always line up.
		$mins = array_unique( array_merge( array_column( $one, 'min' ), array_column( $two, 'min' ) ) );
		sort( $mins );
		if ( ! $mins ) {
			return '';
		}

		$price_at = static function ( array $tiers, int $qty ): ?float {
			$price = null;
			foreach ( $tiers as $t ) {
				if ( $qty >= $t['min'] ) {
					$price = $t['price'];
				}
			}
			return $price;
		};
		$fmt = static fn( ?float $p ) => null === $p ? '—' : number_format( $p, 2, ',', '.' ) . ' €';

		$html = '<ul class="cpt-rows">';
		foreach ( $mins as $i => $min ) {
			$next  = $mins[ $i + 1 ] ?? null;
			/* translators: 1: from, 2: to */
			$label = $next ? sprintf( __( '%1$d a %2$d uds.', 'cpt-easy' ), $min, $next - 1 ) : sprintf( __( '%d o más', 'cpt-easy' ), $min );
			$html .= sprintf(
				'<li><span>%s</span><b><span class="c1">%s</span><span class="c2">%s</span></b></li>',
				esc_html( $label ),
				esc_html( $fmt( $price_at( $one, $min ) ) ),
				esc_html( $fmt( $price_at( $two, $min ) ) )
			);
		}
		return $html . '</ul>';
	}

	/**
	 * On the payment page of a CPT Easy order, hide methods that make no sense for a
	 * custom-printed order (cash on delivery, cheque). Change the list with `cpt_easy_excluded_gateways`.
	 */
	public static function payment_gateways( $gateways ) {
		if ( is_admin() || ! is_array( $gateways ) || ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'order-pay' ) ) {
			return $gateways;
		}
		$order = wc_get_order( absint( get_query_var( 'order-pay' ) ) );
		if ( ! $order || CPT_Easy_Order::CREATED_VIA !== $order->get_created_via() ) {
			return $gateways;
		}
		foreach ( (array) apply_filters( 'cpt_easy_excluded_gateways', array( 'cod', 'cheque' ), $order ) as $id ) {
			unset( $gateways[ $id ] );
		}
		return $gateways;
	}
}
