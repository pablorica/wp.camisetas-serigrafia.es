<?php
/**
 * Frontend: [cpt_easy_form] shortcode, AJAX submission, validation and file uploads.
 *
 * Field names and CSS classes are the same as the Contact Form 7 prototype on
 * /home-nueva-no-publicar/, so the existing .cpt-f-* styles keep working.
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

class CPT_Easy_Form {

	const ACTION       = 'cpt_easy_order';
	const NONCE        = 'cpt_easy_order';
	const UPLOAD_DIR   = 'cpt-easy';
	const MAX_FILE_MB  = 25;
	const RATE_LIMIT   = 5;   // orders…
	const RATE_WINDOW  = 3600; // …per IP per hour.
	const RECAPTCHA_MIN_SCORE = 0.5;

	/** Set while our own upload runs, so the file-type filter only relaxes checks for it. */
	private static bool $uploading = false;

	public static function init(): void {
		add_shortcode( 'cpt_easy_form', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'wp_ajax_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'allow_design_files' ), 10, 4 );
	}

	/* ---------------------------------------------------------------------
	 * Rendering
	 * ------------------------------------------------------------------ */

	public static function register_assets(): void {
		wp_register_script(
			'cpt-easy',
			CPT_EASY_URL . 'assets/cpt-easy.js',
			array(),
			(string) filemtime( CPT_EASY_PATH . 'assets/cpt-easy.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_register_style(
			'cpt-easy',
			CPT_EASY_URL . 'assets/cpt-easy.css',
			array(),
			(string) filemtime( CPT_EASY_PATH . 'assets/cpt-easy.css' )
		);
	}

	/** Data the browser needs: prices, VAT, limits, endpoint. Also usable by the landing price table (window.cptEasy). */
	public static function js_data(): array {
		return array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'action'    => self::ACTION,
			'prices'    => CPT_Easy_Pricing::matrix(),
			'vat'       => CPT_Easy_Pricing::vat_rate(),
			'minQty'    => CPT_Easy_Pricing::min_qty(),
			'maxQty'    => CPT_Easy_Pricing::MAX_QTY,
			'maxFileMb' => self::MAX_FILE_MB,
			'recaptcha' => self::recaptcha_keys()['site'],
			'i18n'      => array(
				'sending' => __( 'Enviando…', 'cpt-easy' ),
				'error'   => __( 'Revisa los campos marcados.', 'cpt-easy' ),
				'network' => __( 'No hemos podido enviar el pedido. Revisa tu conexión e inténtalo de nuevo.', 'cpt-easy' ),
				'tooBig'  => __( 'El archivo supera el tamaño máximo.', 'cpt-easy' ),
			),
		);
	}

	public static function render( $atts = array() ): string {
		if ( ! CPT_Easy_Pricing::get_product() ) {
			return current_user_can( 'manage_woocommerce' )
				? '<p class="cpt-f-error">' . esc_html__( 'CPT Easy: falta el producto con SKU CPT-EASY. Ejecuta «wp cpt-easy install».', 'cpt-easy' ) . '</p>'
				: '';
		}

		// Block themes render the content before wp_enqueue_scripts runs.
		if ( ! wp_script_is( 'cpt-easy', 'registered' ) ) {
			self::register_assets();
		}
		wp_enqueue_script( 'cpt-easy' );
		wp_enqueue_style( 'cpt-easy' );
		wp_add_inline_script( 'cpt-easy', 'window.cptEasy = ' . wp_json_encode( self::js_data() ) . ';', 'before' );

		$min    = CPT_Easy_Pricing::min_qty();
		$accept = '.pdf,.png,.jpg,.jpeg,.ai,.eps,.psd,.tif,.tiff,.webp';

		$qualities = array(
			'Basic'   => __( 'Para pocas puestas y eventos', 'cpt-easy' ),
			'Medium'  => __( 'El más vendido', 'cpt-easy' ),
			'Premium' => __( 'Gramaje alto y algodón orgánico', 'cpt-easy' ),
		);

		$p = self::prefill();

		ob_start();
		?>
		<form class="cpt-form cpt-easy-form" method="post" enctype="multipart/form-data" novalidate data-cpt-easy>
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
			<?php wp_nonce_field( self::NONCE, '_cpt_nonce', false ); ?>
			<div class="cpt-f-hp" aria-hidden="true">
				<label>Web <input type="text" name="cpt_web" tabindex="-1" autocomplete="off"></label>
			</div>

			<div class="cpt-f-cards" role="radiogroup" aria-label="<?php esc_attr_e( 'Calidad de las camisetas', 'cpt-easy' ); ?>">
				<?php foreach ( $qualities as $value => $desc ) : ?>
					<label class="cpt-f-card">
						<input type="radio" name="cpt_calidad_ui" value="<?php echo esc_attr( $value ); ?>" <?php checked( 'Medium', $value ); ?>>
						<span class="cpt-f-card-t"><?php echo esc_html( $value ); ?></span>
						<span class="cpt-f-card-d"><?php echo esc_html( $desc ); ?></span>
					</label>
				<?php endforeach; ?>
				<span class="cpt-f-msg" data-cpt-msg="cpt_calidad_ui"></span>
			</div>

			<div class="cpt-f-grid">
				<?php /* translators: %d: minimum quantity */ ?>
				<?php self::field( 'cantidad', sprintf( __( 'Cantidad (mín. %d)', 'cpt-easy' ), $min ), 'number', array( 'min' => $min, 'max' => CPT_Easy_Pricing::MAX_QTY, 'value' => 50, 'required' => true, 'inputmode' => 'numeric' ) ); ?>
				<label class="cpt-f-field">
					<span class="cpt-f-label"><?php esc_html_e( 'Impresión', 'cpt-easy' ); ?></span>
					<select name="impresion" required>
						<?php foreach ( CPT_Easy_Pricing::PRINTS as $print ) : ?>
							<option value="<?php echo esc_attr( $print ); ?>"><?php echo esc_html( $print ); ?></option>
						<?php endforeach; ?>
					</select>
					<span class="cpt-f-msg" data-cpt-msg="impresion"></span>
				</label>
				<?php self::field( 'color', __( 'Color de las camisetas', 'cpt-easy' ), 'text', array( 'placeholder' => 'Ej.: blanco, negro, azul marino', 'required' => true ) ); ?>
				<?php self::field( 'tallas', __( 'Tallas y unidades', 'cpt-easy' ), 'text', array( 'placeholder' => 'Ej.: S 10, M 20, L 15, XL 5', 'required' => true ) ); ?>

				<?php self::file_field( 'diseno', __( 'Tu diseño', 'cpt-easy' ), $accept, true ); ?>
				<?php self::file_field( 'mockup', __( 'Mockup (opcional)', 'cpt-easy' ), $accept, false ); ?>
			</div>

			<details class="cpt-f-more">
				<summary><?php esc_html_e( 'Datos de facturación', 'cpt-easy' ); ?></summary>
				<div class="cpt-f-grid">
					<?php
					self::field( 'fac_nombre', __( 'Nombre o razón social', 'cpt-easy' ), 'text', array( 'required' => true, 'autocomplete' => 'organization', 'value' => $p['fac_nombre'] ) );
					self::field( 'fac_nif', __( 'NIF / CIF', 'cpt-easy' ), 'text', array( 'required' => true, 'value' => $p['fac_nif'] ) );
					self::field( 'email', __( 'Email', 'cpt-easy' ), 'email', array( 'required' => true, 'autocomplete' => 'email', 'value' => $p['email'] ) );
					self::field( 'telefono', __( 'Teléfono', 'cpt-easy' ), 'tel', array( 'required' => true, 'autocomplete' => 'tel', 'value' => $p['telefono'] ) );
					self::field( 'fac_direccion', __( 'Dirección', 'cpt-easy' ), 'text', array( 'required' => true, 'autocomplete' => 'street-address', 'wide' => true, 'value' => $p['fac_direccion'] ) );
					self::field( 'fac_cp', __( 'Código postal', 'cpt-easy' ), 'text', array( 'required' => true, 'autocomplete' => 'postal-code', 'inputmode' => 'numeric', 'maxlength' => 5, 'value' => $p['fac_cp'] ) );
					self::field( 'fac_poblacion', __( 'Población y provincia', 'cpt-easy' ), 'text', array( 'required' => true, 'autocomplete' => 'address-level2', 'value' => $p['fac_poblacion'] ) );
					?>
				</div>
			</details>

			<details class="cpt-f-more">
				<summary><?php esc_html_e( 'Datos de envío', 'cpt-easy' ); ?></summary>
				<div class="cpt-f-grid">
					<div class="cpt-f-field cpt-f-wide">
						<label><input type="checkbox" name="envio_igual" value="1" <?php checked( $p['envio_igual'] ); ?>> <span><?php esc_html_e( 'La dirección de envío es la misma que la de facturación', 'cpt-easy' ); ?></span></label>
					</div>
					<?php
					$ship = array( 'hidden' => $p['envio_igual'], 'data-cpt-ship' => true );
					self::field( 'env_nombre', __( 'Persona de contacto', 'cpt-easy' ), 'text', $ship + array( 'value' => $p['env_nombre'] ) );
					self::field( 'env_telefono', __( 'Teléfono de entrega', 'cpt-easy' ), 'tel', $ship + array( 'value' => $p['env_telefono'] ) );
					self::field( 'env_direccion', __( 'Dirección de envío', 'cpt-easy' ), 'text', $ship + array( 'wide' => true, 'value' => $p['env_direccion'] ) );
					self::field( 'env_cp', __( 'Código postal', 'cpt-easy' ), 'text', $ship + array( 'inputmode' => 'numeric', 'maxlength' => 5, 'value' => $p['env_cp'] ) );
					self::field( 'env_poblacion', __( 'Población y provincia', 'cpt-easy' ), 'text', $ship + array( 'value' => $p['env_poblacion'] ) );
					?>
				</div>
			</details>

			<div class="cpt-f-legal">
				<label>
					<input type="checkbox" name="privacidad" value="1" required>
					<span>
						<?php
						printf(
							/* translators: %s: privacy policy URL */
							wp_kses( __( 'He leído y acepto la <a href="%s" target="_blank">política de privacidad</a>.', 'cpt-easy' ), array( 'a' => array( 'href' => true, 'target' => true ) ) ),
							esc_url( get_privacy_policy_url() ?: home_url( '/politicas-de-privacidad/' ) )
						);
						?>
					</span>
				</label>
				<span class="cpt-f-msg" data-cpt-msg="privacidad"></span>
			</div>

			<div class="cpt-f-submit">
				<button type="submit" class="has-spinner" disabled><?php esc_html_e( 'Enviar mi pedido', 'cpt-easy' ); ?></button>
				<p class="cpt-f-total">
					<?php esc_html_e( 'TOTAL:', 'cpt-easy' ); ?> <span data-cpt-sin>—</span> + IVA = <strong data-cpt-con>—</strong>
				</p>
			</div>

			<div class="cpt-f-response" role="alert" aria-live="polite" hidden></div>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Billing and shipping values for a logged-in customer, from their WooCommerce account
	 * (the addresses of their last order) and the NIF saved by their last CPT Easy order.
	 */
	public static function prefill(): array {
		$keys = array( 'fac_nombre', 'fac_nif', 'email', 'telefono', 'fac_direccion', 'fac_cp', 'fac_poblacion', 'env_nombre', 'env_telefono', 'env_direccion', 'env_cp', 'env_poblacion' );
		$p    = array_fill_keys( $keys, '' ) + array( 'envio_igual' => true );

		if ( ! is_user_logged_in() ) {
			return $p;
		}

		$user_id = get_current_user_id();
		$user    = wp_get_current_user();
		$c       = new WC_Customer( $user_id );
		$join    = static fn( ...$parts ) => trim( implode( ' ', array_filter( array_map( 'trim', $parts ) ) ) );

		$p['fac_nombre']    = $c->get_billing_company() ?: $join( $c->get_billing_first_name(), $c->get_billing_last_name() ) ?: $join( $user->first_name, $user->last_name );
		$p['fac_nif']       = CPT_Easy_Order::customer_nif( $user_id );
		$p['email']         = $c->get_billing_email() ?: $user->user_email;
		$p['telefono']      = $c->get_billing_phone();
		$p['fac_direccion'] = $join( $c->get_billing_address_1(), $c->get_billing_address_2() );
		$p['fac_cp']        = $c->get_billing_postcode();
		$p['fac_poblacion'] = $c->get_billing_city();

		// A different shipping address: untick "same as billing" and fill it in.
		$ship_street = $join( $c->get_shipping_address_1(), $c->get_shipping_address_2() );
		if ( $ship_street && ( $ship_street !== $p['fac_direccion'] || $c->get_shipping_postcode() !== $p['fac_cp'] ) ) {
			$p['envio_igual']   = false;
			$p['env_nombre']    = $join( $c->get_shipping_first_name(), $c->get_shipping_last_name() ) ?: $c->get_shipping_company();
			$p['env_telefono']  = $c->get_shipping_phone();
			$p['env_direccion'] = $ship_street;
			$p['env_cp']        = $c->get_shipping_postcode();
			$p['env_poblacion'] = $c->get_shipping_city();
		}

		return (array) apply_filters( 'cpt_easy_prefill', $p, $c );
	}

	private static function field( string $name, string $label, string $type, array $args = array() ): void {
		$classes = 'cpt-f-field' . ( ! empty( $args['wide'] ) ? ' cpt-f-wide' : '' );
		$attrs   = '';
		foreach ( array( 'placeholder', 'min', 'max', 'value', 'autocomplete', 'inputmode', 'maxlength' ) as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$attrs .= sprintf( ' %s="%s"', $key, esc_attr( (string) $args[ $key ] ) );
			}
		}
		if ( ! empty( $args['required'] ) ) {
			$attrs .= ' required';
		}
		if ( ! isset( $args['maxlength'] ) && 'number' !== $type ) {
			$attrs .= ' maxlength="400"';
		}
		printf(
			'<label class="%1$s"%2$s%3$s><span class="cpt-f-label">%4$s</span><input type="%5$s" name="%6$s"%7$s><span class="cpt-f-msg" data-cpt-msg="%6$s"></span></label>',
			esc_attr( $classes ),
			! empty( $args['hidden'] ) ? ' hidden' : '',
			! empty( $args['data-cpt-ship'] ) ? ' data-cpt-ship' : '',
			esc_html( $label ),
			esc_attr( $type ),
			esc_attr( $name ),
			$attrs // Escaped above.
		);
	}

	private static function file_field( string $name, string $label, string $accept, bool $required ): void {
		printf(
			'<div class="cpt-f-field cpt-f-file"><span class="cpt-f-label">%1$s</span><label class="cpt-f-filebox"><span class="cpt-f-filebtn">%2$s</span><span class="cpt-f-filename"></span><input type="file" name="%3$s" accept="%4$s"%5$s></label><span class="cpt-f-msg" data-cpt-msg="%3$s"></span></div>',
			esc_html( $label ),
			esc_html__( 'Sube tu archivo', 'cpt-easy' ),
			esc_attr( $name ),
			esc_attr( $accept ),
			$required ? ' required' : ''
		);
	}

	/* ---------------------------------------------------------------------
	 * Submission
	 * ------------------------------------------------------------------ */

	public static function handle(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified right below.
		if ( ! check_ajax_referer( self::NONCE, '_cpt_nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'La sesión ha caducado. Recarga la página y vuelve a enviar el pedido.', 'cpt-easy' ) ), 403 );
		}
		if ( ! empty( $_POST['cpt_web'] ) ) { // Honeypot: pretend it worked.
			wp_send_json_success( array( 'redirect' => home_url( '/' ) ) );
		}
		if ( self::rate_limited() ) {
			wp_send_json_error( array( 'message' => __( 'Has enviado varios pedidos seguidos. Espera un rato o escríbenos.', 'cpt-easy' ) ), 429 );
		}
		if ( ! self::recaptcha_ok( sanitize_text_field( wp_unslash( $_POST['recaptcha'] ?? '' ) ) ) ) {
			wp_send_json_error( array( 'message' => __( 'No hemos podido verificar que no eres un robot. Recarga la página e inténtalo de nuevo.', 'cpt-easy' ) ), 403 );
		}

		$raw = wp_unslash( $_POST );
		// phpcs:enable

		[ $data, $errors ] = self::validate( $raw );
		$errors           += self::check_files();

		if ( $errors ) {
			wp_send_json_error( array( 'fields' => $errors ), 422 );
		}

		$files = array();
		foreach ( array( 'diseno', 'mockup' ) as $key ) {
			$files[ $key ] = null;
			if ( self::has_file( $key ) ) {
				$url = self::store_file( $key );
				if ( is_wp_error( $url ) ) {
					wp_send_json_error( array( 'fields' => array( $key => $url->get_error_message() ) ), 422 );
				}
				$files[ $key ] = $url;
			}
		}

		$order = CPT_Easy_Order::create( $data, $files );
		if ( is_wp_error( $order ) ) {
			wp_send_json_error( array( 'message' => $order->get_error_message() ), 500 );
		}

		self::count_submission();

		wp_send_json_success(
			array(
				'order'    => $order->get_id(),
				'redirect' => apply_filters( 'cpt_easy_redirect_url', $order->get_checkout_payment_url(), $order ),
			)
		);
	}

	/**
	 * @return array{0: array, 1: array<string,string>} [ sanitised data, field => error ]
	 */
	public static function validate( array $raw ): array {
		$text = static fn( $key ) => trim( sanitize_text_field( (string) ( $raw[ $key ] ?? '' ) ) );
		$req  = __( 'Este campo es obligatorio.', 'cpt-easy' );

		$data = array(
			'calidad'       => $text( 'cpt_calidad_ui' ),
			'impresion'     => $text( 'impresion' ),
			'cantidad'      => (int) ( $raw['cantidad'] ?? 0 ),
			'color'         => $text( 'color' ),
			'tallas'        => $text( 'tallas' ),
			'fac_nombre'    => $text( 'fac_nombre' ),
			'fac_nif'       => strtoupper( preg_replace( '/[\s.\-]/', '', $text( 'fac_nif' ) ) ),
			'email'         => sanitize_email( (string) ( $raw['email'] ?? '' ) ),
			'telefono'      => $text( 'telefono' ),
			'fac_direccion' => $text( 'fac_direccion' ),
			'fac_cp'        => $text( 'fac_cp' ),
			'fac_poblacion' => $text( 'fac_poblacion' ),
			'envio_igual'   => ! empty( $raw['envio_igual'] ),
			'env_nombre'    => $text( 'env_nombre' ),
			'env_telefono'  => $text( 'env_telefono' ),
			'env_direccion' => $text( 'env_direccion' ),
			'env_cp'        => $text( 'env_cp' ),
			'env_poblacion' => $text( 'env_poblacion' ),
		);

		$errors = array();

		if ( ! in_array( $data['calidad'], CPT_Easy_Pricing::QUALITIES, true ) ) {
			$errors['cpt_calidad_ui'] = __( 'Elige una calidad.', 'cpt-easy' );
		}
		if ( ! in_array( $data['impresion'], CPT_Easy_Pricing::PRINTS, true ) ) {
			$errors['impresion'] = __( 'Elige el tipo de impresión.', 'cpt-easy' );
		}
		$min = CPT_Easy_Pricing::min_qty();
		if ( $data['cantidad'] < $min || $data['cantidad'] > CPT_Easy_Pricing::MAX_QTY ) {
			/* translators: 1: min, 2: max */
			$errors['cantidad'] = sprintf( __( 'La cantidad debe estar entre %1$d y %2$d.', 'cpt-easy' ), $min, CPT_Easy_Pricing::MAX_QTY );
		}

		$required = array( 'color', 'tallas', 'fac_nombre', 'fac_nif', 'email', 'telefono', 'fac_direccion', 'fac_cp', 'fac_poblacion' );
		if ( ! $data['envio_igual'] ) {
			array_push( $required, 'env_nombre', 'env_direccion', 'env_cp', 'env_poblacion' );
		}
		foreach ( $required as $key ) {
			if ( '' === $data[ $key ] ) {
				$errors[ $key ] = $req;
			}
		}

		if ( ! isset( $errors['email'] ) && ! is_email( $data['email'] ) ) {
			$errors['email'] = __( 'El email no es válido.', 'cpt-easy' );
		}
		if ( ! isset( $errors['fac_nif'] ) && ! self::valid_nif( $data['fac_nif'] ) ) {
			$errors['fac_nif'] = __( 'El NIF / CIF no es válido.', 'cpt-easy' );
		}
		foreach ( array( 'telefono', 'env_telefono' ) as $key ) {
			if ( '' !== $data[ $key ] && strlen( preg_replace( '/\D/', '', $data[ $key ] ) ) < 9 ) {
				$errors[ $key ] = __( 'El teléfono no es válido.', 'cpt-easy' );
			}
		}
		foreach ( array( 'fac_cp', 'env_cp' ) as $key ) {
			if ( '' !== $data[ $key ] && ! isset( $errors[ $key ] ) && ! preg_match( '/^(0[1-9]|[1-4]\d|5[0-2])\d{3}$/', $data[ $key ] ) ) {
				$errors[ $key ] = __( 'El código postal no es válido.', 'cpt-easy' );
			}
		}
		if ( empty( $raw['privacidad'] ) ) {
			$errors['privacidad'] = __( 'Debes aceptar la política de privacidad.', 'cpt-easy' );
		}

		return array( $data, $errors );
	}

	/** DNI, NIE (with control letter) or CIF format. Filterable for foreign VAT numbers. */
	public static function valid_nif( string $nif ): bool {
		$letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
		$valid   = false;

		if ( preg_match( '/^(\d{8})([A-Z])$/', $nif, $m ) ) {
			$valid = $letters[ (int) $m[1] % 23 ] === $m[2];
		} elseif ( preg_match( '/^([XYZ])(\d{7})([A-Z])$/', $nif, $m ) ) {
			$num   = strtr( $m[1], 'XYZ', '012' ) . $m[2];
			$valid = $letters[ (int) $num % 23 ] === $m[3];
		} elseif ( preg_match( '/^[ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J]$/', $nif ) ) {
			$valid = true;
		}

		return (bool) apply_filters( 'cpt_easy_valid_nif', $valid, $nif );
	}

	/* ---------------------------------------------------------------------
	 * Files
	 * ------------------------------------------------------------------ */

	private static function allowed_mimes(): array {
		return apply_filters(
			'cpt_easy_allowed_mimes',
			array(
				'pdf'      => 'application/pdf',
				'png'      => 'image/png',
				'jpg|jpeg' => 'image/jpeg',
				'webp'     => 'image/webp',
				'tif|tiff' => 'image/tiff',
				'ai'       => 'application/postscript',
				'eps'      => 'application/postscript',
				'psd'      => 'image/vnd.adobe.photoshop',
			)
		);
	}

	private static function has_file( string $key ): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		return isset( $_FILES[ $key ] ) && UPLOAD_ERR_NO_FILE !== (int) $_FILES[ $key ]['error'];
	}

	private static function check_files(): array {
		$errors = array();
		if ( ! self::has_file( 'diseno' ) ) {
			$errors['diseno'] = __( 'Sube el archivo con tu diseño.', 'cpt-easy' );
		}
		foreach ( array( 'diseno', 'mockup' ) as $key ) {
			if ( ! self::has_file( $key ) ) {
				continue;
			}
			$file = $_FILES[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput
			if ( UPLOAD_ERR_OK !== (int) $file['error'] || (int) $file['size'] > self::MAX_FILE_MB * MB_IN_BYTES ) {
				/* translators: %d: MB */
				$errors[ $key ] = sprintf( __( 'El archivo no se ha podido subir (máximo %d MB).', 'cpt-easy' ), self::MAX_FILE_MB );
			}
		}
		return $errors;
	}

	/** Moves the upload to uploads/cpt-easy/YYYY/MM/ with an unguessable name. @return string|WP_Error URL */
	private static function store_file( string $key ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$set_dir = static function ( $dirs ) {
			$sub            = '/' . self::UPLOAD_DIR . '/' . gmdate( 'Y/m' );
			$dirs['subdir'] = $sub;
			$dirs['path']   = $dirs['basedir'] . $sub;
			$dirs['url']    = $dirs['baseurl'] . $sub;
			return $dirs;
		};
		$rename = static function ( $dir, $name, $ext ) {
			$base = sanitize_file_name( pathinfo( $name, PATHINFO_FILENAME ) );
			return strtolower( wp_generate_password( 16, false ) ) . '-' . substr( $base, 0, 60 ) . strtolower( $ext );
		};

		self::protect_upload_dir();

		add_filter( 'upload_dir', $set_dir );
		self::$uploading = true;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput
		$result = wp_handle_upload(
			$_FILES[ $key ],
			array(
				'test_form'                => false,
				'mimes'                    => self::allowed_mimes(),
				'unique_filename_callback' => $rename,
			)
		);
		self::$uploading = false;
		remove_filter( 'upload_dir', $set_dir );

		if ( isset( $result['error'] ) ) {
			return new WP_Error( 'cpt_easy_upload', __( 'Formato no admitido. Usa PDF, AI, EPS, PSD, PNG, JPG, TIFF o WEBP.', 'cpt-easy' ) );
		}
		return $result['url'];
	}

	/**
	 * Design files often fail WordPress' real-MIME check (.ai is detected as PDF, .eps/.psd vary).
	 * Only while our own upload runs, accept those extensions when the real type is one of the expected ones.
	 */
	public static function allow_design_files( $data, $file, $filename, $mimes ) {
		if ( ! self::$uploading || ! empty( $data['ext'] ) ) {
			return $data;
		}
		$ext  = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		$real = function_exists( 'finfo_open' ) ? (string) finfo_file( finfo_open( FILEINFO_MIME_TYPE ), $file ) : '';
		$ok   = array(
			'ai'  => array( 'application/pdf', 'application/postscript', 'application/illustrator' ),
			'eps' => array( 'application/postscript', 'image/x-eps', 'application/octet-stream' ),
			'psd' => array( 'image/vnd.adobe.photoshop', 'application/octet-stream', 'image/x-photoshop' ),
		);
		if ( isset( $ok[ $ext ] ) && in_array( $real, $ok[ $ext ], true ) ) {
			return array(
				'ext'             => $ext,
				'type'            => 'psd' === $ext ? 'image/vnd.adobe.photoshop' : 'application/postscript',
				'proper_filename' => $data['proper_filename'] ?? false,
			);
		}
		return $data;
	}

	/** Apache: no directory listing and no script execution in uploads/cpt-easy. */
	private static function protect_upload_dir(): void {
		$dir = wp_get_upload_dir()['basedir'] . '/' . self::UPLOAD_DIR;
		if ( ! wp_mkdir_p( $dir ) ) {
			return;
		}
		if ( ! file_exists( $dir . '/.htaccess' ) ) {
			file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions
				$dir . '/.htaccess',
				"Options -Indexes\n<FilesMatch \"\\.(?i:php\\d?|phtml|phar|pl|py|cgi|sh)$\">\n  Require all denied\n</FilesMatch>\n"
			);
		}
		if ( ! file_exists( $dir . '/index.php' ) ) {
			file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
	}

	/* ---------------------------------------------------------------------
	 * Anti-spam
	 * ------------------------------------------------------------------ */

	private static function rate_key(): string {
		return 'cpt_easy_rl_' . md5( WC_Geolocation::get_ip_address() );
	}

	private static function rate_limited(): bool {
		return (int) get_transient( self::rate_key() ) >= self::RATE_LIMIT;
	}

	private static function count_submission(): void {
		set_transient( self::rate_key(), (int) get_transient( self::rate_key() ) + 1, self::RATE_WINDOW );
	}

	/** Reuses the reCAPTCHA v3 keys already saved in Contact Form 7 (Contacto → Integración). */
	private static function recaptcha_keys(): array {
		$opt  = get_option( 'wpcf7' );
		$keys = is_array( $opt ) && ! empty( $opt['recaptcha'] ) && is_array( $opt['recaptcha'] ) ? $opt['recaptcha'] : array();
		$site = (string) array_key_first( $keys );
		return array(
			'site'   => $site,
			'secret' => $site ? (string) $keys[ $site ] : '',
		);
	}

	private static function recaptcha_ok( string $token ): bool {
		$keys = self::recaptcha_keys();
		if ( ! $keys['secret'] ) {
			return true; // Not configured.
		}
		if ( '' === $token ) {
			return false;
		}
		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $keys['secret'],
					'response' => $token,
					'remoteip' => WC_Geolocation::get_ip_address(),
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			wc_get_logger()->warning( 'reCAPTCHA: ' . $response->get_error_message(), array( 'source' => 'cpt-easy' ) );
			return true; // Google unreachable: don't lose real orders; honeypot + rate limit still apply.
		}
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return ! empty( $body['success'] )
			&& ( $body['action'] ?? '' ) === 'cpt_easy'
			&& (float) ( $body['score'] ?? 0 ) >= self::RECAPTCHA_MIN_SCORE;
	}
}
