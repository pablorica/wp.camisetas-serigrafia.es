<?php

/**
 *
 * This class contains hooks to manipulate the plugin Codigo Woo Grid
 *
 */


 namespace Sadoje;


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
	 * Build the price table
	 *
	 * @since    1.0.0
     * 
     * @return void || WP_Error
	 */
    function cdg_pricetable() {

        //error_log('cdg_budgeteer -> Element Name: '.$this->get_element_name());

        $args = array();
        
        if( null !== get_queried_object_id()) {

            $elementName = $this->get_element_name();
            
            require_once $this->plugin_path. 'admin/'.$elementName.'-admin-rest-api.php';
            $plugin_admin_restapi = new Customisations_For_Cg_Woo_Grid_Admin_Rest_Api();
            $urlspace = $plugin_admin_restapi->get_url_space();

            global $product;

            //  TODO: Make this configurable in backend
            $quantities   = [1000,500,400,200,100,50,30,10,6,1];
            $objectPrices = Array();
            foreach($quantities as $quantity) {
                $objectPrice = new \stdClass();
                $objectPrice->quantity = $quantity;
                $objectPrice->price = strip_tags(
                    wc_price(
                        $this->cdg_alter_regular_price( 
                            $product->get_price(),
                            $product,
                            $quantity
                        )
                    )
                );
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
     * OLD:
     * Updates the regular price based on parameters
     *
     * @param  float  $regular_price
     * @param  object $priceAdd
     * @param  int    $totalprods
     * @param  string $color
     * @param  string $position
     * @return float
     */
    private function cdg_get_price_addictionOLD(
        $regular_price, 
        $priceAdd, 
        $totalprods, 
        $color, 
        $position
    ) {

        $photolithNormal = (float) $priceAdd->screenPrice/$totalprods;
        
        if ($color == 'blanco') { 
            if( isset($priceAdd->whitePrice)) {
                $areaPrice = (float) $priceAdd->whitePrice;
            }
        } else {
            if( isset($priceAdd->colorPrice)) {
                $areaPrice = (float) $priceAdd->colorPrice;
            }
        }
        if($this->verbose) {
            error_log('cdg_get_price_addiction: Here we apply the price changes due to colour and stamping ');
            error_log('cdg_get_price_addiction: Regular price -> '.$regular_price);
            error_log('cdg_get_price_addiction: '.$position.' $stamping -> '.$priceAdd->stamping);
            $comment = '';
            if($photolithNormal == 0) {
                $comment = " (".__('The photolith was added before', 'sadoje').")";
            }
            error_log('cdg_get_price_addiction: '.$position.' $photolithNormal -> '.$photolithNormal.$comment);
            error_log('cdg_get_price_addiction: Color '.$color.' $areaPrice -> '.$areaPrice);
            error_log('cdg_get_price_addiction: Total -> (regular_price + photolithNormal + areaPrice) = '.($regular_price + $photolithNormal + $areaPrice));
        }
        

        return $regular_price + $photolithNormal + $areaPrice;
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
                    error_log('cdg_alter_regular_price: Profit due the number of items -> '.$profit);
                    error_log('cdg_alter_regular_price: Altered price due the number of items -> '.$regular_price);
                }

                if( isset($cgWooGrid->stamping)) {
                    foreach($cgWooGrid->stamping as $stamping) {
                        $addScreenVal           = false;
                        $objIterator            = new \ArrayIterator($stamping);
                        $current_stamping_title = $objIterator->current();
                        $pricecolors            = $stamping->pricecolors;

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

//add_action('woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta');
function my_custom_checkout_field_update_order_meta( $order_id ) {
    if ($_POST['my_field_name']) {
        update_post_meta( $order_id, 'My Field', esc_attr($_POST['my_field_name']));
    }
}

    /**
 * Display field value on the order edit page
 */
//add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );
function cdg_display_order_description( $order ){
    $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
    echo '<p><strong>'.__('My Field Name').':</strong> ' . get_post_meta( $order_id, 'my_field_name', true ) . '</p>';
}


}