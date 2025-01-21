<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    cg-woo-grid
 * @subpackage cg-woo-grid/includes
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */
namespace Cg_Woo_Grid_Public;

class Rest_Api {

    protected $plugin_name;
    protected $version;

    /**** Custom Plugin Properties ****/

    protected $restapinamespace;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( 
        $plugin_name, 
        $version 
    ) {
        $this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->restapinamespace = 'cg-woo-grid/v1';
	}



    /**
     * Define JS constants
     *
     * @return void
     */
    function custom_constants_js() {
        echo '
        <script>
            const TAXONOMY_X       = "'.get_option($this->plugin_name.'_taxonomy_x_setting').'";
            const TAXONOMY_Y       = "'.get_option($this->plugin_name.'_taxonomy_y_setting').'";

            const BAR_MESSAGE    = "'.get_option($this->plugin_name.'_bar_message').'";
            const BAR_SUCCESS    = "'.get_option($this->plugin_name.'_bar_success').'";
            const BAR_ITEMSTITLE = "'.esc_html__("items","cg-woo-grid").'";

            const STOCK_MESSAGE_ZERO = "'.get_option($this->plugin_name.'_stock_zero_message').'";

            const POPUP_MESSAGE1       = "'.get_option($this->plugin_name.'_popup_message1').'";
            const POPUP_MESSAGE2       = "'.get_option($this->plugin_name.'_popup_message2').'";
            const POPUP_SUCESS_TITLE   = "'.get_option($this->plugin_name.'_popup_success_title').'";
            const POPUP_SUCESS_MESSAGE = "'.get_option($this->plugin_name.'_popup_success_message').'";
            const POPUP_ERROR_TITLE    = "'.get_option($this->plugin_name.'_popup_error_title').'";
            const POPUP_ERROR_MESSAGE  = "'.get_option($this->plugin_name.'_popup_error_message').'";
            
            const CART_URL       = "'.get_permalink(wc_get_page_id('cart')).'";
            const REST_POSTS_URL = "'.esc_url_raw( rest_url( $this->restapinamespace ) ).'";
            const REST_NONCE     = "'.wp_create_nonce( 'wp_rest' ).'";
            const LOADING_GIF    = "'.CG_WOOGRID_URL.'/vue/img/loading.gif";
        </script>';
    }

    
    /**
     * REST API endpoint test
     * 
     * @return WP_REST_Response
     */
    function at_rest_testing_endpoint() {
        return new \WP_REST_Response('Howdy!!');
    }

    /**
     * Update cart with the selected products
     * 
     * @param object $request_data
     * 
     * @return object || \WP_Error
     */
    public function build_total_box($request_data) {

        global $post;
        
        $parameters = $request_data->get_params();
        //error_log('l.158 - build_total_box: '.print_r($parameters,true));
        $return = new \stdClass();

        $return->product_id = null;
        if( isset($parameters['product_id']) ) {
            $return->product_id = $parameters['product_id'];
        } elseif( $post instanceof \WP_Post && 
            isset($post->ID) 
        ) {
            $return->product_id = $post->ID;
        } elseif(get_queried_object_id()) {
            $return->product_id = get_queried_object_id();
        }
        
        if(!$return->product_id) {
            return new \WP_Error( 
                'product_invalid', 
                esc_html__( 'No results', 'cg-woo-grid' ), 
                array( 'status' => 404 ) 
            );
        }


        //$return->boxtitle        = __("Your budget","cg-woo-grid");
        $return->boxtitle        = get_option($this->plugin_name.'_totalbox_title');
        $return->rsmdisplay      = get_option($this->plugin_name.'_show_resumebox');
        $return->rsmpriceunitmsg = get_option($this->plugin_name.'_resumebox_priceunit_message');

        $return->unitpricetitle  = __("Unit price","cg-woo-grid");
        $return->pricetitle      = __("Price","cg-woo-grid");
        $return->unitstitle      = __("Items","cg-woo-grid");
        $return->amountitle      = __("Amount","cg-woo-grid");
        $return->subtotaltitle   = __("Sub-total","cg-woo-grid");
        $return->vattitle        = __("VAT","cg-woo-grid");
        $return->totaltitle      = __("Total","cg-woo-grid");
        $return->addtocarttitle  = __("Add to cart","cg-woo-grid");

        $return->zeroprice       = strip_tags(wc_price(0));
        $return->formatzeroprice = wc_price(0);

        //error_log($this->plugin_name.'_taxonomy_y_setting');
        //error_log(get_option($this->plugin_name.'_taxonomy_y_setting'));
        //$return->taxonomy_y = 'pa_color'; 
        $return->taxonomy_y = get_option($this->plugin_name.'_taxonomy_y_setting');
        //$return->taxonomy_y_name = 'Color';
        $return->taxonomy_y_name = get_option($this->plugin_name.'_taxonomy_y_setting_title');
        //$return->taxonomy_x = 'pa_tamano';
        $return->taxonomy_x = get_option($this->plugin_name.'_taxonomy_x_setting');
        //$return->taxonomy_x_name = 'Talla';
        $return->taxonomy_x_name = get_option($this->plugin_name.'_taxonomy_x_setting_title');
        

        return rest_ensure_response($return);
    }


