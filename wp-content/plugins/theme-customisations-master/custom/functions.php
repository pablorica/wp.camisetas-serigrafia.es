<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * functions.php
 * Add PHP snippets here
 */

 /**
  * This snippet tells WooCommerce to allow 300 variations for a product before it stops embedding the JavaScript that allows the web browser to dynamically enable and disable the different dropdown lists. 
  * https://techprimeweb.com/modify-the-ajax-variation-threshold/
  *
  * @param int $qty
  * @param int $product
  * @return void
  */
function codigo_wc_ajax_variation_threshold( $qty, $product ) {
    return 300;
}
 
add_filter( 'woocommerce_ajax_variation_threshold', 'codigo_wc_ajax_variation_threshold', 10, 2 );
