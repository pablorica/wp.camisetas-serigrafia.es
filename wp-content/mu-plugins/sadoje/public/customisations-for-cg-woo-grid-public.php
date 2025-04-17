<?php

/**
 *
 * This class contains hooks to manipulate the plugin Codigo Woo Grid
 *
 */


 namespace Sadoje;


use Automattic\WooCommerce\Admin\Composer\Package;
use Error;

class Customisations_For_Cg_Woo_Grid_Public extends Customisations_For_Cg_Woo_Grid {

    /** ** ** ** ** ** **
     ** ** FRONTEND ** **
     ** ** ** ** ** ** **/

     /**
	 * The arrays with saved stampings
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $saved_stamping_titles
     * @var      array    $saved_price_screenvals
	 */
	protected $saved_stamping_titles  = [];
    protected $saved_price_screenvals = [];
    protected $min_prices = [];
    protected $prices = [];

     /**
	 * Hello World (for testing purposes)
	 *
	 * @access public
	 * @return void
	 */
	public function hello_world() {
        error_log('Public Hello World!');
        //echo "<h1>Hello World!</h1>";
        error_log('Plugin URL: '.$this->plugin_url);
        error_log('Plugin PATH: '.$this->plugin_path);
        error_log('Element Name: '.$this->get_element_name());
	}


    /**
     * Popluate the prices of a product based on the quantity of products
     *  
     * @return void
     */
    function cdg_populate_prices() {

        //error_log('cdg_populate_prices ');

        if( null !== get_queried_object_id()) {

            $elementName = $this->get_element_name();


            global $product;
            $foundQuantities = false;

            //  TODO: Make this configurable in backend
            $quantities_types = get_option($elementName.'_budgeteer_tableprices_types');
            if ($quantities_types) {
                $quantities = maybe_unserialize($quantities_types);
                $product_id = $product->get_id();
                $product_categories = $product->get_category_ids();

                if ( !empty($quantities['product']) 
                     && !$foundQuantities
                ) {
                    foreach ($quantities['product'] as $product_rule_set) {
                        if (!empty($product_rule_set['products'])) {
                            $products_id = array_column($product_rule_set['products'], 'id');

                            if (in_array($product_id, $products_id, true)) {
                                $quantities = explode(',', $product_rule_set['rows']);
                                $foundQuantities = true;
                                break;
                            }
                        }
                    }
                }

                if ( !$foundQuantities 
                     && !empty($quantities['category'])
                ) {
                    foreach ($quantities['category'] as $category_rule_set) {
                        if (!empty($category_rule_set['categories'])) {
                            $category_ids = array_column($category_rule_set['categories'], 'id');

                            if (!empty(array_intersect($product_categories, $category_ids))) {
                                $quantities = explode(',', $category_rule_set['rows']);
                                $foundQuantities = true;
                                break;
                            }
                        }
                    }
                }

                if ( !$foundQuantities 
                     && !empty($quantities['general'])
                ) {
                    $quantities = explode(',', $quantities['general']);
                    $foundQuantities = true;
                }

                if (!$foundQuantities) {
                    $quantities = explode(',', "2000,1000,500,400,200,100,50,30,10,6,1");
                }
            }

            foreach($quantities as $quantity) {
                $objectPrice = new \stdClass();
                $objectPrice->quantity = $quantity;
                $objectPrice->price =  $this->cdg_get_regular_price_by_type($quantity, $product);
                $this->prices[] = $objectPrice;
            }

            


        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error(
                $this->get_element_name().'_error',
                esc_html__( 'No queried object ID', "cg-woo-grid" ),
                array( 'status' => 500 )
            );
        }
    }


