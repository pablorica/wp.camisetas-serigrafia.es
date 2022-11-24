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
 * Remove Storefront Sidebar
 *
 * @return void
 */
function cdg_remove_storefront_sidebar() {
    /*
	if ( is_woocommerce() 
        || is_checkout() 
    ) {
		remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
	}
    */
    remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
}
add_action( 'get_header', 'cdg_remove_storefront_sidebar' );



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
//add_filter( 'woocommerce_ajax_variation_threshold', 'codigo_wc_ajax_variation_threshold', 10, 2 );



