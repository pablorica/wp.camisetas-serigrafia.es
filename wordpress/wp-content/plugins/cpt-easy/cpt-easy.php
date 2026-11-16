<?php
/**
 * Plugin Name:       CPT Easy
 * Description:       Pedido rápido de camisetas: un producto variable (Calidad × Impresión) con precio por tramos de cantidad y un formulario que crea el pedido y lleva al pago.
 * Version:           0.3.0
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            Pablo Rica <pablo@codigo.co.uk>
 * Author URI:        https://codigo.co.uk/
 * Text Domain:       cpt-easy
 * License:           GPL-2.0-or-later
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

define( 'CPT_EASY_VERSION', '0.3.0' );
define( 'CPT_EASY_FILE', __FILE__ );
define( 'CPT_EASY_PATH', plugin_dir_path( __FILE__ ) );
define( 'CPT_EASY_URL', plugin_dir_url( __FILE__ ) );

/** SKU of the parent product. The plugin finds the product by this SKU. */
define( 'CPT_EASY_SKU', 'CPT-EASY' );

require_once CPT_EASY_PATH . 'includes/class-cpt-easy-pricing.php';
require_once CPT_EASY_PATH . 'includes/class-cpt-easy-admin.php';
require_once CPT_EASY_PATH . 'includes/class-cpt-easy-form.php';
require_once CPT_EASY_PATH . 'includes/class-cpt-easy-order.php';
require_once CPT_EASY_PATH . 'includes/class-cpt-easy-landing.php';
require_once CPT_EASY_PATH . 'includes/class-cpt-easy-blocks.php';

// HPOS / cart-checkout blocks compatibility.
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CPT_EASY_FILE, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		CPT_Easy_Admin::init();
		CPT_Easy_Form::init();
		CPT_Easy_Order::init();
		CPT_Easy_Landing::init();
		CPT_Easy_Blocks::init();
	}
);

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once CPT_EASY_PATH . 'includes/class-cpt-easy-cli.php';
	WP_CLI::add_command( 'cpt-easy', 'CPT_Easy_CLI' );
}
