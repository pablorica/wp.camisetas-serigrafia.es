<?php

/*
 * Sadoje
 * 
 * @package sadoje
 * @author Pablo Rica
 * @copyright 2022 Pablo Rica
 * @license GPL-2.0-or-later 
 * 
 * @wordpress-plugin 
 * Plugin Name:     Sadoje
 * Plugin URI:      http://codigo.co.uk/
 * Description:     Adds custom features asked by Sadoje to theme and plugins
 * Version:         0.1.0
 * Author:          Pablo Rica <pablo@codigo.co.uk>
 * Author URI:      http://codigo.co.uk/pablo-rica
 * Text Domain:     sadoje
 * Domain Path:     /languages
 * License:         GPLv2 or later 
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt 
*/



require( 'autoload.php' );

// this is where our site classes live
$namespace = 'Sadoje\\';

// and this is what they're called
$classes = array( 
	'Customisations_For_Cg_Woo_Grid',
    'Customisations_For_Storefront_Template'
);

/**
 *
 * Initialise all of the singleton classes that set up and hook into Wordpress
 *
 * We use singletons so that others can unhook and rehook these functions from their own plugins at a later date if need be.
 *
*/

foreach ( $classes as $class ) {

	// get a fully qualified namespace so we can get the instance
	// @see https://stackoverflow.com/a/34019088/2266770
	$namespaced_class = $namespace . $class;

	// get the singleton and initialise it, while suppressing the undefined method inspection
	$class = $namespaced_class::getInstance();

	$class->init();

}


