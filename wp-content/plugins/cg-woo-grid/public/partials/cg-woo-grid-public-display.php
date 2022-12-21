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

        <!-- Vue -->
        <div id="appTotalBox"
            data-currentproduct = "<?php echo get_queried_object_id() ?>"
        >
            <total-box></total-box>
        </div>
        <!-- / Vue  -->


        <div id="cdg_sizes" 
            class="sw_prize"
        >
            <!-- Vue -->
            <div id="appBar">
                <items-bar></items-bar>
            </div>
            <!-- / Vue  -->

            <!-- Vue -->
            <div id="appMatrix" 
                data-currentproduct = "<?php echo get_queried_object_id() ?>"
            >
                <attributes-matrix></attributes-matrix>
            </div>
            <!-- / Vue  -->

        </div>
    </div>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
</div>
