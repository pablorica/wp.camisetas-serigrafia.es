<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    cg-woo-grid
 * @subpackage cg-woo-grid/includes
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */
?>
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>  
    <h2><?php esc_html_e( 'Attribute Grid', 'codigo' )?></h2>  
        <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
    <?php settings_errors(); ?>  
    <form method="POST" action="options.php">  
        <?php 
            settings_fields( 'cg-woo-grid_general_settings' );
            do_settings_sections( 'cg-woo-grid_general_settings' ); 
        ?>       
        <?php submit_button(); ?>  
    </form> 
</div>
