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
    
    <!-- Vue -->
    <div id="appAdminTest">
        <cdg-test></cdg-test>
    </div>
<!-- / Vue  -->



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
                            <form id="formMinimumBar"
                                method="POST" 
                                action="options.php"
                                
                            >  
                                <?php 
                                    settings_fields( 'cg-woo-grid_bar_settings' );
                                    do_settings_sections( 'cg-woo-grid_bar_settings' ); 
                                ?>    
              
                                <div id="tableMinimumBar"
                                    data-theadertype    = "<?php esc_html_e('Type','cg-woo-grid'); ?>"
                                    data-theaderitem    = "<?php esc_html_e('Item','cg-woo-grid'); ?>"
                                    data-theaderminimum = "<?php esc_html_e('Minimum amount','cg-woo-grid'); ?>"
                                    data-selectgeturl   = "<?php echo esc_url_raw( rest_url( 'wp/v2/') ); ?>"
                                    data-deletetitle    = "<?php esc_html_e('Delete','cg-woo-grid');  ?>"
                                    data-tfootaddrow    = "<?php esc_html_e('Add items','cg-woo-grid'); ?>"
                                    data-selctplachldr  = "<?php esc_html_e('Type a value','cg-woo-grid'); ?>";
                                    data-producttitle   = "<?php esc_html_e('Product','cg-woo-grid'); ?>";
                                    data-categorytitle  = "<?php esc_html_e('Category','cg-woo-grid'); ?>";
                                    data-apiurl         = "<?php echo $urlspace ?>"
                                    data-hiddenboxid    = "cg-woo-grid_minimum_options"
                                >
                                    <cdg-minimumbar></cdg-minimumbar>
                                </div>

                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- #settings-bar.postbox -->

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
                                
                                <div id="tableCustomisedStock"
                                    data-theadertype    = "<?php esc_html_e('Type','cg-woo-grid'); ?>"
                                    data-theaderitem    = "<?php esc_html_e('Item','cg-woo-grid'); ?>"
                                    data-theaderminimum = "<?php esc_html_e('Low stock threshold','cg-woo-grid'); ?>"
                                    data-selectgeturl   = "<?php echo esc_url_raw( rest_url( 'wp/v2/') ); ?>"
                                    data-deletetitle    = "<?php esc_html_e('Delete','cg-woo-grid');  ?>"
                                    data-tfootaddrow    = "<?php esc_html_e('Add items','cg-woo-grid'); ?>"
                                    data-selctplachldr  = "<?php esc_html_e('Type a value','cg-woo-grid'); ?>";
                                    data-producttitle   = "<?php esc_html_e('Product','cg-woo-grid'); ?>";
                                    data-categorytitle  = "<?php esc_html_e('Category','cg-woo-grid'); ?>";
                                    data-apiurl         = "<?php echo $urlspace ?>"
                                    data-hiddenboxid    = "cg-woo-grid_customised_stock"
                                >
                                    <cdg-minimumbar></cdg-minimumbar>
                                </div>

                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- #settings-stock.postbox -->

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


                    <div id="settings-resumebox"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Resume box','cg-woo-grid'); ?></span>
                        </h2>
						<div class="inside">
                            <p><?php 
                            esc_html_e( 
                                'Resume box configuration', 
                                'cg-woo-grid'
                            ); ?></p>
                            <form method="POST" 
                                action="options.php"
                            >  
                                <?php 
                                    settings_fields( 'cg-woo-grid_resumebox_settings' );
                                    do_settings_sections( 'cg-woo-grid_resumebox_settings' ); 
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