<?php

/**
 *
 * This class contains hooks to manipulate the plugin Codigo Woo Grid
 *
 */


 namespace Sadoje;


class Customisations_For_Cg_Woo_Grid_Admin_Rest_Api extends Customisations_For_Cg_Woo_Grid_Admin {

    /**** Plugin properties ****/

    protected $restapinamespace;
    
    /**
     * Sets and returns the property $restapinamespace
     *
     * @since    1.0.0
     * @access   public
     * @return string
     */
    public function get_name_space()
    {
        $this->restapinamespace = $this->get_element_name().'/v1';
        return $this->restapinamespace;

    }

    public function get_url_space()
    {
        return esc_url_raw( rest_url( $this->get_name_space() ) );
    }

    

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

    function serialize_object($request_data) {

        $parameters = $request_data->get_params();
        //error_log(print_r($parameters,true));
        if($object = $parameters['object']) {
            $serialized = maybe_serialize($object);
            //$json = json_encode($unserialized);
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
            $this->get_name_space(), 
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
        //https://local.camisetas-serigrafia.wp.es/wp-json/customisations-for-cg-woo-grid/v1/encode/

        /*
        * Serialize object
        */
        register_rest_route( 
            $this->get_name_space(), 
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
        //https://local.camisetas-serigrafia.wp.es/wp-json/customisations-for-cg-woo-grid/v1/serialize



        /*
        * Test: Hello World
        */
        register_rest_route(
            $this->get_name_space(), 
            'testing', 
            array(
                'methods'   => \WP_REST_Server::READABLE,
                'callback'  => array( 
                    $this, 
                    'at_rest_testing_endpoint' 
                ),
            )
        );
        //customisations-for-cg-woo-grid/v1/testing
        //https://local.camisetas-serigrafia.wp.es/wp-json/customisations-for-cg-woo-grid/v1/testing

    }

}