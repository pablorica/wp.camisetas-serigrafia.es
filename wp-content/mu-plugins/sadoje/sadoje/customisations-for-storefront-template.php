<?php

/**
 *
 * This class contains hooks to manipulate the Storefornt Template
 *
 */


 namespace Sadoje;


class Customisations_For_Storefront_Template extends Sadoje_Singleton {


    /**** Plugin properties ****/

    protected $element_name;

    /**** Plugin Initialize ****/


    //The init method is defined by the singleton abstract and it starts the execution of the plugin. Usually, calls all the following methods needed.
    function init() {
        $this->element_name = 'customisations-for-storefront-template';
        $this->add_actions();
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
            'get_header', 
            array( 
                $this, 
                'cdg_remove_storefront_sidebar' 
            ),
            18
        );
    }


    /**
	 * Frontend_scripts function. 
	 * Hello World (for testing purposes)
	 *
	 * @access public
	 * @return void
	 */
	public function hello_world() {
        error_log('Storefront_Template : Hello World!');
        echo "<h1>Storefront_Template : Hello World!</h1>";
        error_log($this->plugin_url.' '.$this->plugin_path);
	}

    /**
     * Remove Storefront Sidebar
     *
     * @return void
     */
    function cdg_remove_storefront_sidebar() {
        /*
        if ( is_woocommerce() 
            || is_checkout() 
        ) {
            remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
        }
        */
        remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
    }


    /**
     * This snippet tells WooCommerce to allow 300 variations for a product before it stops 
     * embedding the JavaScript that allows the web browser to dynamically enable and 
     * disable the different dropdown lists. 
     * https://techprimeweb.com/modify-the-ajax-variation-threshold/
     *
     * @param int $qty
     * @param int $product
     * @return void
    */
    function codigo_wc_ajax_variation_threshold( $qty, $product ) {
        return 300;
    }
    //add_filter( 'woocommerce_ajax_variation_threshold', 'codigo_wc_ajax_variation_threshold', 10, 2 );

}