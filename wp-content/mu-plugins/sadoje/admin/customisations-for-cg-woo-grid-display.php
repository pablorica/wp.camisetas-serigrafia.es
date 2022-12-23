<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

//global $wp_settings_sections;
//$settings = $wp_settings_sections['cg-woo-grid_general_settings']['cg-woo-grid_general_section'];
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>  
    <h2>Plugin Name Settings</h2>  
        <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
    <?php settings_errors(); ?>  
    <form method="POST" action="options.php">  
        <?php 
            settings_fields( 'plugin_name_general_settings' );
            do_settings_sections( 'plugin_name_general_settings' ); 
        ?>             
        <?php submit_button(); ?>  
    </form> 
</div>