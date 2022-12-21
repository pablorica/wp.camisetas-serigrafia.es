<?php

/**
 *
 * This class contains hooks to manipulate the plugin Codigo Woo Grid
 *
 */


 namespace Sadoje;


class Customisations_For_Cg_Woo_Grid extends Sadoje_Singleton {

    /**** Plugin properties ****/

    protected $plugin_name;
    protected $version;


    /**** Custom Plugin Properties ****/

    protected $plugin_url;
    protected $plugin_path;


    /**** Plugin Initialize ****/


    //The init method is defined by the singleton abstract and it starts the execution of the plugin. Usually, calls all the following methods needed.
    function init() {

        //Initialize all the basics components of the plugin
        $this->plugin_name = 'customisations-for-cg-woo-grid';
        $this->version     = '1.0.0';

        //set the variable for the example endpoint. you can use a option plugin to store it and change it later in the admin page
        $this->plugin_url  = plugin_dir_url( __FILE__ ).$this->plugin_name;
        $this->plugin_path = dirname(__FILE__).$this->plugin_name;
		
        $this->add_actions();
      //$this->add_filters();
    }

    /**** Plugin methods ****/

    private function add_actions()
    {

        // add_action( 
        //     'wp_loaded', 
        //     array( 
        //         $this, 
        //         'hello_world' 
        //     )
        // );

        add_action( 
            'woocommerce_after_single_product_summary', 
            array( 
                $this, 
                'cdg_budgeteer' 
            ),
            18
        );

        // add_action( 
        //     'wp_enqueue_scripts', 
        //     array($this,'themeslug_enqueue_style')
        // );
        // add_action( 
        //     'wp_enqueue_scripts', 
        //     array($this,'themeslug_enqueue_script')
        // );
    }

    private function add_filters()
    {


    }

    /**** Hooks methods ****/

    function themeslug_enqueue_style() {
    	wp_enqueue_style( 
            $this->plugin_name, 
            $this->plugin_url.'/assets/style.css', 
            false 
        );
    }

    //enqueue a script
    function themeslug_enqueue_script() {
    	wp_enqueue_script( 
            $this->plugin_name, 
            $this->plugin_url.'/assets/script.js', 
            false 
        );
    }
	


	/**
	 * Frontend_scripts function. 
	 * Updates the one defined in plugins/wp-job-manager-bookmarks/wp-job-manager-bookmarks.php
	 *
	 * @access public
	 * @return void
	 */
	public function hello_world() {
        error_log('Hello World!');
        echo "<h1>Hello World!</h1>";
        error_log($this->plugin_url.' '.$this->plugin_path);
	}


    /**
	 * Build the products attributes grid
	 *
	 * @since    1.0.0
     * 
     * @return void || WP_Error
	 */
    function cdg_budgeteer() {

        $args = array();
        
        if( null !== get_queried_object_id()) {
            //$args['product'] = wc_get_product(get_queried_object_id());
            include_once $this->plugin_name.'/public-display.php'; 
        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error( 
                $this->plugin_name.'_error', 
                esc_html__( 'Something went wrong', "cg-woo-grid" ), 
                array( 'status' => 500 ) 
            );
        }

    }

}