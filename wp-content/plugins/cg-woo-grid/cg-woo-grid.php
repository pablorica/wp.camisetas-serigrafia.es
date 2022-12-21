<?php
/*
 * Codigo Woo Grid
 * 
 * @package cg-woo-grid
 * @author Pablo Rica
 * @copyright 2022 Pablo Rica
 * @license GPL-2.0-or-later 
 * 
 * @wordpress-plugin 
 * Plugin Name:     Codigo Woo Grid
 * Plugin URI:      http://codigo.co.uk/cg-woo-grid
 * Description:     Adds a matrix of Woocommerce Products variations
 * Version:         0.1.0
 * Author:          Pablo Rica <pablo@codigo.co.uk>
 * Author URI:      http://codigo.co.uk/pablo-rica
 * Text Domain:     cg-woo-grid
 * Domain Path:     /languages
 * License:         GPLv2 or later 
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt 
*/

defined( 'ABSPATH' ) or die( "Access denied !" );


/**
 * Constants
 */
define('CG_WOOGRID_NAME', 'cg-woo-grid');
define('CG_WOOGRID_PATH', dirname(__FILE__));
define('CG_WOOGRID_URL', plugin_dir_url( __FILE__ ));


// Start at version 1.0.0 and use SemVer - https://semver.org
define( 'CG_WOOGRID_VERSION', '0.1.0' );

//Development status (development|production)
define('CG_WOOGRID_STATUS', 'development');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_Cg_Woo_Grid() {
	require_once ('includes/class-cg-woo-grid-activator.php');
	Cg_Woo_Grid_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_Cg_Woo_Grid() {
	require_once ('includes/class-cg-woo-grid-deactivator.php');
	Cg_Woo_Grid_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Cg_Woo_Grid' );
register_deactivation_hook( __FILE__, 'deactivate_Cg_Woo_Grid' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require ('includes/class-cg-woo-grid.php');

//this method initialize the class
function Cg_Woo_Grid_init()
{
  //instantiate the plugin class
  $plugin = new Cg_Woo_Grid();

  //start the execution of the plugin class-base-project
  $plugin->run();
}

// Now that you have declared the function, you need to call it trough the init method of wordpress
// This allow you to start the plugin after all the wordpress core functions are loaded,
// With this, you will not have problems calling methods that does not exist.
add_action( 'init', 'Cg_Woo_Grid_init' );

//Done!
?>
