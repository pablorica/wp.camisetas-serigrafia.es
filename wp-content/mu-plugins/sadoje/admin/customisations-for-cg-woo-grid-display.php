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

<div class="wrap">
    <h2><?php esc_html_e( 'Presupuestador', 'sadoje' )?></h2>  
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
                            <tr><th scope="row">Estado: </th><td><input name="pp_options[power]" type="radio" value="on" checked=""> Activado <input name="pp_options[power]" type="radio" value="off"> Desactivado</td></tr>
                            <tr><th scope="row">Precio de embolsado: </th><td><input name="pp_options[embolsado]" type="text" value="0.25" style="width: 100%;"></td></tr>


                            <?php 
                                //settings_fields( 'customisations-for-cg-woo-grid_budgeteer_bagged' );
                                //do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_bagged' ); 
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
