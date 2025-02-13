<?php
/**
 * Functions for Sadoje template
 * 
 * php version 7.4.1
 *
 * @package    Agni Framework
 * @subpackage Sadoje
 * @author     Pablo Rica <pablo@codigo.co.uk>
 * @license    MIT 
 * @version    GIT: @1.0.0@
 * @link       link
 * @since      Sadoje 1.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


$child_includes = array(
    '/sadoje-setup.php',
    '/sadoje-filters-extras.php',
);
foreach ( $child_includes  as $file ) {
	require_once get_theme_file_path( 'inc' . $file );
}

/**
 * Remove hook that ims forcing Woo Products to use the Gutenberg editor
 * wp-content/themes/cartify/inc/template-functions-woocommerce.php:2584
 * add_filter( 'use_block_editor_for_post_type', 'cartify_enable_block_editor_product', 10, 2 );
 */
remove_filters_with_method_name( 
    'use_block_editor_for_post_type', 
    'cartify_enable_block_editor_product', 
    10 
);

?>