    /**
	 * Build the price table
	 *
	 * @since    1.0.0
     *
     * @return void || WP_Error
	 */
    function cdg_pricetable() {


        if( null !== get_queried_object_id()) {

            $elementName = $this->get_element_name();

            require_once $this->plugin_path. 'admin/'.$elementName.'-admin-rest-api.php';
            $plugin_admin_restapi = new Customisations_For_Cg_Woo_Grid_Admin_Rest_Api();
            $urlspace = $plugin_admin_restapi->get_url_space(); //Required for 'public/display/'.$elementName.'-product-price-table.php';

          
            //$this->cdg_populate_prices();

            $objectPrices = $this->prices;

            //error_log('cdg_pricetable: $objectPrices  -> '.print_r($objectPrices,true));

            //Required for 'public/display/'.$elementName.'-product-price-table.php';
            $jsPrices = json_encode($objectPrices);
            //error_log(print_r($jsPrices,true));

            require_once $this->plugin_path.'public/display/'.$elementName.'-product-price-table.php';

        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error(
                $this->get_element_name().'_error',
                esc_html__( 'Something went wrong', "cg-woo-grid" ),
                array( 'status' => 500 )
            );
        }
    }
    //Deprecated version
    function cdg_pricetableb() {

        //error_log('cdg_budgeteer -> Element Name: '.$this->get_element_name());

        $args = array();

        if( null !== get_queried_object_id()) {

            $elementName = $this->get_element_name();

            require_once $this->plugin_path. 'admin/'.$elementName.'-admin-rest-api.php';
            $plugin_admin_restapi = new Customisations_For_Cg_Woo_Grid_Admin_Rest_Api();
            $urlspace = $plugin_admin_restapi->get_url_space();

            global $product;
            $foundQuantities = false;

            //  TODO: Make this configurable in backend
            $quantities_types = get_option($elementName.'_budgeteer_tableprices_types');
            if ($quantities_types) {
                $quantities = maybe_unserialize($quantities_types);
                $product_id = $product->get_id();
                $product_categories = $product->get_category_ids();

                if (!empty($quantities['product']) && !$foundQuantities) {
                    foreach ($quantities['product'] as $product_rule_set) {
                        if (!empty($product_rule_set['products'])) {
                            $products_id = array_column($product_rule_set['products'], 'id');

                            if (in_array($product_id, $products_id, true)) {
                                $quantities = explode(',', $product_rule_set['rows']);
                                $foundQuantities = true;
                                break;
                            }
                        }
                    }
                }

                if (!$foundQuantities && !empty($quantities['category'])) {
                    foreach ($quantities['category'] as $category_rule_set) {
                        if (!empty($category_rule_set['categories'])) {
                            $category_ids = array_column($category_rule_set['categories'], 'id');

                            if (!empty(array_intersect($product_categories, $category_ids))) {
                                $quantities = explode(',', $category_rule_set['rows']);
                                $foundQuantities = true;
                                break;
                            }
                        }
                    }
                }

                if (!$foundQuantities && !empty($quantities['general'])) {
                    $quantities = explode(',', $quantities['general']);
                    $foundQuantities = true;
                }

                if (!$foundQuantities) {
                    $quantities = explode(',', "2000,1000,500,400,200,100,50,30,10,6,1");
                }
            }
            $objectPrices = Array();
            foreach($quantities as $quantity) {
                $objectPrice = new \stdClass();
                $objectPrice->quantity = $quantity;
                $objectPrice->price =  $this->cdg_get_regular_price_by_type($quantity, $product);
                $objectPrices[] = $objectPrice;
            }

            //error_log(print_r($objectPrices,true));
            $jsPrices = json_encode($objectPrices);

            //error_log(print_r($jsPrices,true));
            require_once $this->plugin_path.'public/display/'.$elementName.'-product-price-table.php';

        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error(
                $this->get_element_name().'_error',
                esc_html__( 'Something went wrong', "cg-woo-grid" ),
                array( 'status' => 500 )
            );
        }
    }

    /**
     * Calculate the price of a product based on the quantity of products
     *
     *
     * @param $variation
     * @param $quantity
     * @param $baseprices
     * @param $profit
     * @param $tax_p
     * @return string
     */
    function cdg_calculate_price(
        $variation, 
        $quantity, 
        $baseprices, 
        $profit,
        $tax_p,
        $prices_include_tax
    ) {
        $variation_id = $variation['variation_id'];
        $product = wc_get_product($variation_id);
        $regular_price = $product->get_regular_price();
        $price_per_box = get_post_meta($variation_id, '_box_price', true);
        $price_per_package = get_post_meta($variation_id, '_pack_price', true);

        if ($quantity >= $baseprices['packagepricemin'] && $quantity < $baseprices['boxpricemin']) {
            $price = $price_per_package * (1 + $profit / 100) ?? 0;
        } elseif ($quantity >= $baseprices['boxpricemin']) {
            $price = $price_per_box * (1 + $profit / 100) ?? 0;
        } else {
            $price = $regular_price * (1 + $profit / 100) ?? 0;
        }

        if ( $prices_include_tax ) {
            //error_log('Prices include tax.');
            $taxes     = $price * $tax_p / (100 + $tax_p);
            $raw_price = $price;
        } else {
            //error_log('Prices exclude tax.');
            $taxes     = $price * $tax_p / 100;
            $raw_price = $price + $taxes;
        }

        if (!isset( $this->min_prices[$product->id]) || $raw_price <  $this->min_prices[$product->id])  $this->min_prices[$product->id] = $raw_price;

        return strip_tags(html_entity_decode(wc_price($raw_price)));
    }

