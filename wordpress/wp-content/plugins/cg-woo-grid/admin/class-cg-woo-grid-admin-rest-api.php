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
namespace Cg_Woo_Grid_Admin;

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
        $this->plugin_name      = $plugin_name;
		$this->version          = $version;
        $this->restapinamespace = 'cg-woo-grid/v1';
	}

    /**
     * Returns the property $restapinamespace
     *
     * @since    1.0.0
     * @access   public
     * @return string
     */
    public function get_name_space()
    {
        return $this->restapinamespace;
    }

    /**
     *  Retrieves and sanitizes the URL to the admin REST endpoint.
     *
     * @since    1.0.0
     * @access   public
     * @return string
     */
    public function get_url_space()
    {
        return esc_url_raw( rest_url( $this->get_name_space() ) );
    }

    /**
     * Unserializes data (only if it was serialized) and 
     * ensures the response is a REST object (JSON).
     *
     * @param object $request_data
     * @return WP_REST_Response|WP_Error
     */
    function jsonencode_serialized_string($request_data) {

        $parameters = $request_data->get_params();
        //error_log(print_r($parameters,true));
        if($serialized = $parameters['serialized']) {
            $unserialized = maybe_unserialize($serialized);
            //$json = json_encode($unserialized);
            return rest_ensure_response(
                $unserialized
            );
        } else {
            return new \WP_Error( 
                'jsonencode_invalid', 
                esc_html__( 'No results', 'sadoje' ), 
                array( 'status' => 404 ) 
            );
        }
    }

    /**
     * Serializes data, if needed, and 
     * ensures the response is a REST response object (JSON).
     *
     * @param object $request_data
     * @return WP_REST_Response|WP_Error
     */
    function serialize_object($request_data) {

        $parameters = $request_data->get_params();
        //error_log(print_r($parameters,true));
        if($object = $parameters['object']) {
            $serialized = maybe_serialize($object);
            //error_log(print_r($serialized,true));
            return rest_ensure_response(
                $serialized
            );
        } else {
            return new \WP_Error( 
                'serialize_invalid', 
                esc_html__( 'No results', 'sadoje' ), 
                array( 'status' => 404 ) 
            );
        }
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
	 * Add custom routes to REST API
	 *
	 * @since    1.0.0
	 */
    function add_routes_api(){

        /*
        * Ecode serialized string to JSON format
        */
        register_rest_route( 
            $this->restapinamespace, 
            'encode', 
            array(
                // By using this constant we ensure that when the \WP_REST_Server changes our readable endpoints will work as intended.
                'methods'  => \WP_REST_Server::CREATABLE,
                'callback' => array( 
                    $this, 
                    'jsonencode_serialized_string'
                )
            )
        ); 
        //https://local.camisetas-serigrafia.wp.es/wp-json/cg-woo-grid/v1/encode/

        /*
        * Serialize object
        */
        register_rest_route( 
            $this->restapinamespace, 
            'serialize', 
            array(
                // By using this constant we ensure that when the \WP_REST_Server changes our readable endpoints will work as intended.
                'methods'  => \WP_REST_Server::CREATABLE,
                'callback' => array( 
                    $this, 
                    'serialize_object'
                )
            )
        ); 
        //https://local.camisetas-serigrafia.wp.es/wp-json/cg-woo-grid/v1/serialize

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


    }
}
