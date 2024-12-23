<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

?>
<!-- Vue --
<div id="appTest">
    <counter></counter>
</div>
 / Vue  -->

<div class="cdg_box">
    <h3 class="cdg_title">
        <?php echo get_option($elementName.'_budgeteer_info_header') ?>
    </h3>
    <!-- Vue -->
    <div id="appEstimator"
        data-apiurl        = "<?php echo $urlspace; ?>"
        data-imprint       = '<?php echo get_option($elementName.'_budgeteer_imprint_types') ?>'
        data-prices        = '<?php echo get_option($elementName.'_budgeteer_prices_types') ?>'
        data-wording       = '<?php echo get_option($elementName.'_budgeteer_stamping_wording') ?>'
        data-baggedprice   = '<?php echo get_option($elementName.'_budgeteer_bagged_price') ?>'
        data-baggedtitle   = '<?php echo get_option($elementName.'_budgeteer_bagged_title') ?>'
        data-baggedwording = '<?php echo get_option($elementName.'_budgeteer_bagged_wording') ?>'
    >
        <estimator></estimator>
    </div>
    <p>&nbsp;</p>

    <!-- / Vue  -->
    <div>
        <?php echo get_option($elementName.'_budgeteer_info_wording') ?>
    </div>
</div>