     /**
     * Get the prices
     * 
     * @param array $args
     * 
     * @return object || \WP_Error
     */
    function get_prices($args)
    {

        $_product = $args['product'];

        if(!$_product instanceof \WC_Product) {
            return new \WP_Error( 
                'rest_api_sad', 
                esc_html__( 'Something went wrong', 'cg-woo-grid' ), 
                array( 'status' => 500 ) 
            );
        }

        $nameproduct = $_product->get_name();

        $gridattributes = array();
        $description = '';

        $numberofprods = 0;
        $price = 0;
        $rowPrices = array();


        if($_product->is_type('variable')){

            $tax_y_slug = get_option($this->plugin_name.'_taxonomy_y_setting');
            $tax_x_slug = get_option($this->plugin_name.'_taxonomy_x_setting');

            $variations = $_product->get_children();
            foreach ($variations as $value) {
                $single_variation = new \WC_Product_Variation($value);
                /*
                echo '<option  value="'.$value.'">'.implode(" / ", $single_variation->get_variation_attributes()).'-'.get_woocommerce_currency_symbol().$single_variation->price.'</option>';
                */
                $variation_slug = $single_variation->get_slug();
                $variation_id   = $single_variation->get_id();

                if (isset($args['quantities'][$variation_slug])) {

                    $quantity   = (int) $args['quantities'][$variation_slug];
                    $rowPrice   = array();
                    $nk    		= false;
                    $attr 		= $single_variation->get_variation_attributes();

                    foreach ($gridattributes as $gakey => $gavalue) {
                        if ($gavalue['y'] == $gavalue[$tax_y_slug]) $nk = $gakey;
                    }
                    if ($nk === false) {
                        $gridattributes[] = array(
                            'y'	=> $attr['attribute_'.$tax_y_slug],
                            'x'	=> array()
                        );
                        end($gridattributes);
                        $nk = key($gridattributes);
                    }
                    $gridattributes[$nk]['x'][] = array($attr['attribute_'.$tax_x_slug], $quantity);
    
                    //$miprecio = (float) $single_variation->get_price();
                    $numberofprods += $quantity;

                    //$uprice = (float) $single_variation->get_regular_price();
                    $uprice = apply_filters( 
                        'cg-woo-grid_get_regular_price', 
                        $single_variation->get_regular_price(),
                        $single_variation,
                        $quantity
                    );

                    $price  += $quantity * $uprice;
                    
                    $rowPrice["tp_ubase_price"]  = $uprice;
                    $rowPrice["tp_y"]            = $attr['attribute_'.$tax_y_slug];
                    $rowPrice["tp_x"]            = $attr['attribute_'.$tax_x_slug];
                    $rowPrice["tp_qant"]         = $quantity;	
                    $rowPrice["tp_addurl"]       = $variation_id.':'.$quantity.',';				

                    //error_log(print_r($rowPrices, true));
                    $rowPrices[] = $rowPrice;
                }
            }
        }

        if($numberofprods > 0){

            // $description .= $nameproduct." - ".$numberofprods ." ".__("products","cg-woo-grid").": ";
            // foreach ($gridattributes as $k => $v) {
            //     $description .= ($k != 0 ? ' - ' : '') . ucfirst($v['y']) . ', ';
            //     foreach ($v['x'] as $kx => $vx) {
            //         $description .= ($kx != 0 ? ', ' : '') . strtoupper($vx[0]) . ' (' . $vx[1] . ')';
            //     }
            // }
            $description = ''; //No description for now

            $html = '';
            $addurl = '';
            foreach($rowPrices as $rowPrice) {

                $nuprice = $rowPrice["tp_ubase_price"];
                $nqprice = $nuprice * $rowPrice["tp_qant"];

                $html .= '
                <tr>
                    <td class="tp_y">'.ucfirst($rowPrice["tp_y"]).'</td>
                    <td class="tp_x">'.strtoupper($rowPrice["tp_x"]).'</td>
                    <td class="tp_uprice">'.wc_price($nuprice).'</td>
                    <td class="tp_qant">'.$rowPrice["tp_qant"].'</td>
                    <td class="text-right tp_subtotal">'.wc_price($nqprice).'</td>
                </tr>';

                $addurl .= $rowPrice["tp_addurl"];
                
            }
            $addurl = rtrim($addurl, ',');


            $price = apply_filters( 
                'cg-woo-grid_get_total_price', 
                $price
            );

            //Taxes
            $tax_p = 0;
            $tax_rates = \WC_Tax::get_rates($_product->get_tax_class());
            if (!empty($tax_rates)) {
                $tax_rate = reset($tax_rates);
                $tax_p = sprintf(_x('%.2f', '', 'wptheme.foundation'), $tax_rate['rate']);
            }

            $taxes  	  = $price / 100 * $tax_p;
            $priceTax     = $price + $taxes;
            $unitPrice    = $price / $numberofprods;
            $unitPriceTax = $priceTax / $numberofprods;

            $return = new \stdClass();
            $return->numberofprods     = $numberofprods;
            $return->unitpricetax      = strip_tags(html_entity_decode(wc_price($unitPriceTax)));
            $return->unitprice         = strip_tags(html_entity_decode(wc_price($unitPrice)));
            $return->subtotal          = $price;

            $return->subtotalformatted = wc_price($price);
            $return->taxesformatted    = wc_price($taxes);
            $return->totalformatted    = wc_price($priceTax);
            $return->description       = $description;
            $return->html              = $html;
            $return->addurl            = $addurl;

            return $return;

        } else {

            return new \WP_Error( 
                'wrong_products_quantity', 
                esc_html__( 'Something went wrong', 'cg-woo-grid' ), 
                array( 'status' => 500 ) 
            );

        }       

        //wp_die();
    }

