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

        // TODO: Check if cg-woo-grid is enabled before init()!!!!!
         
        $this->get_element_name();
        //$this->add_actions();
        //$this->add_filters();
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
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
    }

    private function add_filters()
    {
        //

    }

    /**
     * Sets and returns the property $element_name
     *
     * @since    1.0.0
     * @access   public
     * @return string
     */
    public function get_element_name()
    {
        $this->element_name = 'customisations-for-cg-woo-grid';
        //error_log('Customisations_For_Cg_Woo_Grid::get_element_name() -> '.$this->element_name);
        return $this->element_name;

    }

     /**
	 * Load the required dependencies for this add-on.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {


		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $this->plugin_path. 'admin/'.$this->element_name.'-admin.php';
        require_once $this->plugin_path. 'admin/'.$this->element_name.'-admin-rest-api.php';
        //error_log($this->plugin_path. 'admin/customisations-for-cg-woo-grid-admin.php');

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
        require_once $this->plugin_path. 'public/'.$this->element_name.'-public.php';

	}

    /**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Customisations_For_Cg_Woo_Grid_Admin();

        add_action(
            'admin_menu',
            array( 
                $plugin_admin, 
                'cdg_add_admin_submenu' 
            ),
            10
        );  

        add_action(
            'admin_init',
            array( 
                $plugin_admin, 
                'registerAndBuildBudgeteer' 
            ),
            20
        ); 


        // add_action( 
        //     'wp_loaded', 
        //     array( 
        //         $plugin_admin, 
        //         'hello_world' 
        //     )
        // );

        $plugin_admin_restapi = new Customisations_For_Cg_Woo_Grid_Admin_Rest_Api();

        add_action(
            'rest_api_init',
            array( 
                $plugin_admin_restapi, 
                'add_routes_api' 
            ),
            20
        );

	}

    /**
	 * Register all of the hooks related to the frontend
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Customisations_For_Cg_Woo_Grid_Public();
        /**
         * Hook: woocommerce_after_single_product_summary.
         *
         * @hooked woocommerce_output_product_data_tabs - 10
         * @hooked woocommerce_upsell_display - 15
         * @hooked cdg_products_grid - 16
         * @hooked woocommerce_output_related_products - 20
         */
        add_action( 
            'woocommerce_after_single_product_summary', 
            array( 
                $plugin_public, 
                'cdg_budgeteer' 
            ),
            17
        );

        /**
         * Hook: woocommerce_share
         *
         * @hooked none
         */
        add_action( 
            'woocommerce_share', 
            array( 
                $plugin_public, 
                'cdg_pricetable' 
            ),
            17
        );

        

        // add_action( 
        //     'wp_loaded', 
        //     array( 
        //         $plugin_public, 
        //         'hello_world' 
        //     )
        // );

        add_filter( 
            'cg-woo-grid_get_regular_price', 
            array( 
                $plugin_public, 
                'cdg_alter_regular_price' 
            ),
            100,
            3
        );
        // add_filter( 
        //     'cg-woo-grid_get_total_price', 
        //     array( 
        //         $plugin_public, 
        //         'cdg_alter_total_price' 
        //     ),
        //     100,
        //     2
        // );
        add_action( 
            'woocommerce_before_calculate_totals',
            array( 
                $plugin_public, 
                'cdg_alter_price_cart' 
            ),
            100
        );
    }
}