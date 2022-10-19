<?php
/*
 * Codigo Woo Grid
 * 
 * @package CgWooGrid
 * @author Pablo Rica
 * @copyright 2022 Pablo Rica
 * @license GPL-2.0-or-later 
 * 
 * @wordpress-plugin 
 * Plugin Name: Codigo Woo Grid
 * Plugin URI: http://codigo.co.uk/cg-woo-grid
 * Description: Adds a matrix of Woocommerce Products variations
 * Version: 0.0.1 
 * Author: Pablo Rica
 * Author URI: http://codigo.co.uk/pablo-rica
 * Text Domain: cg-woo-grid
 * License: GPLv2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt 
*/

defined( 'ABSPATH' ) or die( "Access denied !" );

//define constants of the plugin_name
define('CG_WOOGRID_PATH', plugin_dir_url( __FILE__ ));

//add the required class file
require('includes/class-helper.php');

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
