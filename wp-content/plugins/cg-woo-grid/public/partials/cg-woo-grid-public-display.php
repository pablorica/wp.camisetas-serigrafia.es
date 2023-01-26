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


//error_log(print_r($this->current_product_id ,true));
?>

<!-- Vue -->
<div id="appPopUp">
    <pop-up></pop-up>
</div>
<!-- / Vue  -->

<div id="cdg_form" 
    class="cdg_box"
>

    <?php if($header = get_option($this->plugin_name.'_header_setting')):?>
        <h3 class="cdg_title"><?=$header?></h3>
    <?php endif;?>

    <div class="row sidebar_configurator">

        <?php 
        $cssGrid = "";
        if('right' == get_option($this->plugin_name.'_totalbox_position')):
            $cssGrid = "grid-left";
        ?>
        <!-- Vue -->
        <div id="appTotalBox"
            class="box-right"
            data-currentproduct = "<?=$this->current_product_id?>"
        >
            <total-box></total-box>
        </div>
        <!-- / Vue  -->
        <?php endif;?>


        <div id="cdg_grid" 
            class="sw_prize <?= $cssGrid?>"
        >
            <!-- Vue -->
            <div id="appBar"
                data-customminimum = "<?=$custom_minimum?>"
            >
                <items-bar></items-bar>
            </div>
            <!-- / Vue  -->

            <!-- Vue -->
            <div id="appMatrix" 
                data-currentproduct = "<?=$this->current_product_id?>"
                data-customminimum  = "<?=$custom_minimum?>"
            >
                <attributes-matrix></attributes-matrix>
            </div>
            <!-- / Vue  -->

        </div>
    </div>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
</div>
