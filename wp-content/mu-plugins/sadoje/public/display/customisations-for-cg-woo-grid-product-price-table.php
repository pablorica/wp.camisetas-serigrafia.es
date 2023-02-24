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
    <div id="appTableprices"
        data-apiurl    = "<?php echo $urlspace; ?>"
        data-prices = '<?php echo $jsPrices ?>'
    >
        <pricetable></pricetable>
    </div>
</div>