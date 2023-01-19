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



<div id="cdg_toolbox" 
    class="cdg_box"
>
    <h3 class="cdg_title">
        <?php echo get_option($this->plugin_name.'_totalbox_title'); ?>
    </h3>
    <!-- Vue -->
    <div id="appTotalBox"
        class="box-full"
        data-currentproduct = "<?php echo get_queried_object_id() ?>"
    >
        <total-box></total-box>
    </div>
    <!-- / Vue  -->
</div>
