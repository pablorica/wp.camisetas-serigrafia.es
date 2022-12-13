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

global $wp_settings_sections;
$settings = $wp_settings_sections['cg-woo-grid_general_settings']['cg-woo-grid_general_section'];
?>
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>  
    <h2><?php esc_html_e( 'Attribute Grid', 'codigo' )?></h2>  
        <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
    <?php settings_errors(); ?>  

    <div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<div class="inside">
                            <form method="POST" action="options.php">  
                                <?php 
                                    settings_fields( 'cg-woo-grid_general_settings' );
                                    do_settings_sections( 'cg-woo-grid_general_settings' ); 
                                ?>       
                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- .postbox -->
				</div><!-- .meta-box-sortables .ui-sortable -->
			</div><!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<div class="postbox">
						<h2 class="hndle">
                            <span><?php esc_html_e('General settings','cg-woo-grid'); ?></span>
                        </h2>
						<div class="inside">
                        <p><?php 
                        esc_html_e( 
                            'These settings apply to all Attribute Grid functionality.', 
                            'cg-woo-grid'
                        ); ?></p>
						</div><!-- .inside -->
					</div><!-- .postbox -->
				</div><!-- .meta-box-sortables -->
			</div><!-- #postbox-container-1 .postbox-container -->

		</div><!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
	</div><!-- #poststuff -->
    
</div> <!-- .wrap -->