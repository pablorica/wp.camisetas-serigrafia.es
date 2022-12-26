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
                        class="card"
                        style="margin-bottom:3rem"
                    >
                            <form method="POST" 
                                action="options.php"
                            >  
                                <?php 
                                    settings_fields( 'customisations-for-cg-woo-grid_budgeteer_general' );
                                    do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_general' ); 
                                ?>             
                                <?php submit_button(); ?>  
                            </form> 
					</div><!-- .card -->

                    <div id="settings-bagged"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Embolsado','sadoje'); ?></span>
                        </h2>
						<div class="inside">
                            <form method="POST" action="options.php">  
                                <?php 
                                    settings_fields( 'customisations-for-cg-woo-grid_budgeteer_bagged' );
                                    do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_bagged' ); 
                                ?>       
                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- .postbox -->

                    <div id="settings-stamping"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Estampación','sadoje'); ?></span>
                        </h2>
						<div class="inside">
                            <form id="formStamping">

                                <table id="tableStamping"
                                    class="widefat"
                                >
                                    <thead>
                                        <tr>
                                            <th class="row-title">Tipo de estampación</th>
                                            <th>Precio estampación por prenda (prendas blancas)</th>
                                            <th>Precio estampación por prenda (prendas de color)</th>
                                            <th>Precio adicional por pantalla y fotolitos</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="appRows">
                                        <tr v-for="(row, index) in rows"
                                            v-bind:id="'row_'+index" 
                                            v-bind:class="row.class" 
                                        >
                                                <th>
                                                    <input type="text" 
                                                        autocomplete="off"
                                                        v-model="row.stamping"
                                                        :name="'stamping['+index+']'"
                                                    >
                                                </th>
                                                <th>
                                                    <input type="text" 
                                                        autocomplete="off"
                                                        v-model="row.white_price"
                                                        :name="'white_price['+index+']'"
                                                    >
                                                </th>
                                                <th>
                                                    <input type="text" 
                                                        autocomplete="off"
                                                        v-model="row.color_price"
                                                        :name="'color_price['+index+']'"
                                                    >
                                                </th>
                                                <th>
                                                    <input type="text" 
                                                        autocomplete="off"
                                                        v-model="row.screen_price"
                                                        :name="'screen_price['+index+']'"
                                                    >
                                                </th>
                                                <th>
                                                    <button class="button button-secondary button-delete" 
                                                        style="background:#DC3232;color:#FFF;border-color:#FFF;"
                                                        @click="deleteRow(index, $event)"
                                                    ><?php echo __('Borrar fila','sadoje'); ?></button>
                                                </th>
                                        </tr>
                                    </tbody>
                                </table>

                                <p class="submit">
                                    <button class="button button-secondary" 
                                        @click="addRow($event)"
                                    ><?php echo __('Añadir estampación','sadoje'); ?></button>
                                </p>
                            </form>

                                
                            <form method="POST" action="options.php">  
                                <?php 
                                    settings_fields( 'customisations-for-cg-woo-grid_budgeteer_stamping' );
                                    do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_stamping' ); 

                                    //a:2:{i:0;O:8:"stdClass":4:{s:8:"stamping";s:52:"Serigrafía 1 color Tamaño Pequeño 10x15 cm aprox.";s:11:"white_price";d:0.4;s:11:"color_price";d:0.5;s:12:"screen_price";i:30;}i:1;O:8:"stdClass":4:{s:8:"stamping";s:54:"Serigrafía 2 colores Tamaño Pequeño 10x15 cm aprox.";s:11:"white_price";d:0.6;s:11:"color_price";d:0.7;s:12:"screen_price";i:31;}}

                                    /*
                                    $tests = array();
                                    $test  = new \stdClass();
                                    $test->stamping = sanitize_text_field("Serigrafía 1 color Tamaño Pequeño 10x15 cm aprox.");
                                    $test->white_price = 0.4;
                                    $test->color_price = 0.5;
                                    $test->screen_price = 30;
                                    $tests[] = $test;

                                    $test  = new \stdClass();
                                    $test->stamping = sanitize_text_field("Serigrafía 2 colores Tamaño Pequeño 10x15 cm aprox.");
                                    $test->white_price = 0.6;
                                    $test->color_price = 0.7;
                                    $test->screen_price = 31;
                                    $tests[] = $test;

                                    //echo serialize($tests);
                                    */
                                    

                                ?>
                                

                                <?php submit_button(); ?>  
                            </form> 


                                
                                <p><tt id="results"></tt></p>

                                <div id="test">{{ message }}
                                    <div>
                                        {{ count }}
                                        <button @click="decrement">-</button>
                                        <button @click="increment">+</button>
                                    </div>
                                    
                                </div>

                                <script>
                                

                                //TODO : Rewrite all this is VUE. Rows cannot be deleted via jQUery if they are loaded dinamycally, This can be solved via VUE
                                /*
                                    (function($) {

                                        function showValues($form) {
                                            console.log('showValues',$form)
                                            var str = $form.serialize();
                                            $( "#results" ).text( str );
                                        }

                                        function removeStampingRow(rowKey) {
                                            $("form#formStamping #row_"+rowKey).remove();
                                            alert("form#formStamping #row_"+rowKey)
                                        }

                                        function addStampingRow(element, $tbody, $int) {
                                            let classRow = ''
                                            if(int % 2 == 0) {
                                                classRow = 'alternate'
                                            }

                                            stampingValue = ""
                                            whitePriceValue = ""
                                            colorPriceValue = ""
                                            screenPriceValue = ""
                                            if(element) {
                                                stampingValue = "value=\""+element.stamping+"\""
                                                whitePriceValue = "value=\""+element.white_price+"\""
                                                colorPriceValue = "value=\""+element.color_price+"\""
                                                screenPriceValue = "value=\""+element.screen_price+"\""
                                            }

                                            // Append text nodes to the cell
                                            //let newText = document.createTextNode(element.stamping);
                                            let stampingInput = "<th><input type=\"text\" id=\"stamping["+int+"]\" name=\"stamping["+int+"]\" "+stampingValue+" /></th>";
                                            let whitePriceInput = "<th><input type=\"text\" id=\"white_price["+int+"]\" name=\"white_price["+int+"]\""+whitePriceValue+" /></th>";
                                            let colorPriceInput = "<th><input type=\"text\" id=\"color_price["+int+"]\" name=\"color_price["+int+"]\" "+colorPriceValue+" /></th>";
                                            let screenPriceInput = "<th><input type=\"text\" id=\"screen_price["+int+"]\" name=\"screen_price["+int+"]\" "+screenPriceValue+" /></th>";
                                            let deleteRow = "<th><button data-row='"+int+"' class='button button-secondary button-delete' style='background:#DC3232;color:#FFF;border-color:#FFF;'>Delete Row</button></th>";

                                            let $newRow = $("<tr id=\"row_"+int+"\" class=\""+classRow+"\">"
                                                + stampingInput
                                                + whitePriceInput
                                                + colorPriceInput
                                                + screenPriceInput
                                                + deleteRow
                                                + "</tr>"
                                            );
                                            //console.log($newRow);
                                            $tbody.append($newRow);
                                        }

                                        const stampingsObj = JSON.parse(
                                            document
                                            .getElementById(
                                                'customisations-for-cg-woo-grid_budgeteer_stamping_types'
                                            )
                                            .dataset.json
                                        );

                                        //console.log(stampingsObj);
                                        let $tbody = $('table#tableStamping tbody');
                                        let int = 0;
                                        stampingsObj.forEach(element => {
                                            addStampingRow(element, $tbody, int++)
                                        });

                                        $("form#formStamping button#addRow")
                                        .click(function(event ) {
                                            event.preventDefault();
                                            addStampingRow('', $tbody, int++)
                                        });
                                        $( "form#formStamping button.button-delete" )
                                        .on( "click", function( event ) {
                                            event.preventDefault();
                                            const $row = $(this).closest('tr')
                                            $row.remove();
                                        });

                                        $( "form#formStamping" ).on( "submit", function( event ) {
                                            event.preventDefault();
                                            showValues($('form#formStamping'))
                                        });

                                        showValues($('form#formStamping'));

                                        

                                    })( jQuery );
                                    */
                                </script>
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
                            <span><?php esc_html_e('Configuración','sadoje'); ?></span>
                        </h2>
						<div class="inside">
                        <p><?php 
                        esc_html_e( 
                            'Esta configuración se aplica al presupestador', 
                            'sadoje'
                        ); ?></p>
						</div><!-- .inside -->
					</div><!-- .postbox -->
				</div><!-- .meta-box-sortables -->
			</div><!-- #settings-main-postbox.postbox-container -->
		</div><!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
    </div> <!-- #poststuff -->
</div> <!-- .wrap -->