    /**
     * Update cart with the selected products
     * 
     * @param object $request_data
     * 
     * @return array || \WP_Error
     */
    public function update_total_box($request_data) {

        //error_log('get_product_matrix');

        $parameters = $request_data->get_params();
        //error_log(print_r($parameters,true));
        $args = array();
        
        if( isset($parameters['product_id']) ) {
            $args['product'] = wc_get_product($parameters['product_id']);
        } else {
            return new \WP_Error( 
                'product_invalid', 
                esc_html__( 'No results', 'cg-woo-grid' ), 
                array( 'status' => 404 ) 
            );
        }

        $args['taxonomy_y'] = get_option($this->plugin_name.'_taxonomy_y_setting');
        $args['taxonomy_x'] = get_option($this->plugin_name.'_taxonomy_x_setting');

        //$args['taxonomy_y'] = 'pa_color';
        if( isset($parameters['taxonomy_y']) ) {
            $args['taxonomy_y'] = $parameters['taxonomy_y'];
        }

        //$args['taxonomy_x'] = 'pa_tamano';
        if( isset($parameters['taxonomy_x']) ) {
            $args['taxonomy_x'] = $parameters['taxonomy_x'];
        }

        $args['quantities'] = null;
        if( isset($parameters['quantities']) ) {
            $args['quantities'] = $parameters['quantities'];
        }

        return rest_ensure_response(
            $this->get_prices($args)
        );
        
    }


