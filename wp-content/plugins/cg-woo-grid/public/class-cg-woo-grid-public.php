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
class Cg_Woo_Grid_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**** Custom Plugin Properties ****/

    protected $css_filepath;
    protected $js_filepath;
    protected $restapinamespace;
    protected $vue_filepath;
    protected $vue_version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        //set the variable for the example endpoint. you can use a option plugin to store it and change it later in the admin page
        $this->css_filepath = CG_WOOGRID_URL.'public/css/cg-woo-grid.css';
        $this->js_filepath = CG_WOOGRID_URL.'public/js/cg-woo-grid.js';
        $this->restapinamespace = 'cg-woo-grid/v1';

        if(CG_WOOGRID_STATUS == 'development'){
            //https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.js
            //$this->vue_filepath = CG_WOOGRID_URL.'vue/js/vue.js';
            $this->vue_filepath = 'https://unpkg.com/vue@next';
        }
        if(CG_WOOGRID_STATUS == 'production'){
            //https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.prod.min.js
            $this->vue_filepath = CG_WOOGRID_URL.'vue/js/vue.global.prod.min.js';
        }
        $this->vue_version = '3.2.45';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

        wp_enqueue_style( 
            $this->plugin_name, 
            $this->css_filepath, 
            array(),
            $this->version, 
            'all' 
        );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

        //vue.js loads first
        wp_enqueue_script( 
            $this->plugin_name.'-vue',  
            $this->vue_filepath,
            array(), 
            $this->vue_version, 
            true
        );

        wp_enqueue_script( 
            $this->plugin_name, 
            $this->js_filepath, 
            array( 
                'jquery',
                 $this->plugin_name.'-vue'
            ),
            $this->version, 
            true 
        );

        
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
        return new WP_REST_Response('Howdy!!');
    }


    /**
     * Get the matrix of attributes of one product defined in $args
     * 
     * @param array $args
     * 
     * @return array || WP_Error
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

        if(!$product instanceof WC_Product) {
            // If the code somehow executes to here something bad happened return a 500.
            return new WP_Error( 
                'rest_api_sad', 
                esc_html__( 'Something went wrong.', 'codigo' ), 
                array( 'status' => 500 ) 
            );
        }

        $return     = new stdClass();
        $_children  = $product->get_children();
        $matrix     = array();
        $variations = array();
        foreach ($_children as $value) {
            $single_variation = new WC_Product_Variation($value);
            $variations[]     = $single_variation;
            //$varAttributes    = $single_variation->attributes;
            $varAttributes    = $single_variation->get_attributes();

            $currentKey = false;
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
            $matrix[$currentKey]['x'][] = $varAttributes[$taxonomy_x];
        }

        $return->variations = $variations;
        $return->matrix     = $matrix;

        return $return;

    }

    /**
     * Get the matrix of attributes of one product defined in $request_data
     * 
     * @param object $request_data
     * 
     * @return array || WP_Error
     */
    public function get_product_matrix($request_data) {

        //error_log('get_product_matrix');

        $parameters = $request_data->get_params();
        //error_log(print_r($parameters,true));
        $args = array();
        
        if( isset($parameters['product_id']) ) {
            $args['product'] = wc_get_product($parameters['product_id']);
        } else {
            return new WP_Error( 
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
	 * Build the products attributes grig
	 *
	 * @since    1.0.0
     * 
     * @return void || WP_Error
	 */
    function codigo_products_grid() {
        $args = array();
        
        if( null !== get_queried_object_id()) {
            $args['product'] = wc_get_product(get_queried_object_id());
        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new WP_Error( 
                'rest_api_sad', 
                esc_html__( 'Something went wrong.', 'codigo' ), 
                array( 'status' => 500 ) 
            );
        }

        $args['taxonomy_y'] = 'pa_color';
        $args['taxonomy_x'] = 'pa_tamano';

        if($res = $this->build_product_matrix($args)) {

            $minimum    = 0;
            $attributes = $res->matrix;
            $variations = $res->variations;
    
            include_once 'partials/class-cg-woo-grid-public-display.php'; 
        } 

        else {
            // Return a WP_Error because the request product was not found. In this case we return a 404 because the main resource was not found.
            return new WP_Error( 
                'product_invalid', 
                esc_html__( 'No results.', 'codigo' ), 
                array( 'status' => 404 ) 
            );
        }
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
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => array( $this, 'at_rest_testing_endpoint' ),
            )
        );

        /*
        * Attributtes matrix
        */
        register_rest_route( 
            $this->restapinamespace, 
            'attmatrix/product_id=(?P<product_id>\d+)/taxonomy_y=(?P<taxonomy_y>[\,a-zA-Z0-9-_]+)/taxonomy_x=(?P<taxonomy_x>[\,a-zA-Z0-9-_]+)', 
            array(
                // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_product_matrix')
            )
        ); 
        //https://local.camisetas-serigrafia.wp.es/wp-json/cg-woo-grid/v1/attmatrix/product_id=6016/taxonomy_y=pa_color/taxonomy_x=pa_tamano

    }
}