    /**
     * Build the table price configuration to each product by type
     *
     * @param  string $quantity
     * @param  object  $product
     *
     * @return array
     */
    function cdg_get_regular_price_by_type(
        $quantity, 
        $product
    ) {
        // get the quantity of products to establish the type of price (regular, package, box)
        $baseprice = get_option($this->get_element_name().'_budgeteer_baseprice_types');
        $baseprices = maybe_unserialize($baseprice)[0];

        // get the tax rate
        $tax_p = 0;
        $prices_include_tax = false;
        $is_tax_enabled = get_option( 'woocommerce_calc_taxes' ) === 'yes';
        if ( $is_tax_enabled ) {
            //error_log('Taxes are enabled.');
            $tax_rates = \WC_Tax::get_rates($product->get_tax_class());
            if (!empty($tax_rates)) {
                $tax_rate = reset($tax_rates);
                $tax_p = sprintf(_x('%.2f', '', 'wptheme.foundation'), $tax_rate['rate']);
            }
            //error_log('Tax: '.$tax_p);
            $prices_include_tax = get_option( 'woocommerce_prices_include_tax' ) === 'yes';
        } 

        $profit = 0;

        $profit_types = get_option($this->get_element_name().'_budgeteer_profit_types');

        if ($profit_types) {
            $profits = maybe_unserialize($profit_types);
            if (is_array($profits) && count($profits)) {
                $profit = $this->get_profit_by_type($quantity, $product, $profits);
            }
        }
        // Init price structure
        $prices = [
            'adult' => ['white' => null, 'color' => null],
            'child' => ['white' => null, 'color' => null],
        ];

        if (!$product->is_type('variable')) {
            return [];
        }

        // Get product variations
        $variations = $product->get_available_variations();
         // TODO: $variation_map needs to be populated automatically from CMS
        $variation_map = [
            'adult' => ['sizes' => [
                's', 
                'm', 
                'l', 
                'xl', 
                '2xl', 
                '3xl',
                '31',
                '32',
                '33',
                '34',
                '35',
                '36',
                '37',
                '38',
                '39',
                '40',
                '41',
                '42',
                '43',
                '44',
                '45',
                '46',
                '47',
                '48',
                '49',
                '50',
            ]],
            'child' => ['sizes' => [
                'unica',
                '1-2',
                '3-4',
                '4',
                '5-6',
                '7-8',
                '8',
                '9-10',
                '11-12',
                '12',
                '16',
                '6-meses',
                '12-meses',
                '18-meses',
                '2-anos',
                '25',
                '26',
                '27',
                '28',
                '29',
                '30',
            ]],
        ];

        $selected_variations = [
            'adult' => ['white' => null, 'color' => null],
            'child' => ['white' => null, 'color' => null],
        ];

        foreach ($variations as $variation) {
            $size = $variation['attributes']['attribute_pa_tamano'] ?? '';
            $color = $variation['attributes']['attribute_pa_color'] ?? '';

            foreach ($variation_map as $type => $data) {
                if (in_array($size, $data['sizes'], true)) {
                    if ($color === 'blanco' && !$selected_variations[$type]['white']) {
                        $selected_variations[$type]['white'] = $variation;
                    } elseif ($color !== 'blanco' && !$selected_variations[$type]['color']) {
                        $selected_variations[$type]['color'] = $variation;
                    }
                }
            }
        }

        // Calculate prices
        foreach ($selected_variations as $type => $colors) {
            foreach ($colors as $color => $variation) {
                if ($variation) {
                    $prices[$type][$color] = $this->cdg_calculate_price(
                        $variation, 
                        $quantity, 
                        $baseprices, 
                        $profit, 
                        $tax_p,
                        $prices_include_tax
                    );
                    
                }
            }
        }

        if($this->verbose) {
            //error_log('cdg_get_regular_price_by_type: $product -> '.$product->id);
            //error_log('cdg_get_regular_price_by_type: $minprice -> '.$this->min_prices[$product->id]);
        }

        return $prices;
    }