    /**
     * Get the matrix of attributes of one product defined in $args
     * 
     * @param array $args
     * 
     * @return array || \WP_Error
     */
    public function build_product_matrix($args) {

        //error_log('build_product_matrix ');
        //error_log(print_r($args,true));

        // OLD:
        //$args['taxonomy_y'] = 'pa_color';
        //$args['taxonomy_x'] = 'pa_tamano';

        if( !isset($args['taxonomy_y']) ) {
            $args['taxonomy_y'] = get_option($this->plugin_name.'_taxonomy_y_setting');
        }

        if(!isset($args['taxonomy_x']) ) {
            $args['taxonomy_x'] = get_option($this->plugin_name.'_taxonomy_x_setting');
        }

        if(!isset($args['stock']) ) {
            $args['stock'] = array(
                'minimum'           => get_option($this->plugin_name.'_minimum_stock'),
                'show'              => get_option($this->plugin_name.'_show_stock'),
                'message_threshold' => get_option($this->plugin_name.'_stock_threshold_message')
            );
        }
        
        extract(wp_parse_args(
            $args,
            array(
                'product'       => null,
                'taxonomy_y'    => null,
                'taxonomy_x'    => null,
                'stock'         => array(),
            )
        ));

        if(!$product instanceof \WC_Product) {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error( 
                'rest_api_sad', 
                esc_html__( 'Something went wrong', 'cg-woo-grid' ), 
                array( 'status' => 500 ) 
            );
        }

        



        $functions = new Functions( 
            $this->plugin_name, 
            $this->version
        );

        $stock_minimum  = $functions->cdg_filter_serialized_option(
            get_option($this->plugin_name.'_customised_stock'),
            $product->get_id(),
            array('profit' => true)
        );
        //error_log('global stock_minimum -> '.$stock['minimum']);
        //error_log('custom stock_minimum -> '.$stock_minimum);
        if($stock_minimum == -1) {
            $stock_minimum  = $stock['minimum'];
        }
        //error_log('stock_minimum -> '.$stock_minimum);

        $_children  = $product->get_children();
        $matrix     = array();
        //$variations = array();
        foreach ($_children as $value) {
            
            $single_variation = new \WC_Product_Variation($value);

            $varAttributes = $single_variation->get_attributes();
            $currentKey    = false;
            foreach ($matrix as $key => $attribute) {
                if ($attribute['y'] == $varAttributes[$taxonomy_y]) {
                    $currentKey = $key;
                }
            }
            if ($currentKey === false) {
                //$image_variation_url = wp_get_attachment_url($single_variation->get_image_id());
                $matrix[] = array(
                    'image'	=> $single_variation->get_image(),
                    'y'	    => $varAttributes[$taxonomy_y],
                    'x'	    => array()
                );
                end($matrix);
                $currentKey = key($matrix);
            }
            
            $valueX       = new \stdClass();
            $valueX->name = strtoupper($varAttributes[$taxonomy_x]);
            $valueX->slug = $single_variation->get_slug();

            $valueX->stock        = $single_variation->get_stock_quantity() - $stock_minimum;
            $valueX->showStock    = $stock['show'];
            $valueX->messageStock = str_replace("%",$valueX->stock,$stock['message_threshold']);

            $matrix[$currentKey]['x'][] = $valueX;
            //error_log(print_r($matrix[$currentKey], true));
        }

        return $matrix;
    }

