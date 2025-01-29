<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin. It will save all the settings in the wp_options table.
 *
 *
 * The <form> with method="post" and action="options.php" is used on settings pages to save plugin or theme options.
 * It works with the Settings API to validate and store settings in the wp_options database table.
 * Key functions:
 *     settings_fields($option_group): Adds security fields.
 *     do_settings_sections($page): Displays registered settings fields and sections.
 *     submit_button(): Adds the submit button.
 * When submitted, the form sends data to options.php, where WordPress processes and saves the settings.
 * It’s a standard setup for creating configurable admin options.
 *
 * @since      1.0.0
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

// TODO: BUILD SCSS FILES FOR admin/css/sadoje.css

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
                            <span><?php echo __('Precio a aplicar por cantidades','sadoje'); ?></span>
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
                    </div><!-- #settings-baseprice -->

                    <!-- Embolsado -->
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

                    <!-- Estampación (Viejo) -->
                    <div id="settings-stamping"
                         class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Estampación Viejo','sadoje'); ?></span>
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
                                        <th><?php _e('Precio adicional por pantalla y fotolitos','sadoje'); ?></th>
                                        <th class="row-title"><?php _e('Área de estampación','sadoje'); ?></th>
                                        <th><?php _e('Precio estampación por prenda (prendas blancas)','sadoje'); ?></th>
                                        <th><?php _e('Precio estampación por prenda (prendas de color)','sadoje'); ?></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <template v-for="(row, index) in rows">
                                        <tr v-bind:id="'row1_'+index"
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
                                            <th rowspan="2">
                                                <input type="number"
                                                       step="0.05"
                                                       min="0"
                                                       autocomplete="off"
                                                       size="5"
                                                       v-model="row.screen_price"
                                                       :name="'screen_price['+index+']'"
                                                       @input="updateRows()"
                                                >
                                            </th>
                                            <th>
                                                <input type="text"
                                                       autocomplete="off"
                                                       size="30"
                                                       v-model="row.area1"
                                                       :name="'area1['+index+']'"
                                                       @input="updateRows()"
                                                >
                                            </th>
                                            <th>
                                                <input type="number"
                                                       step="0.05"
                                                       min="0"
                                                       autocomplete="off"
                                                       size="5"
                                                       v-model="row.white_price1"
                                                       :name="'white_price1['+index+']'"
                                                       @input="updateRows()"
                                                >
                                            </th>
                                            <th>
                                                <input type="number"
                                                       step="0.05"
                                                       min="0"
                                                       autocomplete="off"
                                                       size="5"
                                                       v-model="row.color_price1"
                                                       :name="'color_price1['+index+']'"
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
                                        <tr v-bind:id="'row2_'+index"
                                            v-bind:class = "(index % 2 == 1)?'':'alternate'"
                                        >
                                            <th>
                                                <input type="text"
                                                       autocomplete="off"
                                                       size="30"
                                                       v-model="row.area2"
                                                       :name="'area2['+index+']'"
                                                       @input="updateRows()"
                                                >
                                            </th>
                                            <th>
                                                <input type="number"
                                                       step="0.05"
                                                       min="0"
                                                       autocomplete="off"
                                                       size="5"
                                                       v-model="row.white_price2"
                                                       :name="'white_price2['+index+']'"
                                                       @input="updateRows()"
                                                >
                                            </th>
                                            <th>
                                                <input type="number"
                                                       step="0.05"
                                                       min="0"
                                                       autocomplete="off"
                                                       size="5"
                                                       v-model="row.color_price2"
                                                       :name="'color_price2['+index+']'"
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
                                            <strong><?php _e('Titulo Tipos','sadoje'); ?></strong><br/>
                                        </th>
                                        <td>
                                            <input type="text"
                                                   autocomplete="off"
                                                   size="40"
                                                   v-model="wording.title1"
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
                                                   @input="updateWording()"
                                            >
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="row-title">
                                            <strong><?php _e('Titulo Áreas','sadoje'); ?></strong><br/>
                                        </th>
                                        <td colspan="3">
                                            <input type="text"
                                                   autocomplete="off"
                                                   size="40"
                                                   v-model="wording.title2"
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
                                                   @input="updateWording()"
                                            >
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <?php submit_button(); ?>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- #settings-stamping -->


                    <!-- Estampación y Configurador General-->
                    <div id="settings-imprint"
                         class="postbox"
                    >
                        <h2>
                            <span><?php echo __('Caja de Estampación (Presupuestador)','sadoje'); ?></span>
                        </h2>
                        <div class="inside">
                            <form id="formImprint"
                                  method="POST"
                                  action="options.php"
                                  data-apiurl = "<?php echo $urlspace; ?>"
                                  class="hide-form-table"
                            >
                                <?php
                                settings_fields( 'customisations-for-cg-woo-grid_budgeteer_imprint' );
                                do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_imprint' );
                                ?>

                                <table id="tableImprint"
                                       class="widefat"
                                >
                                    <thead>
                                    <tr>
                                        <th class="row-title"><?php _e('Variable','sadoje'); ?></th>
                                        <th><?php _e('Etiqueta','sadoje'); ?></th>
                                        <th><?php _e('Valores','sadoje'); ?></th>
                                        <th><?php _e('Ocultar en el front-end','sadoje'); ?></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <template v-for="(row, index) in rows">
                                        <tr v-bind:id="'row_'+index"
                                            v-bind:class="[(index % 2 == 1 ? 'alternate' : ''), row.imprint_unpublish ? 'unpublished' : '']"
                                        >
                                            <th>
                                                <input type="text"
                                                       autocomplete="off"
                                                       size="30"
                                                       v-model="row.imprint"
                                                       :name="'imprint['+index+']'"
                                                       @input="updateRows()"
                                                >
                                            </th>
                                            <th>
                                                <input type="text"
                                                       autocomplete="off"
                                                       size="30"
                                                       v-model="row.imprint_label"
                                                       :name="'imprint_label['+index+']'"
                                                       @input="updateRows()"
                                                >
                                            </th>
                                            <th>
                                                    <textarea
                                                            v-model="row.imprint_values"
                                                            :name="'imprint_values['+index+']'"
                                                            @input="sanitize(index)"
                                                    ></textarea>
                                            </th>
                                            <th>
                                                <input type="checkbox"
                                                       v-bind:disabled="index===0"
                                                       v-model="row.imprint_hide"
                                                       value="true"
                                                       @change="updateRows()"
                                                />
                                            </th>
                                            <th style="text-align:right">
                                                <button  type="button"
                                                         class="button button-secondary button-unpublish"
                                                         style="background:#FFA500; color:#FFF; border-color:#FFF;"
                                                         @click="row.imprint_unpublish = !row.imprint_unpublish; updateRows()"
                                                >
                                                    {{ row.imprint_unpublish ? '<?php echo __('Activar','sadoje'); ?>' : '<?php echo __('Desactivar','sadoje'); ?>' }}
                                                </button>

                                                <button class="button button-secondary button-delete"
                                                        style="background:#DC3232;color:#FFF;border-color:#FFF;"
                                                        @click="deleteRow(index, $event)"
                                                ><?php echo __('Borrar','sadoje'); ?></button>
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
                                                ><?php echo __('Añadir variable','sadoje'); ?></button>
                                            </p>
                                        </th>
                                    </tr>
                                    </tfoot>
                                </table>

                                <?php submit_button(); ?>
                            </form>
                        </div><!-- .inside -->

                        <!-- Configurador General  -->
                        <h2>
                            <span><?php echo __('Configuración general de los tipos de estampación','sadoje'); ?></span>
                        </h2>
                        <div class="inside">
                            <form id="formPrices"
                                  method="POST"
                                  action="options.php"
                                  data-apiurl = "<?php echo $urlspace; ?>"
                                  class="hide-form-table"
                            >
                                <?php
                                settings_fields( 'customisations-for-cg-woo-grid_budgeteer_prices' );
                                do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_prices' );
                                ?>

                                <table id="tablePrices"
                                       class="widefat"
                                >
                                    <thead>
                                    <tr>
                                        <template v-for="(column, index) in columns">
                                            <th class="row-title"
                                                v-text="column.title"
                                            ></th>
                                        </template>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <template v-for="(row, index) in rows">
                                        <tr v-bind:id="'row_a_'+index"
                                            v-bind:class = "(index % 2 == 1)?'':'alternate'"
                                        >
                                            <th v-for="(column, index) in columns">
                                                <select v-model="row[column.slug]"
                                                        @change="updateRows()">
                                                    <option disabled
                                                            value="">
                                                        <?php echo __('Seleccione un valor','sadoje'); ?>
                                                    </option>
                                                    <option  value="all">
                                                        <?php echo __('Todos','sadoje'); ?>
                                                    </option>
                                                    <option v-for="value in column.values" :value="value">
                                                        {{value}}
                                                    </option>
                                                </select>
                                            </th>


                                            <th style="text-align:right" rowspan="2">
                                                <button class="button button-secondary button-delete"
                                                        style="background:#DC3232;color:#FFF;border-color:#FFF;"
                                                        @click="deleteRow(index, $event)"
                                                ><?php echo __('Borrar','sadoje'); ?></button>
                                            </th>
                                        </tr>
                                        <tr v-bind:id="'row_b_'+index"
                                            v-bind:class = "(index % 2 == 1)?'':'alternate'"
                                        >
                                            <th colspan="5" >
                                                <div style="display:flex; justify-content: flex-start">
                                                    <ul>
                                                        <li>
                                                            <input type="checkbox"
                                                                   v-bind:id="'pricescreen_'+index"
                                                                   v-model="row.pricescreen"
                                                                   value="screen"
                                                                   @change="updateRows()"
                                                            />
                                                            <label v-bind:for="'pricescreen_'+index">
                                                                <?php esc_html_e('Precio adicional por pantalla y fotolitos','sadoje'); ?>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                    <ul>
                                                        <li><input type="text"
                                                                   autocomplete="off"
                                                                   size="10"
                                                                   v-model="row.pricescreenval"
                                                                   @input="updateRows()"
                                                                   :disabled="!row.pricescreen"
                                                            ></li>
                                                    </ul>
                                                    <ul>
                                                        <li>
                                                            <input type="checkbox"
                                                                   v-bind:id="'pricecloth_'+index"
                                                                   v-model="row.pricecloth"
                                                                   value="cloth"
                                                                   @change="updateRows()"
                                                            />
                                                            <label v-bind:for="'pricecloth_'+index">
                                                                <?php esc_html_e('Precio estampación por prenda ','sadoje'); ?>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                    <ul>
                                                        <li><input type="text"
                                                                   autocomplete="off"
                                                                   size="10"
                                                                   v-model="row.priceclothval"
                                                                   @input="updateRows()"
                                                                   :disabled="!row.pricecloth"
                                                            ></li>
                                                    </ul>
                                                </div>
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
                                                ><?php echo __('Añadir configuración','sadoje'); ?></button>
                                            </p>
                                        </th>
                                    </tr>
                                    </tfoot>
                                </table>

                                <?php submit_button(); ?>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- #settings-stamping -->

                    <!-- Configurador General Margen de beneficio -->
                    <div id="settings-profit"
                         class="postbox"
                    >

                        <div class="inside">
                            <form id="formProfit"
                                  method="POST"
                                  action="options.php"
                                  data-apiurl = "<?php echo $urlspace; ?>"
                                  class="hide-form-table"
                            >
                                <div class="profit-header">

                                    <h2><span><?php echo __('Margen de beneficio por cantidades','sadoje'); ?></span></h2>
                                    <p v-if="activeTab === 'category'" @click="addProfitTable('category')" class="button button-secondary">
                                        <?php esc_html_e('Añadir nueva tabla categoría','sadoje'); ?>
                                    </p>

                                    <p v-else-if="activeTab === 'product'" @click="addProfitTable('product')" class="button button-secondary">
                                        <?php esc_html_e('Añadir nueva tabla productos','sadoje'); ?>
                                    </p>
                                </div>
                                <div class="profit-tabs">
                                    <p  :class="[activeTab === 'general' ? 'active-tab' : '', 'tabs']"
                                        @click="currentProfitTab('general')"
                                    >
                                        <?php echo __('General','sadoje'); ?>
                                    </p>
                                    <p  :class="[activeTab === 'category' ? 'active-tab' : '', 'tabs']"
                                        @click="currentProfitTab('category')">
                                        <?php echo __('Categoría','sadoje'); ?>
                                    </p>
                                    <p  :class="[activeTab === 'product' ? 'active-tab' : '', 'tabs']"
                                        @click="currentProfitTab('product')">
                                        <?php echo __('Producto','sadoje'); ?>
                                    </p>
                                </div>
                                <?php
                                settings_fields( 'customisations-for-cg-woo-grid_budgeteer_profit' );
                                do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_profit' );
                                ?>

                                <div v-if="activeTab === 'general'">
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
                                        <tr v-for="(row, index) in profitTable.general"
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
                                                       v-model="row.profit"
                                                       :name="'profit['+index+']'"
                                                       @input="updateRows()"
                                                >
                                            </th>
                                            <th style="text-align:right">
                                                <button class="button button-secondary button-delete"
                                                        style="background:#DC3232;color:#FFF;border-color:#FFF;"
                                                        @click="deleteRow(index, $event, 'general')"
                                                ><?php echo __('Borrar','sadoje'); ?></button>
                                            </th>
                                        </tr>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="5">
                                                <p style="text-align:right">
                                                    <button class="button button-secondary"
                                                            @click="addRow($event, 'general')"
                                                    ><?php echo __('Añadir margen','sadoje'); ?></button>
                                                </p>
                                            </th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div v-else-if="activeTab === 'category'">
                                <!--Category selector-->
                                    <div :class="['input-selector', isAccordionOpen[i] ? 'is-open' : 'is-close']" v-for="(categoryRows, i) in profitTable.category">
                                        <div class="input-selector__header" @click="toggleAccordion(i)">
                                            <p class="input-selector__title"><?php esc_html_e('Grupo ','sadoje'); ?>{{ i + 1 }}</p>
                                            <div>
                                                <span class="accordion-icon" @click="toggleAccordion(i)">{{ isAccordionOpen[i] ? '▲' : '▼' }}</span>
                                            </div>
                                        </div>
                                        <p class="input-selector__title"><?php esc_html_e('Selector de Categorías','sadoje'); ?></p>

                                        <div class="input-selector__search-container">
                                            <input
                                                    v-model="searchCategory"
                                                    @focus="isOpen[i] = true"
                                                    @blur="closeDropdown"
                                                    type="text"
                                                    @input="searchCategories(i)"
                                                    placeholder="<?php esc_html_e('Buscar categorías','sadoje'); ?>"
                                                    class="input-selector__search-input"
                                            />
                                            <div v-if="isOpen[i]" class="input-selector__dropdown">
                                                <div
                                                        v-for="category in filteredCategories"
                                                        :key="category.id"
                                                        @mousedown.prevent="toggleCategory(category, i)"
                                                        class="input-selector__dropdown-item"
                                                >
                                                    {{ category.name }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="input-selector__selected-categories">
                                            <div
                                                v-for="category in selectedCategories[i]"
                                                class="input-selector__chip"
                                                :key="category.id"
                                            >
                                                {{ category.name }}

                                                <div @click="removeCategory(category, i)" class="input-selector__chip-remove">
                                                    <span class="input-selector__chip-remove-icon">&times;</span>
                                                </div>
                                            </div>
                                        </div>
                                <!--Category selector-->

                                        <table id="tableProfit" class="widefat">
                                        <thead>
                                        <tr>
                                            <th class="row-title"><?php _e('Cantidad','sadoje'); ?></th>
                                            <th><?php _e('Beneficio','sadoje'); ?></th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(row, index) in categoryRows.rows"
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
                                                       @input="updateRows(i)"
                                                >
                                                <?php _e('hasta','sadoje'); ?>
                                                <input type="number"
                                                       autocomplete="off"
                                                       size="8"
                                                       v-model="row.max"
                                                       :name="'max['+index+']'"
                                                       @input="updateRows(i)"
                                                >
                                            </th>
                                            <th>
                                                <input type="number"
                                                       autocomplete="off"
                                                       size="4"
                                                       v-model="row.profit"
                                                       :name="'profit['+index+']'"
                                                       @input="updateRows(i)"
                                                >
                                            </th>
                                            <th style="text-align:right">
                                                <button class="button button-secondary button-delete"
                                                        style="background:#DC3232;color:#FFF;border-color:#FFF;"
                                                        @click="deleteRow(index, $event, 'category', i)"
                                                ><?php echo __('Borrar','sadoje'); ?></button>
                                            </th>
                                        </tr>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="5">
                                                <p style="text-align:right">
                                                    <button class="button button-secondary"
                                                            @click.prevent="addRow($event, 'category', i)"
                                                    ><?php echo __('Añadir margen','sadoje'); ?></button>

                                                    <div class="button button-secondary button-delete"
                                                            style="background:#DC3232;color:#FFF;border-color:#FFF;margin-left: 10px;float: right"
                                                            @click="removeProfitTable('category', i)"
                                                    ><?php echo __('Borrar tabla','sadoje'); ?></div>
                                                </p>
                                            </th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                    </div>
                                </div>

                                <div v-else-if="activeTab === 'product'">
                                    <div :class="['input-selector', isAccordionOpen[i] ? 'is-open' : 'is-close']" v-for="(productRows, i) in profitTable.product">
                                        <div class="input-selector__header" @click="toggleAccordion(i)">
                                            <p class="input-selector__title"><?php esc_html_e('Grupo ','sadoje'); ?>{{ i + 1 }}</p>
                                            <div>
                                                <span class="accordion-icon" @click="toggleAccordion(i)">{{ isAccordionOpen[i] ? '▲' : '▼' }}</span>
                                            </div>
                                        </div>
                                        <p class="input-selector__title"><?php esc_html_e('Selector de Productos','sadoje'); ?></p>
                                        <div class="input-selector__search-container">
                                            <input
                                                    v-model="searchProduct"
                                                    @focus="isOpen[i] = true"
                                                    @blur="closeDropdown"
                                                    type="text"
                                                    @input="searchProducts(i)"
                                                    placeholder="<?php esc_html_e('Buscar productos','sadoje'); ?>"
                                                    class="input-selector__search-input"
                                            />
                                            <div v-if="isOpen[i]" class="input-selector__dropdown">
                                                <div
                                                        v-for="product in filteredProducts"
                                                        :key="product.id"
                                                        @mousedown.prevent="toggleProduct(product, i)"
                                                        class="input-selector__dropdown-item"
                                                >
                                                    {{ product.title }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-selector__selected-categories">
                                            <div
                                                    v-for="product in selectedProducts[i]"
                                                    :key="product.id"
                                                    class="input-selector__chip"
                                            >
                                                {{ product.title }}
                                                <button @click="removeProduct(product, i)" class="input-selector__chip-remove">
                                                    <span class="input-selector__chip-remove-icon">&times;</span>
                                                </button>
                                            </div>
                                        </div>

                                        <table id="tableProfit" class="widefat">
                                            <thead>
                                            <tr>
                                                <th class="row-title"><?php _e('Cantidad','sadoje'); ?></th>
                                                <th><?php _e('Beneficio','sadoje'); ?></th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(row, index) in productRows.rows"
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
                                                           @input="updateRows(i)"
                                                    >
                                                    <?php _e('hasta','sadoje'); ?>
                                                    <input type="number"
                                                           autocomplete="off"
                                                           size="8"
                                                           v-model="row.max"
                                                           :name="'max['+index+']'"
                                                           @input="updateRows(i)"
                                                    >
                                                </th>
                                                <th>
                                                    <input type="number"
                                                           autocomplete="off"
                                                           size="4"
                                                           v-model="row.profit"
                                                           :name="'profit['+index+']'"
                                                           @input="updateRows(i)"
                                                    >
                                                </th>
                                                <th style="text-align:right">
                                                    <button class="button button-secondary button-delete"
                                                            style="background:#DC3232;color:#FFF;border-color:#FFF;"
                                                            @click="deleteRow(index, $event, 'product', i)"
                                                    ><?php echo __('Borrar','sadoje'); ?></button>
                                                </th>
                                            </tr>
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <th colspan="5">
                                                    <p style="text-align:right">
                                                        <button class="button button-secondary"
                                                                @click="addRow($event, 'product', i)"
                                                        ><?php echo __('Añadir margen','sadoje'); ?></button>

                                                        <div class="button button-secondary button-delete"
                                                             style="background:#DC3232;color:#FFF;border-color:#FFF;margin-left: 10px;float: right"
                                                                @click="removeProfitTable('product', i)"
                                                        ><?php echo __('Borrar tabla','sadoje'); ?></div>
                                                    </p>
                                                </th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                </div>
                                <?php submit_button(); ?>
                            </form>
                        </div>
                        <!-- Configurador General Margen de beneficio -->
                        <!-- .inside -->

                        <h2>
                            <span><?php echo __('Tabla de precios','sadoje'); ?></span>
                        </h2>
                        <div class="inside">
                            <form
                                id="formPriceTable"
                                method="POST"
                                action="options.php"
                                data-apiurl = "<?php echo $urlspace; ?>"
                            >
                                <div class="profit-header">
                                    <h2><span><?php echo __('Margen de beneficio por cantidades','sadoje'); ?></span></h2>
                                    <p v-if="activeTab === 'category'" @click="addPriceTable('category')" class="button button-secondary">
                                        <?php esc_html_e('Añadir nueva tabla categoría','sadoje'); ?>
                                    </p>

                                    <p v-else-if="activeTab === 'product'" @click="addPriceTable('product')" class="button button-secondary">
                                        <?php esc_html_e('Añadir nueva tabla productos','sadoje'); ?>
                                    </p>
                                </div>

                                <div class="price-tabs">
                                    <p  :class="[activeTab === 'general' ? 'active-tab' : '', 'tabs']"
                                        @click="currentPriceTab('general')"
                                    >
                                        <?php echo __('General','sadoje'); ?>
                                    </p>
                                    <p  :class="[activeTab === 'category' ? 'active-tab' : '', 'tabs']"
                                        @click="currentPriceTab('category')">
                                        <?php echo __('Categoría','sadoje'); ?>
                                    </p>
                                    <p  :class="[activeTab === 'product' ? 'active-tab' : '', 'tabs']"
                                        @click="currentPriceTab('product')">
                                        <?php echo __('Producto','sadoje'); ?>
                                    </p>
                                </div>

                                <div v-if="activeTab === 'general'" class="input-price-table">
                                    <label for="columns"><?php echo __('Columnas generales:','sadoje'); ?></label>
                                    <input name="columns" type="text" v-model="priceTable.general" @blur="updateColumns()" />
                                </div>

                                <div v-else-if="activeTab === 'category'" class="input-price-table">
                                    <div :class="['input-selector', isAccordionOpen[i] ? 'is-open' : 'is-close']" v-for="(productRows, i) in priceTable.category">
                                        <div class="input-selector__header" @click="toggleAccordion(i)">
                                            <p class="input-selector__title"><?php esc_html_e('Grupo ','sadoje'); ?>{{ i + 1 }}</p>
                                            <div>
                                                <span class="accordion-icon" @click="toggleAccordion(i)">{{ isAccordionOpen[i] ? '▲' : '▼' }}</span>
                                            </div>
                                        </div>
                                        <p class="input-selector__title"><?php esc_html_e('Selector de Categorías','sadoje'); ?></p>
                                        <div class="input-selector__search-container">
                                            <input
                                                    v-model="searchCategory"
                                                    @focus="isOpen[i] = true"
                                                    @blur="closeDropdown"
                                                    type="text"
                                                    @input="searchCategories(i)"
                                                    placeholder="<?php esc_html_e('Buscar categorías','sadoje'); ?>"
                                                    class="input-selector__search-input"
                                            />
                                            <div v-if="isOpen[i]" class="input-selector__dropdown">
                                                <div
                                                        v-for="category in filteredCategories"
                                                        :key="category.id"
                                                        @mousedown.prevent="toggleCategory(category, i)"
                                                        class="input-selector__dropdown-item"
                                                >
                                                    {{ category.name }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-selector__selected-categories">
                                            <div
                                                    v-for="category in selectedCategories[i]"
                                                    :key="category.id"
                                                    class="input-selector__chip"
                                            >
                                                {{ category.name }}
                                                <button @click="removeCategory(category, i)" class="input-selector__chip-remove">
                                                    <span class="input-selector__chip-remove-icon">&times;</span>
                                                </button>
                                            </div>
                                        </div>

                                        <label for="columns"><?php echo __('Columnas por categoría:','sadoje'); ?></label>
                                        <input name="columns" type="text" v-model="priceTable.category[i].rows" @blur="updateColumns(i)" />

                                        <div class="button button-secondary button-delete delete-table"
                                                style="background:#DC3232;color:#FFF;border-color:#FFF;margin-left: 10px;"
                                                @click="removePriceTable('category', i)"
                                        ><?php echo __('Borrar tabla','sadoje'); ?></div>
                                    </div>
                                </div>

                                <div v-else-if="activeTab === 'product'" class="input-price-table">
                                    <div :class="['input-selector', isAccordionOpen[i] ? 'is-open' : 'is-close']" v-for="(productRows, i) in priceTable.product">
                                        <div class="input-selector__header" @click="toggleAccordion(i)">
                                            <p class="input-selector__title"><?php esc_html_e('Grupo ','sadoje'); ?>{{ i + 1 }}</p>
                                            <div>
                                                <span class="accordion-icon" @click="toggleAccordion(i)">{{ isAccordionOpen[i] ? '▲' : '▼' }}</span>
                                            </div>
                                        </div>
                                        <p class="input-selector__title"><?php esc_html_e('Selector de Productos','sadoje'); ?></p>
                                        <div class="input-selector__search-container">
                                            <input
                                                    v-model="searchProduct"
                                                    @focus="isOpen[i] = true"
                                                    @blur="closeDropdown"
                                                    type="text"
                                                    @input="searchProducts(i)"
                                                    placeholder="<?php esc_html_e('Buscar productos','sadoje'); ?>"
                                                    class="input-selector__search-input"
                                            />
                                            <div v-if="isOpen[i]" class="input-selector__dropdown">
                                                <div
                                                        v-for="product in filteredProducts"
                                                        :key="product.id"
                                                        @mousedown.prevent="toggleProduct(product, i)"
                                                        class="input-selector__dropdown-item"
                                                >
                                                    {{ product.title }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-selector__selected-categories">
                                            <div
                                                    v-for="product in selectedProducts[i]"
                                                    :key="product.id"
                                                    class="input-selector__chip"
                                            >
                                                {{ product.title }}
                                                <button @click="removeProduct(product, i)" class="input-selector__chip-remove">
                                                    <span class="input-selector__chip-remove-icon">&times;</span>
                                                </button>
                                            </div>
                                        </div>

                                        <label for="columns"><?php echo __('Columnas por producto:','sadoje'); ?></label>
                                        <input name="columns" type="text" v-model="priceTable.product[i].rows" @blur="updateColumns(i)" />

                                        <div class="button button-secondary button-delete delete-table"
                                                style="background:#DC3232;color:#FFF;border-color:#FFF;margin-left: 10px;"
                                                @click="removePriceTable('product', i)"
                                        ><?php echo __('Borrar tabla','sadoje'); ?></div>
                                    </div>
                                </div>

                                <?php
                                settings_fields( 'customisations-for-cg-woo-grid_budgeteer_tableprices' );
                                do_settings_sections( 'customisations-for-cg-woo-grid_budgeteer_tableprices' );
                                ?>

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
