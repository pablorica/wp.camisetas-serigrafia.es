<?php
/**
 * Creates the WooCommerce order from a validated CPT Easy request,
 * and shows the CPT Easy data (NIF, files) in the admin and the emails.
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

class CPT_Easy_Order {

	const META_NIF      = '_billing_nif';
	const USER_NIF      = 'billing_nif';
	const META_FLAG     = '_cpt_easy';
	const CREATED_VIA   = 'cpt-easy';
	const ITEM_COLOR    = 'Color';
	const ITEM_SIZES    = 'Tallas';
	const ITEM_DESIGN   = 'Diseño';
	const ITEM_MOCKUP   = 'Mockup';

	/**
	 * True when "WC – APG NIF/CIF/NIE Field" is active. It adds the NIF to the checkout, My Account
	 * and the formatted addresses (admin, emails), so CPT Easy doesn't print it a second time.
	 * Both plugins use the same keys: order meta _billing_nif, user meta billing_nif.
	 */
	public static function apg_active(): bool {
		return class_exists( 'APG_Campo_NIF_en_Direcciones' );
	}

	/** Customer NIF: classic checkout / My Account (billing_nif) or Checkout block (_wc_billing/apg/nif). */
	public static function customer_nif( int $user_id ): string {
		foreach ( array( self::USER_NIF, '_' . self::USER_NIF, '_wc_billing/apg/nif' ) as $key ) {
			$nif = (string) get_user_meta( $user_id, $key, true );
			if ( '' !== $nif ) {
				return $nif;
			}
		}
		return '';
	}

	public static function init(): void {
		add_filter( 'woocommerce_order_item_display_meta_value', array( __CLASS__, 'link_files' ), 10, 2 );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'email_nif' ), 10, 3 );
	}

	/**
	 * @param array $data  Validated, sanitised fields from CPT_Easy_Form::validate().
	 * @param array $files [ 'diseno' => url, 'mockup' => url|null ].
	 * @return WC_Order|WP_Error
	 */
	public static function create( array $data, array $files ) {
		$variation_id = CPT_Easy_Pricing::find_variation_id( $data['calidad'], $data['impresion'] );
		$variation    = $variation_id ? wc_get_product( $variation_id ) : null;
		$unit         = $variation_id ? CPT_Easy_Pricing::unit_price( $variation_id, $data['cantidad'] ) : null;

		if ( ! $variation || null === $unit ) {
			return new WP_Error( 'cpt_easy_price', __( 'No hemos podido calcular el precio. Revisa la calidad, la impresión y la cantidad.', 'cpt-easy' ) );
		}

		try {
			$order = wc_create_order(
				array(
					'customer_id' => get_current_user_id(),
					'created_via' => self::CREATED_VIA,
				)
			);
			if ( is_wp_error( $order ) ) {
				return $order;
			}

			$line_total = CPT_Easy_Pricing::totals( $unit, $data['cantidad'], 0 )['net'];
			$item_id    = $order->add_product(
				$variation,
				$data['cantidad'],
				array(
					'subtotal' => $line_total,
					'total'    => $line_total,
				)
			);
			// The order's own item object (get_item() would load a separate copy from the database).
			$item = $order->get_items()[ $item_id ] ?? $order->get_item( $item_id );
			$item->add_meta_data( self::ITEM_COLOR, $data['color'], true );
			$item->add_meta_data( self::ITEM_SIZES, $data['tallas'], true );
			$item->add_meta_data( self::ITEM_DESIGN, $files['diseno'], true );
			if ( ! empty( $files['mockup'] ) ) {
				$item->add_meta_data( self::ITEM_MOCKUP, $files['mockup'], true );
			}
			$item->save();

			$billing = self::address(
				$data['fac_nombre'],
				$data['fac_nif'],
				$data['fac_direccion'],
				$data['fac_cp'],
				$data['fac_poblacion'],
				$data['telefono']
			);
			$billing['email'] = $data['email'];
			$order->set_address( $billing, 'billing' );

			if ( $data['envio_igual'] ) {
				$shipping = $billing;
			} else {
				$shipping = self::address(
					$data['env_nombre'],
					'',
					$data['env_direccion'],
					$data['env_cp'],
					$data['env_poblacion'],
					$data['env_telefono']
				);
			}
			unset( $shipping['email'] );
			$order->set_address( $shipping, 'shipping' );

			$order->update_meta_data( self::META_NIF, $data['fac_nif'] );

			if ( $order->get_customer_id() ) {
				self::save_to_customer( $order, $data['fac_nif'] );
			}
			$order->update_meta_data( self::META_FLAG, CPT_EASY_VERSION );

			// Addresses first: WooCommerce works out the VAT from them.
			$order->calculate_totals( true );
			$order->set_status( 'pending' );
			$order->save();
			$order->add_order_note( __( 'Pedido creado desde el formulario CPT Easy, pendiente de pago.', 'cpt-easy' ) );

			/**
			 * Send the customer the "pay for this order" email (WooCommerce "Customer invoice"),
			 * so they can pay later if they leave the payment page.
			 */
			if ( apply_filters( 'cpt_easy_send_invoice_email', true, $order ) ) {
				$emails = WC()->mailer()->get_emails();
				if ( isset( $emails['WC_Email_Customer_Invoice'] ) ) {
					$emails['WC_Email_Customer_Invoice']->trigger( $order->get_id(), $order );
				}
			}

			do_action( 'cpt_easy_order_created', $order, $data, $files );

			return $order;
		} catch ( Throwable $e ) {
			wc_get_logger()->error( $e->getMessage(), array( 'source' => 'cpt-easy' ) );
			if ( isset( $order ) && $order instanceof WC_Order ) {
				$order->delete( true );
			}
			return new WP_Error( 'cpt_easy_order', __( 'No hemos podido crear el pedido. Inténtalo de nuevo o escríbenos.', 'cpt-easy' ) );
		}
	}

	/**
	 * Logged-in customer: keep the addresses and NIF in their account, so the next
	 * CPT Easy form (and the normal checkout) comes pre-filled.
	 */
	private static function save_to_customer( WC_Order $order, string $nif ): void {
		$customer = new WC_Customer( $order->get_customer_id() );
		foreach ( array( 'billing', 'shipping' ) as $type ) {
			foreach ( $order->get_address( $type ) as $key => $value ) {
				$setter = "set_{$type}_{$key}";
				if ( is_callable( array( $customer, $setter ) ) ) {
					$customer->{$setter}( $value );
				}
			}
		}
		$customer->save();
		update_user_meta( $order->get_customer_id(), self::USER_NIF, $nif );
		// Keep the Checkout block copy in sync if the customer has one.
		if ( metadata_exists( 'user', $order->get_customer_id(), '_wc_billing/apg/nif' ) ) {
			update_user_meta( $order->get_customer_id(), '_wc_billing/apg/nif', $nif );
		}
	}

	/**
	 * "Población y provincia" is one field in the form: it goes to city; the province is taken from the postcode.
	 */
	private static function address( string $name, string $nif, string $street, string $postcode, string $city, string $phone ): array {
		// A CIF (company) starts with a letter other than X/Y/Z (NIE) or K/L/M.
		$is_company = (bool) preg_match( '/^[ABCDEFGHJNPQRSUVW]/i', $nif );

		return array(
			'first_name' => $is_company ? '' : $name,
			'last_name'  => '',
			'company'    => $is_company ? $name : '',
			'address_1'  => $street,
			'address_2'  => '',
			'city'       => $city,
			'state'      => self::province_from_postcode( $postcode ),
			'postcode'   => $postcode,
			'country'    => 'ES',
			'phone'      => $phone,
			'email'      => '',
		);
	}

	/** Spanish postcode → WooCommerce state code (first two digits = province). */
	public static function province_from_postcode( string $postcode ): string {
		$map = array(
			'01' => 'VI', '02' => 'AB', '03' => 'A', '04' => 'AL', '05' => 'AV', '06' => 'BA', '07' => 'PM',
			'08' => 'B', '09' => 'BU', '10' => 'CC', '11' => 'CA', '12' => 'CS', '13' => 'CR', '14' => 'CO',
			'15' => 'C', '16' => 'CU', '17' => 'GI', '18' => 'GR', '19' => 'GU', '20' => 'SS', '21' => 'H',
			'22' => 'HU', '23' => 'J', '24' => 'LE', '25' => 'L', '26' => 'LO', '27' => 'LU', '28' => 'M',
			'29' => 'MA', '30' => 'MU', '31' => 'NA', '32' => 'OR', '33' => 'O', '34' => 'P', '35' => 'GC',
			'36' => 'PO', '37' => 'SA', '38' => 'TF', '39' => 'S', '40' => 'SG', '41' => 'SE', '42' => 'SO',
			'43' => 'T', '44' => 'TE', '45' => 'TO', '46' => 'V', '47' => 'VA', '48' => 'BI', '49' => 'ZA',
			'50' => 'Z', '51' => 'CE', '52' => 'ML',
		); // phpcs:ignore
		return $map[ substr( $postcode, 0, 2 ) ] ?? '';
	}

	/** Show the uploaded files as links in the order admin, My Account and emails. */
	public static function link_files( $display_value, $meta ) {
		if ( in_array( $meta->key, array( self::ITEM_DESIGN, self::ITEM_MOCKUP ), true ) ) {
			$url = esc_url( $meta->value );
			if ( $url ) {
				return sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', $url, esc_html( wp_basename( $meta->value ) ) );
			}
		}
		return $display_value;
	}

	public static function email_nif( $order, $sent_to_admin, $plain_text ): void {
		$nif = $order->get_meta( self::META_NIF );
		if ( ! $nif || self::apg_active() ) {
			return;
		}
		if ( $plain_text ) {
			echo "\n" . esc_html__( 'NIF / CIF', 'cpt-easy' ) . ': ' . esc_html( $nif ) . "\n";
		} else {
			printf( '<p><strong>%s:</strong> %s</p>', esc_html__( 'NIF / CIF', 'cpt-easy' ), esc_html( $nif ) );
		}
	}
}