    /**
	 * Build the budgeteer
	 *
	 * @since    1.0.0
     *
     * @return void || WP_Error
	 */
    function cdg_budgeteer() {

        //error_log('cdg_budgeteer -> Element Name: '.$this->get_element_name());

        $args = array();

        if( null !== get_queried_object_id()) {

            $elementName = $this->get_element_name();

            require_once $this->plugin_path. 'admin/'.$elementName.'-admin-rest-api.php';
            $plugin_admin_restapi = new Customisations_For_Cg_Woo_Grid_Admin_Rest_Api();
            $urlspace = $plugin_admin_restapi->get_url_space();

            require_once $this->plugin_path.'public/display/'.$elementName.'-display.php';

        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error(
                $this->get_element_name().'_error',
                esc_html__( 'Something went wrong', "cg-woo-grid" ),
                array( 'status' => 500 )
            );
        }
    }

    /**
     * Updates the regular price based on parameters
     *
     * @param  float  $regular_price
     * @param  array  $pricecolors
     * @param  int    $totalprods
     * @param  string $color
     * @param  bool   $addScreenVal
     * @return float
     */
    private function cdg_get_price_addiction(
        $regular_price,
        $pricecolors,
        $totalprods,
        $color,
        $addScreenVal
    ) {

        //Default configuration (any colour)
        $priceelement = array_pop(
            array_filter(
                $pricecolors,
                function($obj){
                    return strtolower($obj->color) ==  'all';
                }
            )
        );

        if( ! $priceelement ) {
            if ($color == 'blanco') {
                $priceelement = array_pop(
                    array_filter(
                        $pricecolors,
                        function($obj){
                            return str_contains(
                                strtolower($obj->color),
                                'blanc'
                            );
                        }
                    )
                );
            } else {
                $priceelement = array_pop(
                    array_filter(
                        $pricecolors,
                        function($obj){
                            return str_contains(
                                strtolower($obj->color),
                                'color'
                            );
                        }
                    )
                );
            }
        }

        if($this->verbose) {
           //error_log('cdg_get_price_addiction: priceelement '.print_r($priceelement, true));
        }

        $photolithNormal = 0;
        $photolithTotal = 0;
        if( $addScreenVal
            && isset($priceelement->pricescreen)
            && $priceelement->pricescreen === true
            && isset($priceelement->pricescreenval)
        ) {
            $photolithTotal = $priceelement->pricescreenval;
            $photolithNormal = (float) $photolithTotal/$totalprods;
        }

        if( isset($priceelement->pricecloth)
            && $priceelement->pricecloth === true
            && isset($priceelement->priceclothval)
        ) {
            $priceclothval = (float) $priceelement->priceclothval;
        }

        if($this->verbose) {
            error_log('cdg_get_price_addiction: Regular price -> '.$regular_price);
            error_log('cdg_get_price_addiction: Total products -> '.$totalprods);
            $comment = '';
            if($photolithNormal == 0) {
                $comment = " (".__('The photolith was added before', 'sadoje').")";
            }
            error_log('cdg_get_price_addiction: photolithTotal -> '.$photolithTotal.$comment);
            error_log('cdg_get_price_addiction: photolithNormal -> '.$photolithNormal.$comment);
            error_log('cdg_get_price_addiction: Color '.$color.': priceclothval -> '.$priceclothval);
            error_log('cdg_get_price_addiction: Total -> (regular_price + photolithNormal + priceclothval) = '.($regular_price + $photolithNormal + $priceclothval));
        }


        return $regular_price + $photolithNormal + $priceclothval;
    }

