<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    cg-woo-grid
 * @subpackage cg-woo-grid/includes
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

?>


<!-- Vue -->
<div id="appTest">
    <counter></counter>
</div>
<!-- / Vue  -->

<div id="cdg_popup" style="display: none;">
    <div class="white">
        <h3 class="text-center"></h3>
        <p class="text-center"></p>
        <div class="text-center buttons">
            <button class="btn btn-danger compra">
                <?php _e("Seguir comprando","cg-woo-grid");?>
            </button>
            <a href="<?php echo get_permalink(wc_get_page_id('cart')); ?>" 
                class="btn btn-danger compra"
            ><?php _e("Ir al carrito de compras","cg-woo-grid");?></a>
            <button class="btn btn-danger cerrar">
                <?php _e("Cerrar","cg-woo-grid");?>
            </button>
        </div>
    </div>
</div>

<form id="cdg_form" class="cdg_box">

    <h3><?php _e("Elige colores y tallas para hacer tu presupuesto","cg-woo-grid");?></h3>

    <div class="row sidebar_configurator">
        <div class="cdg_total">
            <div class="loading" 
                style="display: none;"
            >
                <i class="fa fa-refresh fa-spin"></i>
            </div>
            <div>
                <h3><?php _e("Tu presupuesto","cg-woo-grid");?></h3>
                <div id="cdg_units" 
                    class="units"
                >
                    <div>
                        <?php _e("Unidades blancas:","cg-woo-grid");?> 
                        <input id="cdg_total_b" 
                            type="text" 
                            name="total_b" 
                            value="0 uds." 
                            disabled
                        >
                    </div>
                    <div>
                        <?php _e("Unidades de color:","cg-woo-grid");?> 
                        <input id="cdg_total_c" 
                            type="text" 
                            name="total_c" 
                            value="0 uds." 
                            disabled
                        >
                    </div>
                    <div style="margin-bottom: 15px;text-transform: none;">
                        <?php _e("Precio unitario:","cg-woo-grid");?> 
                        <input id="cdg_price_u" 
                            type="text" 
                            name="price_u" 
                            value="<?php echo strip_tags(wc_price(0)); ?>" 
                            disabled
                        >
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <div id="cdg_prizes_table" class="units">
                    
                    <table id="prizes_table">
                        <thead>
                            <tr>
                                <th><?php _e("Color","cg-woo-grid");?></th>
                                <th><?php _e("Talla","cg-woo-grid");?></th>
                                <th><?php _e("Precio","cg-woo-grid");?></th>
                                <th><?php _e("Unidades","cg-woo-grid");?></th>
                                <th class="text-right"><?php _e("Importe","cg-woo-grid");?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="total">
                    <h5 style="margin-bottom: 15px;">
                        <small>( <?php _e("Precio unitario:","cg-woo-grid");?></small> 
                        <span id="span_price_u"><?php echo wc_price(0); ?></span> )
                    </h5>
                    <h5>
                        <small><?php _e("Subotal:","cg-woo-grid");?></small> 
                        <span id="TotalPrice"><?php echo wc_price(0); ?></span>
                    </h5>
                    <h5>
                        <small><?php _e("IVA:","cg-woo-grid");?></small> 
                        <span id="taxesRow"><?php echo wc_price(0); ?></span>
                    </h5>
                    <h4>
                        <small><?php _e("Total:","cg-woo-grid");?></small> 
                        <span id="TotalPriceIVA"><?php echo wc_price(0); ?></span> 
                        <small></small>
                    </h4>
                </div>
            </div>
            <button id="cdg_add_products" 
                class="btn btn-primary text-uppercase text-center"
                disabled 
            >
                <?php _e("Añadir a la cesta","cg-woo-grid");?>
            </button>
            <p>&nbsp;</p>
            <input type="hidden" 
                name="add-to-cart" 
                value="<?php echo get_queried_object_id(); ?>"
            >
            <input type="hidden" 
                name="product_id" 
                value="<?php echo get_queried_object_id(); ?>"
            >
            <input id="cdg_description" 
                type="hidden" 
                name="custom_options[description]" 
                value=""
            ><br>
            <input id="cdg_price" 
                type="hidden" 
                name="custom_options[custom_price]" 
                value="0"
            ><br>
        </div>

        <div id="cdg_sizes" 
            class="sw_prize"
        >
            <!-- <div class="cdg_cbar">
                <div><span style="color: red">Cantidad mínima de pedido <?=$minimum . ' UDS'; ?></div>
            </div> -->

            <div class="cdg_cbar">
                <span class="bar"><span></span></span>
                0
                <div class="pull-right">
                    <span class="pull-right__header" 
                        style="color: red"
                    ><?php _e("Cantidad mínima de pedido","cg-woo-grid");?> | </span>
                    <span id="cdg_total">0</span>
                    <span class="pull-right__slash">/</span>
                    <span id="cdg_total50"><?php echo (int) $minimum > 0 ? (int) $minimum : 50; ?></span> 
                    <span class="uds"><?php _e("UDS.","cg-woo-grid");?></span>
                </div>
            </div>

            <!-- Vue -->
            <div id="appMatrix">
                <attributes-matrix></attributes-matrix>
            </div>
            <!-- / Vue  -->
            <div id="phpMatrix">
            <?php foreach ($matrix as $key => $value) { ?>
                <div class="cdg_product" 
                    data-color="<?php echo strtolower($value['y']); ?>"
                >
                    <span>
                        <div>
                            <?php echo $value['image']; ?><br>
                            <?php echo strtolower($value['y']); ?>
                        </div>
                    </span>
                    <div>
                        <div>
                            <?php foreach ($value['x'] as $keyX => $valueX) { ?>
                                <span data-talla="<?=$valueX->name?>">
                                    <?=$valueX->name?><br>
                                    <input type="number" 
                                        name="<?=$valueX->slug?>" 
                                        value="0" 
                                        min="0" 
                                        max="500"
                                    >
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
    </div>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
</form>
