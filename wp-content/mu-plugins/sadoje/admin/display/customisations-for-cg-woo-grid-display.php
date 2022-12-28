<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

?>

<div class="wrap">
    <h2><?php esc_html_e( 'Presupuestador', 'sadoje' )?></h2>  
        <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
    <?php settings_errors(); ?>  

    <div id="poststuff">


        <div id="post-body" 
            class="columns-2 metabox-holder"
        >
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
                </div><!-- .meta-box-sortables .ui-sortable -->
			</div><!-- #post-body-content-->
		</div><!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
       
        <div id="post-body" 
            class="columns-2 metabox-holder">
            <div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">

                    <div id="settings-baseprice"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Margen de beneficio por cantidades','sadoje'); ?></span>
                        </h2>
						<div class="inside">
                            <form id="formBaseprice"
                                method="POST" 
                                action="options.php"
                                data-apiurl = "<?php echo $urlspace; ?>"
                                class="hide-form-table"
                            >
                                <?php 
                                    settings_fields( 'customisations-for-cg-woo-grid_budgeteer_baseprice' );
                                    do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_baseprice' ); 
                                ?>

                                <table id="tableBaseprice"
                                    class="widefat"
                                >
                                    <thead>
                                        <tr>
                                            <th class="row-title">
                                                <strong><?php _e('Rango del Precio Unidad','sadoje'); ?></strong><br/>
                                                <em><?php _e('El precio unidad se aplica a todos los productos cuya cantidad se encuentre en el rango de valores indicados en este campo.','sadoje'); ?></em>
                                            </th>
                                            <th><strong><?php _e('Rango del Precio Paquete','sadoje'); ?></strong><br/>
                                                <em><?php _e('El precio paquete se aplica a todos los productos cuya cantidad se encuentre en el rango de valores indicados en este campo.','sadoje'); ?></em>
                                            </th>
                                            <th><strong><?php _e('Rango del Precio Caja','sadoje'); ?></strong><br/>
                                                <em><?php _e('El precio caja se aplica a todos los productos cuya cantidad sea superior al valor indicado en este campo.','sadoje'); ?></em>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in rows"
                                            v-bind:id="'row_'+index" 
                                            v-bind:class = "(index % 2 === 0)?'alternate':''"
                                        >
                                                <th>
                                                    <?php _e('Desde','sadoje'); ?>
                                                    <input type="number" 
                                                        autocomplete="off"
                                                        size="4"
                                                        v-model="row.unitpricemin"
                                                        :name="'unitPriceMin['+index+']'"
                                                        @input="updateRows()"
                                                    >
                                                    <?php _e('hasta','sadoje'); ?>
                                                    <input type="number" 
                                                        autocomplete="off"
                                                        size="4"
                                                        v-model="row.unitpricemax"
                                                        :name="'unitPriceMax['+index+']'"
                                                        @input="updateRows()"
                                                    >
                                                </th>
                                                <th>
                                                    <?php _e('Desde','sadoje'); ?>
                                                    <input type="number" 
                                                        autocomplete="off"
                                                        size="4"
                                                        v-model="row.packagepricemin"
                                                        :name="'unitPackageMin['+index+']'"
                                                        @input="updateRows()"
                                                    >
                                                    <?php _e('hasta','sadoje'); ?>
                                                    <input type="number" 
                                                        autocomplete="off"
                                                        size="4"
                                                        v-model="row.packagepricemax"
                                                        :name="'unitPackageMax['+index+']'"
                                                        @input="updateRows()"
                                                    >
                                                </th>
                                                <th>
                                                    <?php _e('Desde','sadoje'); ?>
                                                    <input type="number" 
                                                        autocomplete="off"
                                                        size="4"
                                                        v-model="row.boxpricemin"
                                                        :name="'unitBoxMin['+index+']'"
                                                        @input="updateRows()"
                                                    >
                                                </th>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php submit_button(); ?>  
                            </form> 

						</div><!-- .inside -->
					</div><!-- #settings-profit -->

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
					</div><!-- #settings-bagged -->

                    <div id="settings-stamping"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Estampación','sadoje'); ?></span>
                        </h2>
						<div class="inside">
                            <form id="formStamping"
                                method="POST" 
                                action="options.php"
                                data-apiurl = "<?php echo $urlspace; ?>"
                                class="hide-form-table"
                            >
                                <?php 
                                    settings_fields( 'customisations-for-cg-woo-grid_budgeteer_stamping' );
                                    do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_stamping' ); 
                                ?>

                                <table id="tableStamping"
                                    class="widefat"
                                >
                                    <thead>
                                        <tr>
                                            <th class="row-title"><?php _e('Tipo de estampación','sadoje'); ?></th>
                                            <th class="row-title"><?php _e('Área de estampación','sadoje'); ?></th>
                                            <th><?php _e('Precio estampación por prenda (prendas blancas)','sadoje'); ?></th>
                                            <th><?php _e('Precio estampación por prenda (prendas de color)','sadoje'); ?></th>
                                            <th><?php _e('Precio adicional por pantalla y fotolitos','sadoje'); ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(row, index) in rows">
                                            <tr v-bind:id="'row_'+index" 
                                                v-bind:class = "(index % 2 == 1)?'':'alternate'"
                                            >
                                                    <th rowspan="2">
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="30"
                                                            v-model="row.stamping"
                                                            :name="'stamping['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                                    <th>
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="30"
                                                            v-model="row.area1"
                                                            :name="'stamping['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                                    <th>
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="4"
                                                            v-model="row.white_price1"
                                                            :name="'white_price['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                                    <th>
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="4"
                                                            v-model="row.color_price1"
                                                            :name="'color_price['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                                    <th>
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="4"
                                                            v-model="row.screen_price1"
                                                            :name="'screen_price['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                                    <th style="text-align:right" 
                                                        rowspan="2"
                                                    >
                                                        <button class="button button-secondary button-delete" 
                                                            style="background:#DC3232;color:#FFF;border-color:#FFF;"
                                                            @click="deleteRow(index, $event)"
                                                        ><?php echo __('Borrar','sadoje'); ?></button>
                                                    </th>
                                            </tr>
                                            <tr v-bind:id="'row_'+index" 
                                                v-bind:class = "(index % 2 == 1)?'':'alternate'"
                                            >
                                                    <th>
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="30"
                                                            v-model="row.area2"
                                                            :name="'stamping['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                                    <th>
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="4"
                                                            v-model="row.white_price2"
                                                            :name="'white_price['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                                    <th>
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="4"
                                                            v-model="row.color_price2"
                                                            :name="'color_price['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                                    <th>
                                                        <input type="text" 
                                                            autocomplete="off"
                                                            size="4"
                                                            v-model="row.screen_price2"
                                                            :name="'screen_price['+index+']'"
                                                            @input="updateRows()"
                                                        >
                                                    </th>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6">
                                                <p style="text-align:right">
                                                    <button class="button button-secondary" 
                                                        @click="addRow($event)"
                                                    ><?php echo __('Añadir estampación','sadoje'); ?></button>
                                                </p>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>

                                <hr/>


                                <table id="tableStampingWording"
                                    class="widefat"
                                >
                                    <thead>
                                        <tr>
                                            <th class="row-title" 
                                                colspan="4"
                                            >
                                                <strong><?php _e('Títulos','sadoje'); ?></strong><br/>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="alternate">
                                            <th class="row-title">
                                                <strong><?php _e('Titulo 1','sadoje'); ?></strong><br/>
                                            </th>
                                            <td>
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.title1"
                                                    :name="'title1['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                            <th>
                                                <strong>
                                                    <?php _e('Opción por defecto','sadoje'); ?>
                                                </strong>
                                            </th>
                                            <td>
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.defaultSelect1"
                                                    :name="'titleRear1['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                        </tr>
                                        <tr class="alternate">
                                            <th>
                                                <strong>
                                                    <?php _e('Caja de selección estampación delantera','sadoje'); ?>
                                                </strong>
                                            </th>
                                            <td>
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.titleFront1"
                                                    :name="'titleFront1['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                            <th>
                                                <strong>
                                                    <?php _e('Caja de selección estampación trasera','sadoje'); ?>
                                                </strong>
                                            </th>
                                            <td>
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.titleRear1"
                                                    :name="'titleRear1['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="row-title">
                                                <strong><?php _e('Titulo 2','sadoje'); ?></strong><br/>
                                            </th>
                                            <td colspan="3">
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.title2"
                                                    :name="'title2['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                <strong>
                                                    <?php _e('Caja de selección área delantera','sadoje'); ?>
                                                </strong>
                                            </th>
                                            <td>
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.titleFront2"
                                                    :name="'titleFront2['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                            <th>
                                                <strong>
                                                    <?php _e('Caja de selección área trasera','sadoje'); ?>
                                                </strong>
                                            </th>
                                            <td>
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.titleRear2"
                                                    :name="'titleRear2['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                <strong>
                                                    <?php _e('Opción por defecto','sadoje'); ?>
                                                </strong>
                                            </th>
                                            <td>
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.defaultSelect2"
                                                    :name="'titleRear1['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                            <th>
                                                <strong>
                                                    <?php _e('Mensaje cuando está desactivado ','sadoje'); ?>
                                                </strong>
                                            </th>
                                            <td>
                                                <input type="text" 
                                                    autocomplete="off"
                                                    size="40"
                                                    v-model="wording.disabledSelect2"
                                                    :name="'titleRear1['+index+']'"
                                                    @input="updateWording()"
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <?php submit_button(); ?>  
                            </form> 


                                
                                <p><tt id="results"></tt></p>


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
					</div><!-- #settings-stamping -->


                    <div id="settings-profit"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Margen de beneficio por cantidades','sadoje'); ?></span>
                        </h2>
						<div class="inside">
                            <form id="formProfit"
                                method="POST" 
                                action="options.php"
                                data-apiurl = "<?php echo $urlspace; ?>"
                                class="hide-form-table"
                            >
                                <?php 
                                    settings_fields( 'customisations-for-cg-woo-grid_budgeteer_profit' );
                                    do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_profit' ); 
                                ?>

                                <table id="tableProfit"
                                    class="widefat"
                                >
                                    <thead>
                                        <tr>
                                            <th class="row-title"><?php _e('Cantidad','sadoje'); ?></th>
                                            <th><?php _e('Beneficio','sadoje'); ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in rows"
                                            v-bind:id="'row_'+index" 
                                            v-bind:class = "(index % 2 === 0)?'alternate':''"
                                        >
                                                <th>
                                                    <?php _e('Desde','sadoje'); ?>
                                                    <input type="number" 
                                                        autocomplete="off"
                                                        size="8"
                                                        v-model="row.min"
                                                        :name="'min['+index+']'"
                                                        @input="updateRows()"
                                                    >
                                                    <?php _e('hasta','sadoje'); ?>
                                                    <input type="number" 
                                                        autocomplete="off"
                                                        size="8"
                                                        v-model="row.max"
                                                        :name="'max['+index+']'"
                                                        @input="updateRows()"
                                                    >
                                                </th>
                                                <th>
                                                    <input type="number" 
                                                        autocomplete="off"
                                                        size="4"
                                                        v-model="row.quantity"
                                                        :name="'quantity['+index+']'"
                                                        @input="updateRows()"
                                                    >
                                                </th>
                                                <th style="text-align:right">
                                                    <button class="button button-secondary button-delete" 
                                                        style="background:#DC3232;color:#FFF;border-color:#FFF;"
                                                        @click="deleteRow(index, $event)"
                                                    ><?php echo __('Borrar','sadoje'); ?></button>
                                                </th>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">
                                                <p style="text-align:right">
                                                    <button class="button button-secondary" 
                                                        @click="addRow($event)"
                                                    ><?php echo __('Añadir margen','sadoje'); ?></button>
                                                </p>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <?php submit_button(); ?>  
                            </form> 

						</div><!-- .inside -->
					</div><!-- #settings-profit -->

                    <div id="settings-info"
                        class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Contenido','sadoje'); ?></span>
                        </h2>
						<div class="inside">
                            <p><?php _e('Mensajes y cabeceras del presupuestador','sadoje'); ?></p>
                            <form method="POST" action="options.php">  
                                <?php 
                                    settings_fields( 'customisations-for-cg-woo-grid_budgeteer_info' );
                                    do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_info' ); 
                                ?>       
                                <?php submit_button(); ?>  
                            </form> 
						</div><!-- .inside -->
					</div><!-- #settings-info -->


				</div><!-- .meta-box-sortables .ui-sortable -->
			</div><!-- #post-body-content-->
			
		</div><!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
    </div> <!-- #poststuff -->
</div> <!-- .wrap -->