    /**
     * Applies the budgeteer configuration to the regular price of each product
     *
     * @param  string                $regular_price
     * @param  WC_Product_Variation  $single_variation
     *
     * @return string
     */
    function cdg_alter_regular_price(
        $regular_price,
        $single_variation,
        $quantity,
        $totalprods = 0
    ) {
        $attr = $single_variation->get_variation_attributes();
        if($this->verbose) {
            error_log('cdg_alter_regular_price: Product -> '.print_r($single_variation->get_name(),true));
            error_log('cdg_alter_regular_price: Colour -> '.$attr['attribute_pa_color']);
            error_log('cdg_alter_regular_price: Original Price -> '.$regular_price);
            error_log('cdg_alter_regular_price: Quantity -> '.$quantity);
        }

        $baseprice = get_option($this->get_element_name().'_budgeteer_baseprice_types');
        $baseprices = maybe_unserialize($baseprice)[0];
        if($this->verbose) {
            //error_log('cdg_alter_regular_price: $baseprices -> '.print_r($baseprices,true));
        }

        if($quantity >= $baseprices['boxpricemin']) {
            if($this->verbose) {
                error_log('cdg_alter_regular_price: The quantity of this item is bigger than  '.$baseprices['boxpricemin'].' , therefore the sale price is the box price');
            }
            $regular_price = get_post_meta(
                $single_variation->get_id(),
                '_box_price',
                true
            );
        }
        elseif($quantity >= $baseprices['packagepricemin']) {
            if($this->verbose) {
                error_log('cdg_alter_regular_price: The quantity of this item is bigger than  '.$baseprices['packagepricemin'].' , therefore the sale price is the package price');
            }
            $regular_price = get_post_meta(
                $single_variation->get_id(),
                '_pack_price',
                true
            );
        }

        if($this->verbose) {
            error_log('cdg_alter_regular_price: Altered price due the quantity of this item -> '.$regular_price);
        }

        if(isset($_COOKIE['cgWooGrid'])) {

            if($this->verbose) {
                //error_log('cdg_alter_regular_price: Reading the COOKIE to get the estimator configuration -> '.print_r($_COOKIE['cgWooGrid'] , true));
                //error_log('cdg_alter_regular_price: This COOKIE was populated in  src/js/templates/Estimator.vue (previously in src/js/templates/Budgetter.vue)');
            }

            $cgWooGrid = json_decode(
                html_entity_decode(
                    stripslashes (
                        $_COOKIE['cgWooGrid']
                    )
                )
            );
            //error_log('cdg_alter_regular_price: COOKIE -> '.print_r($_COOKIE['cgWooGrid'],true));
            //error_log('cdg_alter_regular_price: JSON -> '.print_r($cgWooGrid,true));
            if( isset($cgWooGrid->totalProducts)
                && $cgWooGrid->totalProducts > 0
            ) {
                $description = '';

                if(!$totalprods) {
                    $totalprods  = $cgWooGrid->totalProducts;
                }

                if($this->verbose) {
                    error_log('cdg_alter_regular_price: $totalprods -> '.$totalprods);
                }

                $profit = 0;

                $profit_types = get_option($this->get_element_name().'_budgeteer_profit_types');
                if($profit_types) {
                    $profits = maybe_unserialize($profit_types);

                    $product_id = $single_variation->get_id();
                    if ($single_variation->get_parent_id()) {
                        $product_id = $single_variation->get_parent_id();
                    }
                    if (is_array($profits) && count($profits)) {
                        $product = wc_get_product($product_id);
                        $profit = $this->get_profit_by_type($quantity, $product, $profits);
                    }

                    if($this->verbose) {
                        //error_log('cdg_alter_regular_price: $profits -> '.print_r($profits,true));
                    }
                }

                $regular_price = $regular_price * (1 + $profit/100);

                if($this->verbose) {
                    error_log('cdg_alter_regular_price: Profit due the number of items -> '.$profit);
                    error_log('cdg_alter_regular_price: Altered price due the number of items -> '.$regular_price);
                }

                if( isset($cgWooGrid->stamping)) {
                    foreach($cgWooGrid->stamping as $stamping) {
                        $addScreenVal           = false;
                        $objIterator            = new \ArrayIterator($stamping);
                        $current_stamping_title = $objIterator->current();
                        $pricecolors            = $stamping->pricecolors??[];

                        if($this->verbose) {
                            error_log('cdg_alter_regular_price: Is this is a new stamping ('.$current_stamping_title.')? ');
                        }

                        if( ! $addScreenVal
                            && ! in_array(
                                $current_stamping_title,
                                $this->saved_stamping_titles
                            )
                        ) {
                            if($this->verbose) {
                                error_log('cdg_alter_regular_price: This is a new stamping ('.$current_stamping_title.') so we must add a new photolite and save the stamping name and color value ('.$pricecolors[0]->pricescreenval.') to avoid duplicates in the next loops');
                            }
                            $addScreenVal = true;
                            $this->saved_stamping_titles[]  = $current_stamping_title;
                            $this->saved_price_screenvals[] = $pricecolors[0]->pricescreenval;
                        }


                        if( ! $addScreenVal ) {
                            foreach($pricecolors as $pricecolor) {
                                if( ! in_array(
                                        $pricecolor->pricescreenval,
                                        $this->saved_price_screenvals
                                    )
                                ) {
                                    if($this->verbose) {
                                        error_log('cdg_alter_regular_price: Something has changed with the configuration of this stamping ('.$current_stamping_title.'), probably the number of tints, ther is a new color value ('.$pricecolor->pricescreenval.') so we must add a new photolite');
                                    }
                                    $addScreenVal = true;
                                    $this->saved_price_screenvals[] = $pricecolor->pricescreenval;
                                    break;
                                }
                            }
                        }


                        if( ! $addScreenVal ) {
                            if($this->verbose) {
                                error_log('cdg_alter_regular_price: This is not a new stamping title ('.$current_stamping_title.') so there will not be a new photolite.');
                            }
                        }

                        if($this->verbose) {
                            error_log('cdg_alter_regular_price: Calculating the new price for stamping: '.print_r($stamping, true));
                        }

                        $regular_price = $this->cdg_get_price_addiction(
                            $regular_price,
                            $pricecolors,
                            $totalprods,
                            $attr['attribute_pa_color'],
                            $addScreenVal
                        );

                    }

                    // $description .= '
                    // '.esc_html__('Estampación en pecho','sadoje')
                    //  .'('.$stampingFront
                    //  .' '.$areaFront.') ';
                }

            }

            if($cgWooGrid->bagged) {
                // $description .= '
                // '.esc_html__('Embolsado','sadoje');

                if($this->verbose) {
                    error_log('cdg_alter_regular_price: baggedPrice -> '.print_r($cgWooGrid->bagged,true));
                }

                $regular_price = $regular_price + (float) $cgWooGrid->bagged->baggedprice;
            }

        }

        //OLD
        if(isset($_COOKIE['cgWooGridOLD'])) {

            if($this->verbose) {
                error_log('cdg_alter_regular_price: Reading the COOKIE to get the budgeterr configuration -> '.print_r($_COOKIE['cgWooGrid'] , true));
                error_log('cdg_alter_regular_price: This COOKIE was populated in  src/js/templates/Estimator.vue (prevoously in src/js/templates/Budgetter.vue)');

            }

            $cgWooGrid = json_decode(
                html_entity_decode(
                    stripslashes (
                        $_COOKIE['cgWooGrid']
                    )
                )
            );
            //error_log('cdg_alter_regular_price: COOKIE -> '.print_r($_COOKIE['cgWooGrid'],true));
            //error_log('cdg_alter_regular_price: JSON -> '.print_r($cgWooGrid,true));
            if( isset($cgWooGrid->totalProducts)
                && $cgWooGrid->totalProducts > 0
            ) {
                $description = '';

                if(!$totalprods) {
                    $totalprods  = $cgWooGrid->totalProducts;
                }

                if($this->verbose) {
                    error_log('cdg_alter_regular_price: $totalprods -> '.$totalprods);
                }

                $profit = 0;

                $profit_types = get_option($this->get_element_name().'_budgeteer_profit_types');
                if($profit_types) {
                    $profits = maybe_unserialize($profit_types);
                    if($this->verbose) {
                        //error_log('cdg_alter_regular_price: $profits -> '.print_r($profits,true));
                    }

                    if(is_array($profits)
                        && count($profits)
                    ) {

                        $current_profit_type = array_pop(
                            array_filter(
                                $profits,
                                function($obj) use ($totalprods) {
                                    return ($obj['min'] <= $totalprods && $obj['max'] >= $totalprods);
                                }
                            )
                        );

                        if($this->verbose) {
                            //error_log('cdg_alter_regular_price: Filtered $profit -> '.print_r($current_profit_type,true));
                        }

                        if(isset($current_profit_type['profit'])) {

                            if($this->verbose) {
                                //error_log('cdg_alter_regular_price: Filtered $profit -> '.$current_profit_type['profit']);
                            }

                            $profit = $current_profit_type['profit'];
                        }
                    }
                }

                $regular_price = $regular_price * (1 + $profit/100);

                if($this->verbose) {
                    //error_log('cdg_alter_regular_price: Profit due the number of items -> '.$profit);
                    //error_log('cdg_alter_regular_price: Altered price due the number of items -> '.$regular_price);
                }

                if( isset($cgWooGrid->front)) {
                    $priceAdd      = $cgWooGrid->front;
                    $stampingFront = $priceAdd->stamping;
                    $areaFront     = $priceAdd->area;
                    $regular_price = $this->cdg_get_price_addiction(
                        $regular_price,
                        $priceAdd,
                        $totalprods,
                        $attr['attribute_pa_color'],
                        'Front'
                    );
                    // $description .= '
                    // '.esc_html__('Estampación en pecho','sadoje')
                    //  .'('.$stampingFront
                    //  .' '.$areaFront.') ';
                }
                if( isset($cgWooGrid->rear)) {
                    $priceAdd     = $cgWooGrid->rear;
                    $stampingRear = $priceAdd->stamping;
                    $areaRear     = $priceAdd->area;
                    if($stampingRear == $stampingFront) {
                        $priceAdd->screenPrice = 0; //Only adds once, and it was done previously in the front area
                    }

                    // $description .= '
                    // '.esc_html__('Estampación en espalda','sadoje')
                    //  .'('.$stampingRear
                    //  .' '.$areaRear.') ';

                    $regular_price = $this->cdg_get_price_addiction(
                        $regular_price,
                        $priceAdd,
                        $totalprods,
                        $attr['attribute_pa_color'],
                        'Rear'
                    );
                }
            }

            if($cgWooGrid->bagged) {
                // $description .= '
                // '.esc_html__('Embolsado','sadoje');

                if($this->verbose) {
                    error_log('cdg_alter_regular_price: baggedPrice -> '.$cgWooGrid->bagged);
                }

                $regular_price = $regular_price + (float) $cgWooGrid->bagged;
            }

        }


        if($this->verbose) {
            error_log('cdg_alter_regular_price: Modified Price -> '.$regular_price);
            error_log('');
            error_log('++++++++++++++++++++');
            error_log('');
        }

        return $regular_price;
    }


