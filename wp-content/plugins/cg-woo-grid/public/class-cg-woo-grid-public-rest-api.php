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


    /**** Custom Plugin Properties ****/

    protected $restapinamespace;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {

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
     * @return array || \WP_Error
     */
    public function build_total_box($request_data) {

        global $post;
        $parameters = $request_data->get_params();
        //error_log('l.158 - build_total_box: '.print_r($parameters,true));
        $return = new \stdClass();

        $return->product_id = null;
        if( isset($parameters['product_id']) ) {
            $return->product_id = $parameters['product_id'];
        } elseif( isset($post->ID) ) {
            $return->product_id = $post->ID;
        } elseif(get_queried_object_id()) {
            $return->product_id = get_queried_object_id();
        }
        
        if(!$return->product_id) {
            return new \WP_Error( 
                'product_invalid', 
                esc_html__( 'No results.', 'codigo' ), 
                array( 'status' => 404 ) 
            );
        }


        $return->boxtitle        = __("Tu presupuesto","cg-woo-grid");
        $return->unitpricetitle  = __("Precio unitario","cg-woo-grid");
        $return->pricetitle      = __("Precio","cg-woo-grid");
        $return->unitstitle      = __("Unidades","cg-woo-grid");
        $return->amountitle      = __("Importe","cg-woo-grid");
        $return->subtotaltitle   = __("Subotal","cg-woo-grid");
        $return->vattitle        = __("IVA","cg-woo-grid");
        $return->totaltitle      = __("Total","cg-woo-grid");
        $return->addtocarttitle  = __("Añadir a la cesta","cg-woo-grid");

        $return->zeroprice       = strip_tags(wc_price(0));
        $return->formatzeroprice = wc_price(0);

        $return->taxonomy_y = 'pa_color';
        $return->taxonomy_y_name = 'Color';
        $return->taxonomy_x = 'pa_tamano';
        $return->taxonomy_x_name = 'Talla';

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
                esc_html__( 'Something went wrong.', 'codigo' ), 
                array( 'status' => 500 ) 
            );
        }

        $colores = array();
        $description = '';

        $numberofprods = 0;
        $price = 0;
        $rowPrices = array();


        if($_product->is_type('variable')){

            $variations=$_product->get_children();
            foreach ($variations as $value) {
                $single_variation = new \WC_Product_Variation($value);
                /*
                echo '<option  value="'.$value.'">'.implode(" / ", $single_variation->get_variation_attributes()).'-'.get_woocommerce_currency_symbol().$single_variation->price.'</option>';
                */
                $variation_slug = $single_variation->get_slug();
                $variation_id   = $single_variation->get_id();

                if (isset($args['quantities'][$variation_slug])) {

                    //error_log('l.241 -> get_prices() - product:  '.$variation_slug);

                    $quantity   = (int) $args['quantities'][$variation_slug];
                    $rowPrice   = array();
                    $nk    		= false;
                    $attr 		= $single_variation->get_variation_attributes();

                    foreach ($colores as $k => $vc) {
                        if ($vc['color'] == $vc['pa_color']) $nk = $k;
                    }
                    if ($nk === false) {
                        $colores[] = array(
                            'color'	=> $attr['attribute_pa_color'],
                            'sizes'	=> array()
                        );
                        end($colores);
                        $nk = key($colores);
                    }
                    $colores[$nk]['sizes'][] = array($attr['attribute_pa_tamano'], $quantity);
                    //$attr['attribute_pa_color'];
                    $miprecio = (float) $single_variation->get_price();
                    $numberofprods += $quantity;

                    $uprice = (float) $single_variation->get_regular_price();

                    $price  += $quantity * $uprice;

                    
                    $rowPrice["tp_uprecio_base"] = $uprice;
                    $rowPrice["tp_color"]        = $attr['attribute_pa_color'];
                    $rowPrice["tp_tamano"]       = $attr['attribute_pa_tamano'];
                    $rowPrice["tp_cant"]         = $quantity;	
                    $rowPrice["tp_addurl"]       = $variation_id.':'.$quantity.',';				

                    //error_log(print_r($rowPrices, true));
                    $rowPrices[] = $rowPrice;
                }
            }


        }

        $description .= $numberofprods . ' productos. ';
        foreach ($colores as $k => $v) {
            $description .= ($k != 0 ? ' - ' : '') . ucfirst($v['color']) . ': ';
            foreach ($v['sizes'] as $k2 => $v2) {
                $description .= ($k2 != 0 ? ', ' : '') . strtoupper($v2[0]) . ' (' . $v2[1] . ')';
            }
        }


        $html = '';
        $addurl = '';
        foreach($rowPrices as $rowPrice) {

            $nuprecio = $rowPrice["tp_uprecio_base"];
            $nqprecio = $nuprecio * $rowPrice["tp_cant"];

            $html .= '
            <tr>
                <td class="tp_color">'.ucfirst($rowPrice["tp_color"]).'</td>
                <td class="tp_tamano">'.strtoupper($rowPrice["tp_tamano"]).'</td>
                <td class="tp_uprecio">'.wc_price($nuprecio).'</td>
                <td class="tp_cant">'.$rowPrice["tp_cant"].'</td>
                <td class="text-right tp_subtotal">'.wc_price($nqprecio).'</td>
            </tr>';

            $addurl .= $rowPrice["tp_addurl"];
            
        }
        $addurl = rtrim($addurl, ',');


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

        /*
        $returnb = 
        [0] $numberofprods . '---' . 
        [1] strip_tags(html_entity_decode(wc_price($unitPriceTax))) . '---' . 
        [2] wc_price($price) . '---' . 
        [3] wc_price($priceTax) . '---' . 
        [4] $price . '---' . 
        [5] $description .  '---'  .
        [6] html. '---' .
        [7] wc_price($taxes). '---' .
        [8] strip_tags(html_entity_decode(wc_price($unitPrice)));
        */

        return $return;

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
                esc_html__( 'No results.', 'codigo' ), 
                array( 'status' => 404 ) 
            );
        }

        $args['taxonomy_y'] = 'pa_color';
        if( isset($parameters['taxonomy_y']) ) {
            $args['taxonomy_y'] = $parameters['taxonomy_y'];
        }

        $args['taxonomy_x'] = 'pa_tamano';
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

        extract(wp_parse_args(
            $args,
            array(
                'product' => null,
                'taxonomy_y' => null,
                'taxonomy_x' => null,
            )
        ));

        if(!$product instanceof \WC_Product) {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error( 
                'rest_api_sad', 
                esc_html__( 'Something went wrong.', 'codigo' ), 
                array( 'status' => 500 ) 
            );
        }

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
            
            $valueX = new \stdClass();
            $valueX->name  = strtoupper($varAttributes[$taxonomy_x]);
            $valueX->slug  = $single_variation->get_slug();
            $valueX->stock = $single_variation->get_stock_quantity();

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
                esc_html__( 'No results.', 'codigo' ), 
                array( 'status' => 404 ) 
            );
        }

        $args['taxonomy_y'] = 'pa_color';
        if( isset($parameters['taxonomy_y']) ) {
            $args['taxonomy_y'] = $parameters['taxonomy_y'];
        }

        $args['taxonomy_x'] = 'pa_tamano';
        if( isset($parameters['taxonomy_x']) ) {
            $args['taxonomy_x'] = $parameters['taxonomy_x'];
        }

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
                'callback' => array( $this, 'update_total_box')
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
