<?php

/**
 *
 * This class contains hooks to manipulate the plugin Codigo Woo Grid
 *
 */


 namespace Sadoje;


class Customisations_For_Cg_Woo_Grid extends Sadoje_Singleton {


    /**** Plugin properties ****/

    protected $element_name;

    /**** Plugin Initialize ****/


    //The init method is defined by the singleton abstract and it starts the execution of the plugin. Usually, calls all the following methods needed.
    function init() {
        $this->element_name = 'customisations-for-cg-woo-grid';
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
    }

    private function add_filters()
    {


    }
	


	/**
	 * Frontend_scripts function. 
	 * Hello World (for testing purposes)
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
            include_once $this->plugin_path.'public/'.$this->element_name.'-public-display.php'; 
        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error( 
                $this->element_name.'_error', 
                esc_html__( 'Something went wrong', "cg-woo-grid" ), 
                array( 'status' => 500 ) 
            );
        }

    }

}