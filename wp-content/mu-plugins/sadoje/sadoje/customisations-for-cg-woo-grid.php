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

        //TODO: Check if cg-woo-grid is enabled before init()!!!!!
         
        $this->element_name = 'customisations-for-cg-woo-grid';
        $this->add_actions();
      //$this->add_filters();
    }

    /**** Plugin methods ****/

    private function add_actions()
    {

        //Backend actions

        add_action(
            'admin_menu',
            array( 
                $this, 
                'cdg_add_admin_submenu' 
            ),
            10
        );  


        // add_action( 
        //     'wp_loaded', 
        //     array( 
        //         $this, 
        //         'hello_world' 
        //     )
        // );


        //Frontend actions

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
	

    /** ** ** ** ** ** **
     ** ** BACKEND  ** **
     ** ** ** ** ** ** **/

    /**
     * Adds submenu to administration area
     *
     * @see https://blog.wplauncher.com/create-wordpress-plugin-settings-page/
     * 
     * @return void
     */
    public function cdg_add_admin_submenu() {

        require_once WP_PLUGIN_DIR . '/cg-woo-grid/includes/class-cg-woo-grid.php';
        $cgWooGrid = new \Cg_Woo_Grid();
        require_once WP_PLUGIN_DIR . '/cg-woo-grid/admin/class-cg-woo-grid-admin.php';
        $cgWooGridAdmin = new \Cg_Woo_Grid_Admin( 
            $cgWooGrid->get_plugin_name(), 
            $cgWooGrid->get_version() 
        );
        $cgWooGridAdmin->sayHello('caracola');


        //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
        add_submenu_page( 
            'cg-woo-grid', 
            esc_html__('Presupuestador', 'sadoje'), 
            esc_html__('Presupuestador', 'sadoje'), 
            'administrator', 
            $this->element_name.'-presupuestador', 
            array( 
                $this, 
                'cdg_display_admin_presupuestador' 
            )
        );
    }
    /**
     * Build the admin page
     *
     * @return void
     */
    public function cdg_display_admin_presupuestador() {
        // set this var to be used in the display view
        //$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		require_once $this->plugin_path.'admin/'.$this->element_name.'-display.php'; 
    }








    /** ** ** ** ** ** **
     ** ** FRONTEND ** **
     ** ** ** ** ** ** **/

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
            include_once $this->plugin_path.'public/'.$this->element_name.'-display.php'; 
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