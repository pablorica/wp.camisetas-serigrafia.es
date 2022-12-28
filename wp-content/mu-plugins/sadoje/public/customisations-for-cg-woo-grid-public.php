<?php

/**
 *
 * This class contains hooks to manipulate the plugin Codigo Woo Grid
 *
 */


 namespace Sadoje;


class Customisations_For_Cg_Woo_Grid_Public extends Customisations_For_Cg_Woo_Grid {

    /** ** ** ** ** ** **
     ** ** FRONTEND ** **
     ** ** ** ** ** ** **/

     /**
	 * Hello World (for testing purposes)
	 *
	 * @access public
	 * @return void
	 */
	public function hello_world() {
        error_log('Public Hello World!');
        //echo "<h1>Hello World!</h1>";
        error_log('Plugin URL: '.$this->plugin_url);
        error_log('Plugin PATH: '.$this->plugin_path);
        error_log('Element Name: '.$this->get_element_name());
	}


    /**
	 * Build the budgeteer
	 *
	 * @since    1.0.0
     * 
     * @return void || WP_Error
	 */
    function cdg_budgeteer() {

        //error_log('cdg_budgeteer -> Element Name: '.$this->get_element_name());

        $args = array();
        
        if( null !== get_queried_object_id()) {

            $elementName = $this->get_element_name();
            
            require_once $this->plugin_path. 'admin/'.$elementName.'-admin-rest-api.php';
            $plugin_admin_restapi = new Customisations_For_Cg_Woo_Grid_Admin_Rest_Api();
            $urlspace = $plugin_admin_restapi->get_url_space();

            require_once $this->plugin_path.'public/display/'.$elementName.'-display.php'; 

        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error( 
                $this->get_element_name().'_error', 
                esc_html__( 'Something went wrong', "cg-woo-grid" ), 
                array( 'status' => 500 ) 
            );
        }
    }

    /**
     * Applies the budgeteer configuration to the regular price of each product
     *
     * @param  string $regular_price
     * @return string
     */
    function cdg_alter_regular_price( $regular_price) {
        //error_log('cdg_alter_regular_price: (or) regular price -> '.$regular_price);
        //error_log('cdg_alter_regular_price: cookie -> '.$_COOKIE['addsRegPrice']);
        if(isset($_COOKIE['addsRegPrice'])) {
            $regular_price = $regular_price + (float) $_COOKIE['addsRegPrice'];
        }
        //error_log('cdg_alter_regular_price: regular price -> '.$regular_price);
        
        return $regular_price;
    }
}