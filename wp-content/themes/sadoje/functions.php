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
    '/sadoje-remove-filters.php',
);
foreach ( $child_includes  as $file ) {
	require_once get_theme_file_path( 'inc' . $file );
}


?>