    /**
     * Applies the budgeteer configuration to the total price of the purchase
     *
     * @param  string $price
     * @return string
     */
    function cdg_alter_total_price($price) {
        if(isset($_COOKIE['cgWooGrid'])) {
            $addsTotalPrice = json_decode(
                html_entity_decode(
                    stripslashes (
                        $_COOKIE['cgWooGrid']
                    )
                )
            );
            //error_log('cdg_alter_total_price: JSON -> '.print_r($addsTotalPrice,true));
            $price = $price + (float) $addsTotalPrice->screen_price_front;
        }
        return $price;
    }

    /**
     * Applies the budgeteer configuration to the cart
     *
     * @param  WC_Cart $cart
     * @return void
     */
    function cdg_alter_price_cart( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

        if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) return;

        //error_log(print_r($cart,true));
        $totalitems = $cart->get_cart_contents_count();
        //error_log('$totalitems -> '.$totalitems);

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {

            $product  = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            $price = $this->cdg_alter_regular_price(
                $product->get_price(),
                $product,
                $quantity,
                $totalitems
            );
            $cart_item['data']->set_price( $price);

            //$description = $cart_item['data']->get_description();
        }
    }

    /**
     * Get the profit value based on product ID, category, or general settings.
     *
     * @param int $quantity     The quantity of the product.
     * @param object $product   The product object (WC_Product).
     * @param array $profits    The profits array (includes product, category, and general rules).
     * @return float            The profit value.
     */
    function get_profit_by_type(
        $quantity, 
        $product, 
        $profits
    ) {
        $product_id = $product->get_id();
        $product_categories = $product->get_category_ids();

        $evaluate_rule = function ($rule, $quantity) {
            $has_min = isset($rule['min']) && $rule['min'] !== "";
            $has_max = isset($rule['max']) && $rule['max'] !== "";

            if ($has_min && $has_max) {
                return $quantity >= $rule['min'] && $quantity <= $rule['max'];
            } elseif ($has_min) {
                return $quantity >= $rule['min'];
            } elseif ($has_max) {
                return $quantity <= $rule['max'];
            }

            return true;
        };

        if (!empty($profits['product'])) {
            foreach ($profits['product'] as $product_rule_set) {
                if (!empty($product_rule_set['products'])) {
                    $products_id = array_column($product_rule_set['products'], 'id');

                    if (in_array($product_id, $products_id, true)) {
                        $product_profit = array_filter(
                            $product_rule_set['rows'],
                            function ($rule) use ($quantity, $evaluate_rule) {
                                return $evaluate_rule($rule, $quantity);
                            }
                        );

                        if (!empty($product_profit)) {
                            $selected_product_profit = array_pop($product_profit);
                            return is_numeric($selected_product_profit['profit']) ? $selected_product_profit['profit'] : 0;
                        }
                    }
                }
            }
        }

        if (!empty($profits['category'])) {
            foreach ($profits['category'] as $category_rule_set) {
                if (!empty($category_rule_set['categories'])) {
                    $category_ids = array_column($category_rule_set['categories'], 'id');

                    $matched_categories = array_intersect($product_categories, $category_ids);
                    if (!empty($matched_categories)) {
                        $category_profit = array_filter(
                            $category_rule_set['rows'],
                            function ($rule) use ($quantity, $evaluate_rule) {
                                return $evaluate_rule($rule, $quantity);
                            }
                        );

                        if (!empty($category_profit)) {
                            $selected_category_profit = array_pop($category_profit);
                            return is_numeric($selected_category_profit['profit']) ? $selected_category_profit['profit'] : 0;
                        }
                    }
                }
            }
        }

        if (!empty($profits['general'])) {
            $general_profit = array_filter(
                $profits['general'],
                function ($rule) use ($quantity, $evaluate_rule) {
                    return $evaluate_rule($rule, $quantity);
                }
            );

            if (!empty($general_profit)) {
                $selected_general_profit = array_pop($general_profit);
                return is_numeric($selected_general_profit['profit']) ? $selected_general_profit['profit'] : 0;
            }
        }

        return 0;
    }

    /**
     * Get the minimum price of a product
     * 
     * @param string $price   The price of the product.
     * @param object $product The product object (WC_Product).
     * 
     * @return string         The minimum price.
     */
    public function cdg_get_minimum_price( 
        $price, 
        $product 
    ) {

         if($this->verbose) {
            //error_log('cdg_get_minimum_price: $product -> '.$product->id);
            //error_log('cdg_get_minimum_price: $minprice '.print_r($this->min_prices,true));
        }
        
        if (isset( $this->min_prices[$product->id]) ) {
            if($this->verbose) {
                //error_log('cdg_get_minimum_price: $product -> '.$product->id);
                //error_log('cdg_get_minimum_price: $price -> '.$price);
                //error_log('cdg_get_minimum_price: $HTMLminprice -> '. strip_tags(html_entity_decode(wc_price($this->min_prices[$product->id]))) );
            }
            
            return esc_html( 'Desde', 'sadoje' ).'&nbsp;'.strip_tags(html_entity_decode(wc_price($this->min_prices[$product->id])));
        }

        return $price;
        
    }

    




    /**
     * DEPRECATED FUNCTIONS
     */

    // Render meta on cart and checkout
    function render_meta_on_cart_and_checkout( $cart_data, $cart_item = null ) {
        $custom_items = array();

        if( !empty( $cart_data ) ) {
            $custom_items = $cart_data;
        }
        if( isset( $cart_item['my_field_name'] ) ) {
            $custom_items[] = array( "name" => 'My Field', "value" => $cart_item['my_field_name'] );
        }
        return $custom_items;
    }
    //add_filter( 'woocommerce_get_item_data', 'render_meta_on_cart_and_checkout', 10, 2 );


    // Update the order meta with field value
    function my_custom_checkout_field_update_order_meta( $order_id ) {
        if ($_POST['my_field_name']) {
            update_post_meta( $order_id, 'My Field', esc_attr($_POST['my_field_name']));
        }
    }
    //add_action('woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta');
    //add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

    /**
     * Display field value on the order edit page
     */
    function cdg_display_order_description( $order ){
        $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
        echo '<p><strong>'.__('My Field Name').':</strong> ' . get_post_meta( $order_id, 'my_field_name', true ) . '</p>';
    }
}
