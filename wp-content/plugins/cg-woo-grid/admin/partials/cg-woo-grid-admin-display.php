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
    <h2><?php esc_html_e( 'Attribute Grid', 'cg-woo-grid' )?></h2>  
        <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
    <?php settings_errors(); ?>  

    <div id="poststuff">
        <!-- Main settings -->
		<div id="post-body" 
            class="columns-2 metabox-holder"
        >
			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div id="settings-main" 
                        class="postbox"
                    >
						<div class="inside">
                            <form method="POST" 
                                action="options.php"
                            >  
                                <?php 
                                    settings_fields( 'cg-woo-grid_general_settings' );
                                    do_settings_sections( 'cg-woo-grid_general_settings' ); 
                                ?>       
                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- .postbox -->

                    <div id="settings-bar"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Minimum bar','cg-woo-grid'); ?></span>
                        </h2>
						<div class="inside">
                            <p><?php 
                            esc_html_e( 
                                'Messages to show in the minimum bar', 
                                'cg-woo-grid'
                            ); ?></p>
                            <form method="POST" action="options.php">  
                                <?php 
                                    settings_fields( 'cg-woo-grid_bar_settings' );
                                    do_settings_sections( 'cg-woo-grid_bar_settings' ); 
                                ?>       
                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- .postbox -->

                    <div id="settings-totalbox"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Total box','cg-woo-grid'); ?></span>
                        </h2>
						<div class="inside">
                            <p><?php 
                            esc_html_e( 
                                'Total box configuration', 
                                'cg-woo-grid'
                            ); ?></p>
                            <form method="POST" 
                                action="options.php"
                            >  
                                <?php 
                                    settings_fields( 'cg-woo-grid_totalbox_settings' );
                                    do_settings_sections( 'cg-woo-grid_totalbox_settings' ); 
                                ?>       
                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- .postbox -->

                    <div id="settings-stock"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Stock options','cg-woo-grid'); ?></span>
                        </h2>
						<div class="inside">
                            <p><?php 
                            esc_html_e( 
                                'The stock threshold will be subtracted from the actual stock value of each product, and the purchase of the item will be allowed if the customer selects an amount less than said final value.', 
                                'cg-woo-grid'
                            ); ?></p>
                            <form method="POST" action="options.php">  
                                <?php 
                                    settings_fields( 'cg-woo-grid_stock_settings' );
                                    do_settings_sections( 'cg-woo-grid_stock_settings' ); 
                                ?>       
                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- .postbox -->

                    <div id="settings-popup"
                        class="postbox"
                    >
                        <h2>
                            <span><?php esc_html_e('Pop-up','cg-woo-grid'); ?></span>
                        </h2>
						<div class="inside">
                            <p><?php 
                            esc_html_e( 
                                'Messages to show in the pop-up', 
                                'cg-woo-grid'
                            ); ?></p>
                            <form method="POST" 
                                action="options.php"
                            >  
                                <?php 
                                    settings_fields( 'cg-woo-grid_popup_settings' );
                                    do_settings_sections( 'cg-woo-grid_popup_settings' ); 
                                ?>       
                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- .postbox -->

				</div><!-- .meta-box-sortables .ui-sortable -->
			</div><!-- #post-body-content-->
			<!-- sidebar -->
			<div id="postbox-container-1" 
                class="postbox-container"
            >
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
			</div><!-- #settings-main-postbox.postbox-container -->
		</div><!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
    </div> <!-- #poststuff -->
    
</div> <!-- .wrap -->