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

<div id="cdg_popup" 
    style="display: none;"
>
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

<div id="cdg_form" 
    class="cdg_box"
    data-currentproduct = "<?php echo get_queried_object_id() ?>"
>

    <h3><?php _e("Elige colores y tallas para hacer tu presupuesto","cg-woo-grid");?></h3>

    <div class="row sidebar_configurator">

        <!-- Vue -->
        <div id="appTotalBox">
            <total-box></total-box>
        </div>
        <!-- / Vue  -->


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

        </div>
    </div>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
</div>
