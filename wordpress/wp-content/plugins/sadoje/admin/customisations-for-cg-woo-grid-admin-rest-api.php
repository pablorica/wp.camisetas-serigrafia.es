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
            //error_log(print_r($unserialized,true));
            //error_log(print_r(json_encode($unserialized),true));
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
     * Process uploaded image and save it in the WP directory
     *
     * @param object $file
     * @param integer $post_id
     * @param boot $set_as_featured
     * @return void
     */
    private function cdg_upload_image( 
        $file, 
        $post_id = 0 , 
        $set_as_featured = false 
    ) {
        $upload = wp_upload_bits( 
            $file['name'], 
            null, 
            file_get_contents( $file['tmp_name'] ) 
        );
    
        $wp_filetype = wp_check_filetype( 
            basename( $upload['file'] ), 
            null 
        );

        //TODO:  Rebuild this to force the images to be stored in https://local.camisetas-serigrafia.wp.es/wp-content/uploads/cg_uploads/YYYY/MM instead of default https://local.camisetas-serigrafia.wp.es/wp-content/uploads/YYYY/MM 
        /*
        * @see https://developer.wordpress.org/reference/functions/wp_upload_dir/
        * @see https://stackoverflow.com/questions/9679394/how-to-change-wordpress-upload-folder-programmatically
        * @see https://wordpress.org/support/topic/dynamic-change-of-the-upload-folder/
        */
    
    
        $upload_dir    = wp_upload_dir();
        $cg_upload_dir = $upload_dir['basedir'].'/cg_uploads/';
        $cg_upload_url = $upload_dir['baseurl'].'/cg_uploads/';
        $upload_dir['path'] = $upload_dir['basedir'].'/cg_uploads'.$upload_dir['subdir'];
        $upload_dir['url'] = $upload_dir['baseurl'].'/cg_uploads'.$upload_dir['subdir'];
        //error_log('cdg_process_image $upload_dir : '.print_r($upload_dir ,true));

        if ( ! file_exists( $cg_upload_dir ) ) {
	        wp_mkdir_p( $cg_upload_dir );
        }
        //error_log('cdg_process_image $cg_upload_dir: '.$cg_upload_dir);
        //error_log('cdg_process_image $cg_upload_url: '.$cg_upload_url);
    
        $attachment = array(
            'guid' => $cg_upload_url . _wp_relative_upload_path( $upload['file'] ),
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename( $upload['file'] )),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        //error_log('cdg_process_image attachment[guid]: '.$attachment['guid']);
        
        $attach_id = wp_insert_attachment( 
            $attachment, 
            $upload['file'], 
            $post_id 
        );
    
        require_once(ABSPATH . 'wp-admin/includes/image.php');
    
        $attach_data = wp_generate_attachment_metadata( 
            $attach_id, 
            $upload['file'] 
        );
        wp_update_attachment_metadata( 
            $attach_id, 
            $attach_data 
        );
    
        if( $set_as_featured == true ) {
            update_post_meta( $post_id, '_thumbnail_id', $attach_id );
        }

        return $attach_id;
    
    }

    /**
     * Submit images to the server
     *
     * @see https://stackoverflow.com/questions/35542640/using-php-for-file-upload-in-wordpress
     * @see https://wpmudev.com/blog/upload-file-functions/
     * 
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
     * @see https://gist.github.com/ahmadawais/0ccb8a32ea795ffac4adfae84797c19a
     * @see https://gist.github.com/mklasen/1aae73fa5650acd64476b2ed81a45da3
     * @see https://developer.wordpress.org/reference/classes/wp_rest_request/get_params/
     * 
     * @param WP_REST_Request $request_data
     * @return integer | WP_ERROR
     */
    public function cdg_process_image($request_data) {

            //error_log('cdg_process_image request_data: '.print_r($request_data,true));
            $parameters = $request_data->get_params();
            //error_log('cdg_process_image parameters: '.print_r($parameters,true));

            $files = $request_data->get_file_params();
            //error_log('cdg_process_image $files: '.print_r($files,true));

            if (str_contains($files['file']['type'], 'image')) { 
                $image = $files['file'];
                
                $post_data = array(
                    'post_title' => $parameters['title'],
                    'post_content' => $parameters['caption'],
                    'post_status' => 'draft'
                );
                
                //$post_id = wp_insert_post( $post_data );
                //$this->cdg_upload_image($image,$post_id,true);
                return $this->cdg_upload_image($image);        

            } else {
                return new \WP_Error( 
                    'process_image_invalid', 
                    esc_html__( 'Image cannot be processed', 'sadoje' ), 
                    array( 'status' => 404 ) 
                );
            }
        //wp_redirect( site_url() . '/thank-you/' );
        //die();
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
        * Upload image
        * https://local.camisetas-serigrafia.wp.es/wp-json/customisations-for-cg-woo-grid/v1/upload-image
        */
        register_rest_route( 
            $this->get_name_space(), 
            'upload-image', 
            array(
                // By using this constant we ensure that when the \WP_REST_Server changes our readable endpoints will work as intended.
                'methods'  => \WP_REST_Server::CREATABLE,
                'callback' => array( 
                    $this, 
                    'cdg_process_image'
                )
            )
        ); 
        

        /*
        * Encode serialized string to JSON format
        * https://local.camisetas-serigrafia.wp.es/wp-json/customisations-for-cg-woo-grid/v1/encode/
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

        /*
        * Serialize object
        * https://local.camisetas-serigrafia.wp.es/wp-json/customisations-for-cg-woo-grid/v1/serialize
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