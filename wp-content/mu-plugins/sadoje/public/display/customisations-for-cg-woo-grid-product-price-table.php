<?php
/**
 * Provide a public-facing view for the price table
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

?>

<div class="cdg_table">
    <h4 class="cdg_title">
        Precio por cantidades
    </h4>
    <!-- Vue -->
    <div id="appPriceTable"
        data-apiurl        = "<?php echo $urlspace; ?>"
        data-imprint        = '<?php echo get_option($elementName.'_budgeteer_imprint_types') ?>'
        data-prices        = '<?php echo get_option($elementName.'_budgeteer_prices_types') ?>'
        data-wording       = '<?php echo get_option($elementName.'_budgeteer_stamping_wording') ?>'
        data-baggedprice   = '<?php echo get_option($elementName.'_budgeteer_bagged_price') ?>'
        data-baggedtitle   = '<?php echo get_option($elementName.'_budgeteer_bagged_title') ?>'
        data-baggedwording = '<?php echo get_option($elementName.'_budgeteer_bagged_wording') ?>'
    >
        <pricetable></pricetable>
    </div>
</div>