    /**
     * Get the matrix of attributes of one product defined in $request_data
     * 
     * @param object $request_data
     * 
     * @return array || \WP_Error
     */
    public function get_product_matrix($request_data) {

        //error_log('get_product_matrix');

        $parameters = $request_data->get_params();
        //error_log(print_r($parameters,true));
        $args = array();
        
        if( isset($parameters['product_id']) ) {
            $args['product'] = wc_get_product($parameters['product_id']);
        } else {
            return new \WP_Error( 
                'product_invalid', 
                esc_html__( 'No results', 'cg-woo-grid' ), 
                array( 'status' => 404 ) 
            );
        }

        $args['taxonomy_y'] = get_option($this->plugin_name.'_taxonomy_y_setting');
        $args['taxonomy_x'] = get_option($this->plugin_name.'_taxonomy_x_setting');
        $args['stock']      = array(
            'minimum'           => get_option($this->plugin_name.'_minimum_stock'),
            'show'              => get_option($this->plugin_name.'_show_stock'),
            'message_threshold' => get_option($this->plugin_name.'_stock_threshold_message')
        );


        //$args['taxonomy_y'] = 'pa_color';
        if( isset($parameters['taxonomy_y']) ) {
            $args['taxonomy_y'] = $parameters['taxonomy_y'];
        }

        //$args['taxonomy_x'] = 'pa_tamano';
        if( isset($parameters['taxonomy_x']) ) {
            $args['taxonomy_x'] = $parameters['taxonomy_x'];
        }

        //error_log('get_product_matrix');
        //error_log(print_r($args,true));

        return rest_ensure_response(
            $this->build_product_matrix($args)
        );
        
    }



    /**
	 * Add custom routes to REST API
	 *
	 * @since    1.0.0
	 */
    function add_routes_api(){

        /*
        * Test: Hello World
        */
        register_rest_route(
            $this->restapinamespace, 
            'testing', 
            array(
                'methods'   => \WP_REST_Server::READABLE,
                'callback'  => array( $this, 'at_rest_testing_endpoint' ),
            )
        );

        /*
        * Total box
        */
        register_rest_route( 
            $this->restapinamespace, 
            'totalbox/product_id=(?P<product_id>\d+)', 
            array(
                // By using this constant we ensure that when the \WP_REST_Server changes our readable endpoints will work as intended.
                'methods'  => \WP_REST_Server::READABLE,
                'callback' => array( $this, 'build_total_box')
            )
        ); 
        //https://local.camisetas-serigrafia.wp.es/wp-json/cg-woo-grid/v1/totalbox/product_id=6016


        /*
        * Update Total box
        */
        register_rest_route( 
            $this->restapinamespace, 
            'updatetotalbox', 
            array(
                // By using this constant we ensure that when the \WP_REST_Server changes our readable endpoints will work as intended.
                'methods'  => \WP_REST_Server::CREATABLE,
                'callback' => array( 
                    $this, 
                    'update_total_box'
                )
            )
        ); 
        //https://local.camisetas-serigrafia.wp.es/wp-json/cg-woo-grid/v1/updatetotalbox

        /*
        * Attributtes matrix
        */
        register_rest_route( 
            $this->restapinamespace, 
            'attmatrix/product_id=(?P<product_id>\d+)/taxonomy_y=(?P<taxonomy_y>[\,a-zA-Z0-9-_]+)/taxonomy_x=(?P<taxonomy_x>[\,a-zA-Z0-9-_]+)', 
            array(
                // By using this constant we ensure that when the \WP_REST_Server changes our readable endpoints will work as intended.
                'methods'  => \WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_product_matrix')
            )
        ); 
        //https://local.camisetas-serigrafia.wp.es/wp-json/cg-woo-grid/v1/attmatrix/product_id=6016/taxonomy_y=pa_color/taxonomy_x=pa_tamano

    }
}
